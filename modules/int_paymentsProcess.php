<?
$content='';

switch($_GET['action']) {
    case 'success': $content=processSuccess();break;
    case 'failure': $content='По каким-то причинам платеж не был произведен. Пожалуйста, свяжитесь с нами.';break;
    case 'start': $content=startPayment();break;
    default: $content='Случилась ошибка. Возможно, ваш платеж был завершен, но что-то пошло не так. Пожалуйста, скопируйте URL из адресной строки браузера и свяжитесь с нами.';
    }
    
function processSuccess(){
    $text='<div class="loginWrapper"><h3>Платеж за заказ №'.$_POST['LMI_PAYMENT_NO'].' успешно совершен!</h3>
    <br>'.date('H:i:s d.m.Y',strtotime($_POST['LMI_SYS_PAYMENT_DATE'])).'
    <br>Общая сумма: '.$_POST['LMI_PAYMENT_AMOUNT'].' руб.
    </div>';
    return $text;
    }
    
function startPayment(){
    global $go;
    
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
		
		/*
        $form='
        <form method="post" action="https://paymaster.ru/payment/init">
        <input type="hidden" name="LMI_MERCHANT_ID" value="cee64c56-605a-4079-8807-f62c9603db64">
        <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$TA['amountInvoiced'].'">
        <input type="hidden" name="LMI_CURRENCY" value="643">
        <input type="hidden" name="LMI_PAYMENT_NO" value="'.$TA['orderID'].'">
        <input type="hidden" name="LMI_PAYMENT_DESC" value="'.$TA['paymentDesc'].'">
        <br><input type="submit" class="greyGradient" value="Оплатить">
        </form>
        ';
		*/
		
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
                    container: "paymaster-widget-container"
                });
            }

        </script>
    ';
		
		
        }
    $content.=$form;
	
    
    return $content;
    }
?>


<?=$content?>