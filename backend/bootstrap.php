<?php
declare(strict_types=1);
ini_set('display_errors','0');
$env = parse_ini_file(__DIR__.'/../.env',false,INI_SCANNER_RAW) ?: [];
function env(string $key,string $fallback=''): string {global $env;return (string)($env[$key]??$fallback);}
date_default_timezone_set('Europe/Istanbul');
if (session_status()!==PHP_SESSION_ACTIVE) {session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','path'=>'/']);session_start();}
header('X-Content-Type-Options: nosniff');header('Referrer-Policy: same-origin');header('X-Frame-Options: DENY');
function db(): PDO {static $db; if(!$db){$db=new PDO('mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';dbname='.env('DB_NAME','v404').';charset=utf8mb4',env('DB_USER','root'),env('DB_PASS'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}return $db;}
function query(string $sql,array $args=[]): PDOStatement {$s=db()->prepare($sql);$s->execute($args);return $s;}
function one(string $sql,array $args=[]): ?array {return query($sql,$args)->fetch()?:null;}
function all(string $sql,array $args=[]): array {return query($sql,$args)->fetchAll();}
function fail(string $message,int $status=400): never {http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode(['error'=>$message],JSON_UNESCAPED_UNICODE);exit;}
function out(mixed $data=[]): never {header('Content-Type: application/json; charset=utf-8');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
function me(bool $required=true): ?array {$u=isset($_SESSION['uid'])?one('SELECT id,name,email,role,verified,adult,notifications,status FROM users WHERE id=?',[$_SESSION['uid']]):null;if($u&&$u['status']==='closed'){$u=null;unset($_SESSION['uid']);}if(!$u&&$required)fail('Devam etmek için giriş yapın.',401);return $u;}
function active(): array {$u=me();if(!$u['verified'])fail('Önce e-posta adresinizi doğrulayın.',403);if(!$u['adult'])fail('İşlemi veli hesabı veya okul yetkilisi yürütmelidir.',403);if($u['status']!=='active')fail('Hesabınız geçici olarak kısıtlandı.',403);return $u;}
function admin(bool $moderator=true): array {$u=active();if(!in_array($u['role'],$moderator?['admin','moderator']:['admin'],true))fail('Bu işlem için yetkiniz yok.',403);return $u;}
function str(array $d,string $k,int $max=2000,bool $required=false): string {$s=trim((string)($d[$k]??''));if(mb_strlen($s)>$max||($required&&$s===''))fail($k.' alanını kontrol edin.');return $s;}
function choose(array $d,string $key,array $values,string $default=''): string {$v=(string)($d[$key]??$default);if(!in_array($v,$values,true))fail('Geçersiz seçim: '.$key);return $v;}
function audit(int $uid,string $action,int $target=0,string $detail=''): void {query('INSERT INTO audit(user_id,action,target_id,detail) VALUES(?,?,?,?)',[$uid,$action,$target?:null,$detail]);}
function notify(int $uid,string $title,?int $listing=null): void {query('INSERT INTO notifications(user_id,title,listing_id) VALUES(?,?,?)',[$uid,$title,$listing]);}
function rate(string $key,int $limit=10,int $window=900): void {$key=hash('sha256',$key);query('INSERT IGNORE INTO rate_limits(bucket,attempts,reset_at) VALUES(?,0,?)',[$key,time()+$window]);query('UPDATE rate_limits SET attempts=IF(reset_at<?,1,attempts+1),reset_at=IF(reset_at<?,?,reset_at) WHERE bucket=?',[time(),time(),time()+$window,$key]);if((int)one('SELECT attempts FROM rate_limits WHERE bucket=?',[$key])['attempts']>$limit)fail('Çok fazla deneme. Bir süre sonra tekrar deneyin.',429);}
function membership(int $uid,int $org): ?array {return one("SELECT m.* FROM memberships m JOIN organizations o ON o.id=m.org_id WHERE m.user_id=? AND m.org_id=? AND m.status='approved' AND o.status='approved'",[$uid,$org]);}
function inLocation(?int $loc,int $assigned,int $org): bool {for($i=0;$loc&&$i<8;$i++){if($loc===$assigned)return true;$p=one('SELECT parent_id FROM locations WHERE id=? AND org_id=?',[$loc,$org]);$loc=$p&&$p['parent_id']?(int)$p['parent_id']:null;}return false;}
function orgAccess(array $u,int $org,bool $manage=false): array {$m=membership((int)$u['id'],$org);if(!$m||($manage&&$m['role']!=='org_admin'))fail('Kurum yetkiniz yok.',403);return $m;}
function staffFor(array $u,array $l): bool {if(!$l['org_id'])return false;$m=membership((int)$u['id'],(int)$l['org_id']);if(!$m)return false;if($m['role']==='org_admin')return true;return in_array($m['role'],['staff','location_admin'],true)&&$m['location_id']&&inLocation($l['location_id']?(int)$l['location_id']:null,(int)$m['location_id'],(int)$l['org_id']);}
function canManage(array $u,array $l): bool {return (int)$l['user_id']===(int)$u['id']||staffFor($u,$l);}
function listing(int $id,?array $u): array {$l=one('SELECT l.*,u.name AS owner_name,o.name AS org_name FROM listings l JOIN users u ON u.id=l.user_id LEFT JOIN organizations o ON o.id=l.org_id WHERE l.id=?',[$id]);if(!$l||$l['status']==='deleted')fail('İlan bulunamadı.',404);$priv=$u&&(canManage($u,$l)||in_array($u['role'],['admin','moderator'],true));if(!$priv&&($l['status']==='hidden'||($l['visibility']==='org'&&(!$u||!membership((int)$u['id'],(int)$l['org_id'])))))fail('İlan bulunamadı.',404);return $l;}
function publicListing(array $l,?array $u): array {$manage=$u&&canManage($u,$l);$l['can_manage']=$manage;$l['can_staff']=$u&&staffFor($u,$l);if(!$manage){unset($l['secret'],$l['route'],$l['route_points'],$l['private_location'],$l['location_id']);if($l['latitude']!==null)$l['latitude']=round((float)$l['latitude'],2);if($l['longitude']!==null)$l['longitude']=round((float)$l['longitude'],2);} $l['photos']=all('SELECT id FROM photos WHERE listing_id=?',[$l['id']]);return $l;}
function blocked(int $a,int $b): bool {return (bool)one('SELECT user_id FROM blocks WHERE (user_id=? AND target_id=?) OR (user_id=? AND target_id=?)',[$a,$b,$b,$a]);}
function claimAccess(int $id,array $u): array {$c=one('SELECT * FROM claims WHERE id=?',[$id]);if(!$c)fail('Talep bulunamadı.',404);$l=listing((int)$c['listing_id'],$u);if((int)$c['user_id']!==(int)$u['id']&&!canManage($u,$l))fail('Bu görüşmeye erişemezsiniz.',403);return [$c,$l];}
function daysFor(string $type): int {return in_array($type,['free','adoption'])?30:(str_starts_with($type,'pet_')?60:90);}
function expire(): void {query("UPDATE listings SET status='archived',closed_at=NOW() WHERE expires_at<NOW() AND status IN ('open','reserved')");}
function matchListing(int $id): void {
 $new=one("SELECT * FROM listings WHERE id=? AND status='open'",[$id]);if(!$new||!in_array($new['type'],['lost','found','pet_lost','pet_found']))return;
 $other=['lost'=>'found','found'=>'lost','pet_lost'=>'pet_found','pet_found'=>'pet_lost'][$new['type']];
 $candidates=all("SELECT * FROM listings WHERE type=? AND category=? AND city=? AND status='open' AND expires_at>NOW() AND user_id<>? AND ABS(DATEDIFF(event_date,?))<=30",[$other,$new['category'],$new['city'],$new['user_id'],$new['event_date']]);
 foreach($candidates as $c){if(blocked((int)$new['user_id'],(int)$c['user_id']))continue;if(($new['visibility']==='org'||$c['visibility']==='org')&&(!$new['org_id']||$new['org_id']!==$c['org_id']))continue;
 $score=45;foreach(['color'=>12,'brand'=>12,'model'=>8,'district'=>8] as $k=>$v){if($new[$k]&&mb_strtolower($new[$k])===mb_strtolower($c[$k]))$score+=$v;}
 if(abs(strtotime($new['event_date'])-strtotime($c['event_date']))<=7*86400)$score+=8;
 $words=preg_split('/\W+/u',mb_strtolower($new['description'].' '.$new['title']),-1,PREG_SPLIT_NO_EMPTY);$text=mb_strtolower($c['description'].' '.$c['title']);foreach(array_unique($words) as $w){if(mb_strlen($w)>=4&&str_contains($text,$w)){$score+=5;break;}}
 if($new['route']&&$c['district']&&mb_stripos($new['route'],$c['district'])!==false)$score+=5;
 if($c['route']&&$new['district']&&mb_stripos($c['route'],$new['district'])!==false)$score+=5;
 $aLat=$new['latitude'];$aLon=$new['longitude'];$bLat=$c['latitude'];$bLon=$c['longitude'];
 if($aLat!==null&&$bLat!==null&&geoKm((float)$aLat,(float)$aLon,(float)$bLat,(float)$bLon)<5)$score+=5;
 foreach([[$new,$c],[$c,$new]] as $pair){$pts=json_decode($pair[0]['route_points']??'[]',true)?:[];if($pair[1]['latitude']!==null)foreach($pts as $pt){if(geoKm((float)$pt[0],(float)$pt[1],(float)$pair[1]['latitude'],(float)$pair[1]['longitude'])<2){$score+=5;break;}}}
 if($score<60)continue;$lost=str_contains($new['type'],'lost')?$new:$c;$found=str_contains($new['type'],'found')?$new:$c;
 $s=query('INSERT IGNORE INTO matches(lost_id,found_id,score) VALUES(?,?,?)',[$lost['id'],$found['id'],min(99,$score)]);if($s->rowCount()&&$lost['alert']&&one('SELECT notifications FROM users WHERE id=?',[$lost['user_id']])['notifications'])notify((int)$lost['user_id'],'Bu eşya senin olabilir mi? Olası bir eşleşme var.',(int)$found['id']);
 }
}

function geoKm(float $a,float $b,float $c,float $d): float {$lat=deg2rad($c-$a);$lon=deg2rad($d-$b);$h=sin($lat/2)**2+cos(deg2rad($a))*cos(deg2rad($c))*sin($lon/2)**2;return 6371*2*asin(min(1,sqrt($h)));}
