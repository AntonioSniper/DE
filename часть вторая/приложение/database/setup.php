<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$c=require dirname(__DIR__).'/config.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$s=$db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=?');$s->execute([$c['database']]);
if((int)$s->fetchColumn()>0){echo "The selected database already contains tables. Import skipped.\n";exit;}
$csvRoot=dirname(__DIR__,2).'/импорт CSV';
$tables=['roles','event_types','events','statuses','users','applications','application_members','reviews'];
foreach ($tables as $table) {
    if (!is_readable($csvRoot . '/' . $table . '.csv')) {
        throw new RuntimeException('Missing CSV: ' . $table . '. Place the CSV folder next to the application.');
    }
}
// schema.sql also serves as a standalone import; use the configured name for CLI installs.
$database = '`' . str_replace('`', '``', $c['database']) . '`';
$schema = file_get_contents(__DIR__ . '/schema.sql');
$db->exec(str_replace('college_events', $database, $schema));
$db->beginTransaction();
try{
 foreach($tables as $table){
  $f=fopen($csvRoot.'/'.$table.'.csv','r');if(!$f)throw new RuntimeException('Missing CSV: '.$table);
  $headers=fgetcsv($f,0,',','"','');
  foreach($headers as $h)if(!preg_match('/^[a-z_]+$/D',$h))throw new RuntimeException('Invalid CSV column');
  $query=$db->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',$headers).'`) VALUES('.implode(',',array_fill(0,count($headers),'?')).')');
  while(($row=fgetcsv($f,0,',','"',''))!==false){if($row===[null])continue;if(count($row)!==count($headers))throw new RuntimeException('CSV column count: '.$table);$query->execute($row);}
  fclose($f);
 }
 $db->commit();echo "All eight CSV files imported into the configured database.\n";
}catch(Throwable $ex){$db->rollBack();throw $ex;}
