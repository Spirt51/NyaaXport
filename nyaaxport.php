<?php
if (PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 90000) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('NyaaXport: use nyaaxport.php with PHP 7.x or 8.x. For PHP 5.6 use nyaaxport_56.php.');
}
/**
 * NyaaXport — single-file product exporter for OpenCart, WooCommerce,
 * PrestaShop and 1C-Bitrix.
 * Author: Yevhen Levchenko
 *
 * Upload this file to the web-site root and CHANGE THE ACCESS KEY below.
 * Main build for PHP 7.x–8.x and MySQL/MariaDB through PDO.
 */

const NYAAXPORT_ACCESS_KEY = 'CHANGE-ME-NOW';
const NYAAXPORT_BATCH_SIZE = 500;
const NYAAXPORT_SESSION_TTL = 1800;
const NYAAXPORT_MAX_LOGIN_ATTEMPTS = 8;
const NYAAXPORT_ALLOWED_IPS = '';

session_name('nyaaxport_session');
session_start();

if (NYAAXPORT_ALLOWED_IPS !== '') {
    $allowedIps = array_map('trim', explode(',', NYAAXPORT_ALLOWED_IPS));
    $remoteIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!in_array($remoteIp, $allowedIps, true)) { http_response_code(403); exit('Access denied.'); }
}
if (isset($_SESSION['nyaaxport_last_seen']) && time() - (int)$_SESSION['nyaaxport_last_seen'] > NYAAXPORT_SESSION_TTL) {
    unset($_SESSION['nyaaxport_ok']);
}
$_SESSION['nyaaxport_last_seen'] = time();
if (!isset($_SESSION['nyaaxport_csrf'])) {
    $_SESSION['nyaaxport_csrf'] = function_exists('random_bytes') ? bin2hex(random_bytes(24)) : bin2hex(openssl_random_pseudo_bytes(24));
}
if (!isset($_SESSION['nyaaxport_attempts'])) $_SESSION['nyaaxport_attempts'] = 0;

$NYAA_I18N = [
    'ru'=>['subtitle'=>'Экспорт товаров из CMS','logout'=>'Выйти','access_key'=>'Ключ доступа','open'=>'Открыть','change_key'=>'Перед использованием замените CHANGE-ME-NOW в начале файла на длинный уникальный ключ.','export_fields'=>'Поля выгрузки','format'=>'Формат','download'=>'Скачать товары','safety'=>'После выгрузки удалите файл с сервера или переименуйте его. Экспорт читает базу данных без внесения изменений.','select_field'=>'Выберите хотя бы одно поле.','id'=>'ID','name'=>'Название','sku'=>'Артикул','url'=>'URL','price'=>'Цена','special_price'=>'Акционная цена','quantity'=>'Количество','status'=>'Статус','manufacturer'=>'Производитель','category'=>'Категория','description'=>'Описание','image'=>'Изображение'],
    'uk'=>['subtitle'=>'Експорт товарів із CMS','logout'=>'Вийти','access_key'=>'Ключ доступу','open'=>'Відкрити','change_key'=>'Перед використанням замініть CHANGE-ME-NOW на початку файлу на довгий унікальний ключ.','export_fields'=>'Поля експорту','format'=>'Формат','download'=>'Завантажити товари','safety'=>'Після експорту видаліть файл із сервера або перейменуйте його. Експорт лише читає базу даних і нічого не змінює.','select_field'=>'Виберіть хоча б одне поле.','id'=>'ID','name'=>'Назва','sku'=>'Артикул','url'=>'URL','price'=>'Ціна','special_price'=>'Акційна ціна','quantity'=>'Кількість','status'=>'Статус','manufacturer'=>'Виробник','category'=>'Категорія','description'=>'Опис','image'=>'Зображення'],
    'en'=>['subtitle'=>'CMS product export','logout'=>'Log out','access_key'=>'Access key','open'=>'Open','change_key'=>'Before use, replace CHANGE-ME-NOW at the top of the file with a long unique key.','export_fields'=>'Export fields','format'=>'Format','download'=>'Download products','safety'=>'Delete or rename this file on the server after exporting. The exporter only reads the database and makes no changes.','select_field'=>'Select at least one field.','id'=>'ID','name'=>'Name','sku'=>'SKU','url'=>'URL','price'=>'Price','special_price'=>'Sale price','quantity'=>'Quantity','status'=>'Status','manufacturer'=>'Manufacturer','category'=>'Category','description'=>'Description','image'=>'Image']
];
$requestedLang = isset($_GET['lang']) ? strtolower((string)$_GET['lang']) : '';
if (in_array($requestedLang, ['ru','uk','en'], true)) $_SESSION['nyaaxport_lang'] = $requestedLang;
if (!isset($_SESSION['nyaaxport_lang'])) {
    $browserLang = strtolower(substr(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'ru', 0, 2));
    $_SESSION['nyaaxport_lang'] = in_array($browserLang, ['ru','uk','en'], true) ? $browserLang : 'ru';
}
$lang = $_SESSION['nyaaxport_lang'];
function t($key) { global $NYAA_I18N, $lang; return isset($NYAA_I18N[$lang][$key]) ? $NYAA_I18N[$lang][$key] : $key; }

function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function xmlv($value) { return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function ident($value) {
    if (!preg_match('/^[A-Za-z0-9_]*$/', $value)) { throw new RuntimeException('Unsafe database prefix.'); }
    return $value;
}
function stripText($value) {
    $value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string)preg_replace('/\s+/u', ' ', $value));
}
function requestKey() { return isset($_POST['access_key']) ? (string)$_POST['access_key'] : ''; }
function nv($array, $key, $default = '') { return is_array($array) && isset($array[$key]) ? $array[$key] : $default; }
function fallback($value, $default) { return $value !== null ? $value : $default; }
function csrfField() { return '<input type="hidden" name="csrf" value="'.h($_SESSION['nyaaxport_csrf']).'">'; }
function validCsrf() { return isset($_POST['csrf']) && hash_equals($_SESSION['nyaaxport_csrf'], (string)$_POST['csrf']); }
function siteBase($configured = '') {
    $base = trim((string)$configured);
    if ($base === '') {
        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
        $base = ($https ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/';
    }
    return rtrim($base, '/') . '/';
}

if (isset($_POST['logout']) && validCsrf()) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . strtok(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '', '?'));
    exit;
}
if (requestKey() !== '' && validCsrf()) {
    if ($_SESSION['nyaaxport_attempts'] >= NYAAXPORT_MAX_LOGIN_ATTEMPTS) {
        $loginError = 'Too many login attempts. Close the browser and try again later.';
    } elseif (NYAAXPORT_ACCESS_KEY !== 'CHANGE-ME-NOW' && hash_equals(NYAAXPORT_ACCESS_KEY, requestKey())) {
        $_SESSION['nyaaxport_ok'] = true; $_SESSION['nyaaxport_attempts'] = 0;
    } else { $_SESSION['nyaaxport_attempts']++; }
}
$authenticated = !empty($_SESSION['nyaaxport_ok']);

