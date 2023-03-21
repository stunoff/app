<?php

require_once __DIR__.'/baseCron.php';

use App\Helper\OrderHelper;
use App\Repository\ApiCredentials;
use App\Repository\Fulfillment;
use App\Repository\Orders;
use App\Service\Logger;

// http://websoap.t8.uz/websoap.php
$ApiCredentials = new ApiCredentials();
$FulfillmentRepository = new Fulfillment();
$OrderRepository = new Orders();

$credentials = $ApiCredentials->getCredentialsByName('websoap.t8.uz');
$soapClient = new SoapClient('http://websoap.t8.uz/websoap.php?wsdl',
    array('trace' => 1, 'login' => $credentials['login'], 'password' => $credentials['password'])
);

// Наше название - их айди
$productMap = array(
    'Serum Уз'             => 52,
    'Silk'                 => 60,
    'Альгинатная маска Уз' => 53,
    'Вариоль Уз'           => 56,
    'Верминекс Уз'         => 62,
    'Микосан Уз'           => 50,
    'Слиммер Уз'           => 49,
    'Спартаген Уз'         => 54,
    'Суставитин Уз'        => 51,
);

$orders = $FulfillmentRepository->getUzbOrdersForFF();
foreach ($orders as $order) {
    try {
        $parsedDopComplect = OrderHelper::parseDopComplect($order['dop_complect']);
        if (empty($parsedDopComplect)) {
            throw new Exception('Пустая комплектация');
            continue;
        }

        // Пока пропускаем заказы, где два или больше двух разных наименований
        if (count($parsedDopComplect) > 1) {
            throw new Exception('Два или больше двух разных наименований');
            continue;
        }

        if (!isset($productMap[$parsedDopComplect[0]['product_name']])) {
            throw new Exception('Не найден айди оффер, или проставлен неверный оффер');
            continue;
        }

        $ffProdId = $productMap[$parsedDopComplect[0]['product_name']];
        if (empty($order['c_area'])) {
            throw new Exception('Не указан регион или его id пустое');
            continue;
        }

        $ffId = $soapClient->Adman_Get_Dist_ID(
            $ffProdId,
            $order['name'],
            $order['phone'],
            $order['c_area'],
            $order['address'],
            $order['cd_count'],
            $order['full_price'],
            $credentials['login'], $credentials['password']
        );

        if (empty($ffId) || $ffId == 'ERROR !!!') {
            throw new Exception('Отказ FF');
        }

        $FulfillmentRepository->storeFfOrder($order['id'], $ffId);
        $OrderRepository->updateOrderStatus($order['id'], 0);
    } catch (Exception $e) {
        Logger::writeLog(
            $e->getMessage(),
            $order['id'],
        __CLASS__,
        __LINE__
        );
    }
}
