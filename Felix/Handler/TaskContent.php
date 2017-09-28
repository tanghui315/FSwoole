<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/9/28
 * Time: 下午3:34
 */
namespace Felix\Handler;

class TaskContent{

    public $cmd="";
    public $data=[];
    private $handler;

    function __construct($handler){
        $this->handler=$handler;
    }
    function getHandler()
    {
        return $this->handler;
    }
}