function readPhpConstant($file, $name) {
    if (!is_file($file)) { return null; }
    $text = (string)file_get_contents($file);
    $n = preg_quote($name, '/');
    if (preg_match('/define\s*\(\s*[\'\"]' . $n . '[\'\"]\s*,\s*[\'\"]([^\'\"]*)[\'\"]\s*\)/i', $text, $m)) { return stripcslashes($m[1]); }
    if (preg_match('/const\s+' . $n . '\s*=\s*[\'\"]([^\'\"]*)[\'\"]/i', $text, $m)) { return stripcslashes($m[1]); }
    return null;
}
function readPhpVariable($file,$name,$default=null) {
    if(!is_file($file)) return $default; $text=(string)file_get_contents($file); $n=preg_quote($name,'/');
    if(preg_match('/\[[\'\"]'.$n.'[\'\"]\]\s*=\s*[\'\"]([^\'\"]*)[\'\"]/i',$text,$m)) return stripcslashes($m[1]);
    if(preg_match('/(?:(?:public\s+)?\$|->)'.$n.'\s*=\s*[\'\"]([^\'\"]*)[\'\"]/i',$text,$m)) return stripcslashes($m[1]);
    if(preg_match('/[\'\"]'.$n.'[\'\"]\s*=>\s*[\'\"]([^\'\"]*)[\'\"]/i',$text,$m)) return stripcslashes($m[1]);
    return $default;
}
function readWpPrefix($file) {
    $text = is_file($file) ? (string)file_get_contents($file) : '';
    return preg_match('/\$table_prefix\s*=\s*[\'\"]([^\'\"]+)[\'\"]\s*;/', $text, $m) ? $m[1] : 'wp_';
}
function mysqlConfig($host, $db, $user, $pass, $prefix) {
    $port = null;
    if (preg_match('/^(.+):(\d+)$/', $host, $m)) { $host = $m[1]; $port = (int)$m[2]; }
    return ['host'=>$host, 'port'=>$port, 'db'=>$db, 'user'=>$user, 'pass'=>$pass, 'prefix'=>ident($prefix)];
}

