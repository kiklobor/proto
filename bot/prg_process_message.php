<?
$tg->sendj(array(
    'method'=>'sendMessage',
    'text'=>'Прямые запросы пока не поддерживаются',
    'chat_id'=>$tg->message['from']['id'],
    'disable_notification'=>'false'
    ));
?>
