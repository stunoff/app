<?php

require_once __DIR__ . '/baseCron.php';

use App\Core\DB\DBInstance;

ini_set('max_execution_time', '0'); //300 seconds = 5 minutes
set_time_limit(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$db = DBInstance::getInstance()->getConnection();


$stmt = $db->prepare("
    SELECT id FROM new_user WHERE id NOT IN (378, 712)
");
$stmt->execute();
$userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

function generate_randompassword($passlength = 5){
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $passlength; $i++) {
        $n = mt_rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

foreach ($userIds as $userId) {
    $newPass = generate_randompassword();
    $md5 = md5($newPass);
    
    $stmt = $db->prepare("UPDATE new_user SET pass = '$md5', plain_pass = '$newPass' WHERE id = '$userId'");
    $stmt->execute();
}
