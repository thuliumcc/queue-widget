<?php

$configuration = array(
    'api_url' => 'http://host/panel/panel2.0/api',
    'user' => 'user',
    'password' => 'password',
    'permitted_queue_ids' => array(1, 2),
    'queue_names' => array(
        1 => 'Infolinia (12 3975300)',
        2 => 'HelpDesk'
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