<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/3/7
 * Time: 下午2:29
 */

namespace Felix\Service;
use Felix;
use Felix\Message\JsonMsg;
use Felix\Message\ProtoMsg;
use Felix\Task;

class WebSocketServ extends Felix\Service{

    public $serv;
    public $response;
    public $currentFd;
    public $request;
    public $msg;
    //错误码
    const MISS_ACTION=5001;
    const NOT_FOUND_HANDLER=5002;
    const HANDLER_WRONG=5003; //'The handler type is err,please change to WsHandler'

    public function onOpen($server, $req)
    {
        $this->serv=$server;
        $this->request=$req;
    }
    public function onMessage($server, $frame)
    {
        $this->response=$server;
        $this->currentFd=$frame->fd;
        $data=json_decode($frame->data);
        if(is_null($data)){
            $this->msg=new ProtoMsg($this->response,$frame);
            $data= $this->msg->parse();
        }else{
            $this->msg=new JsonMsg($this->response,$frame);
        }

        if(!isset($data['action'])){
            $this->msg->output(['code'=>self::MISS_ACTION]);
        }
        //判断是不是经过路由映射的连接
        $action=$data['action'];
        $path=$action;
        if(!strpos($action,"/")){
            if(isset($this->config['router'][$action])){
                $tstr=$this->config['router'][$action];
                $path=explode('/', trim($tstr, '/'));
            }
        }

        if(count($path)<3){ //证明不是模
            $cword=ucfirst($path[0]);
            $fclass='app\handler\\'."{$cword}Handler";
            $handlerFile=$this->app_path."/handler/{$cword}Handler.php";
            if(!is_file($handlerFile)){
                $this->msg->output(['code'=>self::NOT_FOUND_HANDLER]);
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
                $this->msg->output(['code'=>self::NOT_FOUND_HANDLER]);
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
        if(!empty($handlerAction)){
            $this->handler=new $fclass($this);
            if(!$this->handler instanceof \Felix\Handler\WsHandler)
            {
                $this->msg->output(['code'=>self::HANDLER_WRONG]);
                return false;
            }

            $this->handler->beforeAction();
            if ($this->maxTaskId >= PHP_INT_MAX) {
                $this->maxTaskId = 0;
            }
            $taskId = ++$this->maxTaskId;
            $task =new Task($taskId,$this->handler,$this->terminate($handlerAction));
            $task->run();

        }else{
            $this->msg->output(['code'=>self::NOT_FOUND_HANDLER]);
            return false;
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

    public function onClose($server, $fd)
    {

    }

//    //发送消息
//    public function send($client_id,$data,$type="json")
//    {
//        if($type=="json"){
//            $this->serv->push($client_id,json_encode($data));
//        }else{
//            $this->serv->push($client_id,$data);
//        }
//    }
    //广播
    public function broadcast()
    {

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

    public function run($host = '0.0.0.0', $port = 8889)
    {
        set_error_handler(array($this, 'onErrorHandle'), E_USER_ERROR);
        register_shutdown_function(array($this, 'onErrorShutDown'));
        $this->serv=new \Swoole\Websocket\Server($host, $port);
        $this->serv->on('Open',[$this,"onOpen"]);
        $this->serv->on('Message',[$this,"onMessage"]);
        $this->serv->on('Close',[$this,"onClose"]);
        if(isset($this->config['swoole_server']['task_worker_num'])) {
            $this->serv->on('Task', array($this, 'onTask'));
            $this->serv->on('Finish', array($this, 'onFinish'));
        }
        $this->serv->set($this->config['swoole_server']);
        $this->serv->start();
    }
}