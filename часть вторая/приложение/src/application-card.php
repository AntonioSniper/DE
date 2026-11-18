<article class="application" id="application-<?=$a['application_id']?>">
<div class="card-top"><span>ЗАЯВКА №<?=$a['application_id']?></span><span class="status status-<?=$a['status_id']?>"><?=e($a['status_name'])?></span></div>
<p class="event-type"><?=e($a['type_name'])?></p>
<h2><?=e($a['event_name'])?></h2>
<dl>
<dt>Дата мероприятия</dt><dd><?=date('d.m.Y',strtotime($a['event_date']))?></dd><?php if($admin):?>
<dt>Представитель</dt><dd><?=e($a['full_name'])?></dd>
<dt>Контакты команды</dt><dd><?=e($a['contact_phone'])?><br><?=e($a['contact_email'])?></dd>
<dt>Состав команды</dt><dd><?=nl2br(e(implode("\n",$members[$a['application_id']] ?? [])))?></dd><?php endif?></dl>
