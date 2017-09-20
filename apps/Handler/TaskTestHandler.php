<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/11/18
 * Time: 10:10
 */

namespace app\handler;
use Felix;

class TaskTestHandler extends Felix\Handler\HttpHandler{

    public function indexAction(){
        //print_r($this->request);
        $this->task(['cmd'=>'save','data'=>"this is test"]);
        $this->task(['cmd'=>'save','data'=>"this is test"]);
        $result=$this->taskwait(['cmd'=>'save','data'=>"this is test"],10);
        if(!$result){

        }
        $this->response("<h1>this is ok</h1>");
    }

    public function onTask($serv,$task_id,$from_id,$data)//线程
    {
        if($data['cmd']=="save"){
            echo "my test by task : {$task_id} \n";
            $this->log->put("my test by task : {$task_id}");
        }
    }

    public function  onFinish($serv,$task_id, $data)
    {
        echo "Task {$task_id} finish";
    }
}