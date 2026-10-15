import fs from 'node:fs';
import Engine from 'php-parser';
for (const [file,version] of [['nyaaxport_56.php','5.6'],['nyaaxport.php','7.0'],['nyaaxport_cloud.php','7.0']]) {
    const parser=new Engine({parser:{version,suppressErrors:false},ast:{withPositions:true}});
    parser.parseCode(fs.readFileSync(file,'utf8'),file);
    console.log(`PASS ${file}: PHP ${version} parser`);
}
