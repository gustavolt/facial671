<?php
include('../db.php');
$p=$_REQUEST;

$sql="select * from req_facial where condominio_id='".$p['condominio_id']."' and usuario_id='".$p['id']."'";
if ($_REQUEST['debug']=='1'){
  echo $sql."<br>";
}
$qr=mysql_query($sql);
if (mysql_num_rows($qr)>0) {
  echo json_encode('Erro facial - Já existe uma requisição processando, aguarde!');
  exit;
}
if($p['id']=='0' || $p['id']=='' || $p['id']==null){
  echo json_encode('Erro facial - Selecione um usuário existente. Em caso de dúvidas entre em contato com á Administração!');
  exit;
}
$idacesso = $p['horario_id'];
if ($p['tipom']=='morador'){
  $idacesso = '1';
}

$sql="select leitores_id from acessos where condominio_id='".$p['condominio_id']."' and tipo like '%".$p['tipom']."%'";
if ($p['restricao']!=''){
  $sql.=" and restricao in (".$p['restricao'].")";
}
if ($p['torrebloco']!=''){
  $sql.=" and torrebloco like '%".$p['torrebloco']."%'";
}
if ($p['enderecointerno']!=''){
  $sql.=" and torrebloco like '%".$p['enderecointerno']."%'";
}  

if ($_REQUEST['debug']=='1'){
  echo $sql."<br>";
}
$qr=mysql_query($sql);
$ln_setor=mysql_fetch_assoc($qr);
if (($ln_setor=='')||($ln_setor==null)){
 echo json_encode('Erro facial - Rota inexistente para usuário, por favor entrar em contato com á Administração!');
 exit();
}

$sql="select ce.id from setores se inner join centrais ce on ce.id = se.controladora_id  where se.id in (".$ln_setor['leitores_id'].") and ce.tipocentral = 'facial'";
if ($_REQUEST['debug']=='1'){
  echo $sql."<br>";
}
$qr=mysql_query($sql);
while($ln_central=mysql_fetch_assoc($qr)){
  $sql="insert into req_facial set central_id='".$ln_central['id']."',usuario_id='".$p['id']."',tipo='".$p['tipo']."',cartao='".$p['cpf']."',datahora=now(),nome='".$p['nome']."',idacesso='".$idacesso."',dti='".$p['dti']."',dtf='".$p['dtf']."',condominio_id='".$p['condominio_id']."'";
  if (($p['codigo']!=null)&&($p['codigo']!='')){
    $sql.=",codigo='".$p['codigo']."'";
  }else{
    $sql.=",foto='".$p['foto']."'";
  }
  if ($_REQUEST['debug']=='1'){
    echo $sql."<br>";
  }
  
  $qr2=mysql_query($sql);
  $qr2=mysql_query($sql);
}
echo json_encode('Foto cadastrada no facial');

?>