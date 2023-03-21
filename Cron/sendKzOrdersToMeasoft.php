<?php

require_once __DIR__.'/baseCron.php';

use App\Helper\OrderHelper;
use App\Models\MeasoftCourier;
use App\Repository\ApiCredentials;
use App\Repository\Measoft;
use App\Repository\Orders;

$ApiCredentials = new ApiCredentials();
$MeasoftRepository = new Measoft();
$OrdersRepository = new Orders();
$credentials = $ApiCredentials->getCredentialsByName('MeasoftCourier');
$MeasoftRepository->getOrdersForMeasoft();
$orders = $MeasoftRepository->getOrdersForMeasoft();

foreach ($orders as $order) {
    $data = array(
        'receiver' => array(
            'person' => $order['name'],
            'phone' => $order['phone'],
            'zipcode' => $order['zip'],
            'town' => $order['city_name'],
            'address' => $order['address'],
            'date' => $order['d_dost'],
            'time_min' => '10:00',
            'time_max' => '16:00',
        ),
        'paytype' => 'CASH',
        'price' => $order['full_price'],
        'deliveryprice' => 0,
        'discount' => 0,
        'inshprice' => $order['full_price'],
        'enclosure' => $order['dop_complect'],
        'instruction' => $order['comment'],
        'return' => false,
    );

    $complect = OrderHelper::parseDopComplect($order['dop_complect']);
    $items = array();
    foreach ($complect as $dop) {
        $items[] = array(
            'name' => $dop['product_name'],
            'quantity' => $dop['product_cnt'],
            'retprice' => round($order['full_price'] / $order['cd_count'], 2),
            'mass' => 0.5,
            'barcode' => $order['id'],
        );
    }
    
    $measoft = new MeasoftCourier($credentials['login'], $credentials['password'], '8');
    $measoftId = $measoft->orderCreate($data, $items);
    $MeasoftRepository->storeOrder($order['id'], $measoftId);
    $OrdersRepository->updateOrderStatus($order['id'], 0);
}
