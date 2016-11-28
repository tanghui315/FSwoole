<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 13:36
 */

namespace App\Handler;
use Felix;

class InfoHandler extends Felix\Handler{

    //get请求
    public function get(){

        $this->response("<h1>hello ,this is Info Page</h1>");
    }
    //post请求
    public function post(){

    }
}