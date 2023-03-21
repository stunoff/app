<?php

if (!function_exists('batch')) {
    function batch($closure, $arr, $batchSize = 100) {
        $batch = array();
        foreach($arr as $item) {
            $batch[] = $item;

            if(count($batch) === (int) $batchSize) {
                $closure($batch);
                $batch = array();
            }
        }

        if(count($batch)) $closure($batch);
    }
}
