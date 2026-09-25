/** V-404 local integration test. Requires a fresh test database and Node 18+.
 * V404_TEST_URL=http://127.0.0.1:8080 V404_TEST_ADMIN_EMAIL=... V404_TEST_ADMIN_PASSWORD=... node tests/integration.mjs
 * Creates test accounts/listings. NEVER run against a real populated site.
 */
import assert from 'node:assert/strict';
const base=(process.env.V404_TEST_URL||'http://127.0.0.1:8080').replace(/\/$/,'');
if(!['localhost','127.0.0.1','[::1]'].includes(new URL(base).hostname))throw Error('Only a localhost test installation is accepted.');
const run=Date.now();let checks=0;
function ok(condition,label){assert.ok(condition,label);checks++;console.log('PASS '+label);}
class Client{
 constructor(){this.cookie='';this.csrf='';}
 async call(action,data=null,params={},expected=200){const init={headers:{Cookie:this.cookie}};if(data!==null){init.method='POST';init.headers['X-CSRF-Token']=this.csrf;init.headers['Content-Type']='application/json';init.body=JSON.stringify(data);}const r=await fetch(base+'/api.php?'+new URLSearchParams({action,...params}),init);const cookie=r.headers.get('set-cookie');if(cookie)this.cookie=cookie.split(';')[0];const text=await r.text();let result;try{result=JSON.parse(text);}catch{throw Error(action+' returned non-JSON: '+text.slice(0,300));}assert.equal(r.status,expected,action+': '+text);if(action==='session')this.csrf=result.csrf;return result;}
 async register(label){await this.call('session');this.email=`${label}.${run}@example.test`;const r=await this.call('register',{name:'Test '+label,email:this.email,password:'Test-pass-404-strong',adult:1,consent:1});ok(/^\d{6}$/.test(r.local_code),'local verification '+label);await this.call('verify',{code:r.local_code});this.user=(await this.call('session')).user;return this;}
}
const owner=await new Client().register('owner'),claimant=await new Client().register('claimant'),other=await new Client().register('other'),guest=new Client();await guest.call('session');
const admin=new Client();await admin.call('session');await admin.call('login',{email:process.env.V404_TEST_ADMIN_EMAIL,password:process.env.V404_TEST_ADMIN_PASSWORD});await admin.call('session');
const today=new Date().toISOString().slice(0,10);
const listing={type:'found',title:'Test lacivert çanta',category:'Çanta ve cüzdan',description:'Düz lacivert çanta bulundu.',city:'İstanbul',district:'Kadıköy',event_date:today,color:'Lacivert',brand:'TestMarka',model:'M1',secret:'İç cebinde mor düğme',route:'Kadıköy sahil',private_location:'Dolap 14',latitude:41.0123456,longitude:29.0345678,route_points:'[[41.01,29.03],[41.02,29.04]]',custody:'Kurumun kayıp eşya biriminde',condition_text:'',health:'',visibility:'public',alert:1,rules:1,preview_confirm:1};
await guest.call('create_listing',listing,{},401);ok(true,'guest mutation denied');
const found=await owner.call('create_listing',listing);const lost=await claimant.call('create_listing',{...listing,type:'lost',title:'Test lacivert çantam kayıp'});
const publicData=await guest.call('listing',null,{id:found.id});ok(!('secret' in publicData)&&!('route' in publicData)&&!('private_location' in publicData),'private listing fields not leaked');
const own=await owner.call('listing',null,{id:found.id});ok(own.secret===listing.secret,'owner private fields available');
ok(Number(publicData.latitude)===41.01&&Number(publicData.longitude)===29.03&&!('route_points' in publicData),'map exact coordinates and route hidden from visitors');ok(Number(own.latitude)===listing.latitude&&JSON.parse(own.route_points).length===2,'owner sees exact map and private route');
const matches=await claimant.call('matches');ok(matches.some(m=>Number(m.found_id)===found.id&&Number(m.lost_id)===lost.id),'matching on shared features');
const before=(await claimant.call('notifications')).length;await owner.call('edit_listing',{...listing,id:found.id});const after=(await claimant.call('notifications')).length;ok(before===after,'match notification deduplicated');
await other.call('edit_listing',{...listing,id:found.id},{},403);ok(true,'unauthorized edit denied');
await owner.call('edit_listing',{...listing,id:found.id,type:'free'},{},400);ok(true,'found item cannot become free item');
const claim=await claimant.call('claim',{listing_id:found.id,proof:'İç cebinde mor düğme var.'});await claimant.call('messages',null,{id:claim.id},403);ok(true,'chat unavailable before acceptance');
await other.call('messages',null,{id:claim.id},403);await other.call('review_claim',{id:claim.id,decision:'accepted'},{},403);ok(true,'other users cannot access or approve claims');
await owner.call('review_claim',{id:claim.id,decision:'accepted'});await claimant.call('send_message',{id:claim.id,body:'Danışmadan teslim alabilirim.'});const chat=await owner.call('messages',null,{id:claim.id});ok(chat.items.length===1,'accepted claim opens private chat');
await claimant.call('delivery',{id:claim.id});ok((await owner.call('listing',null,{id:found.id})).status==='reserved','one delivery confirmation does not close listing');
await owner.call('delivery',{id:claim.id,institution_delivery:true},{},403);ok(true,'ordinary owner cannot impersonate institution delivery');
await owner.call('delivery',{id:claim.id});ok((await guest.call('listing',null,{id:found.id})).status==='delivered','two-sided delivery completes');
await claimant.call('send_message',{id:claim.id,body:'Late'},{},400);ok(true,'completed chat is read-only');
const org=await owner.call('create_org',{name:'Test Okul '+run,kind:'Okul',city:'İstanbul'});await owner.call('org_panel',null,{id:org.id},403);ok(true,'pending institution cannot be used');await admin.call('admin_action',{op:'org',id:org.id,status:'approved'});
await owner.call('org_location',{org_id:org.id,name:'Merkez',kind:'branch'});let panel=await owner.call('org_panel',null,{id:org.id});const branch=panel.locations[0].id;
await owner.call('org_location',{org_id:org.id,name:'B Binası',kind:'building',parent_id:branch});panel=await owner.call('org_panel',null,{id:org.id});const building=panel.locations.find(l=>l.kind==='building').id;
await owner.call('org_location',{org_id:org.id,name:'Diğer Şube',kind:'branch'});panel=await owner.call('org_panel',null,{id:org.id});const otherBranch=panel.locations.find(l=>l.name==='Diğer Şube').id;
await owner.call('org_location',{org_id:org.id,name:'Invalid room',kind:'room',parent_id:branch},{},400);ok(true,'institution hierarchy enforced');
const privateItem=await owner.call('create_listing',{...listing,visibility:'org',org_id:org.id,location_id:building});await guest.call('listing',null,{id:privateItem.id},404);ok(true,'private institution listing inaccessible to guest');
const invite=await owner.call('org_invite',{org_id:org.id});await claimant.call('join_org',{org_id:org.id,code:invite.code});await other.call('join_org',{org_id:org.id,code:invite.code},{},400);ok(true,'single-use invitation enforced');
await other.call('join_org',{org_id:org.id});await other.call('listing',null,{id:privateItem.id},404);ok(true,'pending membership cannot see institution item');
const memberItem=await claimant.call('listing',null,{id:privateItem.id});ok(!('private_location' in memberItem),'ordinary institution member does not see exact location');
panel=await owner.call('org_panel',null,{id:org.id});const cm=panel.members.find(x=>Number(x.user_id)===Number(claimant.user.id));await owner.call('org_member',{org_id:org.id,id:cm.id,op:'save',role:'staff',location_id:otherBranch});await claimant.call('edit_listing',{...listing,id:privateItem.id,visibility:'org',org_id:org.id,location_id:building},{},403);ok(true,'staff cannot manage another branch');
await claimant.call('create_listing',{...listing,visibility:'org',org_id:org.id,location_id:building},{},403);ok(true,'staff cannot create a record outside assigned branch');
await owner.call('org_member',{org_id:org.id,id:cm.id,op:'save',role:'staff',location_id:branch});ok((await claimant.call('listing',null,{id:privateItem.id})).private_location==='Dolap 14','staff inherited scope can access exact custody');
await other.call('admin_panel',null,{section:'users'},403);ok(true,'platform admin API role protected');
const report=await other.call('report',{target_type:'listing',target_id:found.id,reason:'Diğer',body:'Test bildirim'});const reports=await admin.call('admin_panel',null,{section:'reports'});const rr=reports.find(r=>Number(r.user_id)===Number(other.user.id));await admin.call('admin_action',{op:'report',id:rr.id,status:'resolved',reply:'İnceleme tamamlandı.'});ok((await other.call('tickets')).reports[0].reply==='İnceleme tamamlandı.','moderation reply reaches user');
await owner.call('block',{target_id:other.user.id});const free=await owner.call('create_listing',{...listing,type:'free',title:'Ücretsiz test eşya'});await other.call('claim',{listing_id:free.id,proof:'İstiyorum'},{},403);ok(true,'block prevents new claim');
const csrf=owner.csrf;owner.csrf='invalid';await owner.call('profile',{name:'CSRF',notifications:1},{},419);owner.csrf=csrf;ok(true,'CSRF mutation denied');
console.log(JSON.stringify({passed:checks,base,run},null,2));
