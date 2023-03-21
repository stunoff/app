<?php

namespace App\Repository;

use App\Service\Logger;

class Orders extends AbstractRepository
{
    public function updateOrderStatus($orderId, $status = null, $statusLs = null)
    {
        if (empty($orderId)) {
            Logger::writeLog(
                'orderId is empty',
                null,
                __CLASS__,
                __LINE__
            );
            return false;
        }

        if (!isset($status) && !isset($statusLs)) {
            Logger::writeLog(
                'status and status ls not set',
                null,
                __CLASS__,
                __LINE__
            );
            return false;
        }

        $query = sprintf('
            UPDATE orders o
            SET 
                %s%s
                %s
            WHERE o.id = :order_id
        ',
        isset($status) ? 'o.status = :status_id' : '',
        isset($status) && isset($statusLs) ? ',' : '',
        isset($statusLs) ? 'o.status_ls = :status_ls' : ''
        );

        $stmt = $this->db->prepare($query);
        isset($status) ? $stmt->bindParam(':status_id', $status) : null;
        isset($statusLs) ? $stmt->bindParam(':status_ls', $statusLs) : null;
        $stmt->bindParam(':order_id', $orderId);

        return $this->exec($stmt);
    }

    public function exportPage($dateStart, $dateEnd, $actionPay, $userGroup, $departments)
    {
        $actionPay = !empty($actionPay) ? implode(',', $actionPay) : null;
        $userGroup = !empty($userGroup) ? implode(',', $userGroup) : null;
        $departments = !empty($departments) ? implode(',', $departments) : null;

        $query = sprintf('
            SELECT o.post_date, o.id, ns.name, o.full_price, pp_user_id, nu.login, d.name as d_name, 
                   o.d_dost, o.phone, o.name as client_name, dop_complect
            FROM orders o 
                INNER JOIN new_user nu ON o.final_user = nu.id
                INNER JOIN new_status ns ON o.status = ns.id
                INNER JOIN new_dostavka d ON  o.action_pay = d.id
                LEFT JOIN new_user_groups nug ON nug.id = nu.user_group
            WHERE 1
            %s 
            %s 
            %s
            %s
            %s
            ORDER BY o.post_date',
            isset($dateStart) ? 'AND DATE(o.post_date) >= :dateStart' : '',
            isset($dateEnd) ? 'AND DATE(o.post_date) <= :dateEnd' : '',
            !empty($actionPay) ? "AND o.action_pay IN ($actionPay)" : '',
            !empty($userGroup) ? "AND nu.user_group IN ($userGroup)" : '',
            !empty($departments) ? "AND nug.department_id IN ($departments)" : ''
        );
        
        $stmt = $this->db->prepare($query);
        isset($dateStart) ? $stmt->bindParam(':dateStart', $dateStart) : null;
        isset($dateEnd) ? $stmt->bindParam(':dateEnd', $dateEnd) : null;

        return $this->fetchAll($stmt);
    }

    public function exportPageStats($dateStart, $dateEnd, $actionPay, $userGroup, $departments)
    {
        $actionPay = !empty($actionPay) ? implode(',', $actionPay) : null;
        $userGroup = !empty($userGroup) ? implode(',', $userGroup) : null;
        $departments = !empty($departments) ? implode(',', $departments) : null;

        $query = sprintf('
            SELECT 
                SUM(CASE WHEN o.status = 1 THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN o.status = 3 THEN 1 ELSE 0 END) as canceled,
                SUM(CASE WHEN o.status = 0 THEN 1 ELSE 0 END) as send,
                SUM(CASE WHEN o.status = 4 THEN 1 ELSE 0 END) as payed,
                SUM(CASE WHEN o.status = 9 THEN 1 ELSE 0 END) as returned,
                SUM(CASE WHEN o.status = 12 THEN 1 ELSE 0 END) as incorrect
            FROM orders o 
                INNER JOIN new_user nu ON o.final_user = nu.id
                INNER JOIN new_status ns ON o.status = ns.id
                INNER JOIN new_dostavka d ON  o.action_pay = d.id
                LEFT JOIN new_user_groups nug ON nug.id = nu.user_group
            WHERE 1
            %s 
            %s 
            %s
            %s
            %s
            ',
            isset($dateStart) ? 'AND DATE(o.post_date) >= :dateStart' : '',
            isset($dateEnd) ? 'AND DATE(o.post_date) <= :dateEnd' : '',
            !empty($actionPay) ? "AND o.action_pay IN ($actionPay)" : '',
            !empty($userGroup) ? "AND nu.user_group IN ($userGroup)" : '',
            !empty($departments) ? "AND nug.department_id IN ($departments)" : ''
        );

        $stmt = $this->db->prepare($query);
        isset($dateStart) ? $stmt->bindParam(':dateStart', $dateStart) : null;
        isset($dateEnd) ? $stmt->bindParam(':dateEnd', $dateEnd) : null;

        return $this->fetchOne($stmt);
    }

