<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 10:34
 */
namespace app\handler;
use Felix;

class IndexHandler extends Felix\Handler\HttpHandler{

    //get请求
    public function indexAction(){

      // print_r($this->request);
       // $word=$request->redis->get("felix");
       // $request->response("<h1>hello Felix Framework,This is Index Page.{$word}</h1>");
//        var_dump($request);
//        return false;
       yield $this->response("<h1>hello Felix Framework,This is Index Page.</h1>");
    }

    public  function jsonAction(){

        $this->response(json_encode(['dd'=>'hello','ff'=>'放回说的是']),true);
    }

    public function saveAction(){

        $this->response("<h1>save ok </h1>");
    }

    public function uploadAction(){
        $newFileName = time() . '.' . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        $path = WEBPATH.'/upload/vote/';
        $tmp = str_replace('\\\\', '\\', $_FILES['file']['tmp_name']);
        print_r($_FILES);
        echo $path.$newFileName;
        move_uploaded_file($_FILES['file']['tmp_name'],$path.$newFileName);
        //$r= copy($tmp,$path.$newFileName);
        //unlink($path.$newFileName);

        $this->response("<h1>upload ok </h1>");
    }

}