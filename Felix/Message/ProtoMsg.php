<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/9/29
 * Time: 下午3:52
 */
namespace Felix\Message;

use Felix;
use Felix\Struct\StructClass;
class  ProtoMsg extends Felix\Message{
    private $router=[];
    public function parse(){
        $data= $this->frame->data;
        $struct = new StructClass();
        $pack_len = $struct->unpack(">i", substr($data,0,4));
        $pack_len =$pack_len[0];
        $body_len=strlen($data)-8;
        //判断是否粘包
        if($body_len>$pack_len){

        }else if($pack_len>$body_len){ //出现半包

        }else if($pack_len == $body_len )
        {

        }
        print_r($pack_len);
        echo "\n";
        print_r($body_len);
        exit;

        return $this->frame->data;
    }
    public function setRouter($conf){
        $this->router=$conf;
    }

    public function output($data){

    }
}