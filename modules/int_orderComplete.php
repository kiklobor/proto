<?
global $cartCount;

//достаем заказ
$query='SELECT * FROM orders WHERE ID=?i LIMIT 1';
$order=$go->getAll($query,$_GET['order']);
$paymentsOn=false;

if ($go->affectedRows()===1 && $order[0]['status']==0) {
    //достаем менеджеров
    $query='SELECT * FROM managers';
    $content=$go->getAll($query);
    $managers=array();
    $managers[0]='любой';
    foreach($content as $val){
        $managers[$val['ID']]=$val['name'];
    }

    //достаем покупателя
    $query='SELECT * FROM customers WHERE ID=?i LIMIT 1';
    $customer=$go->getAll($query,$order[0]['customer']);

    //достаем состав заказа
    $query='SELECT * FROM ordersContent WHERE orderID=?i';
    $orderContent=$go->getAll($query,$_GET['order']);

    //обсчитываем и собираем текст
    $orderText='
    <br>Состав заказа:';
    $total_count=0;
    $total_cost=0;
    foreach ($orderContent as $key=>$value) {
    	$total_count+=$value['count'];
    	$query='SELECT * FROM products WHERE ID=?i LIMIT 1';
    	$product=$go->getAll($query,$value['productID']);
    // 	$price= (isset($prices[$product[0]['uID']]) && $prices[$product[0]['uID']]>0) ? $prices[$product[0]['uID']] : 0;
        $price=$value['cost'];
    	$total_cost+=$value['count']*$price;
    	$orderText=$orderText.'<br>- '.$product[0]['name'].', '.$product[0]['articleFull'].' ('.$price.' руб.): '.$value['count'];
    	}
    $orderText=$orderText.'<br><br>Всего товаров: '.$total_count;
    $orderText=$orderText.'<br>Общая стоимость: '.$total_cost.' руб.';

    //собираем письмо покупателю
    $mailText='Здравствуйте, '.$customer[0]['name'].'<br><br>Номер вашего заказа: '.$_GET['order'].'<br>'.$orderText.'<br>В ближайшее время с вами свяжется менеджер.<br><br>Спасибо за ваш заказ!<br>С уважением, ООО "Имидж".';

    //отправляем письмо покупателю

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
    $mail->IsSendmail(); // telling the class to use SendMail transport
    $mail->CharSet = 'UTF-8';
    try {
    	$mail->AddAddress($customer[0]['mail'], $customer[0]['name']);
    	$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
    	$mail->Subject = 'Заказ №'.$_GET['order'].' на сайте IMIGE.RU';
    	$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
    	$mail->MsgHTML($mailText);
    	$mail->Send();
    	echo '<div class="orderWrapper w-100">
    Готово!<br>
    Заказ №'.$_GET['order'].' зарегистрирован.<br>
    Проверьте вашу почту.<br>
    </div>';
       	}
    catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
    catch (Exception $e) {$e->getMessage();}

    echo '<div class="loginWrapper"><br><a href= "http://proto.imige.ru/"><button class="greenGradient cartButton3">Вернуться на главную</button></a><br>'.$orderText.'</div>';
    //if ($paymentsOn) echo '
    //<div class="w-100">
    //Вы можете оплатить свой заказ банковской картой.<br>
    //<a href="/payment?action=start&order='.$_GET['order'].'"><button class="greyGradient">Оплатить</button></a>
    //</div>';

    //пилим текст письма для админов и менеджеров
    $mailText='Заказчик: '.$customer[0]['name'].'<br><br>
    Номер заказа: '.$_GET['order'].'<br>
    Телефон: '.$customer[0]['phone'].'<br>
    Почта: '.$customer[0]['mail'].'<br><br>
    '.$orderText.'<br>';

    $botText='Новый заказ №'.$_GET['order'].'
    Заказчик: '.$customer[0]['name'].'
    Телефон: '.$customer[0]['phone'].'
    Почта: '.$customer[0]['mail'].'
    Всего товаров: '.$total_count.'
    Сумма: '.$total_cost.'
    Менеджер: '.$managers[$order[0]['manager']].'
    Полная информация о заказе выслана по почте.';
    // $go->sendMessageAdmin($botText); //отправляем в телеграм

    $telegramMessage=array(
        'method'=>'sendMessage',
        'text'=>$botText,
        'chat_id'=>'',
        'disable_notification'=>'true'
        );
    $telegramMessage['disable_notification']= (isWorkingHours()) ? 'false' : 'true';

    //отправляем копии письма админам
    $query='SELECT * FROM adminDelivery WHERE active=1';
    $delivery=$go->getAll($query);

    foreach($delivery as $key=>$value) {
    	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    	$mail->IsSendmail();
    	$mail->CharSet = 'UTF-8';
        try {
    		$mail->AddAddress($value['mail'], $value['name']);
    		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
    		$mail->Subject = 'Новый заказ №'.$_GET['order'];
    		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
    		$mail->MsgHTML($mailText);
    		$mail->Send();
    		}
    	catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
    	catch (Exception $e) {$e->getMessage();}

    	if ($value['telegramID']!=0) {
    	    $telegramMessage['chat_id']=$value['telegramID'];
    	    $tg->sendj($telegramMessage);
    	    }
    	}

    //отправляем письмо менеджерам
    $telegramMessage['reply_markup']=array(
            "inline_keyboard" => array(
                array(array('text' => 'Принять', 'callback_data' => 'action=accept&what=order&id='.$_GET['order']))
                )
            );

    if ($order[0]['manager']!=0) {
        $query='SELECT * FROM managers WHERE active=1 AND ID=?i';
        $delivery=$go->getAll($query,$order[0]['manager']);
        if ($go->affectedRows()!=1) {
            $query='SELECT * FROM managers WHERE active=1';
            $delivery=$go->getAll($query);
            }
        }
    else {
        $query='SELECT * FROM managers WHERE active=1';
        $delivery=$go->getAll($query);
        }

    foreach($delivery as $key=>$value) {
    	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    	$mail->IsSendmail();
    	$mail->CharSet = 'UTF-8';
    	try {
    		$mail->AddAddress($value['mail'], $value['name']);
    		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
    		$mail->Subject = 'Новый заказ №'.$_GET['order'];
    		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
    		$mail->MsgHTML($mailText);
    		$mail->Send();
    		}
    	catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
    	catch (Exception $e) {$e->getMessage();}

    	if ($value['telegramID']!=0) {
    	    $telegramMessage['chat_id']=$value['telegramID'];
    	    $tg->sendj($telegramMessage);
    	    }
    	}

    //обновляем статус заказа на 1, чистим корзину
    $query='UPDATE `orders` SET `status`=1 WHERE ID=?i';
    $go->query($query,$_GET['order']);

    //пользователь может накидать в корзину и потом авторизоваться, поэтому два удаления.
    if (isset($_COOKIE['cartToken'])) {
      $query = 'DELETE FROM `carts` WHERE `token`=?s';
      $go->query($query, $_COOKIE['cartToken']);
    }
    if (isset($_SESSION['user_id'])) {
      $query = 'DELETE FROM `carts` WHERE `userID`=?s';
      $go->query($query, $_SESSION['user_id']);
    }

    unset($_SESSION['cart']);
    unset($_COOKIE['cartToken']);
    setcookie('cartToken', '', time() - 3600, '/'); // empty value and old timestamp
    $cartCount = 0;

} else echo 'Что-то пошло не так. Скорее всего, этот заказ уже обработан.<div class="loginWrapper"><a href= "http://proto.imige.ru/"><button class="greenGradient cartButton3">Вернуться на главную</button></a></div>';
