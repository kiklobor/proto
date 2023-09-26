<?
// $tg->sendj(array(
//     'method'=>'sendMessage',
//     'text'=>'Callback-запросы пока не поддерживаются',
//     'chat_id'=>$tg->message['chat']['id'],
//     'disable_notification'=>'false'
//     ));
    
parse_str($tg->cb['data'],$callbackData);

/*ОБРАБОТКА ЗАКАЗОВ*/
if ($callbackData['action']=='accept' && $callbackData['what']=='order') {
    //проверяем, что для заказа все еще не установлен менеджер
    //если установлен - отправляем уведомляшку, отключаем кнопки и закругляем выполнение
    $orderID=(is_numeric($callbackData['id'])) ? $callbackData['id'] : 0; 
    
    $order=$go->getRow('SELECT * FROM orders WHERE ID=?i',$orderID);
    if ($go->affectedRows()!=1) {
        $tg->sendj(array(
            'method'=>'answerCallbackQuery',
            'text'=>'Заказ не найден',
            'callback_query_id'=>$tg->cb['id'],
            'show_alert'=>'true'
            ));
        $tg->sendj(array(
            'method'=>'editMessageReplyMarkup',
            'chat_id'=>$tg->message['chat']['id'],
            'message_id'=>$tg->message['message_id'],
            'reply_markup'=>array(
                "inline_keyboard" => array()
                )
            ));
        die();
        }
    
    if ($order['acceptedByManager']!=0) {
        $tg->sendj(array(
            'method'=>'answerCallbackQuery',
            'text'=>'Заказ №'.$orderID.' уже принят',
            'callback_query_id'=>$tg->cb['id'],
            'show_alert'=>'true'
            ));
        $tg->sendj(array(
            'method'=>'editMessageReplyMarkup',
            'chat_id'=>$tg->message['chat']['id'],
            'message_id'=>$tg->message['message_id'],
            'reply_markup'=>array(
                "inline_keyboard" => array()
                )
            ));
        die();
        }
    
    $manager=$go->getRow('SELECT * FROM managers WHERE telegramID=?i LIMIT 1',$tg->cb['from']['id']);
    
    
    // $text='Manager: '.$manager['name'];
    // $text.=PHP_EOL.'Message ID: '.$messageID;
    // $text.=PHP_EOL.'Order ID: '.$orderID;
    // $tg->sendj(array(
    //     'method'=>'sendMessage',
    //     'text'=>$text,
    //     'chat_id'=>'302711179',
    //     'disable_notification'=>'true'
    //     ));

    
    
    //1 приписываем менеджера к заказу
    $go->query('UPDATE orders SET acceptedByManager=?i WHERE ID=?i',$manager['ID'],$orderID);
    
    //2 отправляем уведомление принявшему менеджеру
    $tg->sendj(array(
        'method'=>'answerCallbackQuery',
        'text'=>'Вы приняли заказ №'.$orderID,
        'callback_query_id'=>$tg->cb['id'],
        'show_alert'=>'true'
        ));
    
    // $messageID=$tg->cb['message']['message_id'];
    $tg->sendj(array( //убираем клавиатуру
        'method'=>'editMessageReplyMarkup',
        'chat_id'=>$tg->message['chat']['id'],
        'message_id'=>$tg->message['message_id'],
        'reply_markup'=>array(
            "inline_keyboard" => array()
            )
        ));
        
    // //3 отправляем сообщение о принятии всем менеджерам 
    // $managers=$go->getAll('SELECT * FROM managers WHERE telegramID!=0 AND active=1');
    // foreach($managers as $val) 
    //     $tg->sendj(array(
    //         'method'=>'sendMessage',
    //         'text'=>$manager['name'].' принял(а) заказ №'.$orderID,
    //         'chat_id'=>$val['telegramID'],
    //         'disable_notification'=>'true'
    //         ));
    
    /*3 уведомление о принятии менеджерам
    только в том случае, если был выбран любой менеджер
    если был указан конкретный менеджер, уведомления рассылаются только админам
    */
    if ($order['manager']==0) {
        $managers=$go->getAll('SELECT * FROM managers WHERE telegramID!=0 AND active=1');
        foreach($managers as $val) 
            $tg->sendj(array(
                'method'=>'sendMessage',
                'text'=>$manager['name'].' принял(а) заказ №'.$orderID,
                'chat_id'=>$val['telegramID'],
                'disable_notification'=>'true'
                ));
        }
    
    //отправляем сообщение о принятии всем админам
    $admins=$go->getAll('SELECT * FROM adminDelivery WHERE telegramID!=0 AND active=1');
    foreach($admins as $val) 
        $tg->sendj(array(
            'method'=>'sendMessage',
            'text'=>$manager['name'].' принял(а) заказ №'.$orderID,
            'chat_id'=>$val['telegramID'],
            'disable_notification'=>'true'
            ));

    }















