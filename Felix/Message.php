<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/9/29
 * Time: 下午3:40
 */
namespace Felix;

abstract class Message{
    protected $resp;
    protected $frame;
    function __construct($response,$frame)
    {
        $this->resp=$response;
        $this->frame=$frame;
    }
    abstract public function parse();
    abstract public function output($data);
}