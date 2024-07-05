<?php namespace Xakfull\Tools\Classes;

use Backend\Classes\Controller;
use Backend\Classes\FilterScope;
use Backend\Widgets\Filter;
use Backend\Widgets\Form;
use Backend\Widgets\Lists;
use Event;
use October\Rain\Database\Model;
use Yaml;

/**
 * @package Xakfull/Tools
 * @author Xakfull
 */
abstract class Extender {

    /**
     * @var string
     */
    public $extenderPath;

    /**
     * @var string
     */
    public $formFields = '';
    /**
     * @var string
     */
    public $listColumns = '';
    /**
     * @var string
     */
    public $listScopes = '';
    /**
     * @var string
     */
    public $relationConfig = '';

    /**
     * @var Model
     */
    public $model;
    /**
     * @var Controller
     */
    public $controller;

    /**
     * @var array
     */
    public $fillable = [];
    /**
     * @var array
     */
    public $rules = [];

    /**
     * @var array
     */
    public $jsonable = [];

    /**
     * @var array
     */
    public $implement = [];


    /**
     * @var array
     */
    public $hasMany = [];
    /**
     * @var array
     */
    public $hasManyThrough = [];
    /**
     * @var array
     */
    public $hasOne = [];
    /**
     * @var array
     */
    public $belongsTo = [];
    /**
     * @var array
     */
    public $belongsToMany = [];

    /**
     * @var array
     */
    public $attachOne = [];

    /**
     * @var array
     */
    public $attachMany = [];


    /**
     * @return void
     */
    function __construct($extenderPath, $model=null, $controller=null){

        if (!empty($model))
            $this->model = $model;

        if (!empty($controller))
            $this->controller = $controller;

        $this->extenderPath = $extenderPath;
        $this->__prepareFormFields();
        $this->__prepareListColumns();
        $this->__prepareListScopes();
        $this->__prepareRelationConfig();
        $this->extendController();
        $this->extendModel();
        $this->extendForm();
        $this->extendList();
        $this->extendFormFieldsByEvent();
        $this->extendListColumnsByEvent();
        $this->extendListFilterScopes();
    }

    /**
     * @return void
     */
    protected function __prepareFormFields(){
        $this->formFields = $this->checkFile($this->formFields, 'fields.yaml');
    }

    /**
     * @return void
     */
    protected function __prepareListColumns(){
        $this->listColumns = $this->checkFile($this->listColumns, 'columns.yaml');
    }

    /**
     * @return void
     */
    protected function __prepareListScopes(){
        $this->listScopes = $this->checkFile($this->listScopes, 'list_scopes.yaml');
    }

    /**
     * @return void
     */
    protected function __prepareRelationConfig(){
        $this->relationConfig = $this->checkFile($this->relationConfig, 'relation_config.yaml');
    }

    /**
     * @param string $class
     * @param string $dir
     * @return string
     */
    static function getFilesPath(string $class, string $dir){
        return $dir.'/'.mb_strtolower(class_basename($class));
    }

    /**
     * @param string $path
     * @param string $reserve
     * @return string
     */
    function checkFile($path, $reserve) : string {
        $path = empty($path) ?
            $this->extenderPath."/$reserve" :
            plugins_path($path);

        if (!file_exists($path))
            $path = '';

        return $path;
    }

    /**
     * @return void
     */
    public function extendModel(){
        if(!empty($this->model)) {
            $this->model::extend(function (Model $model) {

                $arAttributes = [
                    'hasOne',
                    'hasMany',
                    'hasManyThrough',
                    'belongsTo',
                    'belongsToMany',
                    'implement',
                    'rules',
                    'attachOne',
                    'attachMany',
                ];

                foreach ($arAttributes as $arAttribute){
                    if (!empty($this->$arAttribute))
                        $model->$arAttribute = array_merge($model->$arAttribute ?? [], $this->$arAttribute);
                }

                if (!empty($this->fillable))
                    $model->addFillable($this->fillable);

                if (!empty($this->jsonable))
                    $model->addJsonable($this->jsonable);

                $this->customExtendModel($model);

            });
        }
    }

