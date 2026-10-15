import {copyFileSync} from 'node:fs';
const throttle='throttle-'+Date.now()+'.php';
copyFileSync('.test-runtime/web/nyaaxport_cloud.php','.test-runtime/web/'+throttle);
const base='http://127.0.0.1:8766/';
function assert(value,message){if(!value)throw new Error(message);console.log('PASS '+message)}
async function session(path){const response=await fetch(base+path);const html=await response.text();return {cookie:response.headers.get('set-cookie').split(';')[0],csrf:html.match(/name="csrf" value="([^"]+)"/)[1]};}
async function post(path,s,values){return fetch(base+path,{method:'POST',headers:{Cookie:s.cookie},body:new URLSearchParams({csrf:s.csrf,...values})});}
const login=await session('nyaaxport_cloud.php?lang=en');
const valid=await post('nyaaxport_cloud.php?lang=en',login,{access_key:'local-regression-key'});
assert(valid.headers.get('set-cookie').split(';')[0]!==login.cookie,'Login rotates session ID');
assert(/<form[^>]*data-export/.test(await valid.text()),'Valid login shows exporter');
const cookie=valid.headers.get('set-cookie').split(';')[0];
const csrfPage=await fetch(base+'nyaaxport_cloud.php?lang=en',{headers:{Cookie:cookie}});
const csrf=(await csrfPage.text()).match(/name="csrf" value="([^"]+)"/)[1];
const invalid=await post('nyaaxport_cloud.php?lang=en',{cookie,csrf:'bad'},{export:'1',ajax:'1'});
assert(invalid.status===422 && !(invalid.headers.get('content-disposition')),'Invalid CSRF never starts download');
const failed=await post('nyaaxport_cloud.php?lang=en',{cookie,csrf},{export:'1',ajax:'1',provider:'json',token:'test',endpoint:'http://127.0.0.1',format:'xml'});
assert(failed.status===422 && !failed.headers.get('content-disposition') && !(await failed.text()).includes('<products>'),'Cloud error returns plain error without partial file');
for(let i=0;i<8;i++){const fresh=await session(throttle+'?lang=en');const r=await post(throttle+'?lang=en',fresh,{access_key:'wrong'});await r.text();}
const fresh=await session(throttle+'?lang=en');
const blocked=await post(throttle+'?lang=en',fresh,{access_key:'local-regression-key'});
assert((await blocked.text()).includes('Too many attempts.'),'Fresh cookie cannot bypass IP throttle');
const logout=await post('nyaaxport_cloud.php',{cookie,csrf},{logout:'1'});
assert(!/<form[^>]*data-export/.test(await logout.text()),'Logout removes authenticated state');
