<?php

namespace App\Repository;

class Ket extends AbstractRepository
{
    public function addKzOrders()
    {
        $stmt = $this->db->query('
            INSERT INTO ket_orders (order_id, send) 
            SELECT o.id, 1
            FROM orders o
            WHERE 
                o.status = 1
                AND o.status_ls NOT IN (41)
                AND o.country_id = 248
                AND o.action_pay IN (3, 4) 
                AND o.id NOT IN (SELECT order_id FROM ket_orders)
        ');

        $this->exec($stmt);

        $stmt = $this->db->query("
        INSERT INTO ket_orders (order_id, send) 
        select o.id, 1
        from orders o
        where 
            o.status = 0
            and o.id not in (
                select order_id
                from ket_orders
            )
            and date(o.post_date) > '2019-11-01'
            and o.action_pay in (3, 4)
            and o.country_id = 248
        ");

        return $this->exec($stmt);
    }

    public function getKzOrders($kzStatus = 1)
    {
        $stmt = $this->db->prepare("
            SELECT
                o.id,
                o.phone,
                o.phone2,
                o.full_price,
                o.name,
                o.zip,
                o.region,
                o.address,
                o.d_dost,
                o.deliveryComment,
                o.pp_user_id as web_id,
                o.company_id as pp_id,
                o.dop_complect,
                o.status,
                o.status_ls,
                o.post_number,
                ket_orders.send,
                o.pp_user_id,
                   
                CASE
                    WHEN o.action_pay = 3 THEN 32
                    WHEN o.action_pay = 4 THEN oadd.ket_city
                    ELSE 32
                END AS post_type,
                   
                CASE
                    WHEN 
                        o.action_pay = 4 
                        AND status_ls = 53 
                        AND DATE(d_dost) = CURDATE() 
                    THEN 1
                    ELSE 0
                END AS is_dvd,
                   
                ket_orders.ket_order_id,
                ket_orders.prev_ket_status AS `prev_ket_status`,
                np.id AS product_id,
                nu.login as operator_name
            FROM ket_orders
                LEFT JOIN orders o ON o.id = ket_orders.order_id
                LEFT JOIN orders_additional_data oadd ON o.id = oadd.order_id
                LEFT JOIN new_product np ON np.site_id = o.site_id
                LEFT JOIN new_user nu ON o.final_user = nu.id
            WHERE 
                send IN (:kz_status)
                AND o.status NOT IN (3,12,4,9)
                AND o.status_ls NOT IN (41)
                AND o.dop_complect > ''
                AND o.action_pay IN (3,4)
                AND o.country_id = 248
                AND date(o.post_date) > '2021-01-01'
                AND date (o.d_dost) = DATE_ADD(CURDATE() , INTERVAL 2 DAY)
        ");

        $stmt->bindParam(':kz_status', $kzStatus);

        return $this->fetchAll($stmt);
    }

    public function getMdOrders($kzStatus = 1)
    {
        $stmt = $this->db->prepare("
            SELECT
                o.id,
                o.phone,
                o.phone2,
                o.full_price,
                o.name,
                o.zip,
                o.region,
                o.address,
                o.d_dost,
                o.deliveryComment,
                o.pp_user_id as web_id,
                o.company_id as pp_id,
                o.dop_complect,
                o.status,
                o.status_ls,
                o.post_number,
                ket_orders.send,
                   
                CASE
                    WHEN o.action_pay = 3 THEN 32
                    WHEN o.action_pay = 4 THEN oadd.ket_city
                    ELSE 32
                END AS post_type,
                   
                CASE
                    WHEN 
                        o.action_pay = 4 
                        AND status_ls = 53 
                        AND DATE(d_dost) = CURDATE() 
                    THEN 1
                    ELSE 0
                END AS is_dvd,
                   
                ket_orders.ket_order_id,
                ket_orders.prev_ket_status AS `prev_ket_status`,
                np.id AS product_id,
                nu.login as operator_name
            FROM ket_orders
                LEFT JOIN orders o ON o.id = ket_orders.order_id
                LEFT JOIN orders_additional_data oadd ON o.id = oadd.order_id
                LEFT JOIN new_product np ON np.site_id = o.site_id
                LEFT JOIN new_user nu ON o.final_user = nu.id
            WHERE 
                send IN (:kz_status)
                AND o.status NOT IN (3,12,4,9)
                AND o.status_ls NOT IN (41)
                AND o.action_pay IN (3,4)
                AND o.dop_complect > ''
                AND o.country_id = 438
        ");

        $stmt->bindParam(':kz_status', $kzStatus);

        return $this->fetchAll($stmt);
    }

    public function updateOrder($orderId, $ketOrderId, $send, $statusKz = 0, $payDate = '0000-00-00 00:00:00')
    {
        $stmt = $this->db->prepare('
            UPDATE ket_orders
            SET 
                ket_order_id = :ket_order_id, 
                send = :send, 
                prev_ket_status = :status_kz, 
                pay_date = :pay_date
            WHERE order_id = :order_id
        ');

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':ket_order_id', $ketOrderId);
        $stmt->bindParam(':send', $send);
        $stmt->bindParam(':status_kz', $statusKz);
        $stmt->bindParam(':pay_date', $payDate);

        return $stmt->execute();
    }

    public function updateGlOrder($orderId, $fullPrice, $deliveryDate, $dopComplect, $cdCount, $status, $statusLs, $postNumber)
    {
        $stmt = $this->db->prepare('
            UPDATE orders
            SET 
                full_price = :full_price,
                d_dost = :delivery_date,
                dop_complect = :dop_complect,
                cd_count = :cd_count,
                status = :status,
                status_ls = :status_ls,
                post_number = :post_number
            WHERE id = :order_id
        ');

        $stmt->bindParam(':full_price', $fullPrice);
        $stmt->bindParam(':delivery_date', $deliveryDate);
        $stmt->bindParam(':dop_complect', $dopComplect);
        $stmt->bindParam(':cd_count', $cdCount);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':status_ls', $statusLs);
        $stmt->bindParam(':post_number', $postNumber);

        return $stmt->execute();
    }

    /**
     * @param $orderId
     * @param $statusKz
     * @param $sendStatus
     * @param $glStatus
     * @param $glStatusLs
     * @param string $message
     * @return bool
     */
    public function writeLog($orderId, $statusKz, $sendStatus, $glStatus, $glStatusLs, $message = '')
    {
        $stmt = $this->db->prepare('
            INSERT INTO ket_status_log (order_id, status_kz, send_status, gl_status, gl_status_ls, message)
            VALUES (:order_id, :status_kz, :send_status, :gl_status, :gl_status_ls, :message)
        ');

        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':status_kz', $statusKz);
        $stmt->bindParam(':send_status', $sendStatus);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':gl_status', $glStatus);
        $stmt->bindParam(':gl_status_ls', $glStatusLs);

        return $this->exec($stmt);
    }
}
