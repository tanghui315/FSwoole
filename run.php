<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:24
 */
define('DEBUG', 'on');
define('WEBPATH', __DIR__);
define('BASEPATH',__DIR__."/");

function log_message($level, $message)
{
    echo "[{$level}]{$message}\n";
}

require_once __DIR__ . '/Felix/Felix.php';
require_once __DIR__ . '/Felix/Loader.php';
require_once __DIR__.'/libs/smarty/libs/Smarty.class.php';

$config = require( __DIR__ . '/config/config.php');
$router = require(__DIR__.'/config/router.php');
$config['router']=$router;

/**
 * 注册顶层命名空间到自动载入器
 */
Felix\Loader::addNameSpace('Felix', __DIR__.'/Felix');
spl_autoload_register('\\Felix\\Loader::autoload');

$felix=new \Felix($config);
if(isset($argv[1])){
    $felix->runCommand($argv);
}else{
    $felix->runHttpServer();
}
