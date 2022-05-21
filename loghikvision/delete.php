<?php
include('../db.php');
$p = $_REQUEST;
$cont = json_encode($_REQUEST); 
$sql="insert into logtable set xlog = '".$cont."',tipo='excluir face'";
$qr=mysql_query($sql);
$sql="delete from log_hikvision where id_usuario in (".$_REQUEST['id_usuario'].") and central = '".$_REQUEST['central']."'";
$qr=mysql_query($sql);
echo json_encode(array("sucesso"=>true,"msg"=>"ok"));
?>
