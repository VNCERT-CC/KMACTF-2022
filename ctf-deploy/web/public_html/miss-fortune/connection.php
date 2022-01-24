<?php
error_reporting(-1);
ini_set('display_errors', 1);

	//$con = mysqli_connect("localhost","root","","blog") or die("Can not connect to MySQL");
	//mysqli_set_charset($con,"UTF8");
	$conn = new PDO("mysql:host=localhost;dbname=default_db", "user", "user_password");
	$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 ?>