    /**
     * @param $maxPostDate //will be current date if empty
     * @param  int  $status
     * @param  int  $statusLs
     */
    public function getPPUserIDDatesByStatusAndPostDate($maxPostDate)
    {
        $query = "
            SELECT DISTINCT o.pp_user_id, date(o.post_date) as date
            FROM orders o
            WHERE 1
                AND o.country_id IN (29, 189, 229, 248, 417, 418, 424, 434, 433, 435, 438, 439, 440)
                AND o.status = '6'
                AND o.status_ls = '21'
                AND date(o.post_date) <= :maxPostDate
            ORDER BY o.post_date
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':maxPostDate', $maxPostDate);

        return $this->fetchColumn($stmt);
    }

    /**
     * @param $maxPostDate //will be current date if empty
     * @param  int  $status
     * @param  int  $statusLs
     */
    public function getDatesByStatusAndPostDate($maxPostDate)
    {
        $query = "
            SELECT date(o.post_date) as date
            FROM orders o
            WHERE 1
                AND o.country_id IN (29, 189, 229, 248, 417, 418, 424, 434, 433, 435, 438, 439, 440)
                and o.status = '6'
                AND o.status_ls = '21'
            AND date(o.post_date) <= :maxPostDate
            ORDER BY o.post_date
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':maxPostDate', $maxPostDate);

        return $this->fetchColumn($stmt);
    }

    public function getOrdersForStatusExchange($day, $ppUsersId)
    {
        $query = "
            SELECT 
                o.id
            FROM orders o
            WHERE 1
                AND o.country_id IN (29, 189, 229, 248, 417, 418, 424, 434, 433, 435, 438, 439, 440)
                AND o.status = 6
                AND o.status_ls = 21
                AND date(o.post_date) = :day
            AND pp_user_id = :ppUsersId
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':day', $day);
        $stmt->bindParam(':ppUsersId', $ppUsersId);

        return $this->fetchColumn($stmt);
    }

    public function updateStatusExchange($status, $statusLs, $whereOrders)
    {
        $query = "
            UPDATE orders SET status = $status, status_ls = $statusLs
            WHERE id IN $whereOrders
        ";

        $stmt = $this->db->prepare($query);
        $this->exec($stmt);

        $query = "
            DELETE FROM tbl_order_status_history
            WHERE order_id IN $whereOrders AND status_id = $status AND DATE(date) = CURDATE()
        ";
        $stmt = $this->db->prepare($query);
        $this->exec($stmt);
    }

    public function CDCountStat($date, $statusOper, $statusLogist, $ppUserIds)
    {
        $query = "
            SELECT
                o.pp_user_id,
                SUM(CASE WHEN o.cd_count = 2 THEN 1 ELSE 0 END) AS cnt_2,
                SUM(CASE WHEN o.cd_count = 4 THEN 1 ELSE 0 END) AS cnt_4,
                SUM(CASE WHEN o.cd_count = 6 THEN 1 ELSE 0 END) AS cnt_6
            FROM orders o
            WHERE date(o.post_date) = '{$date}' 
        ";

        if (!empty($statusOper)) {
            $statusOper = implode(',', $statusOper);
            $query .= " AND o.status IN ({$statusOper})";
        }

        if (!empty($statusLogist)) {
            $statusLogist = implode(',', $statusLogist);
            $query .= " AND o.status_ls IN ($statusLogist)";
        }

        if (!empty($ppUserIds)) {
            $query .= " AND o.pp_user_id NOT IN ({$ppUserIds})";
        }

        $query .= "
            GROUP BY o.pp_user_id
            HAVING cnt_2 > 0 OR cnt_4 > 0 OR cnt_6
        ";
        
        $stmt = $this->db->prepare($query);

        return $this->fetchAll($stmt);
    }

    public function offerByAge(
        $sites,
        $date,
        $ppUserIds,
        $countries,
        $ages,
        $sex
    ) {
        if (empty($sites)) {
            return array();
        }

        if (is_array($sites)) {
            $sitesSql = implode(',', $sites);
        } else {
            $sitesSql = $sites;
        }
        
        $dateSql = '';
        if (!empty($date)) $dateSql = "AND DATE(o.post_date) = '$date'";

        $ppUserIdsSql = '';
        if (!empty($ppUserIds)) {
            $ppUserIds = trim($ppUserIds, ',');
            $ppUserIdsSql = "AND o.pp_user_id IN ($ppUserIds)";
        }

        $countriesSql = '';
        if (!empty($countries)) {
            $countries = implode(',', $countries);
            $countriesSql = "AND o.country_id IN ($countries)";
        }

        $sexSql = '';
        if (!empty($sex)) $sexSql = "AND oad.user_gender = '$sex'";

        if(empty($ages) || (count($ages) == 1 && $ages[0] == 0)) {
            $sql = $this->db->prepare("SELECT * FROM age_list");
        } else {
            $ages = implode(',', $ages);
            $sql = $this->db->prepare("SELECT * FROM age_list WHERE id IN ($ages)");
        }
        $agesList = $this->fetchAll($sql);
        
        $outData = array();
        foreach ($agesList as $age) {
            $ageAlias = "'{$age['label']}' AS age";
            if ($age['id'] != 8) {
                $whereBirth = "
                    AND
                        (TIMESTAMPDIFF(YEAR, oad.user_birth, CURDATE()) > {$age['min_age']}
                            AND TIMESTAMPDIFF(YEAR, oad.user_birth, CURDATE()) <= {$age['max_age']}
                        )
                ";
            } elseif ($age['id'] == 8) {
                $whereBirth = "
                    AND
                        (TIMESTAMPDIFF(YEAR, oad.user_birth, CURDATE()) <= {$age['min_age']}
                            AND TIMESTAMPDIFF(YEAR, oad.user_birth, CURDATE()) > {$age['max_age']}
                        )
                ";
            } else {
                $whereBirth = "AND oad.user_birth IS NULL";

                $ageAlias = "'Не присвоен' AS age";
            }


            $sql = "
                SELECT
                    s.domain,
                       
                    $ageAlias,
    
                    COUNT(o.id) AS count,
                
                    SUM(CASE WHEN o.status IN (0, 1, 9, 4)
                                 THEN 1
                             ELSE 0 END)                                           AS approve_confirmed,
                
                    SUM(CASE WHEN o.status IN (3)
                                 THEN 1
                             ELSE 0 END)                                           AS reject,
                
                
                    ROUND(AVG(CASE WHEN o.status IN (0, 1, 9, 4)
                                       THEN o.full_price END), 2)                              AS average_ticket,
                
                
                    ROUND(((SUM(CASE WHEN o.status IN (0, 1, 9, 4) THEN 1 ELSE 0 END)
                        / (SUM(CASE WHEN o.status IN (0, 1, 9, 4, 3) THEN 1 ELSE 0 END)))
                        * 100), 2)                                                              AS approve,
                
                
                    SUM(CASE WHEN o.status IN (4, 8) AND o.action_pay IN (1,3, 15)
                                 THEN 1
                             ELSE 0 END)                                           AS sum_buyout_post,
                
                    ROUND((SUM(CASE WHEN o.action_pay IN (1,3, 15)
                                 THEN 1
                             ELSE 0 END) / COUNT(o.id) * 100), 2)                  AS percent_post,
                
                    SUM(CASE WHEN o.status IN (4, 8) AND o.action_pay IN (2,4, 15)
                                 THEN 1
                             ELSE 0 END)                                           AS sum_buyout_courier,
                
                    ROUND((SUM(CASE WHEN o.action_pay IN (2,4, 15)
                                        THEN 1
                                    ELSE 0 END) / COUNT(o.id) * 100), 2)           AS percent_courier,
                
                    SUM(CASE WHEN o.status IN (4, 8)
                                 THEN 1
                             ELSE 0 END)                                           AS total_paid,
                
                    ROUND((SUM((CASE
                                    WHEN o.status IN (4)
                                        THEN 1
                                    ELSE 0 END)) / SUM(CASE WHEN o.status IN (0, 1, 9, 4)
                                                                THEN 1
                                                            ELSE 0 END) * 100), 2)        buyout
                FROM orders o
                    INNER JOIN sites s ON o.site_id = s.id
                    LEFT JOIN orders_additional_data oad on o.id = oad.order_id
                WHERE 1
                    AND site_id IN ($sitesSql)
                     $whereBirth
                    $dateSql
                    $ppUserIdsSql
                    $countriesSql
                    $sexSql
                GROUP BY domain
                ORDER BY domain
            ";
            
            $stmt = $this->db->prepare($sql);
            $data = $this->fetchOne($stmt);
            if ($data) $outData[] = $data;

        }
        
        return $outData;
    }
}
