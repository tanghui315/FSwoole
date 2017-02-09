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

    public function __construct()
    {
        log_message('info', 'Model Class Initialized');
    }

}