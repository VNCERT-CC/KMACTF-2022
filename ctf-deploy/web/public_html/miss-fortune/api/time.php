<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../connection.php");

$sql = "SELECT time FROM win ORDER BY id DESC";
$data=$conn->prepare($sql);
$data->execute();
if($data->rowCount()<=0){
    $response = array(
        'success'=> -1,
        'error'=> 0
    );
    echo json_encode($response);
    http_response_code(200);
    //echo json_encode($data);
    //http_response_code(200);
    die();
}
$data=$data->fetch(PDO::FETCH_ASSOC);
$date = new DateTime($data['time']);
$d = $date->getTimestamp();
$date2= new DateTime();
$d2= $date2->getTimestamp();

$d3= $d + 300 -$d2;
if($d3<0){
    $d3=0;
}
$response = array(
    'success'=> $d3,
    'error'=> 0
);
echo json_encode($response);
http_response_code(200);
//echo json_encode($data);
//http_response_code(200);
die();
?>