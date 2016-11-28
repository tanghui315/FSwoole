<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:30
 */

namespace Felix;


class Loader{

    static $felix;
    protected static $namespaces;

    function __construct($felix)
    {
        self::$felix = $felix;
    }
    /**
     * 自动载入类
     * @param $class
     */
    static function autoload($class)
    {
        $root = explode('\\', trim($class, '\\'), 2);
        if (count($root) > 1 and isset(self::$namespaces[$root[0]]))
        {
            include self::$namespaces[$root[0]].'/'.str_replace('\\', '/', $root[1]).'.php';
        }
    }

    /**
     * 设置根命名空间
     * @param $root
     * @param $path
     */
    static function addNameSpace($root, $path)
    {
        self::$namespaces[$root] = $path;
    }
}