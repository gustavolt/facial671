<?php
$p=$_REQUEST;
if (true){
  ini_set('error_reporting', E_ALL);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(0);
} 
if (sizeof($p)<=2){
  if ($p['maquina']==''){
    $a = array("sucesso"=>false,"msg"=>"Acesso Negado"); 
    echo json_encode($a);
    exit();
  }
}
include('../db.php'); 

function informaseri($condominio_id,$serial,$maquina){
  $sql="select * from condominio con inner join servidoracesso se on se.id = con.servidoracesso_id where con.id = '".$condominio_id."'";
  $qr=mysql_query($sql);
  $ln=mysql_fetch_assoc($qr);
  $ufrx=$ln['url']."/acessoapi/updleitura.php?condominio_id=".$condominio_id."&serial=".$serial."&maquina=".$maquina;
  
  file_get_contents($ufrx);
}

function mysql_queryx($sql){
  if ($_REQUEST['debug']=='1'){
    echo "SQLD".$sql.".<br>";
  }
  $time = microtime(true); 
  $ret = mysql_query($sql);  
  $diff = microtime(true)-$time;
  $milliseconds =  intval($diff * 1000); 
  if ($_REQUEST['debug']=='1'){
    if ($milliseconds > 0){
      echo "Resposta:".$milliseconds.".<br>";
    }
  }
  return $ret;
}
function zeroaesquerda($vl,$quant){
      $vlx='';
      for ($i=0;$i<($quant-strlen($vl));$i++){
        $vlx.='0';
      }
      return $vlx.$vl;
   }

if (($p['superkey']!=null)&&($p['superkey']!='')&&($p['maquina']!=null)&&($p['maquina']!='')){
  $p['superkey']=$p['superkey'];
  $sql="update centrais set identificacao='".$p['maquina']."' where chave='".$p['superkey']."' ";
  $qr=mysql_queryx($sql);
 
}

$_SESSION['reletipo']='';
$_SESSION['endcan']='';
$_SESSION['rele']='';

