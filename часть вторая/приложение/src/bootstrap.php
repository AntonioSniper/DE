<?php
declare(strict_types=1);
date_default_timezone_set('Europe/Moscow');
ini_set('session.use_strict_mode','1');
session_name('college_events_session');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','path'=>'/']);
session_start();
header('X-Content-Type-Options: nosniff');header('X-Frame-Options: SAMEORIGIN');
$_SESSION['csrf']??=bin2hex(random_bytes(32));
function e($s):string{return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf():string{return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
function go(string $page):void{header('Location: index.php?page='.$page);exit;}
function flash(string $message):void{$_SESSION['flash']=$message;}
function phoneValid(string $phone):bool{return (bool)preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/D',$phone);}
function nameValid(string $name):bool{return mb_strlen($name)<=255 && (bool)preg_match('/^[А-Яа-яЁё]+(?: +[А-Яа-яЁё]+)*$/uD',$name);}
function hashPassword(string $password):string{
 if(defined('PASSWORD_ARGON2ID'))return password_hash($password,PASSWORD_ARGON2ID);
 return 'sha256:'.password_hash(hash('sha256',$password),PASSWORD_DEFAULT);
}
function passwordMatches(string $password,string $hash):bool{
 return str_starts_with($hash,'sha256:')?password_verify(hash('sha256',$password),substr($hash,7)):password_verify($password,$hash);
}
final class Database{
 public static function connect():PDO{
  $c=require dirname(__DIR__).'/config.php';
  return new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
 }
}
final class PortalRepository{
 public function __construct(public PDO $db){}
 public function query(string $sql,array $params=[]):PDOStatement{$s=$this->db->prepare($sql);$s->execute($params);return $s;}
 public function user(int $id){return $this->query('SELECT user_id,role_id,login,full_name,phone,email FROM users WHERE user_id=?',[$id])->fetch();}
 public function events():array{return $this->query('SELECT e.*,t.type_name FROM events e JOIN event_types t ON e.type_id=t.type_id ORDER BY e.event_id')->fetchAll();}
 public function members(int $id):array{return $this->query('SELECT full_name FROM application_members WHERE application_id=? ORDER BY member_id',[$id])->fetchAll(PDO::FETCH_COLUMN);}
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
$repo=null;$user=null;$errors=[];
try{$repo=new PortalRepository(Database::connect());if(isset($_SESSION['user_id']))$user=$repo->user((int)$_SESSION['user_id'])?:null;}catch(PDOException $ex){$errors['general']='Сервис временно недоступен. Попробуйте позже.';}
$page=$_GET['page']??'home';
if(!in_array($page,['home','register','login','apply','my','admin'],true)){http_response_code(404);$page='missing';}
if(in_array($page,['apply','my','admin'],true)&&!$user)go('login');
if($page==='admin'&&(int)$user['role_id']!==1){http_response_code(403);$page='forbidden';}
$malformed=false;
foreach($_POST as $key=>$value){
 if(!is_string($value)||str_contains($value,"\0")||!mb_check_encoding($value,'UTF-8')){$malformed=true;$_POST[$key]='';}
}
if($_SERVER['REQUEST_METHOD']==='POST'&&$repo){
 if($malformed){http_response_code(400);$errors['general']='Некорректный формат данных формы. Обновите страницу и повторите ввод.';}
 elseif(!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))){http_response_code(403);$errors['general']='Форма устарела. Обновите страницу и повторите отправку.';}
 else{
  $action=$_POST['action']??'';
  try{
   if($action==='logout'){$_SESSION=[];session_regenerate_id(true);go('home');}
   if($action==='register'&&$page==='register'){
    $login=trim($_POST['login']??'');$password=$_POST['password']??'';$name=trim($_POST['full_name']??'');$phone=trim($_POST['phone']??'');$email=trim($_POST['email']??'');
    if(!preg_match('/^[A-Za-z0-9]{6,255}$/D',$login))$errors['login']='Латиница и цифры, не менее 6 символов.';
    if(mb_strlen($password)<8)$errors['password']='Не менее 8 символов.';
    if(!nameValid($name))$errors['full_name']='Кириллица и пробелы, до 255 символов.';
    if(!phoneValid($phone))$errors['phone']='Формат: 8(999)123-45-67.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>254)$errors['email']='Введите корректный email.';
    if(!$errors){
     try{$repo->query('INSERT INTO users(role_id,login,password_hash,full_name,phone,email) VALUES(2,?,?,?,?,?)',[$login,hashPassword($password),$name,$phone,$email]);flash('Регистрация завершена. Войдите в аккаунт.');go('login');}
     catch(PDOException $ex){if($ex->getCode()==='23000')$errors['login']='Этот логин уже занят.';else throw $ex;}
    }
   }
   if($action==='login'&&$page==='login'){
    $a=$repo->query('SELECT * FROM users WHERE login=?',[trim($_POST['login']??'')])->fetch();
    if(!$a||!passwordMatches($_POST['password']??'',$a['password_hash']))$errors['general']='Неверный логин или пароль.';
    else{session_regenerate_id(true);$_SESSION['user_id']=$a['user_id'];go((int)$a['role_id']===1?'admin':'my');}
   }
   if($action==='apply'&&$page==='apply'&&$user){
    $event=(int)($_POST['event_id']??0);$raw=trim($_POST['event_date']??'');$date=DateTimeImmutable::createFromFormat('!d.m.Y',$raw);
    $phone=trim($_POST['contact_phone']??'');$email=trim($_POST['contact_email']??'');
    $members=array_values(array_filter(array_map('trim',preg_split('/\R/u',$_POST['team']??'')),fn($v)=>$v!==''));
    if(!$repo->query('SELECT event_id FROM events WHERE event_id=?',[$event])->fetch())$errors['event_id']='Выберите мероприятие из списка.';
    if(!$date||$date->format('d.m.Y')!==$raw||(int)$date->format('Y')<1000)$errors['event_date']='Укажите существующую дату в формате ДД.ММ.ГГГГ.';
    if(!$members||count(array_filter($members,fn($n)=>!nameValid($n))))$errors['team']='Укажите ФИО участников кириллицей, каждого с новой строки.';
    if(!phoneValid($phone))$errors['contact_phone']='Формат: 8(999)123-45-67.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>254)$errors['contact_email']='Введите корректный email.';
    if(!$errors){$repo->createApplication((int)$user['user_id'],$event,$date->format('Y-m-d'),$phone,$email,$members);flash('Заявка направлена педагогу-организатору.');go('my');}
   }
   if($action==='status'&&$page==='admin'&&(int)$user['role_id']===1){
    $status=(int)($_POST['status_id']??0);
    if(!in_array($status,[1,2,3],true))$errors['general']='Выберите корректный статус.';
    elseif(!$repo->query('SELECT application_id FROM applications WHERE application_id=?',[(int)($_POST['application_id']??0)])->fetch()){http_response_code(404);$errors['general']='Заявка не найдена.';}
    else{$repo->query('UPDATE applications SET status_id=? WHERE application_id=?',[$status,(int)($_POST['application_id']??0)]);flash('Статус заявки сохранён.');go('admin&'.http_build_query(['type'=>max(0,(int)($_GET['type']??0)),'status'=>max(0,(int)($_GET['status']??0)),'p'=>max(1,(int)($_GET['p']??1))]));}
   }
   if($action==='review'&&$page==='my'&&$user){
    $text=trim($_POST['review_text']??'');
    if($text===''||strlen($text)>65535)$errors['general']='Введите текст отзыва (до 65 535 байт).';
    elseif(!$repo->review((int)($_POST['application_id']??0),(int)$user['user_id'],$text)){http_response_code(403);$errors['general']='Отзыв доступен владельцу один раз после подтверждённого участия.';}
    else{flash('Спасибо за отзыв!');go('my&p='.max(1,(int)($_POST['p']??1)));}
   }
  }catch(PDOException $ex){$errors['general']='Не удалось сохранить данные. Попробуйте ещё раз.';}
 }
}
function inputField(string $name,string $label,string $type='text',string $hint='',string $default=''):void{
 global $errors,$page;
 echo '<div class="field"><label for="'.e($name).'">'.e($label).'</label><input id="'.e($name).'" name="'.e($name).'" type="'.e($type).'" required '.($type==='password'?'autocomplete="'.($page==='login'?'current-password':'new-password').'"':'value="'.e($_POST[$name]??$default).'"').' '.($type==='tel'?'inputmode="numeric" autocomplete="tel" data-phone':'').' '.(isset($errors[$name])?'aria-invalid="true"':'').'>';
 echo '<small class="'.(isset($errors[$name])?'field-error':'hint').'">'.e($errors[$name]??$hint).'</small></div>';
}
