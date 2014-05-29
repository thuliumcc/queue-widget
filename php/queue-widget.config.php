<?php

$configuration = array(
    'api_url' => 'http://your_host_name.callcenter.pl/api',
    'user' => 'user',
    'password' => 'password',
    'permitted_queue_ids' => array(1, 2),
    'queue_names' => array(
        1 => 'Infolinia',
        2 => 'Wsparcie Techniczne'
    ),
    'cache' => array(
        'enabled' => true,
        'clean_interval' => 30,
        'class' => 'MemcacheCache',
        'class_path' => __DIR__,
        'server' => 'localhost',
        'port' => '11211'
    )
);
