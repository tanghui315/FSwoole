<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:24
 */
define('WEBPATH', __DIR__);
require_once __DIR__ . '/Felix/Felix.php';
require_once __DIR__ . '/Felix/Loader.php';

$config = require( __DIR__ . '/config/config.php');

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
/**
 * 注册顶层命名空间到自动载入器
 */
Felix\Loader::addNameSpace('Felix', __DIR__.'/Felix');
spl_autoload_register('\\Felix\\Loader::autoload');

$felix=\Felix::getInstance();
$felix->redis=$redis;
$felix->runHttpServer($config);