<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 13:36
 */

namespace app\handler;
use Felix;

class InfoHandler extends Felix\Handler{

    //get请求
    public function indexAction(){

        $this->response("<h1>hello ,this is Info Page</h1>");
    }

}