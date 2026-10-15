const base=process.env.NX_TEST_URL||'http://127.0.0.1:8766/';
const file=process.env.NX_TEST_FILE||'nyaaxport.php';
function assert(ok,message){if(!ok)throw new Error(message);console.log('PASS '+message)}
let r=await fetch(base+file+'?lang=en');let html=await r.text();
assert(r.headers.get('x-robots-tag')==='noindex, nofollow' && html.includes('name="robots" content="noindex, nofollow"'),'HTTP and HTML block indexing/following');
let cookie=r.headers.get('set-cookie').split(';')[0];
const csrf=html.match(/name="csrf" value="([^"]+)"/)[1];
r=await fetch(base+file+'?lang=en',{method:'POST',headers:{Cookie:cookie},body:new URLSearchParams({csrf,access_key:'local-regression-key'})});
cookie=r.headers.get('set-cookie').split(';')[0];html=await r.text();assert(/<form[^>]*data-export/.test(html),'Authenticated CMS export form');
const token=html.match(/name="csrf" value="([^"]+)"/)[1];
for(const format of ['csv','xml','xls','xlsx','google','meta','yml']){
    const data=new URLSearchParams({csrf:token,export:'1',ajax:'1',format,store_id:'1',data_language_id:'1'});
    for(const field of ['name','sku','price'])data.append('fields[]',field);
    const response=await fetch(base+file+'?lang=en',{method:'POST',headers:{Cookie:cookie},body:data});
    const bytes=Buffer.from(await response.arrayBuffer());
    assert(response.ok&&response.headers.get('content-disposition')?.includes('attachment'),format+' returns an attachment'+(response.ok?'':': '+bytes.toString()));
    assert(response.headers.get('x-robots-tag')==='noindex, nofollow',format+' download has noindex header');
    if(format==='xlsx')assert(bytes[0]===0x50&&bytes[1]===0x4b,'XLSX ZIP signature');
    else assert(bytes.toString().includes('Product 2')&&!bytes.toString().includes('Product 1'),format+' contains only selected storefront');
}
const empty=new URLSearchParams({csrf:token,export:'1',ajax:'1',format:'csv',price_min:'1000'});empty.append('fields[]','name');
r=await fetch(base+file+'?lang=en',{method:'POST',headers:{Cookie:cookie},body:empty});
assert(r.status===422&&!r.headers.get('content-disposition')&&(await r.text()).includes('No products matched'),'Empty selection returns actionable error without attachment');
