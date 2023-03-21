<?php

namespace App\Repository;

class Fulfillment extends AbstractRepository
{
    public function getUzbOrdersForFF()
    {
        $stmt = $this->db->query("
            SELECT
                o.id,
                o.name,
                o.phone,
                o.dop_complect,
                o.cd_count,
                   
                CASE WHEN o.city = 1253 
                    THEN ca.external_id 
                    ELSE nc.region_id 
                END AS c_area,
                   
                CONCAT_WS(',', nc.name, o.address) AS address,
                o.full_price
            FROM orders o
            INNER JOIN new_cyti nc ON o.city = nc.id
            LEFT JOIN new_cityarea ca ON o.cityarea = ca.id
            WHERE
                o.country_id = 422
                AND o.status = 1
                AND o.action_pay = 13
                AND o.dop_complect > ''
        ");

        return $this->fetchAll($stmt);
    }

    public function storeFfOrder($orderId, $ffOrderId = null, $status = null)
    {
        $stmt = $this->db->prepare('
            INSERT INTO fulfillment (order_id, ff_id, ff_status)
            VALUES (:order_id, :ff_id, :ff_status)
        ');

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':ff_id', $ffOrderId);
        $stmt->bindParam(':ff_status', $status);

        return $this->exec($stmt);
    }

    public function updateFfStatus($ffOrderId, $status)
    {
        $stmt = $this->db->prepare('
            UPDATE fulfillment 
            SET ff_status = :status_id
            WHERE ff_id = :ff_id
        ');

        $stmt->bindParam(':status_id', $status);
        $stmt->bindParam(':ff_id', $ffOrderId);

        return $this->exec($stmt);
    }

    public function getNotFinalOrders()
    {
        $stmt = $this->db->prepare("
            SELECT
                ff.order_id,
                ff.ff_id
            FROM fulfillment ff
            INNER JOIN orders o ON o.id = ff.order_id
            WHERE o.status NOT IN (4, 9)
        ");

        $stmt->bindParam(':status_id', $statusId);

        return $this->fetchAll($stmt);
    }
}
