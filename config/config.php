<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:30
 */

return [
    "swoole_server"=>[
        "max_request"=>256,
        "worker_num"=>2,
        "keepalive"=>1,
        "task_worker_num"=>200,
        "daemonize"=>false,
        "pid_file"=>__DIR__."/http_server.pid",
        "ssl_cert_file"=>"",
        "ssl_key_file"=>"",  //https证书
    ],
    "web_server"=>[
        "document_root"=>__DIR__."/../",
        "static_dir"=>"static,",
        "static_ext"=>"js,jpg,gif,png,css,html",
        "keepalive"=>1,
        "gzip_open"=>1,
        "gzip_level"=>1,
        "expire_open"=>1,
        "expire_time"=>1800,
    ],
    //日志配置
    "log"=>[
      "type"=>"file", //另一种是 echo  代表直接输出到控制台
        "file"=>"felix.log",//日志文件名
        "dir"=>__DIR__."/../tmp/",
        "date"=>true, //是否按照日期生成
        "verbose"=>false, //如果为真，会显示函数调用信息
        "enable_cache"=>false,// 开启文件缓存
        "cut_file"=>false,//是否对日志文件做分片存储
    ],
//    "mysql"=>[
//        'enabled'=>false,
//        'host' => '192.168.32.121',
//        'port' => 3306,
//        'dbname' => 'felix',
//        'user' => 'root',
//        'pass' => 'EV6ZeYVa'
//    ],
    //模型数据库,多库模型
    "database"=>[
        'default'=>array(
            'dsn'	=> '',
            'hostname' => '192.168.32.128',
            'username' => 'root',
            'password' => '123456',
            'database' => 'applet',
            'dbdriver' => 'mysqli',
            'dbprefix' => '',
            'pconnect' => false,
            'db_debug' => false,
            'cache_on' => false,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            'encrypt' => false,
            'compress' => false,
            'stricton' => false,
            'failover' => array(),
            'save_queries' => true
        )
    ],

    "redis"=>[
        'enabled'=>false,
        'host'=>'127.0.0.1',
        'port'=>6379
    ]
];