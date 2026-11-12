<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$c=require dirname(__DIR__).'/config.php';
$db=new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$s=$db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?');$s->execute([$c['database'],'users']);
if((int)$s->fetchColumn()>0){echo "Database already installed. Existing data was not changed.\n";exit;}
$db->exec(file_get_contents(__DIR__.'/schema.sql'));
$csvRoot=dirname(__DIR__,2).'/импорт CSV';
$tables=['roles','event_types','events','statuses','users','applications','application_members','reviews'];
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
 $db->commit();echo "college_events created; all eight CSV files imported.\n";
}catch(Throwable $ex){$db->rollBack();throw $ex;}
