<?php


namespace App\Repository;


class Measoft extends AbstractRepository
{
    public function getOrdersForMeasoft()
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id,
                o.name,
                o.phone,
                o.zip,
                city.name as city_name,
                o.address,
                o.d_dost,
                o.full_price,
                o.dop_complect,
                o.comment,
                o.cd_count
            FROM orders o 
            INNER JOIN ket_city city ON city.id = o.city
            WHERE 
                o.action_pay = 14 
                AND o.status = 1
        ");

        return $this->fetchAll($stmt);
    }

    public function storeOrder($orderId, $measoftId)
    {
        $stmt =$this->db->prepare("
            INSERT INTO measoft_delivery (order_id, measoft_id) 
            VALUES (:order_id, :measoft_id)
        ");

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':measoft_id', $measoftId);

        return $this->exec($stmt);
    }
}