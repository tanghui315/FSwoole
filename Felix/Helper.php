<?php
/**
 * Created by PhpStorm. 辅助类
 * User: tanghui
 * Date: 2016/11/8
 * Time: 17:41
 */
namespace Felix;

class Helper{

    /**
     * 根据文件名获取扩展名
     * @param $file
     * @return string
     */
    static public function getFileExt($file)
    {
        $s = strrchr($file, '.');
        if ($s === false)
        {
            return false;
        }
        return strtolower(trim(substr($s, 1)));
    }
}