function detectCms($root) {
    // CS-Cart / Multi-Vendor
    if (is_file($root.'/config.local.php') && is_dir($root.'/app') && is_dir($root.'/design')) {
        $f=$root.'/config.local.php'; $cfg=mysqlConfig(readPhpVariable($f,'db_host','localhost'),readPhpVariable($f,'db_name',''),readPhpVariable($f,'db_user',''),readPhpVariable($f,'db_password',''),readPhpVariable($f,'table_prefix','cscart_'));
        return ['id'=>'cscart','name'=>'CS-Cart / Multi-Vendor','version'=>fallback(readPhpConstant($root.'/config.php','PRODUCT_VERSION'),'unknown'),'db'=>$cfg,'base_url'=>siteBase('')];
    }
    // Magento 2 / Adobe Commerce
    if (is_file($root.'/app/etc/env.php') && is_file($root.'/bin/magento')) {
        $env=include $root.'/app/etc/env.php'; $d=isset($env['db']['connection']['default'])?$env['db']['connection']['default']:[]; $prefix=isset($env['db']['table_prefix'])?$env['db']['table_prefix']:'';
        $cfg=mysqlConfig(nv($d,'host','localhost'),nv($d,'dbname',''),nv($d,'username',''),nv($d,'password',''),$prefix);
        return ['id'=>'magento2','name'=>'Magento / Adobe Commerce','version'=>'2.x','db'=>$cfg,'base_url'=>siteBase('')];
    }
    // Shopware 6
    if (is_file($root.'/.env') && is_file($root.'/bin/console') && is_dir($root.'/custom/plugins')) {
        $envText=(string)file_get_contents($root.'/.env'); $dbUrl=''; if(preg_match('/^DATABASE_URL=[\'\"]?([^\r\n\'\"]+)/m',$envText,$m))$dbUrl=$m[1]; $u=parse_url($dbUrl);
        $cfg=mysqlConfig(isset($u['host'])?$u['host']:'localhost',isset($u['path'])?ltrim($u['path'],'/'):'',isset($u['user'])?urldecode($u['user']):'',isset($u['pass'])?urldecode($u['pass']):'','');
        return ['id'=>'shopware6','name'=>'Shopware','version'=>'6.x','db'=>$cfg,'base_url'=>siteBase('')];
    }
    // Webasyst Shop-Script
    if (is_file($root.'/wa-config/db.php') && is_dir($root.'/wa-apps/shop')) {
        $dbs=include $root.'/wa-config/db.php'; $d=isset($dbs['default'])?$dbs['default']:reset($dbs); $cfg=mysqlConfig(nv($d,'host','localhost'),nv($d,'database',''),nv($d,'user',''),nv($d,'password',''),'');
        return ['id'=>'webasyst','name'=>'Webasyst Shop-Script','version'=>'unknown','db'=>$cfg,'base_url'=>siteBase('')];
    }
    // MODX Revolution + miniShop2
    if (is_file($root.'/core/config/config.inc.php') && is_dir($root.'/core/components/minishop2')) {
        $f=$root.'/core/config/config.inc.php'; $dsn=readPhpVariable($f,'database_dsn',''); preg_match('/host=([^;]+)/',$dsn,$hm); preg_match('/dbname=([^;]+)/',$dsn,$dm);
        $cfg=mysqlConfig(isset($hm[1])?$hm[1]:'localhost',isset($dm[1])?$dm[1]:'',readPhpVariable($f,'database_user',''),readPhpVariable($f,'database_password',''),readPhpVariable($f,'table_prefix','modx_'));
        return ['id'=>'minishop2','name'=>'MODX miniShop2','version'=>'unknown','db'=>$cfg,'base_url'=>siteBase('')];
    }
    // Joomla commerce extensions
    if (is_file($root.'/configuration.php') && is_dir($root.'/administrator/components')) {
        $f=$root.'/configuration.php'; $cfg=mysqlConfig(readPhpVariable($f,'host','localhost'),readPhpVariable($f,'db',''),readPhpVariable($f,'user',''),readPhpVariable($f,'password',''),readPhpVariable($f,'dbprefix','jos_'));
        if(is_dir($root.'/administrator/components/com_virtuemart')) return ['id'=>'virtuemart','name'=>'Joomla VirtueMart','version'=>'unknown','db'=>$cfg,'base_url'=>siteBase('')];
        if(is_dir($root.'/administrator/components/com_hikashop')) return ['id'=>'hikashop','name'=>'Joomla HikaShop','version'=>'unknown','db'=>$cfg,'base_url'=>siteBase('')];
        if(is_dir($root.'/administrator/components/com_jshopping')) return ['id'=>'jshopping','name'=>'Joomla JoomShopping','version'=>'unknown','db'=>$cfg,'base_url'=>siteBase('')];
    }
    // Simpla CMS / OkayCMS
    if(is_file($root.'/config/config.php') && (is_dir($root.'/simpla')||is_dir($root.'/Okay')||is_dir($root.'/backend'))) {
        $f=$root.'/config/config.php'; $isOkay=is_dir($root.'/Okay'); $prefix=readPhpVariable($f,'db_prefix',$isOkay?'ok_':'s_');
        $cfg=mysqlConfig(readPhpVariable($f,'db_server','localhost'),readPhpVariable($f,'db_name',''),readPhpVariable($f,'db_user',''),readPhpVariable($f,'db_password',''),$prefix);
        return ['id'=>'simpla','name'=>$isOkay?'OkayCMS':'Simpla CMS','version'=>'unknown','db'=>$cfg,'base_url'=>siteBase('')];
    }
    // WooCommerce / WordPress
    if (is_file($root . '/wp-config.php') && is_dir($root . '/wp-content')) {
        $f = $root . '/wp-config.php';
        $cfg = mysqlConfig(fallback(readPhpConstant($f,'DB_HOST'),'localhost'), fallback(readPhpConstant($f,'DB_NAME'),''), fallback(readPhpConstant($f,'DB_USER'),''), fallback(readPhpConstant($f,'DB_PASSWORD'),''), readWpPrefix($f));
        return ['id'=>'woocommerce','name'=>'WooCommerce / WordPress','version'=>readWpVersion($root),'db'=>$cfg];
    }
    // OpenCart
    if (is_file($root . '/config.php') && (is_dir($root . '/catalog') || is_dir($root . '/system'))) {
        $f = $root . '/config.php';
        $cfg = mysqlConfig(fallback(readPhpConstant($f,'DB_HOSTNAME'),'localhost'), fallback(readPhpConstant($f,'DB_DATABASE'),''), fallback(readPhpConstant($f,'DB_USERNAME'),''), fallback(readPhpConstant($f,'DB_PASSWORD'),''), fallback(readPhpConstant($f,'DB_PREFIX'),'oc_'));
        $base = fallback(readPhpConstant($f,'HTTPS_SERVER'), fallback(readPhpConstant($f,'HTTP_SERVER'), ''));
        return ['id'=>'opencart','name'=>'OpenCart','version'=>readOpenCartVersion($root),'db'=>$cfg,'base_url'=>siteBase($base),'oc_seo'=>'none'];
    }
    // PrestaShop 1.7/8/9
    if (is_file($root . '/app/config/parameters.php')) {
        $params = include $root . '/app/config/parameters.php';
        $p = isset($params['parameters']) ? $params['parameters'] : $params;
        $cfg = mysqlConfig((string)nv($p,'database_host','localhost'), (string)nv($p,'database_name',''), (string)nv($p,'database_user',''), (string)nv($p,'database_password',''), (string)nv($p,'database_prefix','ps_'));
        return ['id'=>'prestashop','name'=>'PrestaShop','version'=>readPrestaVersion($root),'db'=>$cfg];
    }
    // PrestaShop 1.6
    if (is_file($root . '/config/settings.inc.php')) {
        $f = $root . '/config/settings.inc.php';
        $cfg = mysqlConfig(fallback(readPhpConstant($f,'_DB_SERVER_'),'localhost'), fallback(readPhpConstant($f,'_DB_NAME_'),''), fallback(readPhpConstant($f,'_DB_USER_'),''), fallback(readPhpConstant($f,'_DB_PASSWD_'),''), fallback(readPhpConstant($f,'_DB_PREFIX_'),'ps_'));
        return ['id'=>'prestashop','name'=>'PrestaShop','version'=>fallback(readPhpConstant($f,'_PS_VERSION_'),'1.6'),'db'=>$cfg];
    }
    // 1C-Bitrix
    if (is_file($root . '/bitrix/.settings.php')) {
        $s = include $root . '/bitrix/.settings.php';
        $c = isset($s['connections']['value']['default']) ? $s['connections']['value']['default'] : [];
        $cfg = mysqlConfig((string)nv($c,'host','localhost'), (string)nv($c,'database',''), (string)nv($c,'login',''), (string)nv($c,'password',''), 'b_');
        return ['id'=>'bitrix','name'=>'1C-Bitrix','version'=>readBitrixVersion($root),'db'=>$cfg];
    }
    throw new RuntimeException('CMS not detected. Place this file in the actual site root.');
}
function readWpVersion($root) {
    $f=$root.'/wp-includes/version.php'; $t=is_file($f)?(string)file_get_contents($f):'';
    return preg_match('/\$wp_version\s*=\s*[\'\"]([^\'\"]+)/',$t,$m)?$m[1]:'unknown';
}
function readOpenCartVersion($root) {
    foreach ([$root.'/index.php',$root.'/system/startup.php',$root.'/system/framework.php'] as $f) {
        $v=readPhpConstant($f,'VERSION'); if ($v!==null) return $v;
    }
    return 'unknown';
}
function readPrestaVersion($root) {
    foreach ([$root.'/app/AppKernel.php',$root.'/config/defines.inc.php',$root.'/src/Core/Foundation/Version.php'] as $f) {
        if (!is_file($f)) continue; $t=(string)file_get_contents($f);
        if (preg_match('/(?:VERSION|_PS_VERSION_)\s*[=,]\s*[\'\"]([^\'\"]+)/',$t,$m)) return $m[1];
    }
    return 'unknown';
}
function readBitrixVersion($root) {
    $f=$root.'/bitrix/modules/main/install/version.php'; $t=is_file($f)?(string)file_get_contents($f):'';
    return preg_match('/VERSION\s*[\'\"]?\s*=>\s*[\'\"]([^\'\"]+)/',$t,$m)?$m[1]:'unknown';
}
function connectDb($c) {
    $dsn='mysql:host='.$c['host'].';dbname='.$c['db'].';charset=utf8mb4'.($c['port']?';port='.$c['port']:'');
    try { return new PDO($dsn,$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]); }
    catch (PDOException $e) {
        if (strpos($e->getMessage(),'utf8mb4')===false) throw $e;
        $dsn='mysql:host='.$c['host'].';dbname='.$c['db'].';charset=utf8'.($c['port']?';port='.$c['port']:'');
        return new PDO($dsn,$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    }
}
function tableReadable($pdo, $table) {
    try { $pdo->query('SELECT 1 FROM `'.ident($table).'` LIMIT 1'); return true; }
    catch (Exception $e) { return false; }
}
function prepareCms($pdo, $cms) {
    if ($cms['id'] === 'opencart') {
        $p = $cms['db']['prefix'];
        $cms['data_language_id'] = isset($_POST['data_language_id']) ? max(0,(int)$_POST['data_language_id']) : 0;
        $cms['store_id'] = isset($_POST['store_id']) ? max(0,(int)$_POST['store_id']) : 0;
        if ($cms['store_id'] > 0) {
            $st=$pdo->prepare("SELECT url,ssl FROM {$p}store WHERE store_id=?"); $st->execute([$cms['store_id']]); $store=$st->fetch();
            if($store) $cms['base_url']=siteBase(nv($store,'ssl',nv($store,'url','')));
        }
        if (tableReadable($pdo, $p.'seo_url')) $cms['oc_seo'] = 'seo_url';
        elseif (tableReadable($pdo, $p.'url_alias')) $cms['oc_seo'] = 'url_alias';
    }
    if($cms['id']==='virtuemart') {
        $p=$cms['db']['prefix']; $cms['vm_lang_table']='';
        foreach($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) if(strpos($table,$p.'virtuemart_products_')===0){$cms['vm_lang_table']=ident($table);break;}
    }
    return $cms;
}
function cmsOptionsAndDiagnostics($cms) {
    $result=['checks'=>['PHP'=>PHP_VERSION,'PDO MySQL'=>extension_loaded('pdo_mysql')?'OK':'missing','ZipArchive'=>class_exists('ZipArchive')?'OK':'missing (XLSX unavailable)','Temporary directory'=>is_writable(sys_get_temp_dir())?'OK':'not writable'],'languages'=>[],'stores'=>[],'count'=>null];
    try {
        $pdo=connectDb($cms['db']); $result['checks']['Database']='OK'; $p=$cms['db']['prefix'];
        if($cms['id']==='opencart') {
            $result['languages']=$pdo->query("SELECT language_id id,CONCAT(name,' (',code,')') name FROM {$p}language WHERE status=1 ORDER BY sort_order,name")->fetchAll();
            $result['stores'][]=['id'=>0,'name'=>'Default store'];
            foreach($pdo->query("SELECT store_id id,name FROM {$p}store ORDER BY name")->fetchAll() as $s)$result['stores'][]=$s;
            $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}product")->fetchColumn();
        } elseif($cms['id']==='woocommerce') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}posts WHERE post_type='product' AND post_status NOT IN ('trash','auto-draft')")->fetchColumn();
        elseif($cms['id']==='prestashop') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}product")->fetchColumn();
        elseif($cms['id']==='bitrix') $result['count']=(int)$pdo->query('SELECT COUNT(*) FROM b_catalog_product')->fetchColumn();
        elseif($cms['id']==='cscart') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}products")->fetchColumn();
        elseif($cms['id']==='webasyst') $result['count']=(int)$pdo->query('SELECT COUNT(*) FROM shop_product')->fetchColumn();
        elseif($cms['id']==='minishop2') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}ms2_products")->fetchColumn();
        elseif($cms['id']==='magento2') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}catalog_product_entity")->fetchColumn();
        elseif($cms['id']==='shopware6') $result['count']=(int)$pdo->query('SELECT COUNT(*) FROM product')->fetchColumn();
        elseif($cms['id']==='virtuemart') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}virtuemart_products")->fetchColumn();
        elseif($cms['id']==='hikashop') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}hikashop_product")->fetchColumn();
        elseif($cms['id']==='jshopping') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}jshopping_products")->fetchColumn();
        elseif($cms['id']==='simpla') $result['count']=(int)$pdo->query("SELECT COUNT(*) FROM {$p}products")->fetchColumn();
    } catch(Exception $e) {$result['checks']['Database']='ERROR: '.$e->getMessage();}
    return $result;
}

