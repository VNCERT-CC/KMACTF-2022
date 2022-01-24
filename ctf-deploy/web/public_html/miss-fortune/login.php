<?php
require_once("connection.php");
// require_once("captcha.php");
session_start();
?>
<?php
$errors="";
if(isset($_POST['login'])){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $username = strip_tags($username);
    $username = addslashes($username);
    $password = strip_tags($password);
    $password = addslashes($password);
    
    if ($username == "" || $password =="" || strlen($username)>100 || strlen($password)>100) {
        $errors = "Username hoặc Password không đúng";
    }else{
        $sql = "select * from users where username = :username and password = :password ";
        $data=$conn->prepare($sql);
        $data->execute([":username"=>$username,":password"=>$password]);
        //$data=$data->fetch(PDO::FETCH_ASSOC)

        if($data->rowCount()<=0) {
            $errors = "Tên đăng nhập hoặc mật khẩu không đúng !";
        }else{
            $_SESSION['username'] = $username;
            $_SESSION['verify'] = false;
            header('Location: index.php');
        }
    }
}else if(isset($_POST['register'])){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $username = strip_tags($username);
    $username = addslashes($username);
    $password = strip_tags($password);
    $password = addslashes($password);
    if(strlen($username)>100 || strlen($password)>100){
      $errors = "Username hoặc password vượt quá độ dài";
    }else if ($username == "" || $password =="") {
      $errors = "Username hoặc Password không đúng";
    }else {
      $sql = "select * from users where username = :username";
      $data=$conn->prepare($sql);
      $data->execute([":username"=>$username]);
      if($data->rowCount()==0){
        $sql = "insert into users (username, password) values(:username,:password)";
        $data=$conn->prepare($sql);
        $data->execute([":username"=>$username,":password"=>$password]);
        
        $_SESSION['username'] = $username;
        $_SESSION['verify'] = false;
        header('Location: index.php');
      }else{
        $errors = 'Username đã được đăng kí';
      }
    }
    
} ;
?>
<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>KMA CTF 2022</title>
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'><link rel="stylesheet" href="/stylesheet/login-style.css">

</head>
<body>
<!-- partial:index.partial.html -->
<div class="overlay">
<!-- LOGN IN FORM by Omar Dsoky -->
<form method="POST" action="login.php">
   <!--   con = Container  for items in the form-->
   <div class="con">
   <!--     Start  header Content  -->
   <header class="head-form">
      <h2>Log In</h2>
      <!--     A welcome message or an explanation of the login form -->
      <p><?php if($errors != ""){ echo $errors; } else{echo "login here using your username and password";}?></p>
   </header>
   <!--     End  header Content  -->
   <br>
   <div class="field-set">
     
      <!--   user name -->
         <span class="input-item">
           <i class="fa fa-user-circle"></i>
         </span>
        <!--   user name Input-->
         <input name="username" class="form-input" id="txt-input" type="text" placeholder="UserName" required>
     
      <br>
     
           <!--   Password -->
     
      <span class="input-item">
        <i class="fa fa-key"></i>
       </span>
      <!--   Password Input-->
      <input class="form-input" type="password" placeholder="Password" id="pwd"  name="password" required>
     
<!--      Show/hide password  -->
     <span>
        <i class="fa fa-eye" aria-hidden="true"  type="button" id="eye"></i>
     </span>
     
     
      <br>
      

<!--        buttons -->
<!--      button LogIn -->
      <button  type="submit" name="login" class="log-in"> Log In </button>
   </div>
  
<!--   other buttons -->
   <div class="other">
<!--      Forgot Password button-->

      <button class="btn submits frgt-pass">Forgot Password</button>
<!--     Sign Up button -->
      <button type="submit" name="register"  class="btn submits sign-up">Sign Up 
<!--         Sign Up font icon -->
      <i class="fa fa-user-plus" aria-hidden="true"></i>
      </button>
<!--      End Other the Division -->
   </div>
     
<!--   End Conrainer  -->
  </div>
  
  <!-- End Form -->
</form>
</div>
<!-- partial -->
  <script  src="./login-script.js"></script>

</body>
</html>
