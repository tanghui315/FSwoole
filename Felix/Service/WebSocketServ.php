<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/3/7
 * Time: 下午2:29
 */

namespace Felix\Service;
use Felix;

class WebSocketServ extends Felix\Service{

    public $serv;
    public $response;
    public $currentFd;
    public $request;

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
        if(!isset($data['action'])){
            $this->response->push($frame->fd,json_encode(['code'=>-1,'msg'=>'缺少action']));
        }
        //判断是不是经过路由映射的连接
        $action=$data['action'];
        $path=$action;
        if(!strpos($action,"/")){
            if(isset($this->config['router'][$action])){
                $tstr=$this->config['router'][$action];
                $path=explode('/', trim($tstr, '/'));
            }else{
                $this->response->push($frame->fd,json_encode(['code'=>-2,'msg'=>'action not found']));
            }
        }

        if(count($path)<3){ //证明不是模块
            $cword=ucfirst($path[0]);
            $fclass='app\handler\\'."{$cword}Handler";
            $handlerFile=$this->app_path."/handler/{$cword}Handler.php";
            if(!is_file($handlerFile)){
                $this->response->push($frame->fd,json_encode(['code'=>-3,'msg'=>'handler not found']));
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
                $this->response->push($frame->fd,json_encode(['code'=>-3,'msg'=>'handler not found']));
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
            $handler=new $fclass($this);
            $handler->beforeAction();
            $handler->$handlerAction();
            $handler->afterAction();
            return true;
        }else{
            $this->response->push($frame->fd,json_encode(['code'=>-3,'msg'=>'handler not found']));
            return false;
        }


    }
    public function onClose($server, $fd)
    {

    }

    //发送消息
    public function send($client_id,$data,$type="json")
    {
        if($type=="json"){
            $this->serv->push($client_id,json_encode($data));
        }else{
            $this->serv->push($client_id,$data);
        }
    }
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

    }
}