function fields() {
    global $lang;
    $extra = [
        'ru'=>['currency'=>'Валюта','weight'=>'Вес','dimensions'=>'Размеры','barcode'=>'Штрихкод','seo_title'=>'SEO-заголовок','meta_description'=>'Meta description','date_modified'=>'Дата изменения'],
        'uk'=>['currency'=>'Валюта','weight'=>'Вага','dimensions'=>'Розміри','barcode'=>'Штрихкод','seo_title'=>'SEO-заголовок','meta_description'=>'Meta description','date_modified'=>'Дата зміни'],
        'en'=>['currency'=>'Currency','weight'=>'Weight','dimensions'=>'Dimensions','barcode'=>'Barcode','seo_title'=>'SEO title','meta_description'=>'Meta description','date_modified'=>'Modified']
    ];
    return ['id'=>t('id'),'name'=>t('name'),'sku'=>t('sku'),'url'=>t('url'),'price'=>t('price'),'special_price'=>t('special_price'),'currency'=>$extra[$lang]['currency'],'quantity'=>t('quantity'),'status'=>t('status'),'manufacturer'=>t('manufacturer'),'category'=>t('category'),'description'=>t('description'),'image'=>t('image'),'weight'=>$extra[$lang]['weight'],'dimensions'=>$extra[$lang]['dimensions'],'barcode'=>$extra[$lang]['barcode'],'seo_title'=>$extra[$lang]['seo_title'],'meta_description'=>$extra[$lang]['meta_description'],'date_modified'=>$extra[$lang]['date_modified']];
}
function sqlFor($cms) {
    $p=$cms['db']['prefix'];
    switch ($cms['id']) {
        case 'opencart':
            $seoSelect = 'NULL seo_keyword'; $seoJoin = '';
            $languageJoin = (int)nv($cms,'data_language_id',0)>0 ? (int)$cms['data_language_id'] : "(SELECT MIN(language_id) FROM {$p}product_description x WHERE x.product_id=p.product_id)";
            if (nv($cms,'oc_seo','none') === 'seo_url') { $seoSelect = 'MAX(seo.keyword) seo_keyword'; $seoJoin = " LEFT JOIN {$p}seo_url seo ON seo.query=CONCAT('product_id=',p.product_id) AND seo.language_id=pd.language_id AND seo.store_id=".(int)nv($cms,'store_id',0); }
            elseif (nv($cms,'oc_seo','none') === 'url_alias') { $seoSelect = 'MAX(seo.keyword) seo_keyword'; $seoJoin = " LEFT JOIN {$p}url_alias seo ON seo.query=CONCAT('product_id=',p.product_id)"; }
            return "SELECT p.product_id id,pd.name,p.model sku,NULL url,{$seoSelect},p.price,(SELECT MIN(sp.price) FROM {$p}product_special sp WHERE sp.product_id=p.product_id AND (sp.date_start='0000-00-00' OR sp.date_start<=NOW()) AND (sp.date_end='0000-00-00' OR sp.date_end>=NOW())) special_price,(SELECT value FROM {$p}setting WHERE `key`='config_currency' ORDER BY (store_id=".(int)nv($cms,'store_id',0).") DESC LIMIT 1) currency,p.quantity,p.status,m.name manufacturer,GROUP_CONCAT(DISTINCT cd.name ORDER BY cd.name SEPARATOR ', ') category,pd.description,p.image,p.weight,CONCAT(p.length,' x ',p.width,' x ',p.height) dimensions,COALESCE(NULLIF(p.ean,''),NULLIF(p.upc,''),NULLIF(p.jan,''),NULLIF(p.isbn,'')) barcode,pd.meta_title seo_title,pd.meta_description,p.date_modified FROM {$p}product p JOIN {$p}product_description pd ON pd.product_id=p.product_id AND pd.language_id={$languageJoin} LEFT JOIN {$p}manufacturer m ON m.manufacturer_id=p.manufacturer_id LEFT JOIN {$p}product_to_category pc ON pc.product_id=p.product_id LEFT JOIN {$p}category_description cd ON cd.category_id=pc.category_id AND cd.language_id=pd.language_id{$seoJoin} GROUP BY p.product_id ORDER BY p.product_id";
        case 'woocommerce': return "SELECT p.ID id,p.post_title name,sku.meta_value sku,p.guid url,price.meta_value price,sale.meta_value special_price,(SELECT option_value FROM {$p}options WHERE option_name='woocommerce_currency' LIMIT 1) currency,stock.meta_value quantity,(p.post_status='publish') status,'' manufacturer,GROUP_CONCAT(DISTINCT terms.name ORDER BY terms.name SEPARATOR ', ') category,p.post_content description,thumbguid.guid image,NULL weight,NULL dimensions,NULL barcode,NULL seo_title,NULL meta_description,p.post_modified date_modified FROM {$p}posts p LEFT JOIN {$p}postmeta sku ON sku.post_id=p.ID AND sku.meta_key='_sku' LEFT JOIN {$p}postmeta price ON price.post_id=p.ID AND price.meta_key='_regular_price' LEFT JOIN {$p}postmeta sale ON sale.post_id=p.ID AND sale.meta_key='_sale_price' LEFT JOIN {$p}postmeta stock ON stock.post_id=p.ID AND stock.meta_key='_stock' LEFT JOIN {$p}term_relationships tr ON tr.object_id=p.ID LEFT JOIN {$p}term_taxonomy tt ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy='product_cat' LEFT JOIN {$p}terms terms ON terms.term_id=tt.term_id LEFT JOIN {$p}postmeta thumb ON thumb.post_id=p.ID AND thumb.meta_key='_thumbnail_id' LEFT JOIN {$p}posts thumbguid ON thumbguid.ID=thumb.meta_value WHERE p.post_type='product' AND p.post_status NOT IN ('trash','auto-draft') GROUP BY p.ID ORDER BY p.ID";
        case 'prestashop': return "SELECT p.id_product id,pl.name,p.reference sku,CONCAT(COALESCE(CONCAT('https://',su.domain,su.physical_uri,su.virtual_uri),''),'index.php?id_product=',p.id_product,'&controller=product') url,ps.price,NULL special_price,(SELECT iso_code FROM {$p}currency WHERE active=1 ORDER BY id_currency LIMIT 1) currency,sa.quantity,ps.active status,m.name manufacturer,cl.name category,pl.description,p.id_product image,p.weight,CONCAT(p.depth,' x ',p.width,' x ',p.height) dimensions,p.ean13 barcode,pl.meta_title seo_title,pl.meta_description,p.date_upd date_modified FROM {$p}product p JOIN {$p}product_shop ps ON ps.id_product=p.id_product AND ps.id_shop=(SELECT MIN(id_shop) FROM {$p}product_shop x WHERE x.id_product=p.id_product) JOIN {$p}product_lang pl ON pl.id_product=p.id_product AND pl.id_shop=ps.id_shop AND pl.id_lang=(SELECT MIN(id_lang) FROM {$p}product_lang x WHERE x.id_product=p.id_product AND x.id_shop=ps.id_shop) LEFT JOIN {$p}stock_available sa ON sa.id_product=p.id_product AND sa.id_product_attribute=0 AND sa.id_shop=ps.id_shop LEFT JOIN {$p}manufacturer m ON m.id_manufacturer=p.id_manufacturer LEFT JOIN {$p}category_lang cl ON cl.id_category=p.id_category_default AND cl.id_shop=ps.id_shop AND cl.id_lang=pl.id_lang LEFT JOIN {$p}shop_url su ON su.id_shop=ps.id_shop AND su.main=1 ORDER BY p.id_product";
        case 'bitrix': return "SELECT e.ID id,e.NAME name,e.XML_ID sku,CONCAT('/',e.CODE,'/') url,pr.PRICE price,NULL special_price,pr.CURRENCY currency,cp.QUANTITY quantity,(e.ACTIVE='Y') status,'' manufacturer,sec.NAME category,e.DETAIL_TEXT description,e.DETAIL_PICTURE image,cp.WEIGHT weight,NULL dimensions,NULL barcode,NULL seo_title,NULL meta_description,e.TIMESTAMP_X date_modified FROM b_iblock_element e LEFT JOIN b_catalog_product cp ON cp.ID=e.ID LEFT JOIN b_catalog_price pr ON pr.PRODUCT_ID=e.ID AND pr.CATALOG_GROUP_ID=(SELECT MIN(CATALOG_GROUP_ID) FROM b_catalog_price x WHERE x.PRODUCT_ID=e.ID) LEFT JOIN b_iblock_section sec ON sec.ID=e.IBLOCK_SECTION_ID WHERE cp.ID IS NOT NULL ORDER BY e.ID";
        case 'cscart': return "SELECT p.product_id id,pd.product name,p.product_code sku,CONCAT('index.php?dispatch=products.view&product_id=',p.product_id) url,MIN(pp.price) price,NULL special_price,NULL currency,p.amount quantity,(p.status='A') status,'' manufacturer,GROUP_CONCAT(DISTINCT cd.category SEPARATOR ', ') category,pd.full_description description,'' image,p.weight,NULL dimensions,p.ean_code barcode,pd.page_title seo_title,pd.meta_description,p.timestamp date_modified FROM {$p}products p JOIN {$p}product_descriptions pd ON pd.product_id=p.product_id AND pd.lang_code=(SELECT MIN(lang_code) FROM {$p}product_descriptions x WHERE x.product_id=p.product_id) LEFT JOIN {$p}product_prices pp ON pp.product_id=p.product_id LEFT JOIN {$p}products_categories pc ON pc.product_id=p.product_id LEFT JOIN {$p}category_descriptions cd ON cd.category_id=pc.category_id AND cd.lang_code=pd.lang_code GROUP BY p.product_id ORDER BY p.product_id";
        case 'webasyst': return "SELECT p.id,p.name,s.sku,CONCAT('?product_url=',p.url) url,s.price,NULL special_price,p.currency,s.count quantity,(p.status=1) status,'' manufacturer,c.name category,p.description,CONCAT('wa-data/public/shop/products/',p.id) image,p.weight,NULL dimensions,s.sku barcode,p.meta_title seo_title,p.meta_description,p.edit_datetime date_modified FROM shop_product p LEFT JOIN shop_product_skus s ON s.id=p.sku_id LEFT JOIN shop_category_products cp ON cp.product_id=p.id LEFT JOIN shop_category c ON c.id=cp.category_id GROUP BY p.id ORDER BY p.id";
        case 'minishop2': return "SELECT c.id,c.pagetitle name,d.article sku,CONCAT('?id=',c.id) url,d.price,d.old_price special_price,NULL currency,d.remains quantity,(c.published=1 AND c.deleted=0) status,v.name manufacturer,'' category,c.content description,d.image,d.weight,NULL dimensions,'' barcode,c.longtitle seo_title,c.description meta_description,c.editedon date_modified FROM {$p}site_content c JOIN {$p}ms2_products d ON d.id=c.id LEFT JOIN {$p}ms2_vendors v ON v.id=d.vendor ORDER BY c.id";
        case 'magento2': return "SELECT e.entity_id id,MAX(CASE WHEN a.attribute_code='name' THEN v.value END) name,e.sku,MAX(ur.request_path) url,MAX(CASE WHEN ad.attribute_code='price' THEN d.value END) price,MAX(CASE WHEN ad.attribute_code='special_price' THEN d.value END) special_price,NULL currency,MAX(si.qty) quantity,1 status,'' manufacturer,'' category,'' description,'' image,MAX(e.updated_at) date_modified FROM {$p}catalog_product_entity e LEFT JOIN {$p}catalog_product_entity_varchar v ON v.entity_id=e.entity_id LEFT JOIN {$p}eav_attribute a ON a.attribute_id=v.attribute_id LEFT JOIN {$p}catalog_product_entity_decimal d ON d.entity_id=e.entity_id LEFT JOIN {$p}eav_attribute ad ON ad.attribute_id=d.attribute_id LEFT JOIN {$p}cataloginventory_stock_item si ON si.product_id=e.entity_id LEFT JOIN {$p}url_rewrite ur ON ur.entity_id=e.entity_id AND ur.entity_type='product' AND ur.redirect_type=0 GROUP BY e.entity_id ORDER BY e.entity_id";
        case 'shopware6': return "SELECT CONV(HEX(p.id),16,10) id,MAX(pt.name) name,p.product_number sku,MAX(su.seo_path_info) url,JSON_UNQUOTE(JSON_EXTRACT(p.price,'$.*.gross')) price,NULL special_price,NULL currency,p.stock quantity,p.active status,MAX(mst.name) manufacturer,'' category,MAX(pt.description) description,'' image,p.weight,CONCAT(p.length,' x ',p.width,' x ',p.height) dimensions,p.ean barcode,MAX(pt.meta_title) seo_title,MAX(pt.meta_description) meta_description,p.updated_at date_modified FROM product p LEFT JOIN product_translation pt ON pt.product_id=p.id LEFT JOIN seo_url su ON su.foreign_key=p.id AND su.is_canonical=1 LEFT JOIN product_manufacturer_translation mst ON mst.product_manufacturer_id=p.product_manufacturer_id GROUP BY p.id ORDER BY p.id";
        case 'virtuemart': $lt=nv($cms,'vm_lang_table',''); if($lt==='')throw new RuntimeException('VirtueMart language table not found.'); return "SELECT p.virtuemart_product_id id,l.product_name name,p.product_sku sku,CONCAT('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=',p.virtuemart_product_id) url,MIN(pp.product_price) price,NULL special_price,MIN(pp.product_currency) currency,p.product_in_stock quantity,p.published status,'' manufacturer,'' category,l.product_desc description,'' image,p.product_weight weight,CONCAT(p.product_length,' x ',p.product_width,' x ',p.product_height) dimensions,'' barcode,l.metatitle seo_title,l.metadesc meta_description,p.modified_on date_modified FROM {$p}virtuemart_products p JOIN {$lt} l ON l.virtuemart_product_id=p.virtuemart_product_id LEFT JOIN {$p}virtuemart_product_prices pp ON pp.virtuemart_product_id=p.virtuemart_product_id GROUP BY p.virtuemart_product_id ORDER BY p.virtuemart_product_id";
        case 'hikashop': return "SELECT p.product_id id,p.product_name name,p.product_code sku,CONCAT('index.php?option=com_hikashop&ctrl=product&task=show&cid=',p.product_id) url,MIN(pr.price_value) price,NULL special_price,MIN(pr.price_currency_id) currency,p.product_quantity quantity,p.product_published status,p.product_manufacturer_id manufacturer,'' category,p.product_description description,'' image,p.product_weight weight,CONCAT(p.product_length,' x ',p.product_width,' x ',p.product_height) dimensions,p.product_code barcode,p.product_page_title seo_title,p.product_meta_description meta_description,p.product_modified date_modified FROM {$p}hikashop_product p LEFT JOIN {$p}hikashop_price pr ON pr.price_product_id=p.product_id GROUP BY p.product_id ORDER BY p.product_id";
        case 'jshopping': return "SELECT p.product_id id,p.name_en_GB name,p.product_ean sku,CONCAT('index.php?option=com_jshopping&controller=product&task=view&product_id=',p.product_id) url,p.product_price price,NULL special_price,NULL currency,p.product_quantity quantity,p.product_publish status,p.product_manufacturer_id manufacturer,'' category,p.description_en_GB description,p.image image,p.product_weight weight,NULL dimensions,p.product_ean barcode,p.meta_title_en_GB seo_title,p.meta_description_en_GB meta_description,p.date_modify date_modified FROM {$p}jshopping_products p ORDER BY p.product_id";
        case 'simpla': return "SELECT p.id,p.name,v.sku,CONCAT('products/',p.url) url,v.price,v.compare_price special_price,NULL currency,v.stock quantity,p.visible status,b.name manufacturer,GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') category,p.body description,MIN(i.filename) image,NULL weight,NULL dimensions,v.sku barcode,p.meta_title seo_title,p.meta_description,p.last_modify date_modified FROM {$p}products p LEFT JOIN {$p}variants v ON v.product_id=p.id LEFT JOIN {$p}brands b ON b.id=p.brand_id LEFT JOIN {$p}products_categories pc ON pc.product_id=p.id LEFT JOIN {$p}categories c ON c.id=pc.category_id LEFT JOIN {$p}images i ON i.product_id=p.id GROUP BY p.id ORDER BY p.id";
    }
    throw new RuntimeException('Unsupported CMS.');
}
function exportFilters($pdo) {
    $where = [];
    if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') $where[] = 'status=' . ((int)$_POST['filter_status'] ? '1' : '0');
    if (!empty($_POST['in_stock'])) $where[] = 'quantity>0';
    if (isset($_POST['price_min']) && $_POST['price_min'] !== '' && is_numeric($_POST['price_min'])) $where[] = 'price>=' . (float)$_POST['price_min'];
    if (isset($_POST['price_max']) && $_POST['price_max'] !== '' && is_numeric($_POST['price_max'])) $where[] = 'price<=' . (float)$_POST['price_max'];
    if (!empty($_POST['search'])) { $q = '%' . $_POST['search'] . '%'; $where[] = '(name LIKE '.$pdo->quote($q).' OR sku LIKE '.$pdo->quote($q).')'; }
    if (!empty($_POST['category'])) $where[] = 'category LIKE '.$pdo->quote('%'.$_POST['category'].'%');
    if (!empty($_POST['manufacturer'])) $where[] = 'manufacturer LIKE '.$pdo->quote('%'.$_POST['manufacturer'].'%');
    if (!empty($_POST['modified_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['modified_from'])) $where[] = 'date_modified>='.$pdo->quote($_POST['modified_from'].' 00:00:00');
    return $where;
}
function optionVariantRows($pdo,$cms,$row) {
    if($cms['id']!=='opencart' || !isset($_POST['variant_mode']) || $_POST['variant_mode']==='base') return [];
    $p=$cms['db']['prefix']; $lang=(int)nv($cms,'data_language_id',0);
    if($lang<1) $lang=(int)$pdo->query("SELECT MIN(language_id) FROM {$p}product_description WHERE product_id=".(int)$row['id'])->fetchColumn();
    $sql="SELECT pov.product_option_value_id id,od.name option_name,ovd.name value_name,pov.quantity,pov.price,pov.price_prefix FROM {$p}product_option_value pov LEFT JOIN {$p}option_description od ON od.option_id=pov.option_id AND od.language_id=? LEFT JOIN {$p}option_value_description ovd ON ovd.option_value_id=pov.option_value_id AND ovd.language_id=? WHERE pov.product_id=? ORDER BY pov.product_option_value_id";
    try{$st=$pdo->prepare($sql);$st->execute([$lang,$lang,(int)$row['id']]);$out=[];foreach($st->fetchAll() as $v){$x=$row;$x['id']=$row['id'].'-'.$v['id'];$x['name']=$row['name'].' — '.$v['option_name'].': '.$v['value_name'];$x['quantity']=$v['quantity'];$delta=(float)$v['price'];$x['price']=(float)$row['price']+($v['price_prefix']==='-'?-$delta:$delta);$out[]=$x;}return $out;}catch(Exception $e){return [];}
}
function rows($pdo, $cms) {
    $base=preg_replace('/\s+ORDER\s+BY\s+[^)]+$/i','',sqlFor($cms)); $lastId=0; $filters=exportFilters($pdo);
    while (true) {
        $where=array_merge(['id>'.(int)$lastId],$filters);
        $stmt=$pdo->query('SELECT * FROM ('.$base.') export_rows WHERE '.implode(' AND ',$where).' ORDER BY id LIMIT '.NYAAXPORT_BATCH_SIZE);
        $batch=$stmt->fetchAll();
        foreach ($batch as $row) {
            $row['description']=stripText(nv($row,'description',''));
            if ($cms['id'] === 'opencart') {
                $keyword = trim((string)nv($row,'seo_keyword',''), '/');
                $row['url'] = $cms['base_url'] . ($keyword !== '' ? $keyword : 'index.php?route=product/product&product_id=' . $row['id']);
                if (nv($row,'image','') !== '' && !preg_match('~^https?://~i', $row['image'])) $row['image'] = $cms['base_url'] . 'image/' . ltrim($row['image'], '/');
            }
            elseif(isset($cms['base_url'])) {
                if(nv($row,'url','')!==''&&!preg_match('~^https?://~i',$row['url']))$row['url']=$cms['base_url'].ltrim($row['url'],'/');
                if(nv($row,'image','')!==''&&!preg_match('~^https?://~i',$row['image']))$row['image']=$cms['base_url'].ltrim($row['image'],'/');
            }
            unset($row['seo_keyword']);
            $lastId = max($lastId, (int)$row['id']);
            $mode=isset($_POST['variant_mode'])?$_POST['variant_mode']:'base';
            if($mode!=='variants_only') yield $row;
            if($mode==='with_variants'||$mode==='variants_only') foreach(optionVariantRows($pdo,$cms,$row) as $variant) yield $variant;
        }
        if (count($batch)<NYAAXPORT_BATCH_SIZE) break;
    }
}
function chosenFields() {
    $all=fields(); $requested=isset($_POST['fields'])&&is_array($_POST['fields'])?$_POST['fields']:[]; $out=[];
    foreach ($requested as $key) if (isset($all[$key])) $out[$key]=$all[$key];
    if (!$out) throw new RuntimeException(t('select_field'));
    return $out;
}
function safeCell($value) {
    $v=(string)($value !== null ? $value : '');
    if ($v!=='' && in_array($v[0],['=','+','-','@'],true)) $v="'".$v;
    return $v;
}
function sendHeaders($format) {
    $date=date('Y-m-d_H-i-s');
    $types=['csv'=>'text/csv; charset=UTF-8','meta'=>'text/csv; charset=UTF-8','xml'=>'application/xml; charset=UTF-8','google'=>'application/xml; charset=UTF-8','yml'=>'application/xml; charset=UTF-8','prom'=>'application/xml; charset=UTF-8','rozetka'=>'application/xml; charset=UTF-8','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    $extension=in_array($format,['google','yml','prom','rozetka'],true)?'xml':($format==='meta'?'csv':$format);
    header('Content-Type: '.$types[$format]);
    header('Content-Disposition: attachment; filename="products_'.$date.'.'.$extension.'"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate');
}
function exportCsv($rows,$fields) {
    echo "\xEF\xBB\xBF"; $o=fopen('php://output','wb'); fputcsv($o,array_values($fields),';');
    foreach($rows as $r){$line=[];foreach($fields as $k=>$x)$line[]=safeCell(nv($r,$k,''));fputcsv($o,$line,';');} fclose($o);
}
function exportXml($rows,$fields) {
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n<products>\n";
    foreach($rows as $r){echo "  <product>\n";foreach($fields as $k=>$label)echo '    <'.$k.' label="'.xmlv($label).'">'.xmlv(nv($r,$k,'')).'</'.$k.">\n";echo "  </product>\n";} echo "</products>";
}
function exportMeta($rows) {
    echo "\xEF\xBB\xBF"; $o=fopen('php://output','wb');
    fputcsv($o,['id','title','description','availability','condition','price','link','image_link','brand'],';');
    foreach($rows as $r) fputcsv($o,[nv($r,'id'),nv($r,'name'),nv($r,'description'),((int)nv($r,'quantity',0)>0?'in stock':'out of stock'),'new',trim(nv($r,'price').' '.nv($r,'currency')),nv($r,'url'),nv($r,'image'),nv($r,'manufacturer')],';');
    fclose($o);
}
function exportMarketplaceXml($rows,$kind,$cms) {
    if($kind==='google') echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel><title>NyaaXport</title><link>'.xmlv(siteBase(nv($cms,'base_url',''))).'</link><description>Product feed</description>';
    elseif($kind==='yml') echo '<?xml version="1.0" encoding="UTF-8"?><yml_catalog date="'.date('Y-m-d H:i').'"><shop><name>NyaaXport</name><company>NyaaXport</company><url>'.xmlv(siteBase(nv($cms,'base_url',''))).'</url><currencies><currency id="USD" rate="1"/></currencies><categories><category id="1">Products</category></categories><offers>';
    else echo '<?xml version="1.0" encoding="UTF-8"?><products>';
    foreach($rows as $r){
        $price=nv($r,'special_price','')!==''?nv($r,'special_price'):nv($r,'price'); $currency=nv($r,'currency','USD')?:'USD';
        if($kind==='google') echo '<item><g:id>'.xmlv(nv($r,'id')).'</g:id><title>'.xmlv(nv($r,'name')).'</title><description>'.xmlv(nv($r,'description')).'</description><link>'.xmlv(nv($r,'url')).'</link><g:image_link>'.xmlv(nv($r,'image')).'</g:image_link><g:availability>'.((int)nv($r,'quantity',0)>0?'in_stock':'out_of_stock').'</g:availability><g:price>'.xmlv($price.' '.$currency).'</g:price><g:brand>'.xmlv(nv($r,'manufacturer')).'</g:brand><g:condition>new</g:condition></item>';
        elseif($kind==='yml') echo '<offer id="'.xmlv(nv($r,'id')).'" available="'.((int)nv($r,'quantity',0)>0?'true':'false').'"><url>'.xmlv(nv($r,'url')).'</url><price>'.xmlv($price).'</price><currencyId>'.xmlv($currency).'</currencyId><categoryId>1</categoryId><picture>'.xmlv(nv($r,'image')).'</picture><name>'.xmlv(nv($r,'name')).'</name><vendor>'.xmlv(nv($r,'manufacturer')).'</vendor><vendorCode>'.xmlv(nv($r,'sku')).'</vendorCode><description>'.xmlv(nv($r,'description')).'</description></offer>';
        else echo '<product><id>'.xmlv(nv($r,'id')).'</id><name>'.xmlv(nv($r,'name')).'</name><sku>'.xmlv(nv($r,'sku')).'</sku><url>'.xmlv(nv($r,'url')).'</url><price>'.xmlv($price).'</price><currency>'.xmlv($currency).'</currency><category>'.xmlv(nv($r,'category')).'</category><vendor>'.xmlv(nv($r,'manufacturer')).'</vendor><image>'.xmlv(nv($r,'image')).'</image><description>'.xmlv(nv($r,'description')).'</description><quantity>'.xmlv(nv($r,'quantity')).'</quantity></product>';
    }
    if($kind==='google') echo '</channel></rss>'; elseif($kind==='yml') echo '</offers></shop></yml_catalog>'; else echo '</products>';
}
function exportXls($rows,$fields) {
    echo "\xEF\xBB\xBF".'<html><head><meta charset="UTF-8"></head><body><table border="1"><thead><tr>';
    foreach($fields as $label)echo '<th>'.h($label).'</th>'; echo '</tr></thead><tbody>';
    foreach($rows as $r){echo '<tr>';foreach($fields as $k=>$x)echo '<td>'.h(safeCell(nv($r,$k,''))).'</td>';echo '</tr>';} echo '</tbody></table></body></html>';
}
function xlsxCell($value,$style=0) { return '<c t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'.xmlv(safeCell($value)).'</t></is></c>'; }
function exportXlsx($rows,$fields) {
    if(!class_exists('ZipArchive')) throw new RuntimeException('PHP extension ZipArchive is required for XLSX export.');
    $sheet=tempnam(sys_get_temp_dir(),'nyaaxport_sheet_'); $fh=fopen($sheet,'wb');
    fwrite($fh,'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><sheetData>');
    fwrite($fh,'<row r="1">');foreach($fields as $label)fwrite($fh,xlsxCell($label,1));fwrite($fh,'</row>');$n=2;
    foreach($rows as $r){fwrite($fh,'<row r="'.$n.'">');foreach($fields as $k=>$x)fwrite($fh,xlsxCell((string)nv($r,$k,'')));fwrite($fh,'</row>');$n++;}
    fwrite($fh,'</sheetData><autoFilter ref="A1:'.xlsxColumn(count($fields)).max(1,$n-1).'"/></worksheet>');fclose($fh);
    $zipFile=tempnam(sys_get_temp_dir(),'nyaaxport_xlsx_');$zip=new ZipArchive();
    if($zip->open($zipFile,ZipArchive::OVERWRITE)!==true){@unlink($sheet);throw new RuntimeException('Cannot create temporary XLSX archive.');}
    $zip->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
    $zip->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $zip->addFromString('xl/workbook.xml','<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Products" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels','<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
    $zip->addFromString('xl/styles.xml','<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF4338CA"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>');
    $zip->addFile($sheet,'xl/worksheets/sheet1.xml');$zip->close();readfile($zipFile);@unlink($sheet);@unlink($zipFile);
}
function xlsxColumn($number) {$s='';while($number>0){$number--;$s=chr(65+$number%26).$s;$number=(int)floor($number/26);}return $s?:'A';}

$cms=null;$error=isset($loginError)?$loginError:null;$diagnostics=null;
if($authenticated){try{$cms=detectCms(__DIR__);}catch(Exception $e){$error=$e->getMessage();}}
if($authenticated && $cms && !isset($_POST['export'])) $diagnostics=cmsOptionsAndDiagnostics($cms);
if($authenticated && isset($_POST['export'])) {
    try {
        @set_time_limit(0); @ignore_user_abort(true);
        if(!$cms) throw new RuntimeException($error?:'CMS not detected.');
        if(!validCsrf()) throw new RuntimeException('Invalid security token. Reload the page.');
        $format=(string)(isset($_POST['format'])?$_POST['format']:'csv');if(!in_array($format,['csv','xml','xls','xlsx','google','meta','yml','prom','rozetka'],true))throw new RuntimeException('Unknown format.');
        $selected=chosenFields();
        if($format==='xlsx' && !class_exists('ZipArchive')) throw new RuntimeException('PHP extension ZipArchive is required for XLSX export.');
        $pdo=connectDb($cms['db']);$cms=prepareCms($pdo,$cms);sendHeaders($format);$data=rows($pdo,$cms);
        if($format==='csv')exportCsv($data,$selected);elseif($format==='xml')exportXml($data,$selected);elseif($format==='xls')exportXls($data,$selected);elseif($format==='xlsx')exportXlsx($data,$selected);elseif($format==='meta')exportMeta($data);else exportMarketplaceXml($data,$format,$cms);exit;
    } catch(Exception $e) {$error=$e->getMessage();}
}
?><!doctype html><html lang="<?=h($lang)?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>NyaaXport</title><style>
*{box-sizing:border-box}body{margin:0;background:#f5f3ff;color:#1e1b4b;font:15px/1.5 system-ui,-apple-system,Segoe UI,sans-serif}.wrap{max-width:1000px;margin:48px auto;padding:0 18px}.card{background:#fff;border:1px solid #ddd6fe;border-radius:18px;padding:28px;box-shadow:0 18px 55px #312e8114}h1{margin:0 0 4px;font-size:30px}.muted{color:#6b7280}.badge{display:inline-block;padding:6px 11px;background:#ede9fe;border-radius:999px;color:#5b21b6;font-weight:700}.langs{display:flex;gap:5px;margin-bottom:12px}.langs a{padding:5px 8px;border-radius:7px;text-decoration:none;color:#4f46e5;font-weight:700}.langs a.active{background:#4f46e5;color:#fff}.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:10px;margin:18px 0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;margin:18px 0}.field{display:flex;gap:9px;align-items:center;padding:10px;border:1px solid #e5e7eb;border-radius:10px}.row{display:flex;gap:12px;align-items:end;flex-wrap:wrap}.control{flex:1;min-width:170px}label{font-weight:650}input[type=password],input[type=text],input[type=number],input[type=date],select{width:100%;padding:11px;border:1px solid #c4b5fd;border-radius:9px;background:#fff}button{border:0;border-radius:10px;padding:12px 18px;background:#4f46e5;color:white;font-weight:750;cursor:pointer}.secondary{background:#e5e7eb;color:#374151}.top{display:flex;justify-content:space-between;gap:15px;align-items:start}.hint{margin-top:20px;padding:12px;background:#fffbeb;border-radius:10px;color:#92400e}.filters{background:#f9fafb;padding:16px;border-radius:12px;margin:18px 0}.diag{margin-top:20px}.diag table{border-collapse:collapse;width:100%}.diag td{border-bottom:1px solid #eee;padding:6px}.support{margin:24px 0;color:#4c1d95}.support-box{background:#fff;border:1px solid #ddd6fe;border-radius:16px;padding:22px}.support h2{margin:0 0 8px}.support table{border-collapse:collapse;width:100%;margin:14px 0}.support th,.support td{text-align:left;padding:9px;border-bottom:1px solid #ede9fe}.support td:last-child{font-family:ui-monospace,Consolas,monospace;overflow-wrap:anywhere}.support-note{background:#fffbeb;color:#92400e;padding:10px;border-radius:9px}.author{text-align:center;color:#6b7280;margin-top:14px}.support a{color:#4f46e5}code{background:#ede9fe;padding:2px 5px;border-radius:4px}@media(max-width:540px){.wrap{margin:20px auto}.card{padding:20px}.top{display:block}.support th,.support td{display:block;padding:6px}.support tr{display:block;margin-bottom:10px}}
</style></head><body><main class="wrap"><section class="card"><nav class="langs"><?php foreach(['ru'=>'RU','uk'=>'UA','en'=>'EN'] as $lc=>$ll):?><a class="<?=$lang===$lc?'active':''?>" href="?lang=<?=h($lc)?>"><?=h($ll)?></a><?php endforeach;?></nav><div class="top"><div><h1>NyaaXport</h1><div class="muted"><?=h(t('subtitle'))?></div></div><?php if($authenticated):?><form method="post"><?=csrfField()?><button class="secondary" name="logout" value="1"><?=h(t('logout'))?></button></form><?php endif;?></div>
<?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?>
<?php if(!$authenticated):?>
<form method="post" style="margin-top:24px"><?=csrfField()?><label for="key"><?=h(t('access_key'))?></label><div class="row" style="margin-top:8px"><div class="control"><input id="key" type="password" name="access_key" required autofocus></div><button><?=h(t('open'))?></button></div></form>
<?php if(NYAAXPORT_ACCESS_KEY==='CHANGE-ME-NOW'):?><div class="hint"><?=h(t('change_key'))?></div><?php endif;?>
<?php elseif($cms):?>
<p><span class="badge"><?=h($cms['name'])?> <?=h($cms['version'])?></span></p>
<form method="post"><?=csrfField()?>
<?php if($diagnostics && $cms['id']==='opencart'):?><div class="row"><div class="control"><label>Data language</label><select name="data_language_id"><option value="0">Auto</option><?php foreach($diagnostics['languages'] as $o):?><option value="<?=h($o['id'])?>"><?=h($o['name'])?></option><?php endforeach;?></select></div><div class="control"><label>Store</label><select name="store_id"><?php foreach($diagnostics['stores'] as $o):?><option value="<?=h($o['id'])?>"><?=h($o['name'])?></option><?php endforeach;?></select></div></div><?php endif;?>
<div class="filters"><h3>Filters</h3><div class="row"><div class="control"><label>Search</label><input type="text" name="search"></div><div class="control"><label>Category</label><input type="text" name="category"></div><div class="control"><label>Manufacturer</label><input type="text" name="manufacturer"></div><div class="control"><label>Status</label><select name="filter_status"><option value="">All</option><option value="1">Active</option><option value="0">Disabled</option></select></div></div><div class="row"><div class="control"><label>Min price</label><input type="number" step="0.01" name="price_min"></div><div class="control"><label>Max price</label><input type="number" step="0.01" name="price_max"></div><div class="control"><label>Modified from</label><input type="date" name="modified_from"></div><label class="field"><input type="checkbox" name="in_stock" value="1"> In stock only</label></div></div>
<div class="row"><div class="control"><label>Profile</label><select id="profile"><option value="price">Price list</option><option value="marketing">Marketing feed</option><option value="full">Full export</option></select></div><?php if($cms['id']==='opencart'):?><div class="control"><label>Variants</label><select name="variant_mode"><option value="base">Base products</option><option value="with_variants">Products + option values</option><option value="variants_only">Option values only</option></select></div><?php endif;?></div>
<h3><?=h(t('export_fields'))?></h3><div class="grid"><?php foreach(fields() as $key=>$label):?><label class="field"><input class="export-field" type="checkbox" name="fields[]" value="<?=h($key)?>" <?=in_array($key,['name','sku','url','price'],true)?'checked':''?>> <?=h($label)?></label><?php endforeach;?></div>
<div class="row"><div class="control"><label for="format"><?=h(t('format'))?></label><select id="format" name="format"><optgroup label="Tables"><option value="xlsx">XLSX</option><option value="csv">CSV (UTF-8, ;)</option><option value="xml">XML</option><option value="xls">XLS (Excel HTML)</option></optgroup><optgroup label="Feeds"><option value="google">Google Merchant XML</option><option value="meta">Meta Catalog CSV</option><option value="yml">YML</option><option value="prom">Prom.ua XML</option><option value="rozetka">Rozetka XML</option></optgroup></select></div><button name="export" value="1"><?=h(t('download'))?></button></div></form>
<div class="hint"><?=h(t('safety'))?></div>
<?php if($diagnostics):?><details class="diag"><summary>Diagnostics<?=is_int($diagnostics['count'])?' — '.$diagnostics['count'].' products':''?></summary><table><?php foreach($diagnostics['checks'] as $k=>$v):?><tr><td><?=h($k)?></td><td><?=h($v)?></td></tr><?php endforeach;?></table></details><?php endif;?>
<?php endif;?></section><footer class="support"><div class="support-box"><h2>Поддержать проект</h2><p>Если NyaaGuard оказался полезен, вы можете поддержать дальнейшую разработку:</p><table><thead><tr><th>Сеть</th><th>Адрес</th></tr></thead><tbody><tr><td>USDT — TRC20</td><td>TR3fkALFRTDyRaHsDU8aUmANGPRDy2gU1s</td></tr><tr><td>ETH — ERC20</td><td>0x9d20248c779dcb742f7795a0c7461346c0c8934e</td></tr><tr><td>BTC</td><td>1BQwypiKfhNGb1ryKzdp7TBPL4wR6ckU4J</td></tr><tr><td>PayPal</td><td><a href="mailto:spirt51@hotmail.com">spirt51@hotmail.com</a></td></tr></tbody></table><div class="support-note">Перед переводом криптовалюты обязательно проверьте выбранную сеть и адрес.</div><div class="author">NyaaXport · Yevhen Levchenko</div></div></footer></main><script>(function(){var p=document.getElementById('profile');if(!p)return;var sets={price:['name','sku','url','price','special_price','currency','quantity'],marketing:['id','name','sku','url','price','special_price','currency','quantity','manufacturer','category','description','image','barcode'],full:null};p.onchange=function(){var s=sets[p.value];document.querySelectorAll('.export-field').forEach(function(c){c.checked=s===null||s.indexOf(c.value)>=0;});};})();</script></body></html>
