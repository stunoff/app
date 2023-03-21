<?php

namespace App\Repository;

class Logs extends AbstractRepository
{
    public function storeError(
        $errorMessage,
        $orderId = null,
        $class = null,
        $fileLine = null
    )
    {
        $stmt = $this->db->prepare('
            INSERT IGNORE INTO logger (message, order_id, class, file_line) 
            VALUES (:error_message, :order_id, :class, :file_line)
        ');

        $stmt->bindParam(':error_message', $errorMessage);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':class', $class);
        $stmt->bindParam(':file_line', $fileLine);

        return $this->exec($stmt);
    }

    public function storeDeliveryComment($delivery_name, $order_id, $comment = '')
    {
        $stmt = $this->db->prepare('
            INSERT IGNORE INTO delivery_data (delivery_name, sent_comment, order_id) 
            VALUES (:delivery_name, :sent_comment, :order_id)
        ');

        $stmt->bindParam(':delivery_name', $delivery_name);
        $stmt->bindParam(':sent_comment', $comment);
        $stmt->bindParam(':order_id', $order_id);

        return $this->exec($stmt);
    }
}
