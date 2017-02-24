<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/8
 * Time: 下午3:25
 */

namespace Felix;

class Model{

    public $db;

    public function __construct($handler=null)
    {
        if($handler!=null){
            //初始化db
            if(empty($handler->db))
            {
                $handler->loadDB();
            }
            $this->db=$handler->db;
        }
        //log_message('info', 'Model Class Initialized');
    }

}