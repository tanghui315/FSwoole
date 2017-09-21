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

    function __construct($config = array())
    {
        $this->config = $config;
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
        $this->maxTaskId=0;

    }

    public function onWorkerStop($serv, $workerId) {}

    public function onWorkerExit($serv, $workerId)
    {
        $this->handler->releasePool();
    }


}