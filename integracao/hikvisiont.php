<?php
include('../db.php');
$p=$_REQUEST;

$sql="select id from centrais where condominio_id = '".$p['condominio_id']."' and tipocentral = 'facial'";
if ($_REQUEST['debug']=='1'){
    echo $sql."<br>";
   }
$qr=mysql_query($sql);
while($ln_central=mysql_fetch_assoc($qr)){
  $sql="insert into req_horario set central_id='".$ln_central['id']."',horario_id='".$p['id']."',idL='".$p['idL']."',condominio_id='".$p['condominio_id']."'";
  if ($_REQUEST['debug']=='1'){
    echo $sql."<br>";
   }
  $qr2=mysql_query($sql);
}
echo json_encode(1);

?>