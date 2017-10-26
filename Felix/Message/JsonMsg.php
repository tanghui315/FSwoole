<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/9/29
 * Time: 下午3:51
 */
namespace Felix\Message;

use Felix;

class   JsonMsg extends Felix\Message{

    public function parse(){
        if(is_string($this->frame->data)) {
            return json_decode($this->frame->data, true);
        }
        return false;
    }

    public function output($data){
        if(is_array($data)){
            return $this->resp->put($this->frame->fd,json_encode($data));
        }
        return false;
    }
}