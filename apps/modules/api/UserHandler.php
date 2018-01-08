<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2018/1/2
 * Time: 下午5:15
 */

namespace app\modules\api;
use Felix;

class UserHandler extends Felix\Handler\HttpHandler{

    public function loginAction()
    {
        $loginType = $this->request->post['type']; //登陆类型
        switch($loginType){
            case 1:

        }
    }

    public function sendCodeAction()
    {

    }

    private function wxLogin(){

    }

    private function qqLogin(){

    }

    private function phoneLogin(){

    }
}