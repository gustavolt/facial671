<?php
  include('../db.php');
  $p=$_REQUEST;
  $sql="update centrais set proxima_leitura=now() where id ='".$p['id']."'";
  if (SERVIDORLOCAL != '1'){
     $qr=mysql_query($sql);
  }
  echo json_encode(1);
?>
