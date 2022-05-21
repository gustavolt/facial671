<?php
    include('../db.php');
    $ip = $_SERVER['SERVER_ADDR']; 
    $sql="delete FROM `req_facial` WHERE date(datahora) < date(now())";
    $qr=mysql_query($sql);
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
      $sql="select * from centrais where tipo = 'hikvision' and tipocentral = 'facial' and (servidor_local <> 'Sim')  and condominio_id in (select id from condominio where servidoracesso_id = '".$servidoracesso_id."' ".$sdep.") and (proxima_leitura = '0000-00-00 00:00:00' or proxima_leitura <= now())  order by id asc "; 
       if (SERVIDORLOCAL=='1'){ 
         $sql="select * from centrais where tipo = 'hikvision' and tipocentral = 'facial' and servidor_local = 'Sim'  and condominio_id in (".CONDOMINIO_ID.") and (proxima_leitura is null or proxima_leitura = '0000-00-00 00:00:00' or proxima_leitura <= now())  order by id asc "; 
       }
    
    $qr=mysql_query($sql);
    $ar=array();
    while ($ln=mysql_fetch_assoc($qr)){
        $sql="select * from req_facial where central_id = '".$ln['id']."'";
        $qr2=mysql_query($sql);
        $req=array();
        $xsp=array();
        while ($lnr=mysql_fetch_assoc($qr2)){
            //if($lnr['foto']!=''){
            //  $lnr['foto']=pegafoto($lnr['foto']);
            //}
            $req[]=$lnr;
            $xsp[]=$lnr['id'];
        }
        $ln['user']=$ln['usuario'];
        $ln['pass']=$ln['senha'];
        $ln['url']=$ln['ip'].":".$ln['porta'];
        if (SERVIDORLOCAL=='1'){ 
          $ln['url']="http://".$ln['iplocal'].":".$ln['porta']; 
          $sql="delete from req_facial where id in (".implode(',',$xsp).")";
          $qr22=mysql_query($sql);
        }
        $ln['tipo']=$ln['tipocad'];
        $ln['req']=$req;
        $ar[]=$ln;
       // $ar[]=$ln;
    }
    echo json_encode($ar); 
?>
