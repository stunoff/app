<?php

namespace App\Service;

use App\Repository\Logs;

class Logger
{
    /**
     * @param      $errorMessage
     * @param string|null $orderId
     * @param string|null $source
     * @param string|null $fileLine
     */
    public static function writeLog(
        $errorMessage,
        $orderId = null,
        $source = null,
        $fileLine = null
    )
    {
        $errorRepo = new Logs();
        $errorRepo->storeError($errorMessage, $orderId, $source, $fileLine);
    }
}
