<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/23
 * Time: 下午12:35
 */


namespace Felix\Service;
use Felix;

class CmdServ extends Felix\Service{


    function run($parameter)
    {
        $route=strtolower($parameter[1]);
        if(strpos($route,"/"))
        {
            $tmp=explode("/",$route);
            $main=$tmp[0];
            $sub=$tmp[1];
        }else{
            $main=$route;
            $sub="index";
        }

        //构建文件
        $cword=ucfirst($main);
        $fclass='app\command\\'."{$cword}Handler";
        $handlerFile=$this->app_path."/command/{$cword}Handler.php";
        if(!is_file($handlerFile)){
            log_message("CmdFileMiss","{$handlerFile} Not Found!");
            return false;
        }

        $action=$sub."Action";
        $handler=new $fclass;
        $handler->config=$this->config;
        $handler->setLogger($this->log);

        $pnums=count($parameter);
        //处理参数赋值
        if($pnums>2){
            switch($pnums){
                case 3:
                    $handler->$action($parameter[2]);
                    break;
                case 4:
                    $handler->$action($parameter[2],$parameter[3]);
                    break;
                case 5:
                    $handler->$action($parameter[2],$parameter[3],$parameter[4]);
                    break;
                default:
                    log_message("CmdParErr","parameter too much !");
                    return false;
            }
        }else{
            $handler->$action();
        }


    }
}