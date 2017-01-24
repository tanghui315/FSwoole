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
        //$b=Felix\Database\MysqlDb::queryOne("select * from ");

       // $this->response("<h1>hello ,this is Info Page</h1>");
        //测试模版
        $this->render("vote/test.html",['title'=>'这是一个标题','msg'=>'模版内容']);
    }


}