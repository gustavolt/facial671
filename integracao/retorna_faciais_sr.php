<?php
    include('../db.php');
    $ip = $_SERVER['SERVER_ADDR']; 
    $sql="select * from servidoracesso where ip = '".$ip."' ";
   if ($_REQUEST['debug']=='1'){
      echo $sql."<br>";
   }
  
    $qr=mysql_query($sql);
    $ln=mysql_fetch_assoc($qr);
    $servidoracesso_id=$ln['id'];
    $sdep='';
    if ($servidoracesso_id == '1'){
        $sdep="or servidoracesso_id='0' or servidoracesso_id is null";
    }
      $sql="select * from centrais where tipo = 'hikvision' and tipocentral = 'facial'  and condominio_id in (select id from condominio where servidoracesso_id = '".$servidoracesso_id."' ".$sdep.") and (proxima_leitura > now())  order by id asc "; 
      
    
    $qr=mysql_query($sql);
    $ar=array();
    while ($ln=mysql_fetch_assoc($qr)){
        $sql="select * from req_facial where central_id = '".$ln['id']."'";
        $qr2=mysql_query($sql);
        $quant = mysql_num_rows($qr2); 
        $ln['qtreq']=$quant;
        $ar[]=$ln;
    } 
    echo json_encode($ar);
?>