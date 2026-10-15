<?php
require __DIR__.'/regression.php';
$dsn=getenv('NX_TEST_DSN');
if (!$dsn || strpos($dsn,'dbname=nyaaxport_test')===false) throw new RuntimeException('Use the isolated nyaaxport_test database.');
$pdo=new PDO($dsn,'root','nyaaxport-local-test',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$schema=[
    'product'=>'product_id INT PRIMARY KEY,model VARCHAR(50),price DECIMAL(10,2),quantity INT,status INT,image TEXT,weight DECIMAL(10,2),length INT,width INT,height INT,ean VARCHAR(20),upc VARCHAR(20),jan VARCHAR(20),isbn VARCHAR(20),date_modified DATETIME,manufacturer_id INT',
    'product_description'=>'product_id INT,language_id INT,name VARCHAR(100),description TEXT,meta_title TEXT,meta_description TEXT',
    'manufacturer'=>'manufacturer_id INT,name VARCHAR(100)',
    'product_to_category'=>'product_id INT,category_id INT',
    'category_description'=>'category_id INT,language_id INT,name VARCHAR(100)',
    'product_special'=>'product_id INT,price DECIMAL(10,2),date_start DATE,date_end DATE',
    'setting'=>'store_id INT,`key` VARCHAR(100),value TEXT',
    'product_to_store'=>'product_id INT,store_id INT',
    'language'=>'language_id INT,name VARCHAR(100),code VARCHAR(20),status INT,sort_order INT',
    'store'=>'store_id INT,name VARCHAR(100),url TEXT,`ssl` TEXT',
    'product_option_value'=>'product_option_value_id INT,product_id INT,option_id INT,option_value_id INT,quantity INT,price DECIMAL(10,2),price_prefix CHAR(1)',
    'option_description'=>'option_id INT,language_id INT,name VARCHAR(100)',
    'option_value_description'=>'option_value_id INT,language_id INT,name VARCHAR(100)'
];
foreach($schema as $table=>$columns){$pdo->exec('DROP TABLE IF EXISTS oc_'.$table);$pdo->exec('CREATE TABLE oc_'.$table.' ('.$columns.')');}
for($id=1;$id<=2;$id++){
    $pdo->exec("INSERT INTO oc_product VALUES ($id,'SKU-$id',100,5,1,'test.jpg',1,1,1,1,'','','','',NOW(),1)");
    $pdo->exec("INSERT INTO oc_product_description VALUES ($id,1,'Product $id','Description','','')");
}
$pdo->exec("INSERT INTO oc_product_to_store VALUES (1,0),(2,1)");
$pdo->exec("INSERT INTO oc_setting VALUES (0,'config_currency','USD'),(1,'config_currency','EUR')");
$pdo->exec("INSERT INTO oc_language VALUES (1,'English','en-gb',1,0)");
$pdo->exec("INSERT INTO oc_store VALUES (1,'Second store','https://example.com/','https://example.com/')");
$pdo->exec("INSERT INTO oc_product_option_value VALUES (10,2,1,1,2,5,'+')");
$pdo->exec("INSERT INTO oc_option_description VALUES (1,1,'Size')");
$pdo->exec("INSERT INTO oc_option_value_description VALUES (1,1,'Large')");
$_POST=['variant_mode'=>'base'];
$cms=['id'=>'opencart','db'=>['prefix'=>'oc_'],'store_id'=>1,'data_language_id'=>1,'base_url'=>'https://example.com/'];
$actual=iterator_to_array(rows($pdo,$cms));
check(count($actual)===1 && (int)$actual[0]['id']===2 && $actual[0]['currency']==='EUR','SQL export includes only chosen storefront products/currency');
$_POST=['variant_mode'=>'variants_only'];
$actual=iterator_to_array(rows($pdo,$cms));
check(count($actual)===1 && $actual[0]['id']==='2-10' && (float)$actual[0]['price']===105.0,'SQL option export emits selected storefront option and adjusted price');
$_POST=['price_min'=>'1000'];
check(count(iterator_to_array(rows($pdo,$cms)))===0,'SQL price filter produces an empty result');
echo "All MariaDB fixture checks passed.\n";
