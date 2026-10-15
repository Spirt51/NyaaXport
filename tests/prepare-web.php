<?php
$root='/tmp/nyaaxport-web';
if(!is_dir($root.'/system'))mkdir($root.'/system',0700,true);
foreach(['nyaaxport.php','nyaaxport_56.php','nyaaxport_cloud.php'] as $file){
    $source=file_get_contents(__DIR__.'/../'.$file);
    $source=preg_replace('/(const NYAAXPORT_(?:ACCESS|CLOUD)_KEY\s*=\s*)["\']CHANGE-ME-NOW["\']/','$1"local-regression-key"',$source);
    file_put_contents($root.'/'.$file,$source);
}
file_put_contents($root.'/throttle.php',file_get_contents($root.'/nyaaxport_cloud.php'));
file_put_contents($root.'/system/startup.php','<?php // Local detection fixture.');
file_put_contents($root.'/config.php',"<?php define('DB_HOSTNAME','nyaaxport-audit-db-20261015');define('DB_DATABASE','nyaaxport_test');define('DB_USERNAME','root');define('DB_PASSWORD','nyaaxport-local-test');define('DB_PREFIX','oc_');define('HTTP_SERVER','https://example.com/');");
