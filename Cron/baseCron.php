<?php

if ((php_sapi_name() !== 'cli')) {
//    exit('Не не не. Меня только из коммандной строки употреблять можно');
}

ini_set('max_execution_time', 0);
set_time_limit(0);

require_once __DIR__."/../../config.php";
require_once __DIR__."/../../vendor/autoload.php";
require_once __DIR__."/../Core/functions.php";
