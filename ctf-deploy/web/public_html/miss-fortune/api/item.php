<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../connection.php");
session_start();

if(isset($_POST['buy'])&&isset($_POST['item'])&&filter_input(INPUT_POST, 'item', FILTER_VALIDATE_INT)){
    $id_item = (int)filter_input(INPUT_POST, 'item', FILTER_VALIDATE_INT);
    if(!isset($_SESSION['username'])){
        $response = array(
            'success'=> 0,
            'error'=> 'Forbidden'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
    $username = $_SESSION['username'];
    $sql = "select * from users where username = :username";
    $data=$conn->prepare($sql);
    $data->execute([":username"=>$username]);
    $data=$data->fetch(PDO::FETCH_ASSOC);
    $amount_user =$data['amount'];


    $sql = "select * from item where id = :item";
    $data=$conn->prepare($sql);
    $data->execute([":item"=>$id_item]);
    
    if($data->rowCount()==0){
        $response = array(
            'success'=> 0,
            'error'=> 'Item is invalid'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
    $data=$data->fetch(PDO::FETCH_ASSOC);
    $item_price =$data['price'];
    if($amount_user<$item_price){
        $response = array(
            'success'=> 0,
            'error'=> 'Số dư không đủ'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
    $amount_user=$amount_user-$item_price;
    $sql = "UPDATE `users` SET amount = :amount WHERE username = :username"; 
    $data=$conn->prepare($sql);
    $data->execute([":amount"=>$amount_user,":username" => $username]);

    $sql = "insert into own (username, id_item) values(:username,:id_item)";
    $data=$conn->prepare($sql);
    $data->execute([":username"=>$username,":id_item"=>$id_item]);

    $response = array(
        'success'=> 'Mua hàng thành công',
        'error'=> 0
    );
    echo json_encode($response);
    http_response_code(200);
    die();
}

if(isset($_GET['myItem'])){
    if(!isset($_SESSION['username'])){
        $response = array(
            'success'=> 0,
            'error'=> 'Forbidden'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM own INNER JOIN item ON own.id_item = item.id where own.username = :username";
    $data=$conn->prepare($sql);
    $data->execute([":username"=>$username]);
    $data=$data->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    http_response_code(200);
    die();
}
$sql = "SELECT id,name,content,price,image FROM  item ";
$data=$conn->prepare($sql);
$data->execute();
$data=$data->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
http_response_code(200);
?>