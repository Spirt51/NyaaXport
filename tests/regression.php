<?php
ob_start();
date_default_timezone_set('UTC');
// Load function definitions without running a web controller or accessing a store.
function loadFunctions($file, $rename = []) {
    $tokens = token_get_all(file_get_contents($file));
    $code = '';
    for ($i=0; $i<count($tokens); $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0]!==T_FUNCTION) continue;
        $j=$i+1;
        while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0]===T_WHITESPACE) $j++;
        if (!isset($tokens[$j]) || !is_array($tokens[$j]) || $tokens[$j][0]!==T_STRING) continue;
        $name=$tokens[$j][1];
        $depth=0;$started=false;$function='';
        for (; $i<count($tokens);$i++) {
            $token=$tokens[$i];$text=is_array($token)?$token[1]:$token;
            if (is_array($token) && in_array($token[0],[T_CURLY_OPEN,T_DOLLAR_OPEN_CURLY_BRACES],true)) $depth++;
            elseif ($text==='{') {$depth++;$started=true;}
            if ($text==='}') $depth--;
            if ($i===$j && isset($rename[$name])) $text=$rename[$name];
            $function.=$text;
            if ($started && $depth===0) break;
        }
        $code.=$function."\n";
    }
    eval($code);
}
loadFunctions(__DIR__.'/../src/common.php');
loadFunctions(__DIR__.'/../src/cms.php',['fields'=>'cmsFields']);
loadFunctions(__DIR__.'/../src/cloud.php',['apiRequest'=>'realApiRequest','h'=>'cloudH']);
$_SESSION=['nyaaxport_lang'=>'en'];
$lang='en';
$NYAA_I18N=['en'=>['select_field'=>'Select at least one field.']];
define('NYAAXPORT_BATCH_SIZE',500);
function check($condition,$message) { if(!$condition)throw new RuntimeException($message); echo 'PASS '.$message."\n"; }
function captured($fn) { ob_start();try {$fn();return ob_get_contents();} finally {ob_end_clean();} }
function expectFailure($fn,$message) { try {$fn();} catch(Exception $e) { check(true,$message);return; } throw new RuntimeException($message); }

$sql=sqlFor(['id'=>'opencart','db'=>['prefix'=>'oc_'],'store_id'=>2,'data_language_id'=>1]);
check(strpos($sql,'product_to_store')!==false && strpos($sql,'pts.store_id=2')!==false,'OpenCart storefront constrains product membership');
$sql=sqlFor(['id'=>'simpla','db'=>['prefix'=>'s_']]);
check(strpos($sql,'MIN(v0.id)')!==false,'Simpla base variant selection is deterministic');

$headersCalled=false;
$output=captured(function()use(&$headersCalled){
    try { nxDownload('csv',function(){echo str_repeat('x',140000);throw new RuntimeException('broken page 2');},function()use(&$headersCalled){$headersCalled=true;}); }
    catch(RuntimeException $e) {}
});
check($output==='' && !$headersCalled,'Failed late export sends no bytes or download headers');
$output=captured(function(){nxDownload('csv',function(){echo 'complete';},function(){});});
check($output==='complete','Prepared export preserves exact bytes');

$sample=[['id'=>1,'name'=>'A','price'=>'100','special_price'=>'','currency'=>'EUR','quantity'=>3],['id'=>2,'name'=>'B','price'=>'90','special_price'=>'80','currency'=>'UAH','quantity'=>1]];
$xml=captured(function()use($sample){exportMarketplaceXml($sample,'yml',['base_url'=>'https://example.com/']);});
$doc=simplexml_load_string($xml);
check($doc!==false && count($doc->shop->currencies->currency)===2,'YML declares every currency used by offers');
check((string)$doc->shop->offers->offer[1]->price==='80','Sale price used only when lower than regular');
expectFailure(function()use($sample){$sample[0]['currency']='';captured(function()use($sample){exportMarketplaceXml($sample,'yml',[]);});},'YML rejects missing currency instead of inventing USD');
$csv=captured(function(){exportMeta([['id'=>'=1+1','name'=>'+cmd']]);});
check(strpos($csv,"'=1+1")!==false && strpos($csv,"'+cmd")!==false,'Meta CSV neutralizes formula cells');
$xlsx=captured(function(){exportXlsx([['id'=>1,'name'=>'A & B']],['name'=>'Name']);});
$file=tempnam(sys_get_temp_dir(),'nx_test_');file_put_contents($file,$xlsx);$zip=new ZipArchive();
check($zip->open($file)===true && $zip->locateName('xl/worksheets/sheet1.xml')!==false,'XLSX is a readable workbook archive');$zip->close();unlink($file);
foreach(['http://example.com/api','file:///etc/passwd','https://127.0.0.1/','https://localhost/','https://user:pass@example.com/','https://example.com:8443/','https://[::1]/'] as $url)expectFailure(function()use($url){publicApiTarget($url);},'Reject unsafe API target '.$url);
foreach(['127.0.0.1','10.1.2.3','169.254.169.254','100.64.0.1','224.0.0.1','::1','::ffff:127.0.0.1','2002:7f00:1::'] as $ip)check(!publicApiIp($ip),'Reject nonpublic IP '.$ip);
check(publicApiIp('8.8.8.8') && publicApiIp('2606:4700:4700::1111'),'Public IP validation accepts routable addresses');

$calls=0;
function apiRequest($method,$url,$headers=[],$body=null) {
    global $calls;
    $calls++;
    $variant=['id'=>'gid://shopify/ProductVariant/1','sku'=>'SKU','price'=>'80','compareAtPrice'=>'100','inventoryQuantity'=>2];
    if(strpos($body['query'],'query Variants')!==false) {
        $variant['id']='gid://shopify/ProductVariant/101';
        return [['data'=>['product'=>['variants'=>['nodes'=>[$variant],'pageInfo'=>['hasNextPage'=>false,'endCursor'=>'last']]]]],[]];
    }
    $variants=[];for($i=1;$i<=100;$i++){$v=$variant;$v['id']='gid://shopify/ProductVariant/'.$i;$variants[]=$v;}
    return [['data'=>['shop'=>['currencyCode'=>'EUR'],'products'=>['nodes'=>[['id'=>'gid://shopify/Product/1','title'=>'Test','status'=>'ACTIVE','onlineStoreUrl'=>'https://example.com/product','variants'=>['nodes'=>$variants,'pageInfo'=>['hasNextPage'=>true,'endCursor'=>'page1']]]],'pageInfo'=>['hasNextPage'=>false,'endCursor'=>'end']]]],[]];
}
$rows=iterator_to_array(shopifyRows('test.myshopify.com','test-token'));
check(count($rows)===101 && $calls===2,'Shopify follows variant pagination past 100 rows');
check($rows[100]['price']==='100' && $rows[100]['special_price']==='80' && $rows[100]['currency']==='EUR','Shopify comparison price maps to regular and actual price to sale');
expectFailure(function(){iterator_to_array(shopifyRows('evil.example.com','token'));},'Shopify rejects non-Shopify token destination');
echo "All regression checks passed.\n";
