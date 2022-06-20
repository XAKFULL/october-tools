<?php namespace Xakfull\Tools\Classes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use October\Rain\Exception\ApplicationException;
use Xakfull\Domovedov\Models\MainSettings;
use Xakfull\Moveit\Models\Settings;
use Tymon\JWTAuth\Facades\JWTAuth;
use Xakfull\Shop\Models\OneSSettings;

/**
 * Class ResponseGenerator
 * @package Xakfull\Tools\Classes
 */
abstract class RestGenerator
{
    const UNAUTORIZED = 'client_not_found';
    const NOT_FOUND = 'url_not_found';

    /**
     *
     * @var bool check JWT auth and get 401 on false
     */
    protected $checkAuth = true;
    /**
     * @var bool decode request content and pu in class->$data
     */
    protected $unwrapContent = false;
    /**
     * @var array by default disabled if != [] activ
     */
    protected $validateRules = [];
    /**
     * @var array messages on validation error
     */
    protected $validateMessages = [];
    /**
     * @var string
     */
    protected $method = 'GET';
    /**
     * @var bool
     */
    protected $returnUser = false;

    /**
     * @var array data from request->content
     */
    protected $data = [];
    /**
     * @var array will returned inside response
     */
    protected $result = [];

    private $errors = [];
    private $code = 401;

    public function apply(){
        try {
            ini_set('memory_limit', '500M');
            // apply params and validate data
            $this->prepare();

            if(OneSSettings::get('save_requests'))
                $this->saveToFile($this->data);

            // do custom methods
            if (!$this->check())
                $this->error('check_was_aborted', 422);

//            $this->saveToFile('requestData/');

            if (!$this->do())
                $this->error('do_was_aborted', 500);

            $this->result = array_merge(
                $this->result,
                $this->return()
            );

            // return
            return $this->success();

        } catch (\Exception $exception){
            trace_log($exception);
            $this->saveToFile($this->data, null, true);
            $this->error($exception->getMessage(), 500);
        }
    }

    // support methods
    private function prepare(){
        if (request()->method() !== $this->method)
            $this->error(self::NOT_FOUND, 404);

        if ($this->checkAuth)
            if (!$this->user = JWTAuth::toUser(request()->bearerToken()))
                $this->error(self::UNAUTORIZED);
            else
                $this->user->touchLastSeen();

        if ($this->unwrapContent)
            $this->data = json_decode(request()->getContent(), true);
        if (is_null($this->data))
            $this->data = [];

        if ($this->validateRules != []) {

            if (!$this->unwrapContent)
                $this->error('data_accept_required_for_validation', 500);

            $validator = Validator::make($this->data, $this->validateRules, $this->validateMessages);

            if ($validator->fails())
                $this->error($validator->errors(), 422);
        }

        if ($this->returnUser)
            if ($this->checkAuth)
                $this->result['user'] = $this->user;
            else
                $this->error('auth_check_required_for_add_user_to_result', 500);

    }
    private function success(){
        return response()->json([
            'result' => true,
            'content' => (array)$this->result,
            'code'   => 200,
        ], 200);
    }
    protected function error($message, int $code = null){
        $code = $code ? $code : $this->code;
        $result = [
            'result' => false,
            'content' => ['errors' => $this->errors + [$message]],
            'code'   => $code,
        ];

        response()->json($result, $code)->send();

        if (OneSSettings::get('debug'))
            Log::error("Request error: $code, URL: ".request()->fullUrl(), [
                'response' => $result,
                'vars' => $this->data,
            ]);

        exit();
        return;
//        exit($code);
    }
    protected function addError($message){
        $this->errors[] = $message;
        return $this;
    }

    protected function saveToFile($data, $fileName = null, $error = false){
        $folderPath = plugins_path('xakfull/api');

        if (!file_exists($folderPath))
            mkdir($folderPath, 0777, true);

        $folderPath .= '/';

        if (is_null($fileName))
            $fileName = date('h:i:s_d-m-Y').'_'.request()->segment(count(request()->segments()));

        if ($error)
            $fileName.='_ERROR';

        if (file_exists($folderPath.$fileName))
            $fileName .= str_random(4);

        File::put($folderPath.$fileName.'.json', json_encode($data));
    }

    // customized methods
    protected function check() : bool {
        return true;
    }
    protected function do() : bool{
        return true;
    }
    protected function return() : array {
        return [];
    }

}
