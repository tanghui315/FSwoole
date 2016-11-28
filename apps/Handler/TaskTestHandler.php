<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/11/18
 * Time: 10:10
 */

namespace App\Handler;
use Felix;

class TaskTestHandler extends Felix\Handler{

    public function get(){


        self::$serv->task(['cmd'=>'save','data'=>"this is test".self::$currentFd]);
        $this->response("<h1>this is ok</h1>");
    }

    public function onTask($serv,$task_id,$from_id,$data)
    {

    }
}