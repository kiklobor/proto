<div class="bread">
<a href="/">Главная</a> / Корзина
</div>

<h1>Корзина</h1>
<!-- <h3><strong><font color='red'>ВНИМАНИЕ!!! Цена со скидкой и оптовая цена действуют при заказе от одной коробки на каждый товар!</font></strong></h3> -->
<?
global $paymentsOn; //--- Переменная - будет ли оплата онлайн
$mngrs=array();
array_push($mngrs,'
<label class="btn col-4 col-md-3 col-lg-2 p-0 mngrMethod active">
<div class="method p-1">
<img class="rounded-circle" src="/styles/images/noAvatar.png">
<div>Любой менеджер</div>
</div>
<input type="radio" value="0" name="mngr" checked>
</label>');

$query='SELECT * FROM managers WHERE active=1 AND display=1 ORDER BY name';
$mngrsOb=$go->getAll($query);

foreach($mngrsOb as $key=>$val) {
    $images=glob(MPICS.$val['ID'].'*');
            if (count($images)!=0) {
                usort($images,'imgSort');
                $mpic=$images[0];
                $mpic = \Utilsw\Image\Image::getResizeImg($mpic);
                }
		    else $mpic='/styles/images/noAvatar.png';
    $row='
    <label class="btn col-4 col-md-3 col-lg-2 p-0 mngrMethod">
    <div class="method p-1">
    <img class="rounded-circle" src="'.$mpic.'">
    <div>'.$val['name'].'</div>
    </div>
	<input type="radio" value="'.$val['ID'].'" name="mngr">
	</label>
			';
	array_push($mngrs,$row);
    }




if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
	$i=1;
	$alert=false;
	$costAlertStyle='';

	foreach ($_SESSION['cart'] as $key=>$value) {
		echo '<div class="cartRow row w-100 m-0 pb-1 align-items-center" product="'.$key.'">';
		$query='SELECT * FROM products WHERE ID=?i AND active=1 LIMIT 1';
		$product=$go->getAll($query,$key);
		if ($go->affectedRows()>0) {
			$prName=$product[0]['name'];
			$prCost= (isset($prices[$product[0]['uID']]) && $prices[$product[0]['uID']]>0) ? $prices[$product[0]['uID']] : 0;
			if ($prCost==0) {
			    $alert=true;
			    $costAlertStyle=' style="color:red"';
			    }
			else $costAlertStyle='';

			$prPhoto=$product[0]['name'];
			$prUrl=$product[0]['url'];
			$prArticle=$product[0]['articleFull'];

			//$prPhoto='files/'.$product[0]['uID'].'.png';
		//	if (!file_exists($prPhoto)) $prPhoto='styles/images/noPhoto.png';

		    $images=glob(FPATH.$product[0]['uID'].'*');
            if (count($images)!=0) {
                usort($images,'imgSort');
                $prPhoto='/'.$images[0];
                $prPhoto = \Utilsw\Image\Image::getResizeImg($prPhoto);
                }
		    else $prPhoto=NOPH;

			//$go->free($product);
		} else {
			echo 'Случилась нехорошая ошибка. Пожалуйста, напишите нам об этом.';
			unset($_SESSION['cart']);
			exit;
		}
		echo '<div class="col-3 col-sm-2 col-md-1 p-1"><img src="'.$prPhoto.'" class="cartImg"></div>';
		echo '<div class="col-9 col-sm-10 col-md-6 p-1"><a href="/product/'.$prUrl.'"><span>'.$prName.'</span></a></div>';
		echo '<div class="col-4 col-md-2 p-1 text-left d-flex align-items-center"><button class="cartProductCount" action="0" product="'.$key.'">-</button><input class="cartProductCount" type="text" autocomplete="off" value="'.$value['count'].'" product="'.$key.'"><button class="cartProductCount" action="1" product="'.$key.'">+</button>&nbsp;шт.</div>';
		echo '<div class="col-4 col-md-2 p-1 text-center cart-price" data-id="'.$product[0]['ID'].'"'.$costAlertStyle.'>'.$value['cost'].' руб/шт</div>';
		echo '<div class="col-4 col-md-1 p-1 text-right"><span class="deleteFromCart" product="'.$key.'">Удалить</span></div>';
		echo '</div>';
		$i++;
		}
	//echo '<div class="cartFinal">Наименований: <b id="cartcount2">'.$_SESSION['cart_count'].'</b><br>';
	echo '<div class="cartFinal w-100">Всего товаров: <b id="carttotal">'.$_SESSION['cart_total'].'</b> шт.<br>';
	echo 'Итого: <b id="cartcost">'.$_SESSION['cart_cost'].'</b> руб.</br>';
	if ($alert) echo '<div style="color:red;text-align:center;margin: 15px auto;">Обратите внимание: один или более товаров в вашей корзине имеют нулевую цену, однако они не распространяются бесплатно. После оформления заказа с вами свяжется менеджер, проинформирует об актуальной цене товара и сделает пересчет итоговой стоимости.
			<br>Приносим извинения за неудобства.</div>';
  $placeOrder = '&nbsp;<button class="greenGradient cartButton1 cartButton2">Оформить</button>';
  if (isset($sentResult) AND is_array($sentResult) AND count($sentResult)>0) {
    $placeOrder = '';
  }
  echo 'Вы можете оплатить заказ онлайн на сайте.</br>';
  echo '<img src="../styles/images/payment_systems.png" alt="payment_system"></br>';
  if ($alert)	echo 'Оплатить онлайн <input type="checkbox" id= name="PaymentOnline" disabled/></br>';
  	else echo 'Оплатить онлайн <input type="checkbox" name="PaymentOnline"/></br>';
	echo '<div class="cartFinal"><a class="textlink goback"> Назад </a>&nbsp;<a id="erase" class="textlink"> Очистить </a>'.$placeOrder.'</div></div>';
	}
