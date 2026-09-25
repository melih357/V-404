<?php
require __DIR__.'/backend/bootstrap.php';
if(!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit('Kurulum yalnızca localhost üzerinden açılabilir.');}
$error='';$success=false;$ready=false;
try {$ready=(bool)one("SELECT id FROM users WHERE role='admin' LIMIT 1");}catch(Throwable $e){}
$_SESSION['setup_csrf']??=bin2hex(random_bytes(24));
if($_SERVER['REQUEST_METHOD']==='POST'&&!$ready){
 try{
  if(!hash_equals($_SESSION['setup_csrf'],$_POST['csrf']??''))throw new Exception('Sayfayı yenileyin.');
  $email=trim($_POST['email']??'');$name=trim($_POST['name']??'');$pass=$_POST['password']??'';if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<12||!$name)throw new Exception('Ad, geçerli e-posta ve en az 12 karakter parola girin.');
  $dbName=env('DB_NAME','v404');if(!preg_match('/^[a-zA-Z0-9_]+$/',$dbName))throw new Exception('DB_NAME yalnızca harf, sayı ve alt çizgi içerebilir.');
  $pdo=new PDO('mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';charset=utf8mb4',env('DB_USER','root'),env('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $sql=file_get_contents(__DIR__.'/database/schema.sql');$sql=str_replace(['DATABASE IF NOT EXISTS v404','USE v404;'],['DATABASE IF NOT EXISTS `'.$dbName.'`','USE `'.$dbName.'`;'],$sql);
  foreach(explode(';',$sql) as $statement)if(trim($statement)!=='')$pdo->exec($statement);
  $lock=$pdo->query("SELECT GET_LOCK('v404_first_admin',10)")->fetchColumn();if(!$lock)throw new Exception('Kurulum meşgul. Tekrar deneyin.');
  if($pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()>0)throw new Exception('Kurulum zaten tamamlanmış.');
  $s=$pdo->prepare("INSERT INTO users(name,email,password_hash,role,verified) VALUES(?,?,?,'admin',1)");$s->execute([$name,strtolower($email),password_hash($pass,PASSWORD_DEFAULT)]);$pdo->query("SELECT RELEASE_LOCK('v404_first_admin')");$success=true;$ready=true;
 }catch(Throwable $e){$error=$e instanceof PDOException?'MySQL bağlantısı kurulamadı veya şema işlenemedi. WampServer ve .env bilgilerini kontrol edin.':$e->getMessage();}
}
?><!doctype html><html lang="tr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>V-404 • Kurulum</title><link rel="stylesheet" href="frontend/style.css"><body><main class="setup panel"><a href="index.php" class="brand"><span class="logo"></span></a><p class="eyebrow">YEREL KURULUM</p><h1>V-404'e hoş geldiniz.</h1><p>WampServer çalışırken bu sihirbaz veritabanını oluşturur ve ilk yönetici hesabını tanımlar.</p><?php if($error):?><p class="notice danger"><?=htmlspecialchars($error)?></p><?php endif;?><?php if($ready):?><p class="notice success">Kurulum tamamlandı. Kurulum ekranı kilitlendi.</p><a class="button primary" href="admin.php">Yönetim paneline git</a><a class="button" href="index.php">Siteyi aç</a><?php else:?><p class="notice">Önce <strong>.env</strong> dosyasındaki MySQL kullanıcı adı, parola ve portunu kontrol edin. PHP'de pdo_mysql, mbstring ve gd açık olmalı.</p><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['setup_csrf'])?>"><label>Yönetici adı<input name="name" maxlength="80" required autocomplete="name"></label><label>Yönetici e-postası<input name="email" type="email" required autocomplete="email"></label><label>Yönetici parolası<input name="password" type="password" minlength="12" required autocomplete="new-password"></label><button class="primary">Veritabanını kur ve hesabı oluştur</button></form><?php endif;?></main></body></html>
