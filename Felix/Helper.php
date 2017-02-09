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

    static public function saveFile($fileName, $text) {
        if (!$fileName || !$text)
            return false;
        if (self::makeDir(dirname($fileName))) {
            if ($fp = fopen($fileName, "w")) {
                if (@fwrite($fp, $text)) {
                    fclose($fp);
                    return true;
                } else {
                    fclose($fp);
                    return false;
                }
            }
        }
        return false;
    }

    static public function makeDir($dir, $mode=0755) {
        /*function makeDir($dir, $mode="0777") { 此外0777不能加单引号和双引号，
          加了以后，"0400" = 600权限，处以为会这样，我也想不通*/
        if (!$dir) return false;
        if(!file_exists($dir)) {
            return mkdir($dir,$mode,true);
        } else {
            return true;
        }
    }
}