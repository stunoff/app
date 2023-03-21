<?php
// http://89.218.86.178/api/doc.txt
// тут отправляем заказы в кет, либо удаляем их из кета
require_once __DIR__ . '/baseCron.php';

use App\Models\Ket as KetModel;
use App\Repository\ApiCredentials;
use App\Repository\Ket;
use App\Repository\Products;
use App\Service\Curl;
use App\Service\Logger;

$ApiCredentials = new ApiCredentials();
$curl = new Curl();
$ketRepo = new Ket();
$productsRepo = new Products();

$credentials = $ApiCredentials->getCredentialsByName('ket.md');
$products = $productsRepo->getProductList();
$ketRepo->addKzOrders();
$orders = $ketRepo->getMdOrders(KetModel::ORDER_UPDATE);

batch(function ($batchOrders) use ($curl, $credentials, $products, $ketRepo) {
    try {
        $data = array();
        $ordersOldInfo = array();
        foreach ($batchOrders as $order) {
            $data[] = array('id' => $order['ket_order_id']);
            $ordersOldInfo[$order['ket_order_id']] = $order;
        }

        $data = json_encode($data);
        $hash = KetModel::generateHash($data, $credentials['login']);
        $curl->setUrl(
            KetModel::ORDER_STATUS_URL
            . "uid={$credentials['login']}"
            . "&s={$credentials['password']}"
            . "&hash=$hash"
            . "&" . KetModel::getStatusFieldsAsUrlQuery()
        );
        $curl->setData($data);
        $result = json_decode($curl->sendRequest(), true);

        if (isset($result['success']) && $result['success'] == false) {
            throw new Exception("Кет: Ошибка при получении статусов. {$result['success']}");
        }

        foreach ($result as $ketOrderId => $ketData) {
            $orderId = $ordersOldInfo[$ketOrderId]['id'];
            $orderFullPrice = $ketData['total_price'];
            $orderDeliveryDate = $ketData['date_delivery'];
            $orderPayDate = $ketData['return_date'];

            $postNumber = $ketData['kz_code'];
            $newDopComplect = KetModel::generateGetLidsOfferString($ketData['offer'], $products);
            $orderDopComplect = $newDopComplect['dop_complect'];
            $orderCdCount = $newDopComplect['cd_count'];

            $orderStatus = $ordersOldInfo[$ketOrderId]['status'];
            $orderStatusLs = $ordersOldInfo[$ketOrderId]['status_ls'];
            $ketSendStatus = $ketData['send_status'];
            $ketStatusKz = $ketData['status_kz'];

            if ($ketData['kz_delivery'] == 'Почта') {
                if (array_key_exists($ketSendStatus, KetModel::$finalPostStatusMap)) {
                    if (isset(KetModel::$finalPostStatusMap[$ketSendStatus]['status'])) {
                        $orderStatus = KetModel::$finalPostStatusMap[$ketSendStatus]['status'];
                    }
                    if (isset(KetModel::$finalPostStatusMap[$ketSendStatus]['status_ls'])) {
                        $orderStatusLs = KetModel::$finalPostStatusMap[$ketSendStatus]['status_ls'];
                    }
                } else {
                    // Не обновлять "отправлен" + "в пути" у нас если есть ШПИ
                    // Ждем статусов оплачен/возврат по таким заказам
                    if (in_array($ketStatusKz, array(11))
                        && !empty($ordersOldInfo[$ketOrderId]['post_number'])
                    ) {
                        continue;
                    }

                    if (isset(KetModel::$postStatusMap[$ketStatusKz]['status'])) {
                        $orderStatus = KetModel::$postStatusMap[$ketStatusKz]['status'];
                    }
                    if (isset(KetModel::$postStatusMap[$ketStatusKz]['status_ls'])) {
                        $orderStatusLs = KetModel::$postStatusMap[$ketStatusKz]['status_ls'];
                    }
                }
            } else {
                if (array_key_exists($ketSendStatus, KetModel::$finalCourierStatusMap)) {
                    if (isset(KetModel::$finalCourierStatusMap[$ketSendStatus]['status'])) {
                        $orderStatus = KetModel::$finalCourierStatusMap[$ketSendStatus]['status'];
                    }
                    if (isset(KetModel::$finalCourierStatusMap[$ketSendStatus]['status_ls'])) {
                        $orderStatusLs = KetModel::$finalCourierStatusMap[$ketSendStatus]['status_ls'];
                    }
                } else {
                    if (isset(KetModel::$courierStatusMap[$ketStatusKz]['status'])) {
                        $orderStatus = KetModel::$courierStatusMap[$ketStatusKz]['status'];
                    }
                    if (isset(KetModel::$courierStatusMap[$ketStatusKz]['status_ls'])) {
                        $orderStatusLs = KetModel::$courierStatusMap[$ketStatusKz]['status_ls'];
                    }
                }
            }

            $ketRepo->updateOrder($orderId, $ketOrderId, KetModel::ORDER_UPDATE, $ketStatusKz, $orderPayDate);
            $ketRepo->updateGlOrder($orderId, $orderFullPrice, $orderDeliveryDate, $orderDopComplect, $orderCdCount, $orderStatus, $orderStatusLs, $postNumber);
        }
    } catch (Exception $e) {
        Logger::writeLog(
            $e->getMessage(),
            null,
            __FILE__,
            __LINE__
        );
    }
}, $orders);

