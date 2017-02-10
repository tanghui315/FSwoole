<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/9
 * Time: 下午3:40
 */


namespace Felix\Service;
use Felix;

class HttpServ{

    const HTTP_EOF = "\r\n\r\n";
    public $serv;
    public $config;
    public $log;
    public $smarty;
    public $app_path;
    public $currentHandler;

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

    public function setGlobal($request)
    {
        $_GET =isset($request->get)?$request->get:[];
        $_POST = isset($request->post)?$request->post:[];
        $_FILES = isset($request->files)?$request->files:[];
        $_COOKIE =isset($request->cookie)?$request->cookie:[];
        $_SERVER = isset($request->server)?$request->server:[];
        $_REQUEST = array_merge(isset($request->get)?$request->get:[], isset($request->post)?$request->post:[], isset($request->cookie)?$request->cookie:[]);
    }

    public function onRequest($request,$response)
    {

        $this->setGlobal($request);
        //获取body
        list($tmphead, $request->body) = explode(self::HTTP_EOF, $request->data, 2);
       // $info=$server->currentRequest;
        $fhandler=new Felix\Handler;
        $fhandler->initE($this->serv,$this->config);
        $fhandler->request=$request;
        $fhandler->response=$response;
        $url=strtolower($request->server['path_info']);
        $handlerAction="";
        $fclass="";
        if($url == "/"){ //首页处理
            $handlerFile=$this->app_path . '/handler/IndexHandler.php';
            if(!is_file($handlerFile)){
                 $fhandler->httpError(404);
               // $this->httpError($response);
                return false;
            }
            $fclass='app\handler\\'."IndexHandler";
            $handlerAction="indexAction";
        }else{
            //是否为静态文件
            $tag=$fhandler->doStaticRequestE($request);
            if($tag==1)
            {
                //这里最好是判断请求，如果是移动端就不要压缩
                $fhandler::$gzip=false;
                return true;
            }elseif($tag==-1)
            {
                return false;
            }

            //动态路由处理
            $path = explode('/', trim($request->server['path_info'], '/'));
            //print_r($path);
            if(count($path)<3){ //证明不是模块
                $cword=ucfirst($path[0]);
                $fclass='app\handler\\'."{$cword}Handler";
                $handlerFile=$this->app_path."/handler/{$cword}Handler.php";
                if(!is_file($handlerFile)){
                    $fhandler->httpError(404);
                    return false;
                }
                if(!isset($path[1])){
                    $handlerAction="indexAction";
                }else {
                    $class_reflect = new \ReflectionClass($fclass);
                    $action_name = strtolower($path[1] . "action");
                    foreach ($class_reflect->getMethods() as $method) {
                        $cMName = $method->getName();
                        $tmpName = strtolower($cMName);
                        if ($action_name == $tmpName) {
                            $handlerAction = $cMName;
                        }
                    }
                }
            }else{ //模块处理
                //0  是flag 标记
                $modName=strtolower($path[1]);
                $cword=ucfirst($path[2]);
                $fclass="app\\modules\\{$modName}\\"."{$cword}Handler";
                $handlerFile=$this->app_path."/modules/{$modName}/{$cword}Handler.php";
                if(!is_file($handlerFile)){
                    $fhandler->httpError(404);
                    return false;
                }
                if(!isset($path[3])){
                    $handlerAction="indexAction";
                }else{
                    $class_reflect = new \ReflectionClass($fclass);
                    $action_name=strtolower($path[3]."action");
                    foreach($class_reflect->getMethods() as $method){
                        $cMName=$method->getName();
                        $tmpName=strtolower($cMName);
                        if($action_name == $tmpName){
                            $handlerAction=$cMName;
                        }
                    }
                }
            }
        }
        if(!empty($handlerAction)){
            $handler=new $fclass;
            $handler->request=$request;
            $handler->response=$response;
            $handler->setLogger($this->log);
            $handler->smarty=$this->smarty;
            $handler->beforeAction($request,$this->config);
            $handler->$handlerAction();
            return true;
        }else{
            $fhandler->httpError(404);
            return false;
        }
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
     * 错误显示
     * @param $error
     */
    private function errorResponse($error)
    {
        $errorMsg = "{$error['message']} ({$error['file']}:{$error['line']})";
        $message = Felix\Error::info("FSwooleFramework"." Application Error", $errorMsg);
        if (empty($this->currentHandler))
        {
            $this->currentHandler = new Felix\Handler();
            $this->currentHandler->init($this,$this->config['web_server']);
        }
        $this->currentHandler->setHttpStatus(500);
        $this->currentHandler->body = $message;
        $this->currentHandler->body = (defined('DEBUG') && DEBUG == 'on') ? $message : '';
        $this->currentHandler->response();
    }

    //异步任务
    function onTask($serv,$task_id,$from_id,$data)
    {

        if(isset($data['handler'])){
            $data['handler']->onTask($serv,$task_id,$from_id,$data);
        }
        return $data;
    }

    function onFinish($serv,$task_id, $data) {
        if(isset($data['handler'])){
            $data['handler']->onFinish($serv,$task_id, $data);
        }
    }


    public function run($host = '0.0.0.0', $port = 9889)
    {
        set_error_handler(array($this, 'onErrorHandle'), E_USER_ERROR);
        register_shutdown_function(array($this, 'onErrorShutDown'));
        $this->serv = new \Swoole\Http\Server($host, $port);
        $this->serv->on("Request",[$this,"onRequest"]);
        if(isset($this->config['swoole_server']['task_worker_num'])) {
            $this->serv->on('Task', array($this, 'onTask'));
            $this->serv->on('Finish', array($this, 'onFinish'));
        }
        $this->serv->set($this->config['swoole_server']);
        $this->serv->start();
    }

}