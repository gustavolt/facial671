<?php
include('../db.php');
$p=$_REQUEST;

  $arrayips = ['161.35.127.156','142.93.54.147','159.89.53.222','206.189.229.247','104.248.224.109'];
  $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
  $indice = intval(array_search($ip,$arrayips));
  $pg = $indice*25; 
  $pgf = $pg+24;
  if ($pg > 0){
    $pg = $pg-1;
  }
  if ($indice >= sizeof($arrayips)-1){
    $pgf=$pgf+20;
  }
  $sqldep = " limit ".$pg.",".$pgf;
  

if ($p['pgo']=='1'){
	$sql="select * from centrais where tipo = 'hikvision' and tipocentral = 'facial' and conexao <> 'Local' order by id asc ".$sqldep;
  if (SERVIDOR_LOCAL){ 
    $sql="select * from centrais where tipo = 'hikvision' and tipocentral = 'facial' and conexao = 'Local' order by id asc ".$sqldep;
  }
    $qr=mysql_query($sql);
    $ar=array();
    while ($ln=mysql_fetch_assoc($qr)){
    	$sql="select * from req_facial where central_id = '".$ln['id']."'";
    	$qr2=mysql_query($sql);
    	$req=array();
    	while ($lnr=mysql_fetch_assoc($qr2)){
    		if($lnr['foto']!=''){
    			$lnr['foto']=pegafoto($lnr['foto'],$lnr['condominio_id']);
    		}
    		$req[]=$lnr;
    	}
    	$ln['user']=$ln['usuario'];
    	$ln['pass']=$ln['senha'];
    	$ln['url']=$ln['ip'].":".$ln['porta'];
      $ln['tipo']=$ln['tipocad'];
    	$ln['req']=$req;
   	    $ar[]=$ln;
    }
    echo json_encode($ar);
    exit();
}
if (isset($p['idreq'])&&($p['idreq']!='')){
   $sql="delete from req_facial where id = '".$p['idreq']."'";
   $qr=mysql_query($sql);
   echo json_encode(1);
   exit();
}
if (isset($p['datahora'])&&($p['datahora']!='')){
   $sql="update centrais set ultdt = '".$p['datahora']."' where identificacao = '".$p['id']."'";
   if ($_REQUEST['debug']=='1'){
    echo $sql."<br>";
   }
   $qr=mysql_query($sql);
   echo json_encode(1);
   exit();
}
 
function pegafoto($url,$condominio_id){
  $urlx="http://f.imga.xyz/upload64.php?url=".$url."&condominio_id=".$condominio_id;
  $xx=json_decode(file_get_contents($urlx));
  $urlp = $xx->arquivo;
  return $urlp;
}

?>