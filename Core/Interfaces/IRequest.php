<?php

namespace App\Core\Interfaces;

interface IRequest
{
    public function getQueryKey($key, $default = null);
    
    public function getPostKey($key, $default = null);
    
    public function getURL();
}