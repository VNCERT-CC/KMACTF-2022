<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../connection.php");
require_once("../captcha.php");

//session_start();
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
if(isset($_POST['buy'])&&isset($_POST['ticket'])&&filter_input(INPUT_POST, 'ticket', FILTER_VALIDATE_INT)){
    $ticket = (int)filter_input(INPUT_POST, 'ticket', FILTER_VALIDATE_INT);
    if(10000>$ticket && $ticket>0){
        $sql = "SELECT * FROM win ORDER BY id DESC";
        $data=$conn->prepare($sql);
        $data->execute();
        $round=$data->fetch(PDO::FETCH_ASSOC);
        $round=$round['id']+1;
        $billid=uniqid($round.'kmactf',true);
        $sql = "insert into ticket (billid, number,round) values(:billid,:number,:round)";
        $data=$conn->prepare($sql);
        $data->execute([":billid"=>$billid,":number"=>$ticket,":round"=>$round]);
        $response = array(
            'success' => $billid,
            'error'=> 0
        );
        echo json_encode($response);
        http_response_code(200);
        die();
    }else{
        $response = array(
            'success'=>0,
            'error'=> 'Forbidden'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
}

if(isset($_POST['verify']) && isset($_POST['billid']) && isset($_POST['ticket'])){
    $username = $_SESSION['username'];
    $sql = "select * from users where username = :username";
    $data=$conn->prepare($sql);
    $data->execute([":username"=>$username]);
    $data=$data->fetch(PDO::FETCH_ASSOC);
    if($data['amount']<1000){
        $response = array(
            'success'=> 0,
            'error'=> 'Số dư không đủ'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }

    $ticket = (int)filter_input(INPUT_POST, 'ticket', FILTER_VALIDATE_INT);
    if(100000>$ticket && $ticket>0 && is_string($_POST['billid'])){
        $billid=$_POST['billid'];
        $sql = "select * from ticket where billid = :billid and number = :number ";
        $data=$conn->prepare($sql);
        $data->execute([":billid"=>$billid,":number"=>$ticket]);
        //$data=$data->fetch(PDO::FETCH_ASSOC)
        if($data->rowCount()<=0) {
            $response = array(
                'success'=>0,
                'result'=> 'Verify failed'
            );
            echo json_encode($response);
            http_response_code(403);
            die();
        }else{
            $_SESSION['verify'] = true;
            $_SESSION['billid'] = $billid;
            $response = array(
                'success'=> genCaptcha(),
                'error'=>0
            );
            echo json_encode($response);
            http_response_code(200);
            die();

        }
    }
    $response = array(
        'success'=>0,
        'result'=> 'Verify failed'
    );
    echo json_encode($response);
    http_response_code(403);
    die();
}

if(isset($_POST['excute']) && isset($_POST['billid']) && isset($_POST['captcha'])){
    if(!$_SESSION['verify'] || $_POST['billid'] != $_SESSION['billid']){
        $response = array(
            'success'=>0,
            'error'=> 'Transation is invalid'
        );
        echo json_encode($response);
        http_response_code(200);
        die();
    }
    if($_POST['captcha'] != $_SESSION['captcha'] ){
        $response = array(
            'success'=>0,
            'error'=> 'Captcha is invalid'
        );
        echo json_encode($response);
        http_response_code(200);
        die();
    }
    $_SESSION['captcha']="";
    $username = $_SESSION['username'];
    $sql = "select * from users where username = :username";
    $data=$conn->prepare($sql);
    $data->execute([":username"=>$username]);
    $data=$data->fetch(PDO::FETCH_ASSOC);
    $id_user=$data['id'];
    $amount_user=$data['amount'];
    if($data['amount']<1000){
        $response = array(
            'success'=>0,
            'error'=> 'Số dư không đủ'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    };
    
    $billid =$_POST['billid'];
    $sql = "select * from ticket where billid = :billid";
    $data=$conn->prepare($sql);
    $data->execute([":billid"=>$billid]);
    if($data->rowCount()==0){
        $response = array(
            'success'=>0,
            'error'=> 'BillId không tồn tại'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
    $data=$data->fetch(PDO::FETCH_ASSOC);
    $ticket_number=$data['number'];
    if($data['id_user'] == null){
        $_SESSION['verify'] = false;
        $_SESSION['billid'] = "";
        $sql = "UPDATE ticket SET id_user=:id_user WHERE billid=:billid";
        $data=$conn->prepare($sql);
        $data->execute([":billid"=>$billid,":id_user" => $id_user]);

        $amount_user = $amount_user -1000;
        $sql = "UPDATE `users` SET amount = :amount WHERE id = :id"; 
        $data=$conn->prepare($sql);
        $data->execute([":amount"=>$amount_user,":id" => $id_user]);

        $sql = "insert into history (username,ticket) values(:username,:ticket)";
        $data=$conn->prepare($sql);
        $data->execute([":username"=>$username,":ticket"=>$ticket_number]);

        $response = array(
            'success'=> 'Successful',
            'error'=>0
        );
        echo json_encode($response);
        http_response_code(200);
        die();
    }else{
        $response = array(
            'success'=>0,
            'error'=> 'Bill da co nguoi mua'
        );
        echo json_encode($response);
        http_response_code(403);
        die();
    }
}

if(isset($_GET['history'])){
    $username=$_SESSION['username'];
    $sql = "SELECT ticket FROM history WHERE username=:username ORDER BY id DESC ";
    $data=$conn->prepare($sql);
    $data->execute([":username"=>$username]);
    $data=$data->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    http_response_code(200);
    die();
}

$response = array(
    'success'=>0,
    'error'=> 'Unknown'
);
echo json_encode($response);
http_response_code(403);
die();
?>
