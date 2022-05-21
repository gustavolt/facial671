<?php

function pegadia($lista,$dia){
  $dias = [
    'Dom',
    'Seg',
    'Ter',
    'Qua',
    'Qui',
    'Sex',
    'Sab'];

  $days = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'];

    $resp ='';
    for ($i=0;$i<sizeof($lista);$i++){
      $ob = $lista[$i];
      if ($ob['dia']==$dia){
        $resp=$ob;
      } 
    }

    if ($resp !=''){
      $xdia = '';
      for ($i=0;$i<sizeof($dias);$i++){
        if ($dias[$i]==$resp['dia']){
          $xdia=$days[$i];
        }
      }
      $s = '{"week": "'.$xdia.'",
            "id": 1 ,
            "enable": true ,
            "TimeSegment":{
              "beginTime":"'.$resp['hora_inicio'].'",
              "endTime":"'.$resp['hora_fim'].'"}}';
        $resp=$s;
    }
    return $resp;
}


function addhorario($id,$lista){ 
  $listax ='';
  $mon =  pegadia($lista,'Seg');
  $tue =  pegadia($lista,'Ter');
  $wed =  pegadia($lista,'Qua');
  $thu =  pegadia($lista,'Qui');
  $fri =  pegadia($lista,'Sex');
  $sat =  pegadia($lista,'Sab');
  $sun =  pegadia($lista,'Dom');
  $ret = [$mon,$tue,$wed,$thu,$fri,$sat,$sun]; 
  for ($i=0;$i<sizeof($ret);$i++){
    if ($ret[$i]!=''){
      if ($listax!=''){
        $listax=$listax.",";
      }
      $listax=$listax.$ret[$i];
    }
  }
  return $listax;
}

if ($_REQUEST['id']!=''){
  $id=$_REQUEST['id'];
  $x = (array) json_decode(file_get_contents('http://vn3anjo.com.br/vn3/api/regrahorario/return_regra_dia.php?regra_id='.$id));
  $arx=array();
  for ($i=0;$i<sizeof($x);$i++){
    $arx[]=(array) $x[$i];
  }
  $s = addhorario('12345678',$arx);
  echo $s;
}
?>