<?php
ini_set("memory_limit","96550M");

define("URLAPI","http://vn3anjo.com.br/vn3");
class AsyncOperation extends Thread {
     public $rfw;
     public $central;
     public $to;
     public $difftw; 

    public function __construct($rf,$central,$to,$difftw) {
        echo "const\n"; 
        $this->rfw = $rf; 
        $this->central = $central; 
        $this->to = $to; 
        $this->difftw = $difftw;  
    }

    public function run() {
      $timex1 = $this->difftw;
      $difft = intval(microtime(true)-$timex1);
       echo "\n\n\nTEMPO2: ".$difft."\n\n\n\n\n";
      if ($this->to == 'atualiza'){ 
        pegaeventos($this->central);
      }else{
        $arx = $this->rfw;
        $rf = (array) $arx;
         
        if ($rf['tipo']=='add'){
            echo "Cria Usuario:".print_r($rf,true)."\nCentral:".print_r($this->central,true)."\n";
            criarusuario($rf,$this->central);
        }else if ($rf['tipo']=='del'){
            echo "Apagar Usuario:".print_r($rf,true)."\nCentral:".print_r($this->central,true)."\n";
            apagarusuario($rf,$this->central);
        }
      }
      $difft = intval(microtime(true)-$timex1);
      
       echo "\n\n\nTEMPO3: ".$difft."\n\n\n\n\n";
      if ($difft > 45){
         exit();
      }
    }
}

$pool = new Pool(4);
$timex1 = microtime(true); 
$difft = 0;
while ($difft<=60){
  echo "\n\n\nTEMPO: ".$difft."\n\n\n\n\n";
  echo "pegando cadastros\n";
echo URLAPI.'/api/integracao/verifica_hikvision.php?pgo=1&ix='.date('his').'\n'; 
$req = json_decode(file_get_contents(URLAPI.'/api/integracao/verifica_hikvision.php?pgo=1&ix='.date('his'))); 
echo sizeof($req).'\n';
if (sizeof($req)>0){
    for ($i=0;$i<sizeof($req);$i++){
        echo "Rodando ".$i."/".sizeof($req)."\n"; 
        $l = (array) $req[$i]; 
        if (sizeof($l['req'])>0){ 
            $lr = $l['req'];  
            unset($l['req']);
            $central = $l;
            for ($a=0;$a<sizeof($lr);$a++){
                $rf = (array) $lr[$a];
                $pool->submit(new AsyncOperation($rf,$central,'',$timex1));
            }
        }else{ 
            unset($l['req']);
            $central = $l;
        }
        echo "Pega Eventos Central:".$i."\n";
        $pool->submit(new AsyncOperation(array(),$central,'atualiza',$timex1));
    }

$difft = intval(microtime(true)-$timex1);
}
$difft = intval(microtime(true)-$timex1);
sleep(3);

  echo "\n\n\nTEMPO F: ".$difft."\n\n\n\n\n";
  if ($difft >= 60){
    echo "PARAAA";
    break;
  }
}

 function pegaeventos($central){ 
    $date = strtotime("-30 day", strtotime(date('d-m-Y')));
    $dti = date('Y-m-d', $date).'T00:00:01-03:00';
    $dtf = date('Y-m-d').'T23:59:59-03:00';
    $ultimo_id=$central['ultdt'];
    if ($ultimo_id!=''){
      $dti =$ultimo_id;
      $datetime = explode("T", $dti);
      $dti =  new DateTime($datetime[1]);
      $dti->modify('+1 seconds');
      $dti = $datetime[0].'T'.$dti->format('H:i:s').'-03:00';
      echo $ultimo_id."\n";
      echo $dti."\n";
    }
     
    $method = 'POST';
    $user=$central['user'];
    $pass=$central['pass'];
    $url = $central['url']."/ISAPI/AccessControl/AcsEvent?format=json"; 
    $data = '{
    "AcsEventCond": {
        "searchID": "1",
        "searchResultPosition": 0,
        "maxResults": 10,
        "major": 5,
        "minor": 75,
        "_comment": "Necessário",
        "startTime": "'.$dti.'",
        "endTime": "'.$dtf.'",
        "timeReverseOrder": true
        }
    }'; 
    $r = httpPost($url,$data,$user,$pass,$method); 
    if (intval($r['code'])==0){
        $xurl = $central['urlservidor']."/acessoapi/integracao/falha_faciais.php?id=".$central['id'];
                  echo $xurl."\n";
        $r = file_get_contents($xurl);
    } 
    $data = json_decode($r['data']);

        $zero=true;
    if (($data!=null)&&($data->AcsEvent!=null)&&($data->AcsEvent->InfoList!=null)){
       $lista = $data->AcsEvent->InfoList; 
        if (sizeof($lista) > 0){
           $zero=false;
           $dtix = $lista[0]->time; 
           $ident = $central['identificacao'];
           $xurl = $central['urlservidor']."/acessoapi/integracao/verifica_hikvision.php?id=".$ident."&datahora=".$dtix;
                  echo $xurl."\n";
           $r = file_get_contents($xurl);
           for ($i=0;$i<sizeof($lista);$i++){
               $evt = $lista[$i];
               $user_id = $evt->employeeNoString;
               $datahora = $evt->time;
               if (($user_id != '')&&($user_id != '0')){ 
                  $xurl = $central['urlservidor']."/acessoapi/integracao/verifica_acoes.php?maquina=".$ident."&facial=".$user_id."&tipocad=".$central['tipo']."&datahora=".$datahora."&np=1";
                  echo $xurl."\n";
                  $r = file_get_contents($xurl);
               }
           }
        }else{
        $xurl = $central['urlservidor']."/acessoapi/integracao/verifica_acoes.php?maquina=".$ident;
                  echo $xurl."\n";
      //            $r = file_get_contents($xurl);
       }
    } 
 }


