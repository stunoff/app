<?php

namespace App\Repository;

class AsteriskNotice extends AbstractRepository
{
    public function getNoticeCount($startDate, $endDate, $user_id)
    {
        $stmt = $this->db->prepare("
            SELECT
                   count(*) as total_notifications
            FROM asterisk_notice note
            INNER JOIN orders o ON o.id = note.order_id
            WHERE
                  DATE(note.notice_date) BETWEEN :start_date AND :end_date
                  AND note.operator = (SELECT login FROM new_user WHERE id = :user_id)
                  AND note.status = 0
        ");
    
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->bindParam(':user_id', $user_id);
        
        return $this->fetchField($stmt);
    }
    
    public function getNotice($startDate, $endDate, $user_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                note.id AS note_id,
                order_id,
                note.comment AS comment,
                created,
                notice_date,
                CASE WHEN note.order_id = 0 THEN order_complectation ELSE o.dop_complect END as order_complectation,
                CASE WHEN note.order_id = 0 THEN order_price ELSE o.full_price END as order_price,
                CASE WHEN note.order_id = 0 THEN customer_name ELSE o.name END as customer_name,
                CASE WHEN note.order_id = 0 THEN customer_phone ELSE o.phone END as customer_phone,
                notice_execution_date,
                operator,
                division,
                ns.name AS status_name
            FROM asterisk_notice note
                INNER JOIN new_status ns ON note.status = ns.id
                LEFT JOIN orders o ON note.order_id = o.id
            WHERE 
                  DATE(note.notice_date) BETWEEN :start_date AND :end_date
                  AND note.operator = (SELECT login FROM new_user WHERE id = :user_id)
                  AND note.status = 0
        ");
    
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->bindParam(':user_id', $user_id);
    
        return $this->fetchAll($stmt);
    }
}
