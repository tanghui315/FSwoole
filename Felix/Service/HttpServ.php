<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/9
 * Time: 下午3:40
 */


namespace Felix\Service;
use Felix;
use Felix\Task;
class HttpServ extends Felix\Service{

    const HTTP_EOF = "\r\n\r\n";
    public $smarty;
    public $currentHandler;
    public $request;
    public $response;

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
        log_message("sys","onRequest");
        if($request->server["request_method"]=="OPTIONS"){ //不处理option
            $response->header("Access-Control-Allow-Origin","*");
            $response->header("Access-Control-Allow-Credentials","true");
            $response->header("Access-Control-Allow-Headers","Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization, accept, origin, Cache-Control, X-Requested-With");
            $response->header("Access-Control-Allow-Methods","POST, OPTIONS, GET, PUT");
            $response->header("Access-Control-Max-Age",6888686868);
            $response->status(204);
            return true;
        }
        $this->setGlobal($request);
        $this->request=$request;
        $this->response=$response;
        //获取body
        list($tmphead, $request->body) = explode(self::HTTP_EOF, isset($request->data)?$request->data:$request->getData(), 2);
       // $info=$server->currentRequest;
        $fhandler=new Felix\Handler\HttpHandler($this);
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
            $tag=$fhandler->doStaticRequest($request);
            if($tag==1)
            {
                //这里最好是判断请求，如果是移动端就不要压缩
                $fhandler->gzip=false;
                return true;
            }elseif($tag==-1)
            {
                return false;
            }

            //动态路由处理
            $path = explode('/', trim($request->server['path_info'], '/'));
            //print_r($path);
            //判断是不是经过路由映射的连接
            if(count($path)==1){
                if(isset($this->config['router'][$path[0]])){
                    $tstr=$this->config['router'][$path[0]];
                    $path=explode('/', trim($tstr, '/'));
                }
            }
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
                $modName=strtolower($path[0]);
                $cword=ucfirst($path[1]);
                $fclass="app\\modules\\{$modName}\\"."{$cword}Handler";
                $handlerFile=$this->app_path."/modules/{$modName}/{$cword}Handler.php";
                if(!is_file($handlerFile)){
                    $fhandler->httpError(404);
                    return false;
                }
                if(!isset($path[2])){
                    $handlerAction="indexAction";
                }else{
                    $class_reflect = new \ReflectionClass($fclass);
                    $action_name=strtolower($path[2]."action");
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

            $this->handler=new $fclass($this);
            if(!$this->handler instanceof \Felix\Handler\HttpHandler)
            {
                $fhandler->httpError(500,"The handler type is err,please change to HttpHandler");
                return false;
            }

            $this->handler->beforeAction();
            if ($this->maxTaskId >= PHP_INT_MAX) {
                $this->maxTaskId = 0;
            }
            $taskId = ++$this->maxTaskId;
            $task =new Task($taskId,$this->handler,$this->terminate($handlerAction));
            $task->run();
            //unset($task);
        }else{
            $fhandler->httpError(404);
        }
        unset($fhandler);
        unset($class_reflect);
        unset($this->request);
        unset($this->response);
        return true;
    }
    /*
     * 异步处理
     */
    public function terminate($handlerAction)
    {
        yield $this->handler->$handlerAction();

        unset($this->handler);
    }

    /**
     * 错误显示
     * @param $error
     */
    function errorResponse($error)
    {
        $errorMsg = "{$error['message']} ({$error['file']}:{$error['line']})";
        $message = Felix\Error::info("FSwooleFramework"." Application Error", $errorMsg);
        $this->log->put($errorMsg,4);
        if (empty($this->currentHandler))
        {
            $this->currentHandler = new Felix\Handler\HttpHandler($this);
        }
        $this->currentHandler->setHttpStatus(500);
        $this->currentHandler->body = $message;
        $this->currentHandler->body = (defined('DEBUG') && DEBUG == 'on') ? $message : '';
        $this->currentHandler->response();
    }

    //异步任务
    function onTask($serv,$task_id,$from_id,$content)
    {
        parent::onTask($serv,$task_id,$from_id,$content);
        if($content instanceof \Felix\Handler\TaskContent){
            $content->getHandler()->onTask($serv,$task_id,$from_id,$content);
        }
        return $content;
    }

    function onFinish($serv,$task_id, $content) {
        if($content instanceof \Felix\Handler\TaskContent){
            $content->getHandler()->onFinish($serv,$task_id, $content);
        }
    }


    public function run($host = '0.0.0.0', $port = 9889)
    {
        set_error_handler(array($this, 'onErrorHandle'), E_USER_ERROR);
        register_shutdown_function(array($this, 'onErrorShutDown'));
        $this->serv = new \Swoole\Http\Server($host, $port);
        $this->serv->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->serv->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->serv->on("Request",[$this,"onRequest"]);
        if(isset($this->config['swoole_server']['task_worker_num'])) {
            $this->serv->on('Task', array($this, 'onTask'));
            $this->serv->on('Finish', array($this, 'onFinish'));
        }
        $this->serv->set($this->config['swoole_server']);
        $this->serv->start();
    }

}