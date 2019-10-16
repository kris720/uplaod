<?php

return array(
    //数据库配置信息
    'db' => array(
        'driver' => 'mysql',
        'host' => 'localhost',
        'username'=>'root',
        'password'=>'',
        'database'=>'plume',
        'port' => '3306',
        'charset'=>'utf8'
    ),
    'redis' => array(
        'host' => '127.0.0.1',
        'port' => '6379'
    ),

    'fileType'=>array(
        'jpeg', 'jpg', 'png', 'gif',
        'mp3',
        'mp4', 'avi','flv','mkv',
        'doc','xls','ppt','pdf'
    )
);