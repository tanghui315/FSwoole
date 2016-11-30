<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/28
 * Time: 10:34
 */
namespace App\Handler;
use Felix;

class IndexHandler extends Felix\Handler{

    //get请求
    public function get(){
       // $word=$request->redis->get("felix");
       // $request->response("<h1>hello Felix Framework,This is Index Page.{$word}</h1>");
//        var_dump($request);
//        return false;

        $this->response("<h1>hello Felix Framework,This is Index Page.</h1>");
    }
    //post请求
    public function post(){

    }
}