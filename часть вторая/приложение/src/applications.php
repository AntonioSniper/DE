<?php

$where = $admin ? '1=1' : 'a.user_id=?';
$params = $admin ? [] : [$user['user_id']];
if ($admin && $type > 0) {
    $where .= ' AND e.type_id=?';
    $params[] = $type;
}
if ($admin && $status > 0) {
    $where .= ' AND a.status_id=?';
    $params[] = $status;
}
$from=' FROM applications a JOIN events e ON a.event_id=e.event_id JOIN event_types t ON e.type_id=t.type_id JOIN statuses s ON a.status_id=s.status_id JOIN users u ON a.user_id=u.user_id LEFT JOIN reviews r ON a.application_id=r.application_id';

$total=(int)$repo->query('SELECT COUNT(*)'.$from.' WHERE '.$where,$params)->fetchColumn();
$pages=max(1,(int)ceil($total/3));
$current=min($pages,max(1,(int)($_GET['p']??1)));
$offset=($current-1)*3;

$items=$repo->query('SELECT a.*,e.event_name,t.type_name,s.status_name,u.full_name,r.review_id,r.review_text'.$from.' WHERE '.$where.' ORDER BY a.application_id DESC LIMIT 3 OFFSET '.$offset,$params)->fetchAll();

if ($admin && $items) {
    $ids = array_column($items, 'application_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $members = $repo->query(
        "SELECT application_id, full_name FROM application_members
         WHERE application_id IN ($placeholders) ORDER BY member_id",
        $ids
    )->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
}