function addvisita($morador_id,$prestador_id,$veiculo_id,$p,$lnsetor,$nomeleitor){

  if($p['facial']!=''){
    if($p['datahora']!=''){
      $Date = new DateTime($p['datahora']);
      $Date->modify('-5 minutes');
      $Date = $Date->format('Y-m-d H-i-s');
      $sqldate = "select id from visita where condominio_id = '".$p['condominio_id']."' and (datahora_entrada >= '".$Date."' and datahora_entrada <= '".$p['datahora']."' or datahora_saida >= '".$Date."' and datahora_saida <= '".$p['datahora']."') and (nomeleitorentrada = '".$nomeleitor."' or nomeleitorsaida = '".$nomeleitor."')";
      if ($_REQUEST['debug']=='1'){ 
        echo " <br>".$sqldate." <br>";
      }
      $qrdt=mysql_query($sqldate);
      if (mysql_num_rows($qrdt)>0){
        echo "Já tem evento";
        exit();
      }
    }
  }


$msg=$p['serial']." Nao Encontrada em ".$nomeleitor;
$tipousu='Morador';
  $sql="select visita.*,timediff(now(),visita.datahora_entrada) tdif from visita where condominio_id='".$p['condominio_id']."' and situacao='Entrada' ";
  $nomedele='';
  if (intval($morador_id) > 0){
     $sql.=" and morador_id = '".$morador_id."' ";
     $qs=mysql_queryx("select nome from usuario where id = '".$morador_id."'");
     $ls=mysql_fetch_assoc($qs);
     $nomedele=$ls['nome'];
     $msg=$nomedele." em ".$nomeleitor;
     $tipousu='Morador';
  }else if (intval($prestador_id) > 0){
     $sql.=" and prestador_id = '".$prestador_id."' ";
     $qs=mysql_queryx("select nome,restricao,foto,torrebloco,cpf from prestador where id = '".$prestador_id."'");
     $ls=mysql_fetch_assoc($qs);
     $nomedele=$ls['nome'];
     $restricao=$ls['restricao'];
     $foto=$ls['foto'];
     $cartao=$ls['cpf'];
     $torrebloco=$ls['torrebloco'];
     $msg=$nomedele." em ".$nomeleitor;
  }
  $sql.=" order by id desc limit 1";
  if ($_REQUEST['debug']=='1'){ 
  	echo $sql." XX<br>";
  }
  $qr=mysql_queryx($sql);
  $sqlpp='';
  $tipodeacesso='';
  $autorizada=true;
  $lnvisita='';
  $sqlurl="select * from condominio con inner join servidoracesso se on se.id = con.servidoracesso_id where con.id = '".$p['condominio_id']."'";
  $qrurl=mysql_query($sqlurl);
  $lnurl=mysql_fetch_assoc($qrurl);
if ($_REQUEST['debug']=='1'){ 
    echo $sqlurl." XX<br>";
  }
  if (mysql_num_rows($qr)==0){
    //nao tem visita esta entrando
    $tipodeacesso='Entrada';
    if (intval($prestador_id) > 0){
       $sql="select * from visitapre where condominio_id='".$p['condominio_id']."' and prestador_id = '".$prestador_id."' ";
       if ($_REQUEST['debug']=='1'){
		  	echo $sql."<br>";
		  }
       $qr=mysql_queryx($sql);
       if (mysql_num_rows($qr)>0){
        $lnvisita=mysql_fetch_assoc($qr);
        $sqlx="delete from visitapre where id='".$lnvisita['id']."'";
        $qrx=mysql_queryx($sqlx);
        unset($lnvisita['datahora_entrada']);
        unset($lnvisita['id']);
        $tipousu=$lnvisita['tipo'];
        $lnvisita['nomeleitorentrada']=$nomeleitor;
         if($p['facial']!=''){
           $xurl= $lnurl['url']."/acessoapi/acesso/hikvision_prestador.php?id=".$p['facial']."&condominio_id=".$p['condominio_id']."&tipo=add&tipo_visita=".$tipousu."&tipo_leitor=Saida&restricao=".$restricao."&torrebloco=".$torrebloco."&nome=".$nomedele."&cartao=".$cartao."&foto=".$foto;
           $xurl= str_replace(' ', '+', $xurl);
           $r = file_get_contents($xurl);
    }
        $sqlpp="insert into visita set datahora_entrada=now(),";
        if($p['datahora']!=''){
          $sqlpp="insert into visita set datahora_entrada='".$p['datahora']."',";
        }
        //
        $artx=array();
        foreach ($lnvisita as $key => $value){
          if (($value != null)&&($value != 'NULL')){
            $artx[]=$key." = '".$value."'";
          }
        }
        for ($i=0;$i<sizeof($artx);$i++){
          $sqlpp.=$artx[$i];
          if ($i<sizeof($artx)-1){
            $sqlpp.=",";
          }
        }
        //
       }else{
         $autorizada=false;
       }
    }
  }else{
    $lnvisita=mysql_fetch_assoc($qr);
    $tipodeacesso='Saida';
    //tem visita esta saindo
  }
  if ($_REQUEST['debug']=='1'){
	  print_r($lnsetor);
	  echo "<br>";
	  echo $tipodeacesso;
	  echo "<br>";
	}
  if (($lnsetor['id']==null)||($lnsetor['id']=='')){
      $autorizada=false;
      //leitor nao identificado
  }
  if ($autorizada){
    if ($sqlpp !=''){

    }else{
      if ($tipodeacesso=='Entrada'){

        $obsg='';
        $sqlpp="insert into visita set condominio_id='".$p['condominio_id']."',obs='".$obsg."',acesso_id='".$lnx['acesso_id']."',moradornome='".$nomedele."',morador_id='".$morador_id."',situacao='Entrada',tipo='Morador',sensor='".$p['sensor']."',dispositivo='".$p['disp']."',serial='".$p['serial']."',nomeleitorentrada='".$nomeleitor."'";
        if($p['datahora']!=''){
          $sqlpp.=",datahora_entrada='".$p['datahora']."'";
        }else{
          $sqlpp.=",datahora_entrada=now()";
        }
      }else{
        $sqlppp="insert into visita set sensor='".$p['sensor']."',dispositivo='".$p['disp']."',serial='".$p['serial']."',nomeleitorsaida='".$nomeleitor."',situacao='Saida',";
        if($p['datahora']!=''){
          $sqlppp.="datahora_saida='".$p['datahora']."',";
        }else{
          $sqlppp.="datahora_saida=now(),";
        }
        $sqlpp="update visita set situacao='Saida' where id = '".$lnvisita['id']."' and condominio_id = '".$p['condominio_id']."'" ; 
        //
        $artxx=array();
        unset($lnvisita['datahora_entrada']);
        unset($lnvisita['datahora_saida']);
        unset($lnvisita['nomeleitorentrada']);
        unset($lnvisita['id']);
        unset($lnvisita['situacao']);
        unset($lnvisita['sensor']);
        unset($lnvisita['dispositivo']);
        unset($lnvisita['serial']);
        unset($lnvisita['nomeleitorsaida']);
        unset($lnvisita['tdif']);
        foreach ($lnvisita as $key => $value){
          if (($value != null)&&($value != 'NULL')){
            $artxx[]=$key." = '".$value."'";
          }
        }
        for ($i=0;$i<sizeof($artxx);$i++){
          $sqlppp.=$artxx[$i];
          if ($i<sizeof($artxx)-1){
            $sqlppp.=",";
          }
        }
        //
      
        if ($lnsetor['baixar_visita']=='Não'){
        	$sqlpp='';
        }
        if ($lnsetor['tipo']=='Entrada'){
        	$sqlpp='';
        	$autorizada=true; 
        	if ($lnvisita['tdif']>'00:00:00'){
        		$autorizada=false;
        	}
        }else{
          if(($p['facial']!='')&&($lnvisita['tipo']!='Morador')){
             $xurl= $lnurl['url']."/acessoapi/acesso/hikvision_prestador.php?id=".$p['facial']."&condominio_id=".$p['condominio_id']."&tipo=del&tipo_visita=".$lnvisita['tipo']."&restricao=".$restricao."&torrebloco=".$torrebloco."&nome=".$nomedele."&foto=".$foto;
                 $xurl= str_replace(' ', '+', $xurl);
                 $r = file_get_contents($xurl);
          }
        }
      }
    }
    //notificacao de entrada ou saida realizada
    //$tipousu morador visitante
    //$tipodeacesso Entrada ou Saida
    //$nomeleitor
    if ($_REQUEST['debug']=='1'){
              echo $sqlppp."<- insert saida <br>";
            }
            if ($_REQUEST['debug']=='1'){
              echo $sqlpp."<- insert saida <br>";
            }
    $msg=$tipodeacesso.' '.$tipousu.' '.$nomedele.' '.$nomeleitor;
    if ($sqlppp!=''){
      $qrr=mysql_query($sqlppp);
    }
    if ($sqlpp!=''){
	    $qr=mysql_queryx($sqlpp);
	   }else{
    if ($autorizada==false){
	//	$msg=$nomedele.' '.$nomeleitor.' Nao Liberado!';
    }
	}
    
  }
  if ($autorizada==false){
    $sql="insert into naolidos set condominio_id='".$p['condominio_id']."',datahora=now(),detalhes='Serialx2:".$msg."'";
          $qr=mysql_query($sql);
  }
  $sql="insert into notificacoes set condominio_id = '".$p['condominio_id']."',msg = '".$msg."',tipo = '".$tipodeacesso."',datahora=now(),leitura='".rpop($lnsetor['leitura_automatica'])."',cadastro='".rpop($lnsetor['cadastro_automatico'])."',serial='".$p['serial']."'";
     if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
    $qr=mysql_queryx($sql);

    atualizaacesso($p['condominio_id']);
    return $autorizada;
}//fim function addvisita
 
$lnsetor='';

if ($p['serial']=='000000'){
  echo json_encode("1");
  exit();
}

function rpop($s){
  return $s;
}

function getttp($condominio_id,$serial){
  $sql="select * from visitapre where serial like '%".$serial."%'  and condominio_id='".$condominio_id."'";
  $tp ='Visitante';
  $qr=mysql_queryx($sql);
  if (mysql_num_rows($qr)==0){
    $tp ='Morador';
  }

   return $tp;
}

function getttpV($condominio_id,$serial){

  if ($serial !=''){
      $sql="select torrebloco from usuario where id in (select morador_id from acessodispositivos where serial like '%".$serial."%'  and condominio_id='".$condominio_id."') order by id desc limit 1";
      $qr=mysql_queryx($sql);
      $tp ='Morador';
     if (mysql_num_rows($qr)==0){
        $sql="select * from visitapre where serial like '%".$serial."%'  and condominio_id='".$condominio_id."'";
        $qr=mysql_queryx($sql);
        $tp ='Visitante';
     }else{

     }
   }else{
        $tp ='Visitante';
   }
   return $tp;
}


