<?php

namespace App\Repository;

class LogsRepository extends AbstractRepository
{
    public function getLogs($orderId = '', $phone = '', $customerName = '')
    {
        $this->db->query("SET @lag = 0");

        $query = sprintf('
            SELECT
                    new_user.login,
                    logs.id_item AS order_id,
                    logs.full_price,
                    logs.data AS date,
                    logs.name,
                    logs.phone,
                    logs.comment,
                    ns.name AS status,
                    logs.dop_complect,
                    logs.d_dost,
                    logs.region,
                    logs.zip,
                    logs.delivery_comment,
                    CASE
                        WHEN logs.dostavka = 4 THEN kc2.name
                        ELSE city.name
                    END AS city,
                    @lag lag_coll,
                    @lag:=coll cur_coll,
                    dost.name as dost_name,
                    pt.name AS payment_type
                FROM log_new logs
                LEFT JOIN new_user ON logs.user = new_user.id
                LEFT JOIN new_cyti city ON city.id = logs.city
                LEFT JOIN new_dostavka dost ON logs.dostavka = dost.id
                LEFT JOIN new_status ns ON ns.id = logs.status
                LEFT JOIN orders_additional_data oad ON logs.id_item = oad.order_id
                LEFT JOIN ket_city kc ON oad.ket_city = kc.id
                LEFT JOIN ket_city kc2 ON kc2.id = logs.ket_city_id
                LEFT JOIN payment_type pt ON logs.payment_type = pt.id
                WHERE %s %s %s',

        !empty($orderId) ? 'logs.id_item = :order_id' : '',
        !empty($phone) ? 'AND logs.phone = :phone' : '',
        !empty($customerName) ? 'AND logs.name = :customer_name' : ''
        );

        $stmt = $this->db->prepare($query);

        !empty($orderId) ?  $stmt->bindValue(':order_id', $orderId) : null;
        !empty($phone) ? $stmt->bindValue(':phone', $phone) : null;
        !empty($customerName) ? $stmt->bindValue(':customer_name', $customerName) : null;

        return $this->fetchAll($stmt);
    }
}
