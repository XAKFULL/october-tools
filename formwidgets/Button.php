<?php namespace Xakfull\Tools\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Flash;
use Backend\Facades\Backend;

class Button extends FormWidgetBase
{
    protected $defaultAlias = 'button';

    public function init(){}

    public function render() {
        $this->prepareVars();
        return $this->makePartial('button');
    }

    public function prepareVars()
    {
        $this->vars['function'] = $this->config->function;
        $this->vars['text'] = $this->config->text;
        $this->vars['controller'] = $this->config->controller;
        $this->vars['redirect'] = (isset($this->config->redirect)) ? Backend::url($this->config->redirect): NULL;
    }


    public function onActivate(){
        $controller = post('controller');
        $controller = str_replace('/','\\',$controller);
        $function = post('function');
        $controller = new $controller;
        $result = $controller->$function(post());
        Flash::success($result);
        return;
    }
}
