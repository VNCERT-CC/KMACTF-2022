<?php
try{
  $db = new mysqli('localhost', 'user', 'user_password', 'default_db');
  if ($db->connect_errno) {
    exit('mysqli connection error: ' . $db->connect_error);
  }
  mysqli_set_charset($db, 'utf8mb4');

  if (strlen($_GET['order']) >= 100 || !$_GET['order']) $_GET['order'] = 'id';

  $q = $db->query('select 1 from items where id = 1 order by ' . $_GET['order']);
  if (!$q) {
    exit('mysqli query error: ' . $db->error);
  }
  var_dump($q->fetch_assoc());
}catch(\Throwable $e){
  exit($e->getMessage());
}
?>
<h1>Try sqlmap</h1>
<h2>
  <a href="?order=id">?order=id</a>
</h2>
