<?php
    include('../db.php');
    $id=$_REQUEST['id'];
    $sql="update centrais set proxima_leitura = DATE_ADD(now(),interval 30 minute) where id = '".$id."'";
    $qr=mysql_query($sql);
    echo json_encode($ar); 
?>