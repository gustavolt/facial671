<?php
  include('../db.php'); 
  $sql= "select log.*, con.nome condominio from log_hikvision log inner join condominio con on con.id = log.condominio_id where log.condominio_id = '".$_REQUEST['condominio_id']."' order by log.datahora desc"; 
  $qr=mysql_query($sql);
  $log=array();
  while ($ln=mysql_fetch_assoc($qr)){
	 $log[]=$ln;  
  }
  echo json_encode($log);
 ?>