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

    public function parse(){
        $data= $this->frame->data;
        $struct = new StructClass();
        $head = $struct->unpack(">ii", substr($data,0,8));
        //print_r($unpack);
        exit;

        return $this->frame->data;
    }

    public function output($data){

    }
}