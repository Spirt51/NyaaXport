import {readFileSync,writeFileSync,mkdirSync} from 'node:fs';
mkdirSync('.test-runtime/web/system',{recursive:true});
for(const file of ['nyaaxport.php','nyaaxport_56.php','nyaaxport_cloud.php']) {
    writeFileSync('.test-runtime/web/'+file,readFileSync(file,'utf8').replace(/(const NYAAXPORT_(?:ACCESS|CLOUD)_KEY\s*=\s*)["']CHANGE-ME-NOW["']/, '$1"local-regression-key"'));
}
writeFileSync('.test-runtime/web/throttle.php',readFileSync('.test-runtime/web/nyaaxport_cloud.php','utf8'));
writeFileSync('.test-runtime/web/config.php',"<?php define('DB_HOSTNAME','127.0.0.1');define('DB_DATABASE','fixture');define('DB_USERNAME','fixture');define('DB_PASSWORD','');define('DB_PREFIX','oc_');define('HTTP_SERVER','https://example.com/');");
writeFileSync('.test-runtime/web/system/startup.php','<?php // Detection fixture; no live store.');
