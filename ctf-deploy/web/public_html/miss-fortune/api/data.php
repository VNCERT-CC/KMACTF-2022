<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../connection.php");
session_start();

if(!isset($_SESSION['username'])){
    //$response = array();
    $response = array(
        'success'=>0,
        'error'=> 'Forbidden'
    );
    echo json_encode($response);
    http_response_code(403);
    die();
}
$username = $_SESSION['username'];
$sql = "SELECT username,amount FROM users WHERE username= :username";
$data=$conn->prepare($sql);
$data->execute([":username"=>$username]);
$data=$data->fetch(PDO::FETCH_ASSOC);
$response = array(
    'success'=> $data,
    'error'=> 0
);
echo json_encode($response);
http_response_code(200);
//echo json_encode($data);
//http_response_code(200);
die();
?>