<?php

require_once __DIR__.'/baseCron.php';

use App\Repository\ApiCredentials;
use App\Repository\Fulfillment;
use App\Repository\Orders;

// http://websoap.t8.uz/websoap.php
$ApiCredentials = new ApiCredentials();
$FulfillmentRepository = new Fulfillment();
$OrderRepository = new Orders();

$credentials = $ApiCredentials->getCredentialsByName('websoap.t8.uz');
$soapClient = new SoapClient('http://websoap.t8.uz/websoap.php?wsdl',
    array('trace' => 1, 'login' => $credentials['login'], 'password' => $credentials['password'])
);

// обновление статусов
//фф статус - наш статус
$statusMap = array(
    'Выкуп' => array(
        'status' => 4,
        'status_ls' => null,
    ),
    'Готов к отгрузке' => array(
        'status' => 0,
        'status_ls' => 14,
    ),
    'Передан в подотчёт' => array(
        'status' => 0,
        'status_ls' => 19,
    ),
    'Отказ во время доставки' => array(
        'status' => 9,
        'status_ls' => 41,
    ),
);

$orders = $FulfillmentRepository->getNotFinalOrders();

foreach ($orders as $order) {
    $ffStatus = $soapClient->Adman_Get_Status($order['ff_id'], $credentials['login'], $credentials['password']);
    if (isset($order['ff_status']) && $order['ff_status'] == $ffStatus) {
        continue;
    }

    $FulfillmentRepository->updateFfStatus($order['ff_id'], $ffStatus);

    if (isset($statusMap[$ffStatus])) {
        $OrderRepository->updateOrderStatus($order['order_id'], $statusMap[$ffStatus]['status'], $statusMap[$ffStatus]['status_ls']);
    }
}
