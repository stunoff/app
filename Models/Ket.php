<?php

namespace App\Models;

class Ket
{
    const ORDER_SENT = 1;
    const ORDER_UPDATE = 2;
    const ORDER_REMOVE = 3;
    const KZ_COUNTRY_CODE = 'kz';
    const ORDER_SEND_URL = "http://ketkz.com/api/send_order.php?uid=";
    const ORDER_REMOVE_URL = "http://ketkz.com/api/change_status.php?uid=";
    const ORDER_STATUS_URL = "http://ketkz.com/api/get_orders.php?";

    // заменяем один оффер на другой
    static $offerForReplace = array(
        298 => 372,
    );

    // эти поля посылаем в curl'e в кет
    static $statusFields = array(
        'id',
        'total_price',
        'kz_delivery',
        'send_status',
        'date_delivery',
        'status_kz',
        'offer',
        'kz_code',
        'return_date',
    );

    // поле status_kz из их апи
    static $postStatusMap = array(
        2  => array('status' => 0),
        11 => array('status' => 0, 'status_ls' => 15),
        21 => array('status' => 9, 'status_ls' => 20),
        22 => array('status' => 9, 'status_ls' => 41),
        23 => array('status' => 0, 'status_ls' => 14),
        4 => array('status' => 0, 'status_ls' => 14),
        6 => array('status' => 0, 'status_ls' => 14),
        8 => array('status' => 0, 'status_ls' => 14),
        14 => array('status' => 0, 'status_ls' => 29),
    );

    // поле send_status из их апи
    static $finalPostStatusMap = array(
        4 => array('status' => 9, 'status_ls' => 34),
        5 => array('status' => 4),
    );

    // поле status_kz из их апи
    static $courierStatusMap = array(
        0 => array('status' => 0, 'status_ls' => 13),
        1 => array('status' => 0, 'status_ls' => 18),
        2 => array('status' => 0, 'status_ls' => 19),
        4 => array('status' => 0, 'status_ls' => 14),
        6 => array('status' => 0, 'status_ls' => 14),
        8 => array('status' => 0, 'status_ls' => 14),
        11 => array('status' => 0, 'status_ls' => 15),
        14 => array('status' => 0, 'status_ls' => 29),
        15 => array('status' => 0, 'status_ls' => 46),
        16 => array('status' => 0, 'status_ls' => 31),
        17 => array('status' => 0, 'status_ls' => 21),
        23 => array('status' => 0, 'status_ls' => 14),
    );

    // поле send_status из их апи
    static $finalCourierStatusMap = array(
        4 => array('status' => 9, 'status_ls' => 41),
        5 => array('status' => 4),
    );

    public static function generateKetOfferString($dopComplect, $productList)
    {
        $offers = array();
        $productList = array_flip($productList);
        foreach ($dopComplect as $offer) {
            $offerId = $productList[$offer['product_name']];
            if (isset(self::$offerForReplace[$offerId])) {
                $offerId = self::$offerForReplace[$offerId];
            }

            $offers[] = "$offerId - {$offer['product_cnt']}";
        }

        return implode(',', $offers);
    }

    public static function generateGetLidsOfferString($dopComplect, $productList)
    {
        $ketOffers = !empty($dopComplect) ? explode(',', $dopComplect) : array();
        $totalCdCount = 0;
        $retDopComplect = array();
        $tmp = '';

        foreach ($ketOffers as $offer) {
            $offer = explode('-', $offer);
            $offerId = (int)$offer[0];
            $currOfferCount = (int)$offer[1];
            $totalCdCount += $currOfferCount;
            if (!isset($productList[$offerId])) {
                continue;
            }
            $offerName = $productList[$offerId];
            if (!empty($offerName) && !empty($currOfferCount)) {
                $tmp .= "$offerName($currOfferCount);";
            }
        }

        $retDopComplect['dop_complect'] = $tmp;
        $retDopComplect['cd_count'] = $totalCdCount;

        return $retDopComplect;
    }

    public static function generateHash($orderData, $uid)
    {
        $hash_str = strlen($orderData) . md5($uid);
        return hash("sha256", $hash_str);
    }

    public static function getStatusFieldsAsUrlQuery()
    {
        return 'fields=' . implode(',', self::$statusFields);
    }
}
