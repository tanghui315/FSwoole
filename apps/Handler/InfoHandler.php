<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 13:36
 */

namespace app\handler;
use Felix;
use app\models\VotebaseModel;

class InfoHandler extends Felix\Handler\HttpHandler{

    //get请求
    public function indexAction(){
        //$b=Felix\Database\MysqlDb::queryOne("select * from ");

       // $this->response("<h1>hello ,this is Info Page</h1>");
        //测试模版
        $this->render("vote/test.html",['title'=>'这是一个标题','msg'=>'模版内容']);
    }

    //车是model

    public function voteAction(){

       // $this->loadModel("votebase");
//        $d=new VotebaseModel();
//        $d->db=$this->loadDB("",true);
        //$this->votebase->updateTitle(51,"顶你个肺");
//        $data=$d->getData();

       // return $this->response(json_encode($data),true);
    }


}