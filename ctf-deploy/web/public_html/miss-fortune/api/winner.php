<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../connection.php");

$sql = "SELECT * FROM win ORDER BY id DESC";
$data=$conn->prepare($sql);
$data->execute();
$data=$data->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
http_response_code(200);
die();
?>