<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/23
 * Time: 下午12:38
 */

namespace Felix;

class Service{

    public $config;
    public $log;
    public $app_path;
    public $maxTaskId;
    public $handler;
    public $felix;
    public $serv;

    function __construct(\Felix $felix)
    {
        $this->felix = $felix;
        $this->config = $felix->config;
    }

    /**
     * 设置Logger
     * @param $log
     */
    function setLogger($log)
    {
        $this->log = $log;
    }

    /**
     * 捕获set_error_handle错误
     */
    function onErrorHandle($errno, $errstr, $errfile, $errline)
    {
        $error = array(
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
        );
        $this->errorResponse($error);
    }

    /**
     * 捕获register_shutdown_function错误
     */
    function onErrorShutDown()
    {
        $error = error_get_last();
        if (!isset($error['type'])) return;
        switch ($error['type'])
        {
            case E_ERROR :
            case E_PARSE :
            case E_USER_ERROR:
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                break;
            default:
                return;
        }
        $this->errorResponse($error);
    }

    function errorResponse($error)
    {
        $errorMsg = "{$error['message']} ({$error['file']}:{$error['line']})";
        $this->log->put($errorMsg,4);
        log_message("Error",$errorMsg);
    }
    function terminate($handlerAction)
    {

    }

    function onWorkerStart($serv, $workerId)
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        log_message("sys","WorkerStart".$workerId);
        if(!$serv->taskworker){
            $this->maxTaskId=0;
            $this->felix->initService();
            $this->felix->registerProServices();
        }

    }

    function onTask($serv,$task_id,$from_id,$content)
    {

    }

    function onFinish($serv,$task_id, $content) {

    }

    public function onWorkerStop($serv, $workerId) {
        log_message("sys","WorkerStop".$workerId);
        if(!$serv->taskworker) {
            $this->felix->release();
        }
    }


}