<?php

namespace App\Helper;

class OrderHelper
{
    public static function parseDopComplect($dopComplect)
    {
        if (!is_string($dopComplect) || empty($dopComplect)) {
            throw new \Exception('dopComplect is not string or empty');
        }

        $dopComplect = trim($dopComplect, ';');
        $dopComplect = explode(';', $dopComplect);
        $data = array();

        foreach ($dopComplect as $dop) {
            preg_match('/(.*)\((\d+)\)/', $dop, $matches);
            $productName  = $matches[1];
            $productCount = $matches[2];

            if (empty($productName) || empty($productCount)) {
                throw new \Exception('productName or productCount is empty');
                continue;
            }

            $data[] = array(
                'product_name' => $productName,
                'product_cnt'  => $productCount,
            );
        }

        if (empty($data)) {
            throw new \Exception('Пустая комплектация');
        }

        return $data;
    }
}