else echo 'Корзина пуста';
//$go->free($product);

/*
echo '<pre>';
var_dump($sentResult);
echo '</pre>';
/**/
//var_dump($_SESSION['user_id']);

function setinputsw ($inputName, $inputsArr, $inputsUserData) {
  if (array_key_exists($inputName, $inputsArr) AND $inputsArr[$inputName]!='')
    return $inputsArr[$inputName];
  else
    return $inputsUserData[$inputName];
}
function CheckInputFieldHasError($inpName, $ErrArr) {
  if (is_array($ErrArr) AND count($ErrArr)>0) {
    //return 'has-error';
    if (in_array($inpName, $ErrArr))
      return 'has-error';
  }
  return '';
}
function wrapErrorsText($sentResult) {
  $res = '';
  foreach ($sentResult as $val) {
    $res = $res.'<div>'.$val.'</div>';
  }
  return $res;
}

$hasDataForUser = FALSE;
$userData = array();
if (array_key_exists('user_id', $_SESSION) AND $_SESSION['user_id']!="") {
    $query = "SELECT ID, name, phone, address, mail FROM users WHERE ID=?s LIMIT 1";
		$userData=$go->getRow($query,$_SESSION['user_id']);
    //$userData=$go->getAll($q, $_SESSION['user_id']);
    //var_dump($userData);
		if ($userData!='') {
      //var_dump($userData);
      $hasDataForUser = TRUE;
    }
}
if (isset($orderData) AND is_array($orderData) AND count($orderData)>0) {
  $hasDataForUser = TRUE;
  $userData['name'] = setinputsw('name', $orderData, $userData);
  $userData['phone'] = setinputsw('phone', $orderData, $userData);
  $userData['mail'] = setinputsw('mail', $orderData, $userData);
}
//var_dump(CheckInputFieldHasError('phone', $ErrorFields));
/*
	$orderData['name']=$_POST['oFIO'];
	$orderData['phone']=$_POST['oPhone'];
	$orderData['mail']=$_POST['oMail'];
/**/

?>

<?php if (isset($sentResult) AND is_array($sentResult) AND count($sentResult)>0) {?><div class="form-result"><div class="error"><?php echo wrapErrorsText($sentResult);?></div></div><?php }?>
<div class="cartFormWrapper p-3">

<form id="makeOrder" method="post">
    <div class="row">    
<input required class="cartInput col-12 col-md-4 <?php echo CheckInputFieldHasError('name', $ErrorFields)?>" type="text" form="makeOrder" name="oFIO" <?php if($hasDataForUser AND $userData['name']!='') { echo 'value="'.$userData['name'].'"'; }?> placeholder="Ф.И.О.*">
<input required class="cartInput col-12 col-md-4 <?php echo CheckInputFieldHasError('phone', $ErrorFields)?>" type="text" form="makeOrder" name="oPhone" <?php if($hasDataForUser AND $userData['phone']!='') { echo 'value="'.$userData['phone'].'"'; }?> placeholder="Телефон*">
<input required class="cartInput col-12 col-md-4 <?php echo CheckInputFieldHasError('mail', $ErrorFields)?>" type="text" form="makeOrder" name="oMail" <?php if($hasDataForUser AND $userData['mail']!='') { echo 'value="'.$userData['mail'].'"'; }?> placeholder="Электронная почта*">
</div>

<div class="mngrWrap mt-2 mb-2">
	<div class="btn-group mngrBtnGroup btn-group-justified d-flex flex-row flex-wrap justify-content-center" data-toggle="buttons">
		<?foreach($mngrs as $val)echo($val);?>
	</div>
</div>

<div class="cartFinal">
<div class="g-recaptcha d-flex justify-content-end" data-sitekey="6LeIt0wUAAAAAE5bKMeMw29Gor0AguFj8lOSHpEZ"></div>
<input type="hidden" name="action" value="confirmOrder" form="makeOrder">
<button class="greenGradient cartButton1" type="submit">Завершить</button>
</div>
</form>
</div>
