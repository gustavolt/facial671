<?php
include('../db.php');
$p=$_REQUEST;

if ($p['pgo']=='1'){
  $sql="select * from centrais where tipo = 'hikvision' and tipocentral = 'facial'";
    $qr=mysql_query($sql);
    $ar=array();
    while ($ln=mysql_fetch_assoc($qr)){
      $sql="select * from req_horario where central_id = '".$ln['id']."'";
      $qr2=mysql_query($sql);
      $req=array();
      while ($lnr=mysql_fetch_assoc($qr2)){
        $req[]=$lnr;
      }
      $ln['user']=$ln['usuario'];
      $ln['pass']=$ln['senha'];
      $ln['url']=$ln['ip'].":".$ln['porta'];
      $ln['req']=$req;
        $ar[]=$ln;
    }
    echo json_encode($ar);
    exit();
  //verifica se tem grupos e dias a serem add
  
}
if (isset($p['idreq'])&&($p['idreq']!='')){
   $sql="delete from req_horario where id = '".$p['idreq']."'";
   $qr=mysql_query($sql);
   echo json_encode(1);
   exit();
}

?>