function getmacc($condominio_id,$tpa,$serial){
  $tbloco='';
  $sql="select * from acessos where condominio_id = '".$condominio_id."' and tipo like '%".$tpa."%'";
  $qrf=mysql_queryx($sql);
  $ln=mysql_fetch_assoc($qrf);
  if (mysql_num_rows($qrf)>1){
    //add parte 
    
  }
  return $ln['id'];
}


function getmaccww($condominio_id,$tpa,$serial){
  $tbloco='';
  if ($serial != ''){
  $sql="select torrebloco from usuario where id in (select morador_id from acessodispositivos where serial like '%".$serial."%'  and condominio_id='".$condominio_id."') order by id desc limit 1";
  $qr=mysql_queryx($sql);
  
   if (mysql_num_rows($qr)==0){
      $sql="select * from visitapre where serial like '%".$serial."%'  and condominio_id='".$condominio_id."'";
      $qr=mysql_queryx($sql);
   }
   $ln=mysql_fetch_assoc($qr);
   $tbloco = $ln['torrebloco'];
 }
  $sql="select * from acessos where condominio_id = '".$condominio_id."' and tipo like '%".$tpa."%'";
  if (($tbloco!=null)&&($tbloco!='')){
    $sql.=" and torrebloco = '".$tbloco."'";
  }
  $qrf=mysql_queryx($sql);
  $ln=mysql_fetch_assoc($qrf);
  return $ln['id'];
}


