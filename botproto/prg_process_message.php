<?
$orderID=rand(3,5);

$tg->sendj(array(
    'method'=>'sendMessage',
    'text'=>'Новый тестовый заказ №'.$orderID,
    'chat_id'=>$tg->message['from']['id'],
    'disable_notification'=>'true',
    // 'reply_markup' => array(
    //     "inline_keyboard" => array(
    //         array(array('text' => 'Принять', 'callback_data' => 'accept'.$orderID))
    //         )
    //     )
    'reply_markup' => array(
        "inline_keyboard" => array(
            array(array('text' => 'Принять', 'callback_data' => 'action=accept&what=order&id='.$orderID))
            )
        )
    ));
?>
