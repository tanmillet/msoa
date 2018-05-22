<?php
/**
 * @author  Chris
 */
return [
    'DEBUG' => false,
    'LOG_DIR' => '/data/log/project',
    'LOG_DIR_PREFIX' => 'msoa.stosz.com',
    'redis_servers' => '127.0.0.1:6379',
    "medoo" => [
        'database_type' => 'mysql',
        'database_name' => 'msoa',
        'server' => '127.0.0.1',
        'username' => 'root',
        'password' => 'bgn123',
        'charset' => 'utf8'
    ],
    'db_servers' => [
        'master' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'username' => 'root',
            'password' => 'bgn123',
            'dbname' => 'msoa'
        ],
        'slave' => [
            [
                'host' => '127.0.0.1',
                'port' => '3306',
                'username' => 'root',
                'password' => 'bgn123',
                'dbname' => 'msoa'
            ]
        ]
    ],
];