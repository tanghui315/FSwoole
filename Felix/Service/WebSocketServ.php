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


    public function onOpen($server, $req)
    {

    }
    public function onMessage($server, $frame)
    {

    }
    public function onClose($server, $fd)
    {

    }

    //发送消息
    public function send($client_id,$data,$type="json")
    {
        if($type=="json"){
            $this->serv->push(json_encode($data));
        }else{
            $this->serv->push($data);
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
            $data['wsHandler']->onTask($serv,$task_id,$from_id,$data);
        }
        return $data;
    }

    function onFinish($serv,$task_id, $data) {
        if(isset($data['handler'])){
            $data['wsHandler']->onFinish($serv,$task_id, $data);
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