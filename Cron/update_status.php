<?php

require_once __DIR__ . '/baseCron.php';

use App\Core\DB\DBInstance;
ini_set('max_execution_time', '0'); //300 seconds = 5 minutes
set_time_limit(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$db = DBInstance::getInstance()->getConnection();


$stmt = $db->prepare("
    SELECT DISTINCT o.pp_user_id, date(o.post_date) as date
    FROM orders o
    WHERE 1
      AND o.country_id IN (29, 189, 229, 248, 417, 418, 424, 434, 433, 435, 438, 439, 440)
      and o.status = 6
      AND o.status_ls = 21
  and date(o.post_date) <= '2021-09-07'
  order by o.post_date
");
$stmt->execute();
$ppusersIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $db->prepare("
    SELECT DISTINCT date(o.post_date) as date
    FROM orders o
    WHERE 1
      AND o.country_id IN (29, 189, 229, 248, 417, 418, 424, 434, 433, 435, 438, 439, 440)
      and o.status = 6
      AND o.status_ls = 21
  and date(o.post_date) <= '2021-09-07'
  order by o.post_date
");
$stmt->execute();
$days = $stmt->fetchAll(PDO::FETCH_COLUMN);


$totalUpdated = 0;
foreach ($days as $day) {
    $statusChange = 0;
    foreach ($ppusersIds as $ppusersId) {

        $stmt = $db->prepare("
            SELECT 
                o.id
            FROM orders o
            WHERE 1
              AND o.country_id IN (29, 189, 229, 248, 417, 418, 424, 434, 433, 435, 438, 439, 440)
              and o.status = 6
              and date(o.post_date) = '{$day}'
              and pp_user_id = $ppusersId
            ;
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty(count($orders))) continue;

        $chunkSize = round(count($orders) / 2);

        $statuses = [
            3, //отменен(z)
            12, //Некорректный(z)
        ];

        batch(function($batch) use ($db, &$statusChange, $statuses, &$totalUpdated) {
            $whereOrders = "id IN (" . implode(',', $batch) . ")";
            $status = $statuses[$statusChange % 2];

            if ($status == 3) {
                $statusLs = 21;
            } else {
                $statusLs = 37;
            }

            $innerStmt = $db->prepare("
                UPDATE orders SET status = $status, status_ls = $statusLs
                WHERE $whereOrders
            ");
            $innerStmt->execute();

            $statusChange++;
            $totalUpdated++;
        }, $orders, $chunkSize);
    }
}

echo "<pre>";
echo $totalUpdated;
echo "</pre>";