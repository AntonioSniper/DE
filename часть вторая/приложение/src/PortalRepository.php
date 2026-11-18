<?php
final class Database{
 public static function connect():PDO{
  $c=require dirname(__DIR__).'/config.php';
  return new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false,PDO::MYSQL_ATTR_FOUND_ROWS=>true]);
 }
}
final class PortalRepository{
 public function __construct(public PDO $db){}
 public function query(string $sql,array $params=[]):PDOStatement{$s=$this->db->prepare($sql);$s->execute($params);return $s;}
 public function user(int $id){return $this->query('SELECT user_id,role_id,login,full_name,phone,email FROM users WHERE user_id=?',[$id])->fetch();}
 public function events():array{return $this->query('SELECT e.*,t.type_name FROM events e JOIN event_types t ON e.type_id=t.type_id ORDER BY e.event_id')->fetchAll();}
 public function createApplication(int $user,int $event,string $date,string $phone,string $email,array $members):void{
  $this->db->beginTransaction();
  try{
   $this->query('INSERT INTO applications(user_id,event_id,event_date,contact_phone,contact_email,status_id) VALUES(?,?,?,?,?,1)',[$user,$event,$date,$phone,$email]);$id=(int)$this->db->lastInsertId();
   foreach($members as $member)$this->query('INSERT INTO application_members(application_id,full_name) VALUES(?,?)',[$id,$member]);
   $this->db->commit();
  }catch(Throwable $ex){$this->db->rollBack();throw $ex;}
 }
 public function review(int $application,int $user,string $text):bool{
  $this->db->beginTransaction();
  try{
   $a=$this->query('SELECT application_id FROM applications WHERE application_id=? AND user_id=? AND status_id=3 FOR UPDATE',[$application,$user])->fetch();
   $exists=$this->query('SELECT review_id FROM reviews WHERE application_id=?',[$application])->fetch();
   if(!$a||$exists){$this->db->rollBack();return false;}
   $this->query('INSERT INTO reviews(application_id,review_text) VALUES(?,?)',[$application,$text]);$this->db->commit();return true;
  }catch(Throwable $ex){if($this->db->inTransaction())$this->db->rollBack();throw $ex;}
 }
}