function apagarusuario($usuario,$central){
        $method = 'PUT';
        $user=$central['user'];
        $pass=$central['pass'];
        $url = $central['url']."/ISAPI/AccessControl/UserInfoDetail/Delete?format=json";
            $data = '{
                      "UserInfoDetail":{
                        "mode":"byEmployeeNo",
                        "EmployeeNoList":[{
                          "employeeNo":"'.$usuario['usuario_id'].'"
                        }]
                      }
                    }';
            $r = httpPost($url,$data,$user,$pass,$method); 
            if($r["code"] == 200){
                confirmaop($usuario['id'],$central['urlservidor']);
                $xurl = $central['urlservidor']."/acessoapi/loghikvision/delete.php?id_usuario=".$usuario['usuario_id']."&central=".$central['url']."&ret=ok";
                echo $xurl."\n";
                $x = file_get_contents($xurl);
            }  else{
                $xurl = $central['urlservidor']."/acessoapi/loghikvision/delete.php?id_usuario=".$usuario['usuario_id']."&central=".$central['url']."&ret=erro";
                echo $xurl."\n";
                $x = file_get_contents($xurl);
            }
}

function tfx($s){
  $a = explode("https",$s);
  if (sizeof($a)>1){
    $s="http".$a[1];
  }
  return $s;

}

