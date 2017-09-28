<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/12/7
 * Time: 10:44
 */

namespace app\modules\admin;
use Felix;

class IndexHandler extends Felix\Handler\HttpHandler{

    public function indexAction(){
        $this->response("<h1>hello Felix Framework,This is Admin Index Page.</h1>");
    }

    public function testAction(){
        $this->response("<h1>hello Felix Framework,This is Admin Test Page.</h1>");
    }
}