<?php
// http://89.218.86.178/api/doc.txt
// тут отправляем заказы в кет, либо удаляем их из кета
require_once __DIR__.'/baseCron.php';

use App\Helper\OrderHelper;
use App\Models\Ket as KetModel;
use App\Repository\ApiCredentials;
use App\Repository\Ket;
use App\Repository\Orders;
use App\Repository\Products;
use App\Repository\Logs;
use App\Service\Curl;
use App\Service\Logger;

$ApiCredentials = new ApiCredentials();
$curl = new Curl();
$ketRepo = new Ket();
$productsRepo = new Products();
$OrderRepository = new Orders();
$Logs = new Logs();

$credentials = $ApiCredentials->getCredentialsByName('ket.kz');
$products = $productsRepo->getProductList();
$ketRepo->addKzOrders();

// Add orders to ket
$orders = $ketRepo->getKzOrders(KetModel::ORDER_SENT);
foreach ($orders as $order) {
    try {
        $parsedDopComplect = OrderHelper::parseDopComplect($order['dop_complect']);
        $offers = KetModel::generateKetOfferString($parsedDopComplect, $products);
        $phone = ltrim($order['phone'], '+');
        $phone2 = ltrim($order['phone2'], '+');
        $data = array(
            "phone"          => $phone,
            "phone_sms"      => $phone2,
            "price"          => $order['full_price'],
            "order_id"       => $order['id'],
            "name"           => $order['name'],
            // двухбуквенный (ISO 3166-1 alpha-2) код страны в нижнем регистре
            "country"        => KetModel::KZ_COUNTRY_CODE,
            "index"          => $order['zip'],
            "addr"           => "{$order['region']}, {$order['address']}",
            "status"         => 1,
            "kz_delivery"    => $order['post_type'],
            "offer"          => $offers,
            "secret"         => $credentials['password'],
            'date_delivery'  => $order['d_dost'],
            'deliv_desc'     => $order['deliveryComment'],
            "is_dvd"         => $order['is_dvd'],
            "brd_sended"     => $order['pp_id'],
            "saller_butique" => $order['operator_name'],
            'web'            => $order['pp_user_id'],
        );


        $data = json_encode($data);
        $hash = KetModel::generateHash($data, $credentials['login']);
        $curl->setUrl(KetModel::ORDER_SEND_URL . $credentials['login'] . "&hash=$hash");
        $curl->setData(array('data' => $data));
        $result = json_decode($curl->sendRequest(), true);

        if ($result['result']['success'] == 'TRUE' && $order['send'] == 1) {
            $ketRepo->updateOrder($order['id'], $result['result']['id'], 2);
            $Logs->storeDeliveryComment('ket', $order['id'], $order['deliveryComment']);
        } else {
            throw new Exception('Заказ не улетел в кет');
        }
    } catch (Exception $e) {
        Logger::writeLog(
            $e->getMessage(),
            $order['id'],
        __CLASS__,
        __LINE__
        );
    }
}

// удаление заказов
$orders = $ketRepo->getKzOrders(KetModel::ORDER_REMOVE);
foreach ($orders as $order) {
    $data = json_encode(array(
        array('id' => $order['ket_order_id']),
    ));

    $hash = KetModel::generateHash($data, $credentials['login']);
    $curl->setUrl(KetModel::ORDER_REMOVE_URL . $credentials['login'] . "&s={$credentials['password']}&hash=$hash");
    $curl->setData($data);
    $curl->sendRequest();
}