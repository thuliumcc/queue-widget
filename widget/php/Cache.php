<?php

interface Cache
{
    public function __construct($configuration);

    public function set($id, $data);

    public function get($id);
}