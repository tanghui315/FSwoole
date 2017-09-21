<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2016/10/27
 * Time: 16:30
 */

return [
    "swoole_server"=>[
        "max_request"=>20000,
        "worker_num"=>2,
        "keepalive"=>1,
        "task_worker_num"=>200,
        "daemonize"=>false,
        "pid_file"=>__DIR__."/http_server.pid",
        'buffer_output_size'=>9000000000,
        'open_eof_check' => 9000000000,
        'package_max_length' => 9000000000,
        "ssl_cert_file"=>"",
        "ssl_key_file"=>"",  //https证书
    ],
    "web_server"=>[
        "document_root"=>__DIR__."/../",
        "static_dir"=>"static,upload",
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
            'port'  =>3306,
            'save_queries' => true,
            'maxPool'=>50,  //连接池大小
            'timeout'=>5, //连接超时
        )
    ],
    "listen"=>[
        'host'=>"0.0.0.0",
        'port'=>9889
    ],
    "smarty"=>true,
    "redis"=>[
        'enabled'=>false,
        'host'=>'127.0.0.1',
        'port'=>6379
    ]
];