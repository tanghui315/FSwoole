<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/10/20
 * Time: 下午4:18
 */

namespace Felix\Handler;

use Felix\Handler;

class WsHandler extends Handler{

    protected $msg;

    function __construct($serv=null)
    {
        parent::__construct($serv);
        $this->msg=$this->server->msg;
    }
}