    /**
     * @return void
     */
    public function extendController(){
        if(!empty($this->controller)) {
            $this->controller::extend(function (Controller $controller) {

                if (!empty($this->relationConfig)) {

                    if (
                        !in_array('Backend\Behaviors\RelationController', $controller->implement)
                        and !in_array('Backend.Behaviors.RelationController', $controller->implement)
                    )
                        $controller->implement[] = 'Backend\Behaviors\RelationController';

                    if (!isset($controller->relationConfig)) {
                        $controller->addDynamicProperty('relationConfig');
                        $controller->relationConfig = $this->relationConfig;
                    } else
                        $controller->relationConfig = (object)array_merge(
                            (array)$controller->makeConfig($this->relationConfig),
                            (array)$controller->makeConfig($controller->relationConfig)
                        );

                }

                $this->customExtendController($controller);
            });
        }
    }

    /**
     * @return void
     */
    public function extendList(){
        if(!empty($this->controller)) {
            $this->controller::extendListColumns(function (Lists $list, Model $model) {
                if (!$model instanceof $this->model) {
                    return;
                }

                if (!empty($this->listColumns)) {
                    $list->addColumns(array_get(Yaml::parseFile($this->listColumns), 'columns', []));
                }

                $this->customExtendList($list, $model);
            });
        }
    }

    /**
     * @return void
     */
    public function extendListFilterScopes(){
        if(!empty($this->controller) and !empty($this->listScopes)){
            $this->controller::extendListFilterScopes(function(Filter $filter) {

                $extend = true;

                if (!empty($this->model) and !($filter->model instanceof $this->model))
                    $extend = false;

                if ($extend) {
                    $filter->addScopes(array_get(Yaml::parseFile($this->listScopes), 'scopes', []));
                    $filter->addCss(array_get(Yaml::parseFile($this->listScopes), 'css', []));
                }
            });
        }
    }

    /**
     * @return void
     */
    public function extendForm(){
        if(!empty($this->controller)) {
            $this->controller::extendFormFields(function (Form $form, Model $model, ?string $context) {
                if (!$model instanceof $this->model or $form->isNested) {
                    return;
                };

                if (!empty($this->formFields)) {

                    $fields = Yaml::parseFile($this->formFields);

                    $form->addFields(array_get($fields, 'fields', []));
                    $form->addTabFields(array_get(array_get($fields, 'tabs', []), 'fields', []));
                    $form->addSecondaryTabFields(array_get(array_get($fields, 'secondary_tabs'), 'fields', []));

                }

                $this->customExtendForm($form, $model, $context);
            });
        }
    }

    /**
     * @return void
     */
    public function extendFormFieldsByEvent(){
        if(!empty($this->model)){
            Event::listen('backend.form.extendFields', function ($form){
                if (!$form->isNested and $form->model instanceof $this->model) {
                    $this->customExtendFormFieldsByEvent($form);
                }
            });
        }
    }

    /**
     * @return void
     */
    public function extendListColumnsByEvent(){
        if(!empty($this->model)) {
            Event::listen('backend.list.extendColumns', function ($list) {
                if (!$list->isNested and $list->model instanceof $this->model) {
                    $this->customExtendListColumnsByEvent($list);
                }
            });
        }
    }

    /**
     * @param Model $model
     * @return void
     */
    public function customExtendModel(Model $model){}

    /**
     * @param Controller $controller
     * @return void
     */
    public function customExtendController(Controller $controller){}

    /**
     * @param Lists $list
     * @param Model $model
     * @return void
     */
    public function customExtendList(Lists $list, Model $model){}

    /**
     * @param Form $form
     * @param Model $model
     * @param string $context
     * @return void
     */
    public function customExtendForm(Form $form, Model $model, ?string $context){}

    /**
     * @param Form $form
     * @return void
     */
    public function customExtendFormFieldsByEvent(Form $form){}

    /**
     * @param Lists $list
     * @return void
     */
    public function customExtendListColumnsByEvent(Lists $list){}
}
