<?php
// CLI only. Schedule daily in Windows Task Scheduler.
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require __DIR__.'/bootstrap.php';
try {
 if(($argv[1]??'')==='anonymize'){
  $id=(int)($argv[2]??0);$user=one('SELECT * FROM users WHERE id=?',[$id]);
  if(!$user||$user['role']==='admin')throw new RuntimeException('Kullanıcı bulunamadı veya yönetici hesabı.');
  if(($argv[3]??'')!=='CONFIRM')throw new RuntimeException('Silme isteğini doğruladıktan sonra: php backend/maintenance.php anonymize KULLANICI_ID CONFIRM');
  db()->beginTransaction();query("UPDATE users SET name='Silinen kullanıcı',email=?,password_hash=?,status='closed',notifications=0 WHERE id=?",['deleted-'.$id.'@invalid.local',password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),$id]);
  $images=all('SELECT path FROM photos WHERE user_id=?',[$id]);query('DELETE FROM photos WHERE user_id=?',[$id]);
  query("UPDATE listings SET title='Silinen ilan',description='',secret='',route='',private_location='',latitude=NULL,longitude=NULL,route_points=NULL,health='',condition_text='',status='deleted',closed_at=NOW() WHERE user_id=?",[$id]);
  query("UPDATE claims SET proof='',chat_closed=1 WHERE user_id=? OR listing_id IN (SELECT id FROM listings WHERE user_id=?)",[$id,$id]);
  query('DELETE FROM messages WHERE user_id=?',[$id]);query('DELETE FROM memberships WHERE user_id=?',[$id]);query('DELETE FROM notifications WHERE user_id=?',[$id]);
  query("UPDATE tickets SET body='Kişisel veri talebiyle temizlendi',reply=NULL,subject='Silinen başvuru' WHERE user_id=?",[$id]);query("UPDATE reports SET body='Kişisel veri talebiyle temizlendi',reply=NULL WHERE user_id=?",[$id]);
  query('DELETE FROM blocks WHERE user_id=? OR target_id=?',[$id,$id]);query('DELETE FROM verifications WHERE user_id=?',[$id]);db()->commit();foreach($images as $p)@unlink(__DIR__.'/../uploads/'.$p['path']);echo "Kullanıcı anonimleştirildi.\n";exit;
 }
 expire();
 $ids=array_column(all("SELECT id FROM listings WHERE status IN ('closed','delivered','deleted','archived') AND COALESCE(closed_at,expires_at)<DATE_SUB(NOW(),INTERVAL 180 DAY)"),'id');
 foreach($ids as $id){$images=all('SELECT path FROM photos WHERE listing_id=?',[$id]);query('DELETE FROM photos WHERE listing_id=?',[$id]);foreach($images as $p)@unlink(__DIR__.'/../uploads/'.$p['path']);query("UPDATE listings SET secret='',route='',private_location='',latitude=NULL,longitude=NULL,route_points=NULL WHERE id=?",[$id]);query('DELETE m FROM messages m JOIN claims c ON c.id=m.claim_id WHERE c.listing_id=?',[$id]);query("UPDATE claims SET proof='',chat_closed=1 WHERE listing_id=?",[$id]);}
 query('DELETE FROM audit WHERE created_at<DATE_SUB(NOW(),INTERVAL 365 DAY)');query('DELETE FROM rate_limits WHERE reset_at<?',[time()]);query('DELETE FROM verifications WHERE expires_at<NOW()');
 query('DELETE FROM reports WHERE created_at<DATE_SUB(NOW(),INTERVAL 365 DAY) AND status=\'resolved\'');
 query('DELETE FROM notifications WHERE created_at<DATE_SUB(NOW(),INTERVAL 180 DAY)');
 echo 'Bakım tamamlandı. Temizlenen ilan sayısı: '.count($ids)."\n";
}catch(Throwable $e){if(db()->inTransaction())db()->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
