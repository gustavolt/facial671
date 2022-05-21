<?php
include('../db.php');
$p = $_REQUEST;

$cont = json_encode($_REQUEST); 
$sql="insert into logtable set xlog = '".$cont."',tipo='excluir face'";
$qr=mysql_query($sql);
$sql="insert into log_hikvision set id_usuario ='".$p['id_usuario']."', datahora=now(), foto='".$p['foto']."', tipo='".$p['tipo']."', idacesso='".$p['idacesso']."', central='".$p['central']."', cartao='".$p['cartao']."', nome='".$p['nome']."', condominio_id='".$p['condominio_id']."' ";
$qr=mysql_query($sql);
echo json_encode(array("sucesso"=>true,"msg"=>"ok"));
?>