function verificaentradaacesso($acesso_id,$leitor_id){
  if ($_REQUEST['debug']=='1'){
    echo "Acesso:".$acesso_id.",".$leitor_id."<br>";
  }
  $sql="select * from acessos where id = '".$acesso_id."' and leitores_id like '%".$leitor_id."%'";
  $qrf=mysql_queryx($sql);
  if (mysql_num_rows($qrf)>0){
    return true;
  }else{
    //
    $sql="insert into notificacoes set condominio_id = '".$_SESSION['condominio_id']."',msg = 'Acesso Negado',tipo = 'Entrada',datahora=now()"; 
    $qr=mysql_queryx($sql);

    atualizaacesso($_SESSION['condominio_id']);
    //
    return false;
  }
}

   function transfap($s){
      $x='';
      if (strlen($s)>0){
        $a=0;
        if ($s[0]=='0'){
          $a=1;
        }
        for ($i=$a;$i<strlen($s);$i++){
          $x.=$s[$i];
        }
      }
      return $x;
   } 


   $arraycarros=array();
   $arraycarros[0]='AUDI';
   $arraycarros[1]='BMW';
   $arraycarros[2]='CHEVROLET';
   $arraycarros[3]='CHRYSLER';
   $arraycarros[4]='CITROEN';
   $arraycarros[5]='FERRARI';
   $arraycarros[6]='FIAT';
   $arraycarros[7]='FORD';
   $arraycarros[8]='GM';
   $arraycarros[9]='HONDA';
   $arraycarros[10]='HYUNDAI';
   $arraycarros[11]='IMPORTADO';
   $arraycarros[12]='JAGUAR';
   $arraycarros[13]='JEEP';
   $arraycarros[14]='KIA';
   $arraycarros[15]='LAMBORGHINI';
   $arraycarros[16]='LAND ROVER';
   $arraycarros[17]='MAZDA';
   $arraycarros[18]='MERCEDES';
   $arraycarros[19]='MITSUBISHI';
   $arraycarros[20]='MOTO';
   $arraycarros[21]='NISSAN';
   $arraycarros[22]='VEICULO';
   $arraycarros[23]='PEUGEOT';
   $arraycarros[24]='PORSCHE';
   $arraycarros[25]='RENAULT';
   $arraycarros[26]='SUBARU';
   $arraycarros[27]='SUZUKI';
   $arraycarros[28]='TOYOTA';
   $arraycarros[29]='VOLKSWAGEN';
   $arraycarros[30]='VOLVO';
   $arraycarros[31]='SEM VEICULO';
    
   $arraycores=array();
   $arraycores[0]='AMARELO';
   $arraycores[1]='AZUL';
   $arraycores[2]='BEGE';
   $arraycores[3]='BRANCO';
   $arraycores[4]='CINZA';
   $arraycores[5]='DOURADO';
   $arraycores[6]='FANTASIA';
   $arraycores[7]='GRENA';
   $arraycores[8]='LARANJA';
   $arraycores[9]='MARROM';
   $arraycores[10]='PRATA';
   $arraycores[11]='PRETO';
   $arraycores[12]='ROSA';
   $arraycores[13]='ROXO';
   $arraycores[14]='VERDE';
   $arraycores[15]='VERMELHO';

   $arrayblocos = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

   function pegaindex($s,$ar,$sel=0){
     $s =  strtoupper($s);
     $xsel=$sel;
     for ($i=0;$i<sizeof($ar);$i++){
        if ($ar[$i]==$s){
          $xsel=$i;
        }
     }
     return $xsel;
   }


      $disp=''; 
      $can='';  
      $rele='';

   $usuario_id='';
   $p=$_REQUEST;
   $parax=false;


            $sqlx="select * from centrais where identificacao = '".$p['maquina']."'";
            if ($_REQUEST['debug']=='1'){
              echo $sqlx."<br>";
            }
            $qrx=mysql_queryx($sqlx);
            if (mysql_num_rows($qrx)==0){
              $sqlw="select * from central_pendente where codigo = '".$p['maquina']."'";
              $qrw=mysql_queryx($sqlw);
              if (mysql_num_rows($qrw)==0){
                $sqlw="insert into central_pendente set codigo = '".$p['maquina']."'";
                $qrw=mysql_queryx($sqlw);
              }
              
              echo "Central Pendente:".$p['maquina'];
              exit();
            } 
            $lncentral=mysql_fetch_assoc($qrx);
            $p['condominio_id']=$lncentral['condominio_id'];

   if ( (!isset($p['sensor'])) && (!isset($p['disp']))  ){
     $parax=true;
   }else{


   $sqlx="select * from setores where endcan = '".$p['sensor']."' and rele = '".$p['disp']."' and condominio_id='".$p['condominio_id']."' and controladora_id = '".$lncentral['id']."'  order by dispositivo_linear desc";
        if ($_REQUEST['debug']=='1'){
          echo $sqlx."2<br>";
        }
        $qrx=mysql_queryx($sqlx);
      if (mysql_num_rows($qrx)>0){
         $lnsetor=mysql_fetch_assoc($qrx);
         $nomeleitor=$lnsetor['descricao'];
         $tipoentrada=$lnsetor['tipo'];
         $disp=$lnsetor['receptor'];
         $can=$lnsetor['endcan'];
         $rele=$lnsetor['rele'];
         //bombaagua
          if ($lnsetor['bombaagua']=='Sim'){
             $valor='';
             if ($p['serial'] == '000001'){
               $valor='1';
             }else if ($p['serial'] == '000002'){
               $valor='2';
             }else if ($p['serial'] == '000003'){
               $valor='3';
             }else if ($p['serial'] == '000004'){
               $valor='3';
             }
             if ($valor!=''){
                $sqlx="update setores set nivel='".$valor."',datahora_nivel=now() where id='".$lnsetor['id']."'";
                $qrw=mysql_query($sqlx);
                atualizabomba($p['condominio_id']);
                echo json_encode(1);
                exit();
             }
          }

      }else{

          $sql="insert into naolidos set condominio_id='".$p['condominio_id']."',datahora=now(),detalhes='Disp ".$p['sensor']." Rele ".$p['disp']."'"; 
          $qr=mysql_query($sql);
      }
}

            $sqlx="update centrais set ultimaatualizacao=now() where id = '".$lncentral['id']."'";
            if ($_REQUEST['debug']=='1'){
              echo $sqlx."<br>";
            }
            $qrx=mysql_queryx($sqlx);

            $_SESSION['condominio_id']=$p['condominio_id'];
            $p['central_id']=$lncentral['id'];

            $sqlx="select * from condominio where id = '".$p['condominio_id']."'";
            if ($_REQUEST['debug']=='1'){
              echo $sqlx."<br>";
            }
            $qrx=mysql_queryx($sqlx);
            $ln_condominio=mysql_fetch_assoc($qrx);
            if ($parax){

               //se for biometria cadastro 
                if (($lncentral['tipo']=='linear')&&($lncentral['tipocentral']=='biometria')){
                  //30-211  
                  if ($_REQUEST['debug']=='1'){
                  echo "1c<br>";
                }
                  $sql="select req.*,imp.codigo,imp.codigointerno,imp.usuario_id,usu.nome,imp.id iid,0 as card from req_digital req inner join impressao_digital imp on imp.id = req.impressao_id inner join usuario usu on usu.id = imp.usuario_id where req.central_id = '".$lncentral['id']."'";
                  if ($_REQUEST['debug']=='1'){
                    echo $sql."<br>";
                  }
                  $qr=mysql_queryx($sql);
                  if (mysql_num_rows($qr)>0){ 
                    $lnr=mysql_fetch_assoc($qr);
                    if (($lnr['codigointerno']==null)||($lnr['codigointerno']=='')){
                      $lnr['codigointerno']=$lnr['iid'];
                    }
                    $sql="delete from req_digital where id  = '".$lnr['id']."'";
                    $qr=mysql_queryx($sql);
                    echo json_encode($lnr);
                  }else{
                     $sql="select req.*,'' codigo,req.serial card,acessodispositivos_id codigointerno,req.morador_id usuario_id,usu.nome,acessodispositivos_id iid from req_serial req inner join usuario usu on usu.id = req.morador_id where req.central_id = '".$lncentral['id']."'";
                      if ($_REQUEST['debug']=='1'){
                        echo $sql."<br>";
                      }
                      $qr=mysql_queryx($sql);
                      if (mysql_num_rows($qr)>0){ 
                        $lnr=mysql_fetch_assoc($qr);
                        if (($lnr['codigointerno']==null)||($lnr['codigointerno']=='')){
                          $lnr['codigointerno']=$lnr['iid'];
                        }
                        $sql="delete from req_serial where id  = '".$lnr['id']."'";
                        $qr=mysql_queryx($sql);
                        echo json_encode($lnr);
                      }else{
                        echo json_encode(1);
                      }
                    
                  }
                 if ($p['codigointerno']==''){
                  exit();
                 } 
                }
                 //se for biometria cadastro

              if (($p['codigointerno']=='')&&($lncentral['tipo']=='linear')&&($lncentral['tipocentral']=='biometria')&&(sizeof($p)=='1')){
                echo json_encode(12);
                exit();
              } 
               
            }

            $gettipo = getttp($p['condominio_id'],$p['serial']);
            $ln_condominio['morador_acesso_id']=getmacc($p['condominio_id'],'morador',$p['serial']);
            $ln_condominio['visitante_acesso_id']=getmacc($p['condominio_id'],'visitante',$p['serial']);
            $ln_condominio['prestador_acesso_id']=getmacc($p['condominio_id'],'prestador',$p['serial']);
            if ($gettipo == 'Morador'){
              $px['acesso_id'] = $ln_condominio['morador_acesso_id'];
            }else if ($gettipo == 'Visitante'){
              $px['acesso_id'] = $ln_condominio['visitante_acesso_id'];
            }
             


              

   $tipo='';
   $msg='';
   $r = false;
   if ($p['limpar']=='1'){
     $sql="update requisicao set maquina = '' where id > 0";
     $qr=mysql_queryx($sql);
   }

   if (isset($p['atualizatudo'])){
     $sql="insert into loglinear set descricao='".print_r($p,true)."',datahora=now();";
    // $qr=mysql_queryx($sql);
   }else{
	   $sql="insert into loglineartudo set descricao='".print_r($p,true)."',datahora=now();";
   // $qr=mysql_queryx($sql);
   }
   if ($p['resposta']!=''){
    if ($_REQUEST['debug']=='1'){
      echo "1b<br>";
    }
        $sql="update requisicao set resposta = '".print_r($p,true)."',datahora_resposta=now() where id = '".$p['id']."'";
        $qr=mysql_queryx($sql);

        $xsql="select * from requisicao where id = '".$p['id']."'";
        $xqr=mysql_queryx($xsql);
        $lna=mysql_fetch_assoc($xqr);
        if (($lna['cracha_id']!=null)&&($lna['cracha_id']!='')&&($lna['cracha_id']!='0')){
          $sql="update cracha set serial = '".$p['serial']."' where id = '".$lna['cracha_id']."'";
          $qr=mysql_queryx($sql);      
        }else{
           $sql="update acessodispositivos set serial = '".$p['serial']."' where id = '".$lna['acessodispositivos_id']."'";
           $qr=mysql_queryx($sql);      
        }
       

   }else if ((($p['serial']!='- - - -')&&($p['serial']!=''))||(($p['qrcode']!=null)&&($p['qrcode']!=''))||(($p['senha']!=null)&&($p['senha']!=''))||(($p['codigointerno']!=null)&&($p['codigointerno']!=''))||(($p['facial']!=null)&&($p['facial']!=''))){
   	if ($_REQUEST['debug']=='1'){
            echo "senha ok<br>";
          }
      $xserial=$p['serial'];
      if (($xserial[0]=='0')&&(strlen($xserial)==7)){
        $p['serial']=$xserial[1].$xserial[2].$xserial[3].$xserial[4].$xserial[5].$xserial[6];
      }
      $obsg=''; 
      $nomeleitor="Can ".$p['sensor']." Disp ".$p['disp']."....";
      if ($lnsetor['descricao'] != ''){
        $nomeleitor=$lnsetor['descricao'];
      }

      $tipoentrada='Ambos';
      $disp='';
      $can=''; 
      $rele='';
      $morador_id='';
      $prestador_id='';
      $veiculo_id='';

      if ($p['facial']!=''){//fim qr code
        $xxsg=explode('9999',$p['facial']);
        $xxsg=$xxsg[sizeof($xxsg)-1];
        if ($xxsg==$p['facial']){
           $sql="select * from usuario where id='".$p['facial']."' and condominio_id='".$p['condominio_id']."'";
           if ($_REQUEST['debug']=='1'){
             echo $sql."<br>";
           }
           $qr=mysql_queryx($sql);
           if (mysql_num_rows($qr)>0){
             $ln=mysql_fetch_assoc($qr);
             $morador_id=$ln['id'];
             if ($_REQUEST['debug']=='1'){
                echo $morador_id."<br>";
              }
           }
         }else{
           $p['facial']=$xxsg;
           $sql="select * from prestador where id='".$p['facial']."' and condominio_id='".$p['condominio_id']."'";
           if ($_REQUEST['debug']=='1'){
             echo $sql."<br>";
           }
           $qr=mysql_queryx($sql);
           if (mysql_num_rows($qr)>0){
             $ln=mysql_fetch_assoc($qr);
             $prestador_id=$ln['id'];
             if ($_REQUEST['debug']=='1'){
                echo $prestador_id."<br>";
             }  
           }else{
             $msg=$p['facial']." Nao Encontrada em ".$nomeleitor;
             $sql="insert into naolidos set condominio_id='".$p['condominio_id']."',datahora=now(),detalhes='Face id".$p['facial']."'";
             $qr=mysql_query($sql);
             $sql="insert into notificacoes set condominio_id = '".$p['condominio_id']."',msg = '".$msg."',tipo = '".$tipo."',datahora=now(),leitura='".rpop($lnsetor['leitura_automatica'])."',cadastro='".rpop($lnsetor['cadastro_automatico'])."',Cod='".$p['facial']."'";
             if ($_REQUEST['debug']=='1'){
               echo $sql."<br>";
              }
             $qr=mysql_queryx($sql);
             atualizaacesso($p['condominio_id']);
             //codigo recebido nao reconhecido.
             $a = array("sucesso"=>false,"msg"=>"Acesso Negado"); 
             echo json_encode($a);
             exit();
           }
          } 
      }else if (($lncentral['tipo']=='linear')&&($lncentral['tipocentral']=='biometria')){
               ///
        echo "Bio Linear<br>";
         $id = $p['codigointerno'];
         if ($p['tipo']=='impressao'){
            $sql="select usuario_id from impressao_digital where (codigointerno='".$id."' or id='".$id."') and condominio_id = '".$p['condominio_id']."'";
             if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
            $qrw=mysql_queryx($sql);
            if (mysql_num_rows($qrw)>0){
              $lnw=mysql_fetch_assoc($qrw);
              $morador_id=$lnw['usuario_id'];
            }else{
                $sql="select id from usuario where (codigointerno='".zeroaesquerda($id,7)."' or id='".$id."') and condominio_id = '".$p['condominio_id']."'";
                 if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
                $qrw=mysql_query($sql);
                if (mysql_num_rows($qrw)>0){
                  $lnw=mysql_fetch_assoc($qrw);
                  $morador_id=$lnw['id'];
                }
            }
         }else{
           $sql="select morador_id from req_serial where (acessodispositivos_id='".$id."' or id='".$id."') and condominio_id = '".$p['condominio_id']."'";
             if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
            $qrw=mysql_queryx($sql);
            if (mysql_num_rows($qrw)>0){
              $lnw=mysql_fetch_assoc($qrw);
              $morador_id=$lnw['morador_id'];
            }else{
                $sql="select id from usuario where (codigointerno='".zeroaesquerda($id,7)."' or id='".$id."') and condominio_id = '".$p['condominio_id']."'";
                if ($_REQUEST['debug']=='1'){
                  echo $sql."<br>";
                }
                $qrw=mysql_queryx($sql);
                if (mysql_num_rows($qrw)>0){
                  $lnw=mysql_fetch_assoc($qrw);
                  $morador_id=$lnw['id'];
                }
            }
         }
          if ($_REQUEST['debug']=='1'){
              echo "MORADOR:".$morador_id."<br>";
            }
      }else if ($p['codigointerno']!=''){//fim qr code
         $sql="select * from impressao_digital where (codigointerno='".$p['codigointerno']."' or codigointerno='".zeroaesquerda($p['codigointerno'],7)."' or (codigointerno='' and (usuario_id='".$p['codigointerno']."' ))) and condominio_id='".$p['condominio_id']."'";
         if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
         $qr=mysql_queryx($sql);
         if (mysql_num_rows($qr)>0){
           $ln=mysql_fetch_assoc($qr);
           $morador_id=$ln['usuario_id'];
           if ($_REQUEST['debug']=='1'){
              echo $morador_id."<br>";
            }
         }else{
          $sql="select * from usuario where (codigointerno='".$p['codigointerno']."' or codigointerno='".zeroaesquerda($p['codigointerno'],7)."' ) and condominio_id='".$p['condominio_id']."'";
         if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
         $qr=mysql_queryx($sql);
         if (mysql_num_rows($qr)>0){
           $ln=mysql_fetch_assoc($qr);
           $morador_id=$ln['id'];
           if ($_REQUEST['debug']=='1'){
              echo $morador_id."<br>";
            }
         }else{
              $msg=$p['codigointerno']." Nao Encontrada em ".$nomeleitor;

          $sql="insert into naolidos set condominio_id='".$p['condominio_id']."',datahora=now(),detalhes='Codigo Interno".$p['codigointerno']."'";
          $qr=mysql_query($sql);

              $sql="insert into notificacoes set condominio_id = '".$p['condominio_id']."',msg = '".$msg."',tipo = '".$tipo."',datahora=now(),leitura='".rpop($lnsetor['leitura_automatica'])."',cadastro='".rpop($lnsetor['cadastro_automatico'])."',serial='".$p['serial']."'";
               if ($_REQUEST['debug']=='1'){
                        echo $sql."<br>";
                      }
               $qr=mysql_queryx($sql);

               atualizaacesso($p['condominio_id']);
               //codigo recebido nao reconhecido.
               $a = array("sucesso"=>false,"msg"=>"Acesso Negado"); 
               echo json_encode($a);
               exit();
             }
       }
      }else if ($p['senha']!=''){
          //senha
          $sqlw="select * from prestador where rg like '".$p['senha']."%' and condominio_id = '".$p['condominio_id']."' and id in (select prestador_id from visitapre where condominio_id = '".$p['condominio_id']."')";
          if ($_REQUEST['debug']=='1'){
            echo $sqlw."<br>";
          }
          $qrw=mysql_queryx($sqlw);
          if (mysql_num_rows($qrw)>0){
            $lnprestador = mysql_fetch_assoc($qrw);
            $prestador_id=$lnprestador['id'];
          }else{
            $sqlw="select * from prestador where rg like '".$p['senha']."%' and condominio_id = '".$p['condominio_id']."' and id in (select prestador_id from visita where condominio_id = '".$p['condominio_id']."' and datahora_saida='0000-00-00 00:00:00')";
            $qrw=mysql_queryx($sqlw);
            if (mysql_num_rows($qrw)>0){
              $lnprestador = mysql_fetch_assoc($qrw);
              $prestador_id=$lnprestador['id'];
            }else{
              $msg = 'Registro Nao Encontrado!';
            }
            
          }
          //senha
      }else if ($p['serial']!=''){

          $sql="select * from acessodispositivos where serial like '%".$p['serial']."%'  and condominio_id='".$p['condominio_id']."' order by id desc limit 1";
          if ($_REQUEST['debug']=='1'){
            echo $sql."<br>";
          }
          $qr=mysql_queryx($sql);
          if (mysql_num_rows($qr)==0){
            //nao encontrou serial
            $sql="select * from visitapre where serial like '%".$p['serial']."%'  and condominio_id='".$p['condominio_id']."' order by id desc limit 1";
            $qr=mysql_queryx($sql);
            if (mysql_num_rows($qr)>0){
              $ln=mysql_fetch_assoc($qr);
              if ($ln['morador_id']>0){
                $morador_id=$ln['morador_id'];
              } if ($ln['prestador_id']>0){
                $morador_id='';
                $prestador_id=$ln['prestador_id'];
              }
            }else{
              $sql="select * from visita where serial like '%".$p['serial']."%'  and condominio_id='".$p['condominio_id']."' order by id desc limit 1";
              $qr=mysql_queryx($sql);
              if (mysql_num_rows($qr)>0){
                $ln=mysql_fetch_assoc($qr);
                if ($ln['morador_id']>0){
                  $morador_id=$ln['morador_id'];
                }if ($ln['prestador_id']>0){
                  $morador_id='';
                  $prestador_id=$ln['prestador_id'];
                }
              }else{
                $sql="insert into recebimento_leitura set condominio_id = '".$p['condominio_id']."' , serial='".$p['serial']."', maquina='".$p['maquina']."',datahora=now()";
                if ($_REQUEST['debug']=='1'){
                  echo $sql."<br>";
                }
                $qr=mysql_queryx($sql);
                informaseri($p['condominio_id'],$p['serial'],$p['maquina']);
              }
            }
          }else{
            // encontrou serial
            $ln=mysql_fetch_assoc($qr);
            $morador_id=$ln['morador_id'];
          }
      }else if ($p['qrcode']!=''){
        $sqlw="select * from prestador where cpf = '".$p['qrcode']."' and condominio_id = '".$p['condominio_id']."' and id in (select prestador_id from visitapre where condominio_id = '".$p['condominio_id']."'  ) ";
          $qrw=mysql_queryx($sqlw);
          if (mysql_num_rows($qrw)>0){
            $lnprestador = mysql_fetch_assoc($qrw);
            $prestador_id=$lnprestador['id'];
          }else{
            $msg = 'Registro Nao Encontrado!';
          }/*
          $sqla="select convites.*,if(datahora_inicial < now() and datahora_final > now(),1,0) valido from convites where convites.codigo = '".$_REQUEST['qrcode']."' and condominio_id = '".$ln_condominio['id']."'";
          $qra=mysql_queryx($sqla);
          if (mysql_num_rows($qra)>0){ 
          	$liu=mysql_fetch_assoc($qra);
              if (($lnsetor['rele']!=null)&&($lnsetor['rele']!='')){ 
                $urlx='https://www.vn3anjo.com.br/vn3/api/setores/abredispenser.php?condominio_id='.$lnsetor['condominio_id']."&id=".$lnsetor['id'];
                if ($_REQUEST['debug']=='1'){
                    echo "Libera RELE <br>".$urlx."<br>";
                }
                $x=file_get_contents($urlx);
              }
               $prestador_id=$liu['prestador_id'];
          }else{
            $tipo='Saida';

          $sql="insert into naolidos set condominio_id='".$p['condominio_id']."',datahora=now(),detalhes='QRCODE ".$p['qrcode']."'";
          $qr=mysql_query($sql);
            $msg='QRCODE '.$_REQUEST['qrcode']." Nao Encontrado";
            $sql="insert into notificacoes set condominio_id = '".$p['condominio_id']."',msg = '".$msg."',tipo = '".$tipo."',datahora=now(),leitura='".rpop($lnsetor['leitura_automatica'])."',cadastro='".rpop($lnsetor['cadastro_automatico'])."',serial='".$p['serial']."'";
                 if ($_REQUEST['debug']=='1'){
                        echo $sql."<br>";
                      }
                $qr=mysql_queryx($sql);

                atualizaacesso($p['condominio_id']);
                $a = array("sucesso"=>false); 
                echo json_encode($a);
          }*/
      }
        $nomeleitor="Can ".$p['sensor']." Disp ".$p['disp']."..";
       
        $sqlx="select * from setores where endcan = '".$p['sensor']."' and rele = '".$p['disp']."' and condominio_id='".$p['condominio_id']."' and controladora_id = '".$lncentral['id']."' order by dispositivo_linear desc";
        if ($_REQUEST['debug']=='1'){
          echo $sqlx."1<br>";
        }

        $qrx=mysql_queryx($sqlx);
        if (mysql_num_rows($qrx)>0){
          $lnsetor=mysql_fetch_assoc($qrx);
          $nomeleitor=$lnsetor['descricao'];
          $tipoentrada=$lnsetor['tipo'];
          $disp=$lnsetor['receptor'];
          $can=$lnsetor['endcan'];
          $rele=$lnsetor['rele'];
        }

        if ($lnsetor['descricao'] != ''){
          $nomeleitor=$lnsetor['descricao'];
        }
      	
      	if ($_REQUEST['debug']=='1'){
          echo "M".$morador_id."p".$prestador_id."<br>";
        }

        if (($morador_id!='')||($prestador_id!='')||($veiculo_id!='')){
          $r = addvisita($morador_id,$prestador_id,$veiculo_id,$p,$lnsetor,$nomeleitor);
          $msg="";
          if ($r==false){
            $msg='Acesso Negado';
          }else{
            if ($_REQUEST['xxss']=='1'){
             // $x=file_get_contents($urlx); essa linha
              $_SESSION['reletipo']=$lnsetor['receptor'];
              $_SESSION['endcan']=$lnsetor['endcan'];
              $_SESSION['rele']=$lnsetor['rele'];
              $resp = array("sucesso"=>'liberar',"id"=>$id,"reletipo"=>$_SESSION['reletipo'],"endcan"=>$_SESSION['endcan'],"rele"=>$_SESSION['rele']);
              echo json_encode($resp);
              exit();
            }
          }
          $a = array("sucesso"=>$r,"msg"=>$msg); 
          echo json_encode($a);
          exit();
        }else{

          $sql="insert into naolidos set condominio_id='".$p['condominio_id']."',datahora=now(),detalhes='Serial:".$p['serial']."'";
          $qr=mysql_query($sql);
          $msg=$p['serial']." Nao Encontrada em ".$nomeleitor;
          $sql="insert into notificacoes set condominio_id = '".$p['condominio_id']."',msg = '".$msg."',tipo = '".$tipo."',datahora=now(),leitura='".rpop($lnsetor['leitura_automatica'])."',cadastro='".rpop($lnsetor['cadastro_automatico'])."',serial='".$p['serial']."'";
           if ($_REQUEST['debug']=='1'){
                    echo $sql."<br>";
                  }
           $qr=mysql_queryx($sql);

           atualizaacesso($p['condominio_id']);
           //codigo recebido nao reconhecido.
           $a = array("sucesso"=>false,"msg"=>"Acesso Negado"); 
           echo json_encode($a);
           exit();
        }
 


    //parte nova
   
} 


    

     $sql="select count(req.id) qt from requisicao req  where req.condominio_id = '".$p['condominio_id']."'  and req.maquina = '' and req.maquinaid = '".$p['maquina']."' order by req.id asc ";
     if ($_REQUEST['debug']=='1'){
       echo $sql."<br>";
     }
     $qr=mysql_queryx($sql);
     $lnr=mysql_fetch_assoc($qr);

     $qtx=$lnr['qt'];


	if ($_REQUEST['debug']=='1'){
        echo "CCC<br>";
    }
 

   $sql="select req.*,vc.marca,vc.modelo,vc.placa,vc.cor, if (vm.morador_id > 0,vm.morador_id,acd.morador_id) morador_id,acd.veiculo_id from requisicao req inner join acessodispositivos acd on acd.id = req.acessodispositivos_id left join veiculo_cond vc on vc.id = acd.veiculo_id left join veiculo_morador vm on vm.veiculo_id = acd.veiculo_id where req.condominio_id = '".$p['condominio_id']."'  and req.maquina = '' and req.maquinaid = '".$p['maquina']."' order by req.id asc limit 1 ";  
   if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
   $qr3=mysql_queryx($sql);
   if (mysql_num_rows($qr3)>0){
   	 $lnx=mysql_fetch_assoc($qr3);

   	   $sql="update requisicao set usuario_id='".$lnx['morador_id']."' where id = '".$lnx['id']."' ";
       if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
         $qr=mysql_queryx($sql);

     if (($lnx['veiculo_id']!= null)&&($lnx['veiculo_id']!= '')&&($lnx['veiculo_id']!= '0')){
   	 
         $sql="update usuario set carro_marca='".$lnx['marca']."',carro_cor='".$lnx['cor']."',carro_placa='".$lnx['placa']."' where id = '".$lnx['morador_id']."' ";
         if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
         $qr=mysql_queryx($sql);
   	 } 
   }
         
   $sql="select req.*,usu.nome,usu.torrebloco2,usu.torrebloco,usu.carro_marca,usu.carro_placa,usu.carro_cor from requisicao req left join usuario usu on usu.id = req.usuario_id where req.condominio_id = '".$p['condominio_id']."'  and req.maquina = ''  and req.maquinaid = '".$p['maquina']."' order by req.id asc limit 1 ";
    if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
   $qr=mysql_queryx($sql);
   $id='';
   if (mysql_num_rows($qr)>0){
     $ln=mysql_fetch_assoc($qr);
     $id=$ln['id'];
     $r=true;   	
   	 $sql="update requisicao set maquina = '".$p['maquina']."' where id = '".$id."'";
     if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
     $qr=mysql_queryx($sql);

     $unidade = intval(soNumero($ln['torrebloco2']));
     //$bloco = pegaindex($ln['torrebloco'],$arrayblocos);
     $bloco = $ln['torrebloco'];
     $marca = pegaindex($ln['carro_marca'],$arraycarros,31);
     $cor = pegaindex($ln['carro_cor'],$arraycores);
     if (($disp!=null)&&($disp!='')){
        $r='liberar';
     }

     $xxxtp=array();
         //01 - Controle TX (RF)
         //02 - TAG Ativo (TA)
         //03 - Cartão (CT/CTW)
         //05 - Biometria (BM)
         //06 - TAG Passivo (TP/UHF)
         //07 - Senha (SN)
  $xxxtp['cartao']='3';
  $xxxtp['tag']='6';
  $xxxtp['controle']='1';
  $tipo_id=$xxxtp[$ln['tipoacesso']];


  $xportas='0000';
  $xrecs='00000000';

  if (($ln['contador']!=null)&&($ln['contador']!='')&&(strlen($ln['contador'])>3)){
    $xportas=$ln['contador'];
  }

  if (true){ 

    $xrecs='00000000';
    if ($ln['leitor1']=='true'){
       $xrecs[0]='1';
    }
    if ($ln['leitor2']=='true'){
       $xrecs[1]='1';
    }
    if ($ln['leitor3']=='true'){
       $xrecs[2]='1';
    }
    if ($ln['leitor4']=='true'){
       $xrecs[3]='1';
    }
    if ($ln['leitor5']=='true'){
       $xrecs[4]='1';
    }
    if ($ln['leitor6']=='true'){
       $xrecs[5]='1';
    }
    if ($ln['leitor7']=='true'){
       $xrecs[6]='1';
    }
    if ($ln['leitor8']=='true'){
       $xrecs[7]='1';
    }
    $xrecs=strrev($xrecs);
  } 

  $sql2="select * from label where condominio_id = '".$p['condominio_id']."' and tipo = 'labels' and trim(descricao) like '%".$bloco."'"; 

     $qr2=mysql_queryx($sql2);
     if (mysql_num_rows($qr2)>0){
       $ln2=mysql_fetch_assoc($qr2);
      $bloco=$ln2['indice'];
      } 


      $sql="update requisicao set maquina = '".$p['maquina']."' where id = '".$id."'";
      if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
     $qrx=mysql_queryx($sql);
 
     $resp = array("sucesso"=>$r,"id"=>$id,"usuario_id"=>$ln['usuario_id'], "tipo"=>$ln['tipoacesso'],"tipo_id"=>$tipo_id, "nome"=>$ln['nome'], "bloco"=>$bloco,"unidade"=>$unidade, "marca"=>$marca, "cor"=>$cor, "serial"=>$ln['xserial'], "placa"=>$ln['carro_placa'], "leitor1"=>$ln['leitor1'], "leitor2"=>$ln['leitor2'], "leitor3"=>$ln['leitor3'], "leitor4"=>$ln['leitor4'], "leitor5"=>$ln['leitor5'], "leitor6"=>$ln['leitor6'], "leitor7"=>$ln['leitor7'], "leitor8"=>$ln['leitor8'],"disp"=>$disp,"can"=>$can,"rele"=>$rele,"portas"=>$xportas,"recs"=>$xrecs,"qtx"=>$qtx );
 
     echo json_encode($resp);
   }else{
      //se for um cracha
    
      $sql="select req.*,usu.descricao nome from requisicao req inner join cracha usu on usu.id = req.cracha_id where req.condominio_id = '".$p['condominio_id']."'  and req.maquina = ''  and req.maquinaid = '".$p['maquina']."' order by req.id asc limit 1 ";
      if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
     $qr=mysql_queryx($sql);
     $id='';
     if (mysql_num_rows($qr)>0){
       $ln=mysql_fetch_assoc($qr);
       $id=$ln['id'];
       $r=true;     
       $sql="update requisicao set maquina = '".$p['maquina']."' where id = '".$id."'";
       $qr=mysql_queryx($sql);

       $unidade = '';
       $bloco = '';
       $marca = '';
       $cor = '';
       if (($disp!=null)&&($disp!='')){
        $r='liberar';
       }

     $xxxtp=array();
         //01 - Controle TX (RF)
         //02 - TAG Ativo (TA)
         //03 - Cartão (CT/CTW)
         //05 - Biometria (BM)
         //06 - TAG Passivo (TP/UHF)
         //07 - Senha (SN)
  $xxxtp['cartao']='3';
  $xxxtp['tag']='6';
  $xxxtp['controle']='1';
  $tipo_id=3;

  $xportas='0000';
  $xrecs='00000000';

  if (($ln['contador']!=null)&&($ln['contador']!='')&&(strlen($ln['contador'])>3)){
    $xportas=$ln['contador'];
  }

  if (true){ 

    $xrecs='00000000';
    if ($ln['leitor1']=='true'){
       $xrecs[0]='1';
    }
    if ($ln['leitor2']=='true'){
       $xrecs[1]='1';
    }
    if ($ln['leitor3']=='true'){
       $xrecs[2]='1';
    }
    if ($ln['leitor4']=='true'){
       $xrecs[3]='1';
    }
    if ($ln['leitor5']=='true'){
       $xrecs[4]='1';
    }
    if ($ln['leitor6']=='true'){
       $xrecs[5]='1';
    }
    if ($ln['leitor7']=='true'){
       $xrecs[6]='1';
    }
    if ($ln['leitor8']=='true'){
       $xrecs[7]='1';
    }
    strrev($xrecs);
  }


      $sql="update requisicao set maquina = '".$p['maquina']."' where id = '".$id."'";
      if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
     $qrx=mysql_queryx($sql);

       $resp = array("sucesso"=>$r,"id"=>$id,"cracha_id"=>$ln['cracha_id'], "tipo"=>'prestador', "nome"=>$ln['nome'],"tipo_id"=>$tipo_id, "bloco"=>$bloco,"unidade"=>$unidade, "marca"=>$marca, "cor"=>$cor, "placa"=>'', "leitor1"=>$ln['leitor1'], "leitor2"=>$ln['leitor2'], "leitor3"=>$ln['leitor3'], "leitor4"=>$ln['leitor4'], "leitor5"=>$ln['leitor5'], "leitor6"=>$ln['leitor6'], "leitor7"=>$ln['leitor7'], "leitor8"=>$ln['leitor8'],"disp"=>$disp,"can"=>$can,"rele"=>$rele,"serial"=>$ln['xserial'],"portas"=>$xportas,"recs"=>$xrecs,"qtx"=>$qtx);
       echo json_encode($resp);
       //se for um cracha
     }else{

       if (($disp!=null)&&($disp!='')){
        $r='liberar';
        $resp = array("sucesso"=>$r,"disp"=>$disp,"can"=>$can,"rele"=>$rele);
       echo json_encode($resp);exit();
       }
       
      
          $sql="select * from requisicaoapagar where resposta is null and maquinaid = '".$p['maquina']."' and condominio_id = '".$p['condominio_id']."' order by id asc";
          if ($_REQUEST['debug']=='1'){
              echo $sql."<br>";
            }
          $qr=mysql_queryx($sql);
          if (mysql_num_rows($qr)>0){
            $la=mysql_fetch_assoc($qr);
            $r='apagar';
            $sql="delete from requisicaoapagar where id = '".$la['id']."'";
            $qr=mysql_queryx($sql);
            $xserial = transfap($la['serial']);
            $xtipo=$la['tipo'];
            $resp = array("sucesso"=>$r,"serial"=>$xserial,"tipo"=>$xtipo);
            echo json_encode($resp);
          }else{
            $resp = array("sucesso"=>$r,"id"=>$id);
            echo json_encode($resp);
          }
     }//else

   }//else
?>
