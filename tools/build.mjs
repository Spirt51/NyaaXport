import { readFileSync, writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
const read = path => readFileSync(new URL(path, import.meta.url), 'utf8').replace(/\r\n/g, '\n');
const common = read('../src/common.php').replace(/^<\?php\s*/, '').replace('/* NX_UI_CSS */', read('../assets/components.css') + read('../src/interface.css'));
const cms = read('../src/cms.php').replace('/* BUILD_COMMON */', common).trimEnd() + '\n';
const legacy = cms.replace('PHP_VERSION_ID < 70000 || PHP_VERSION_ID >= 90000', 'PHP_VERSION_ID < 50600 || PHP_VERSION_ID >= 70000').replace('NyaaXport: use nyaaxport.php with PHP 7.x or 8.x. For PHP 5.6 use nyaaxport_56.php.', 'NyaaXport: this build requires PHP 5.6. Use nyaaxport.php for PHP 7.x or 8.x.');
const cloud = read('../src/cloud.php').replace('/* BUILD_COMMON */', common).trimEnd() + '\n';
let sums = '';
for (const [file,content] of Object.entries({'nyaaxport.php':cms,'nyaaxport_56.php':legacy,'nyaaxport_cloud.php':cloud})) {
    writeFileSync(new URL('../'+file,import.meta.url),content);
    sums += createHash('sha256').update(content).digest('hex') + '  ' + file + '\n';
}
writeFileSync(new URL('../SHA256SUMS',import.meta.url),sums);
