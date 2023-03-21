<?php

namespace App\Filters;

class OrderFilter
{
    public static function hidePhonesByUserTip(array $orders, $user)
    {
        $hideForGroupsId = array(7, 8,20);

        if (is_array($orders)) {
            foreach ($orders as &$order) {
                if (in_array($user['tip'], $hideForGroupsId)) {
                    $order['phone'] = '***';
                }
            }
        } else {
            if (in_array($user['tip'], $hideForGroupsId)) {
                $order['phone'] = '***';
            }
        }

        return $orders;
    }
}