function criarusuario($usuario,$central){
        $method = 'POST';
        $user=$central['user'];
        $pass=$central['pass'];
        $url = $central['url']."/ISAPI/AccessControl/UserInfo/Record?format=json";  
        $idacesso = 1;
        $dti=$usuario['dti'];
        $dtf=$usuario['dtf'];
        if($dti==''){
          $dti='2000-01-01T00:00:00';
        }else{
          $dti=$dti.':00';
        }
        if($dtf==''){
          $dtf='2037-12-31T00:00:00';
        }else{
          $dtf=$dtf.':00';
        }
        echo $dti."\n";
        echo $dtf."\n";
        $nome=substr($usuario['nome'],0,32);
        if (intval($usuario['idacesso'])>0){
          $idacesso=$usuario['idacesso'];
        }
        $data = '{
          "UserInfo": {
              "employeeNo":"'.$usuario['usuario_id'].'",
              "userType":"normal",
              "name":"'.$nome.'",
              "userType": "normal",
                  "closeDelayEnabled": false,
                  "Valid": {
                    "enable": true,
                    "beginTime": "'.$dti.'",
                    "endTime": "'.$dtf.'",
                    "timeType": "local"
                  },
                  "belongGroup": "",
                  "password": "",
                  "doorRight": "1",
                  "RightPlan": [
                    {
                      "doorNo": 1,
                      "planTemplateNo": "'.$idacesso.'"
                    }
                  ],
                  "maxOpenDoorTime": 0,
                  "openDoorTime": 0,
                  "roomNumber": 0,
                  "floorNumber": 0,
                  "localUIRight": false,
                  "numOfCard": 0,
                  "numOfFP": 1,
                  "numOfFace": 1
          }
        }'; 

        $r = httpPost($url,$data,$user,$pass,$method);  
        echo $data;
        echo "CODE R1:".$r['code']."\n"; 
        if ($r["code"] == 200) {
            $xurl = $central['urlservidor']."/acessoapi/loghikvision/add.php?id_usuario=".$usuario['usuario_id']."&nome=".$usuario['nome']."&foto=".$usuario['foto']."&tipo=add+usuario&idacesso=".$idacesso."&central=".$central['url']."&cartao=".$usuario['cartao']."&condominio_id=".$usuario['condominio_id'];
            $xurl= str_replace(' ', '+', $xurl);
            echo $xurl."\n";
            $x = file_get_contents($xurl);
        }
        if (($r['code']!='')&&($r['code']!='0')){
          echo "Cartao:".$usuario['cartao']."\n Foto".$usuario['foto']."\n\n";
           if (($usuario['cartao']!=null)&&($usuario['cartao']!='')){
                 if (($usuario['cartaotipo']==null)||($usuario['cartaotipo']=='')){
                    $usuario['cartaotipo']='normalCard';
                 }
                 $method = "POST"; 
                 $url = $central['url']."/ISAPI/AccessControl/CardInfo/Record?format=json"; 
                 $data = '{
                    "CardInfo": {
                        "employeeNo": "'.$usuario['usuario_id'].'",
                        "cardNo": "'.$usuario['cartao'].'",
                        "cardType":"'.$usuario['cartaotipo'].'"
                    }
                 }';
                 $r = httpPost($url,$data,$user,$pass,$method); 
                 echo "CODE R2:".$r['code']."\n";
                 if($r["code"] == 200){
                    //apagar requisicao
                    confirmaop($usuario['id'],$central['urlservidor']);
                    $xurl = $central['urlservidor']."/acessoapi/loghikvision/add.php?id_usuario=".$usuario['usuario_id']."&nome=".$usuario['nome']."&foto=".$usuario['foto']."&tipo=add+cartao&idacesso=".$idacesso."&central=".$central['url']."&cartao=".$usuario['cartao']."&condominio_id=".$usuario['condominio_id'];
                    $xurl= str_replace(' ', '+', $xurl);
                    echo $xurl."\n";
                    $x = file_get_contents($xurl);
                 }
            }    
            if (($usuario['foto']!=null)&&($usuario['foto']!='')){
                $url = $central['url']."/ISAPI/Intelligent/FDLib/FaceDataRecord?format=json"; 
                $img=tfx($usuario['foto']); 
                $data = '{
                "faceURL":"'.$img.'",
                "faceLibType":"blackFD",
                "FDID":"1",
                "FPID":"'.$usuario['usuario_id'].'",
                "name":"'.$nome.'"
                }'; 
                $r = httpPost($url,$data,$user,$pass,$method);
                echo $data."\n";
                echo "CODE R3:".$r['code']."\n";
                if($r["code"] == 200){
                    //apagar requisicao
                    confirmaop($usuario['id'],$central['urlservidor']);
                    $xurl = $central['urlservidor']."/acessoapi/loghikvision/add.php?id_usuario=".$usuario['usuario_id']."&nome=".$usuario['nome']."&foto=".$usuario['foto']."&tipo=add+face&idacesso=".$idacesso."&central=".$central['url']."&cartao=".$usuario['cartao']."&condominio_id=".$usuario['condominio_id'];
                    $xurl= str_replace(' ', '+', $xurl);
                    echo $xurl."\n";
                    $x = file_get_contents($xurl);
                }else{
                   confirmaop($usuario['id'],$central['urlservidor'],$r["code"]);
                  echo "\n\n\n\n Erro COD:".$r["code"]."\n\n\n\n";
                }
            }
        }else{
          confirmaop($usuario['id'],$central['urlservidor'],$r["code"]);
                  echo "\n\n\n\n Erro COD 2:".$r["code"]."\n\n\n\n";
            /*echo "\n\n";
            print_r($r);

            echo "\n\n";*/
        }

}

function confirmaop($id,$urlp,$code=200){
    $r = file_get_contents($urlp.'/acessoapi/integracao/verifica_hikvision.php?idreq='.$id.'&resp='.$code);
    return $r;
}

function httpPost($url, $data, $user, $pass, $method) {
    //echo $url."\n";
    // Make request via cURL.
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    //echo "\nDATA:".$data."\n\n";
    // Set options necessary for request.
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($curl, CURLOPT_USERPWD, $user . ":" . $pass);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,5);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
  
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  
    // Send request
    $response = curl_exec($curl);
    //echo $response."\n";
    return array(
      'code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
      'data' => $response,
    );
  }

 
?>

