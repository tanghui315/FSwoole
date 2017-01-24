<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/1/24
 * Time: 下午3:31
 */

namespace Felix\Database;
use Felix;

class FRedis {

    public static $redis;

    public static function init($redis_config){
        self::$redis = new \Redis();
        self::$redis ->connect($redis_config['host'], $redis_config['port']);
    }
}