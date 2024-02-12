<?

$content='';

switch($_GET['action']) {
    case 'success': $content=processSuccess();break;
    case 'failure': $content='<div class="loginWrapper">По каким-то причинам платеж не был произведен. Пожалуйста, свяжитесь с нами.<br><br><a href= "http://proto.imige.ru/"><button class="greenGradient cartButton3">Вернуться на главную</button></a></div>';break;
    case 'start': $content=startPayment();break;
    default: $content='<div class="loginWrapper">Случилась ошибка. Возможно, ваш платеж был завершен, но что-то пошло не так. Пожалуйста, скопируйте URL из адресной строки браузера и свяжитесь с нами.<br><br><a href= "http://proto.imige.ru/"><button class="greenGradient cartButton3">Вернуться на главную</button></a></div>';
    }
    
function processSuccess(){
	global $go;
	global $tg;
	global $cartCount;

//достаем заказ
$query='SELECT * FROM orders WHERE ID=?i LIMIT 1';
$order=$go->getAll($query,$_POST['LMI_PAYMENT_NO']);
$paymentsOn=false;

if ($go->affectedRows()===1) {

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
    $orderContent=$go->getAll($query,$_POST['LMI_PAYMENT_NO']);

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
    $mailText='Здравствуйте, '.$customer[0]['name'].'<br><br>Номер вашего заказа: '.$_POST['LMI_PAYMENT_NO'].'<br>'.$orderText.'<br>Оплачен онлайн.<br><br>Спасибо за ваш заказ!<br>С уважением, ООО "Имидж".';

    //отправляем письмо покупателю

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
    $mail->IsSendmail(); // telling the class to use SendMail transport
    $mail->CharSet = 'UTF-8';
    try {
    	$mail->AddAddress($customer[0]['mail'], $customer[0]['name']);
    	$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
    	$mail->Subject = 'Заказ №'.$_POST['LMI_PAYMENT_NO'].' на сайте IMIGE.RU';
    	$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
    	$mail->MsgHTML($mailText);
    	$mail->Send();
    	//echo '<div class="orderWrapper w-100">
    //Готово!<br>
    //Заказ №'.$_GET['order'].' зарегистрирован.<br>
    //Проверьте вашу почту.<br><br>
    //</div>';
    	}
    catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
    catch (Exception $e) {$e->getMessage();}

    //echo $orderText;
    //if ($paymentsOn) echo '
    //<div class="w-100">
    //Вы можете оплатить свой заказ банковской картой.<br>
    //<a href="/payment?action=start&order='.$_GET['order'].'"><button class="greenGradient cartButton3">Оплатить</button></a>
    //</div>';

    //пилим текст письма для админов и менеджеров
    $mailText='Заказ №' . $_POST['LMI_PAYMENT_NO'] . ', на сумму ' . $total_cost . ' оплачен онлайн.';

    $botText='Новый заказ №'.$_POST['LMI_PAYMENT_NO'].'
    Заказчик: '.$customer[0]['name'].'
    Телефон: '.$customer[0]['phone'].'
    Почта: '.$customer[0]['mail'].'
    Всего товаров: '.$total_count.'
    Сумма: '.$total_cost.'
    Менеджер: '.$managers[$order[0]['manager']].'
    Полная информация о заказе выслана по почте.';
    // $go->sendMessageAdmin($botText); //отправляем в телеграм

    $telegramMessage = array(
       'method' => 'sendMessage',
       'text' => 'Заказ №' . $_POST['LMI_PAYMENT_NO'] . ', на сумму ' . $total_cost . ' оплачен онлайн.',
       'chat_id' => '',
       'disable_notification' => 'true'
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
    		$mail->Subject = 'Новый заказ №'.$_POST['LMI_PAYMENT_NO'];
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
    //$telegramMessage['reply_markup']=array(
    //        "inline_keyboard" => array(
     //           array(array('text' => 'Принять', 'callback_data' => 'action=accept&what=order&id='.$_POST['LMI_PAYMENT_NO']))
     //           )
    //        );

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
    		$mail->Subject = 'Новый заказ №'.$_POST['LMI_PAYMENT_NO'];
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
		
}
    /*
    // достаем и обсчитываем заказ
    $query='SELECT * FROM orders WHERE ID=?i LIMIT 1';
    $order=$go->getRow($query,$_GET['order']);
    $orderDate=date('d.m.Y',strtotime($order['date']));
    $content='<h3>Платеж за заказ №'.$_GET['order'].' от '.$orderDate. ' оплачен онлайн"!</h3>';
    //достаем покупателя
    $query='SELECT * FROM customers WHERE ID=?i LIMIT 1';
    $customer=$go->getRow($query,$order['customer']);
    //достаем состав заказа
    $query='SELECT * FROM ordersContent WHERE orderID=?i';
    $orderContent=$go->getAll($query,$_GET['order']);
    
    //обсчитываем и собираем текст
    $orderText='
    <br>Состав заказа:';
    $total_count=0;
    $total_cost=0;
    $includesZeroCostItems=false;
    foreach ($orderContent as $key=>$value) {
    	$total_count+=$value['count'];
    	$query='SELECT * FROM products WHERE ID=?i LIMIT 1';
    	$product=$go->getRow($query,$value['productID']);
    	$cost=$value['cost'];
    	if ($cost==0) $includesZeroCostItems=true;
    	$total_cost+=$value['count']*$cost;
    	$orderText.='<br>- '.$product['name'].', '.$product['articleFull'].' ('.$cost.' руб.): '.$value['count'];
    	}
    $orderText.='<br><br>Всего товаров: '.$total_count;
    $orderText.='<br>Общая стоимость: '.$total_cost.' руб.';

    $content.='<div class="w-100">'.$orderText.'</div>';
	
	    // Send Telegram message
    //$telegramMessage = array(
    //    'method' => 'sendMessage',
    //    'text' => 'Заказ №' . $_GET['order'] . ', на сумму ' . $total_cost . ' оплачен онлайн.',
     //   'chat_id' => '',
    //    'disable_notification' => 'true'
    //);
$botText = 'Заказ №' . $_GET['order'] . ', на сумму ' . $total_cost . ' оплачен онлайн.';
 $go->sendMessageAdmin($botText);
*/
	
	
    $text='<div class="loginWrapper"><h3>Платеж за заказ №'.$_POST['LMI_PAYMENT_NO'].' успешно совершен!</h3>
    <br>'.date('H:i:s d.m.Y',strtotime($_POST['LMI_SYS_PAYMENT_DATE'])).'
    <br>Общая сумма: '.$_POST['LMI_PAYMENT_AMOUNT'].' руб.</br></br></br>
    <a href= "http://proto.imige.ru/"><button class="greenGradient cartButton3">Вернуться на главную</button></a>
    </div>
	
	
	
';

    return $text;
    }
    
function startPayment(){
	    global $go;
	global $tg;
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
    	//echo '<div class="orderWrapper w-100">
    //Готово!<br>
    //Заказ №'.$_GET['order'].' зарегистрирован.<br>
    //Проверьте вашу почту.<br><br>
    //</div>';
    	}
    catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
    catch (Exception $e) {$e->getMessage();}

    //echo $orderText;
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

} //else echo 'Что-то пошло не так. Скорее всего, этот заказ уже обработан.';

    // достаем и обсчитываем заказ
    $query='SELECT * FROM orders WHERE ID=?i LIMIT 1';
    $order=$go->getRow($query,$_GET['order']);
    $orderDate=date('d.m.Y',strtotime($order['date']));
    $content='<h3>Оплата заказа №'.$_GET['order'].' от '.$orderDate.'</h3>';
    //достаем покупателя
    $query='SELECT * FROM customers WHERE ID=?i LIMIT 1';
    $customer=$go->getRow($query,$order['customer']);
    //достаем состав заказа
    $query='SELECT * FROM ordersContent WHERE orderID=?i';
    $orderContent=$go->getAll($query,$_GET['order']);
    
    //обсчитываем и собираем текст
    $orderText='
    <br>Состав заказа:';
    $total_count=0;
    $total_cost=0;
    $includesZeroCostItems=false;
    foreach ($orderContent as $key=>$value) {
    	$total_count+=$value['count'];
    	$query='SELECT * FROM products WHERE ID=?i LIMIT 1';
    	$product=$go->getRow($query,$value['productID']);
    	$cost=$value['cost'];
    	if ($cost==0) $includesZeroCostItems=true;
    	$total_cost+=$value['count']*$cost;
    	$orderText.='<br>- '.$product['name'].', '.$product['articleFull'].' ('.$cost.' руб.): '.$value['count'];
    	}
    $orderText.='<br><br>Всего товаров: '.$total_count;
    $orderText.='<br>Общая стоимость: '.$total_cost.' руб.';

    $content.='<div class="w-100">'.$orderText.'</div>';
    
    $TA=$go->getRow('SELECT * FROM transactions WHERE orderID=?i',$_GET['order']);
    // если транзакции нет, создаем ее
    if ($go->affectedRows()!=1) {
        $desc='Оплата заказа №'.$_GET['order'].' от '.$orderDate;
        $go->query('INSERT INTO `transactions`(`orderID`, `amountInvoiced`, `paymentDesc`) VALUES (?i,?s,?s)',$_GET['order'],$total_cost,$desc);
        $TA=$go->getRow('SELECT * FROM transactions WHERE orderID=?i',$_GET['order']);
        }
    
    if ($includesZeroCostItems) $form='<br>Заказ содержит товары с нулевой ценой, поэтому оплатить его онлайн нельзя. С вами свяжется менеджер для обсуждения деталей.<br>Приносим извинения за неудобства.';
    elseif ($TA['amountInvoiced']==$TA['amountPaid']) $form='<br>Этот заказ уже оплачен.';
    elseif ($TA['amountPaid']>0 && $TA['amountPaid']<$TA['amountInvoiced']) $form='<br>Похоже, этот заказ оплачен только частично. Пожалуйста, свяжитесь с менеджером.';
    else {
		
		
        $form='
        <form style="display:none" method="post" action="https://paymaster.ru/payment/init">
        <input type="hidden" name="LMI_MERCHANT_ID" value="cee64c56-605a-4079-8807-f62c9603db64">
        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$TA['amountInvoiced'].'">
        <input type="hidden" name="LMI_CURRENCY" value="643">
        <input type="hidden" name="LMI_PAYMENT_NO" value="'.$TA['orderID'].'">
        <input type="hidden" name="LMI_PAYMENT_DESC" value="'.$TA['paymentDesc'].'">
        <br><input type="submit" class="greyGradient" value="Оплатить">
        </form>
		
		<script>
		document.querySelector(".greyGradient").click();
		</script>
        ';
        
		/*
		$form = '
        <button class="greenGradient" onclick="pay()">Оплатить</button>
        <script src="https://paymaster.ru/cpay/sdk/payment-widget.js"></script>
        <script>
            // Initialize PayMaster Payment Widget
            function pay() {
                var paymentWidget = new cpay.PaymentWidget();

                paymentWidget.init({
                    merchantId: "cee64c56-605a-4079-8807-f62c9603db64", // Replace with your actual merchantId
                    invoice: {
                        description: "'.$TA['paymentDesc'].'"
                    },
                    amount: {
                        value: '.$TA['amountInvoiced'].',
                        currency: "RUB"
                    },
                    container: "paymaster-widget-container",
        onSuccess: function() {
            
            alert("Платеж успешно завершен.");
            
        }
                });
            }
document.querySelector(".greenGradient").click();
        </script>
    ';
		*/
		
        }
    $content=$form;
	
    
	
	
    return $content;
    }
?>


<?=$content?>