/*ОБРАБОТКА ОБРАТНЫХ ЗВОНКОВ*/
elseif ($callbackData['action']=='accept' && $callbackData['what']=='callback') {
    //проверяем, что для заказа все еще не установлен менеджер
    //если установлен - отправляем уведомляшку, отключаем кнопки и закругляем выполнение
    $ID=(is_numeric($callbackData['id'])) ? $callbackData['id'] : 0; 
    
    $order=$go->getRow('SELECT * FROM callbacks WHERE ID=?i',$ID);
    if ($go->affectedRows()!=1) {
        $tg->sendj(array(
            'method'=>'answerCallbackQuery',
            'text'=>'Заявка не найдена',
            'callback_query_id'=>$tg->cb['id'],
            'show_alert'=>'true'
            ));
        $tg->sendj(array(
            'method'=>'editMessageReplyMarkup',
            'chat_id'=>$tg->message['chat']['id'],
            'message_id'=>$tg->message['message_id'],
            'reply_markup'=>array(
                "inline_keyboard" => array()
                )
            ));
        die();
        }
    
    if ($order['acceptedByManager']!=0) {
        $tg->sendj(array(
            'method'=>'answerCallbackQuery',
            'text'=>'Заявка №'.$ID.' уже принята',
            'callback_query_id'=>$tg->cb['id'],
            'show_alert'=>'true'
            ));
        $tg->sendj(array(
            'method'=>'editMessageReplyMarkup',
            'chat_id'=>$tg->message['chat']['id'],
            'message_id'=>$tg->message['message_id'],
            'reply_markup'=>array(
                "inline_keyboard" => array()
                )
            ));
        die();
        }
    
    $manager=$go->getRow('SELECT * FROM managers WHERE telegramID=?i LIMIT 1',$tg->cb['from']['id']);
    
    //1 приписываем менеджера к перезвону
    $go->query('UPDATE callbacks SET acceptedByManager=?i WHERE ID=?i',$manager['ID'],$ID);
    
    //2 отправляем уведомление принявшему менеджеру
    $tg->sendj(array(
        'method'=>'answerCallbackQuery',
        'text'=>'Вы приняли заявку на обратный звонок №'.$ID,
        'callback_query_id'=>$tg->cb['id'],
        'show_alert'=>'true'
        ));
    
    // $messageID=$tg->cb['message']['message_id'];
    $tg->sendj(array( //убираем клавиатуру
        'method'=>'editMessageReplyMarkup',
        'chat_id'=>$tg->message['chat']['id'],
        'message_id'=>$tg->message['message_id'],
        'reply_markup'=>array(
            "inline_keyboard" => array()
            )
        ));
        
    //3 отправляем сообщение о принятии всем менеджерам и админам
    $telegramMessage=array(
            'method'=>'sendMessage',
            'text'=>$manager['name'].' принял(а) заявку на обратный звонок №'.$ID,
            'chat_id'=>'',
            'disable_notification'=>'true'
            );
            
    $managers=$go->getAll('SELECT * FROM managers WHERE telegramID!=0 AND active=1');
    foreach($managers as $val) {
        $telegramMessage['chat_id']=$val['telegramID'];
        $tg->sendj($telegramMessage);
        }
            
    //отправляем сообщение о принятии всем админам
    $admins=$go->getAll('SELECT * FROM adminDelivery WHERE telegramID!=0 AND active=1');
    foreach($admins as $val) {
        $telegramMessage['chat_id']=$val['telegramID'];
        $tg->sendj($telegramMessage);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
/*ОБРАБОТКА КАСТОМНЫХ*/
elseif ($callbackData['action']=='accept' && $callbackData['what']=='custom') {
    //проверяем, что для заказа все еще не установлен менеджер
    //если установлен - отправляем уведомляшку, отключаем кнопки и закругляем выполнение
    $ID=(is_numeric($callbackData['id'])) ? $callbackData['id'] : 0; 
    
    $order=$go->getRow('SELECT * FROM customOrders WHERE ID=?i',$ID);
    if ($go->affectedRows()!=1) {
        $tg->sendj(array(
            'method'=>'answerCallbackQuery',
            'text'=>'Заявка на заказ не найдена',
            'callback_query_id'=>$tg->cb['id'],
            'show_alert'=>'true'
            ));
        $tg->sendj(array(
            'method'=>'editMessageReplyMarkup',
            'chat_id'=>$tg->message['chat']['id'],
            'message_id'=>$tg->message['message_id'],
            'reply_markup'=>array(
                "inline_keyboard" => array()
                )
            ));
        die();
        }
    
    if ($order['acceptedByManager']!=0) {
        $tg->sendj(array(
            'method'=>'answerCallbackQuery',
            'text'=>'Заявка на заказ №'.$ID.' уже принята',
            'callback_query_id'=>$tg->cb['id'],
            'show_alert'=>'true'
            ));
        $tg->sendj(array(
            'method'=>'editMessageReplyMarkup',
            'chat_id'=>$tg->message['chat']['id'],
            'message_id'=>$tg->message['message_id'],
            'reply_markup'=>array(
                "inline_keyboard" => array()
                )
            ));
        die();
        }
    
    $manager=$go->getRow('SELECT * FROM managers WHERE telegramID=?i LIMIT 1',$tg->cb['from']['id']);
    
    //1 приписываем менеджера к перезвону
    $go->query('UPDATE customOrders SET acceptedByManager=?i WHERE ID=?i',$manager['ID'],$ID);
    
    //2 отправляем уведомление принявшему менеджеру
    $tg->sendj(array(
        'method'=>'answerCallbackQuery',
        'text'=>'Вы приняли заявку на индивидуальный заказ №'.$ID,
        'callback_query_id'=>$tg->cb['id'],
        'show_alert'=>'true'
        ));

    $tg->sendj(array( //убираем клавиатуру
        'method'=>'editMessageReplyMarkup',
        'chat_id'=>$tg->message['chat']['id'],
        'message_id'=>$tg->message['message_id'],
        'reply_markup'=>array(
            "inline_keyboard" => array()
            )
        ));
        
    //3 отправляем сообщение о принятии всем менеджерам и админам
    $telegramMessage=array(
            'method'=>'sendMessage',
            'text'=>$manager['name'].' принял(а) заявку на индивидуальный заказ №'.$ID,
            'chat_id'=>'',
            'disable_notification'=>'true'
            );
            
    $managers=$go->getAll('SELECT * FROM managers WHERE telegramID!=0 AND active=1');
    foreach($managers as $val) {
        $telegramMessage['chat_id']=$val['telegramID'];
        $tg->sendj($telegramMessage);
        }
            
    //отправляем сообщение о принятии всем админам
    $admins=$go->getAll('SELECT * FROM adminDelivery WHERE telegramID!=0 AND active=1');
    foreach($admins as $val) {
        $telegramMessage['chat_id']=$val['telegramID'];
        $tg->sendj($telegramMessage);
        }
    }
    


































/*ОБРАБОТКА ОТКАЗОВ в данный момент не функционирует*/
elseif ($callbackData['action']=='decline') {
    $tg->sendj(array(
        'method'=>'answerCallbackQuery',
        'text'=>'Вы отклонили заказ',
        'callback_query_id'=>$tg->cb['id'],
        'show_alert'=>'true'
        ));
    }
?>