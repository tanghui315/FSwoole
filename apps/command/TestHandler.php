<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/24
 * Time: 上午11:30
 */

namespace app\command;
use Felix;

class TestHandler extends Felix\Handler
{
    public function indexAction($ok)
    {
        echo "hello command {$ok} \n";
    }

    public function dboptAction()
    {
        $this->loadModel("votebase");
        $data=$this->votebase->getData();
        print_r($data);
    }
}