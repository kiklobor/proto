<?php

session_start();

$result=array();

require_once('../setup.php');
require_once('../cls_prices.php');
global $go;
$go=new prices(); //подключаемся к БД
$go->setVat(); // установка НДС, переключатель, vatKeys
$go->defaultPrices(); // дефолтные цены
// $go->makeWorkingPricesSet(); // массив с рабочими ценами создадим позже

if (isset($_POST['productid'])) $product_id=(integer) $_POST['productid'];
$count = 1;
if (isset($_POST['count'])) $count=(integer) $_POST['count'];

if (isset($_POST['action'])) {
	switch($_POST['action']) {
		case 'add': $result['answer']=addToCart($product_id, $count);break;
		case 'erase': $result['answer']=eraseCart();break;
		case 'update':$result['answer']=updateProductCount($product_id,$count);break;
		case 'delete': $result['answer']=deletePosition($product_id);break;
		}

	updateCart();

	$result['cart']=$_SESSION['cart'];
	$result['cart_count']=$_SESSION['cart_count'];
	$result['cart_cost']=$_SESSION['cart_cost'];
	$result['cart_total']=$_SESSION['cart_total'];
  $result['discountStatus']=$go->discountLevel;
	$result=json_encode($result);
	echo($result);
	unset($_POST);
	}

function addToCart($product_id, $count=1) {

	global $go;
  //$_SESSION['cart']='1111';
  //var_dump($product_id);
  //$_SESSION['cart'][$product_id]['count']=1;
  //$_SESSION['cart']['prod']=2;
  //$_SESSION['cart'][$product_id]=3;
  //var_dump($_SESSION);
  //die();

  if (array_key_exists('cart', $_SESSION) AND is_string($_SESSION['cart'])) {
    unset($_SESSION['cart']);
    $_SESSION['cart'] = array();
  }

	//проверяем, не был ли добавлен товар в корзину ранее:
	if (!empty($_SESSION['cart'][$product_id])) {
		//$_SESSION['cart'][$product_id]['count'] = $_SESSION['cart'][$product_id]['count']+$count; //если товар уже есть, увеличиваем количество
		$_SESSION['cart'][$product_id]['count'] = $count;
	}	else {
    $_SESSION['cart'][$product_id]['count'] = $count;
  }

  //var_dump($_SESSION);
  //die();
// 	$price=(isset($go->prices[$go->mapping[$product_id]])) ? $go->prices[$go->mapping[$product_id]] : 0;
// 	$_SESSION['cart'][$product_id]['cost']=$price;
  if (array_key_exists('cost', $_SESSION['cart'][$product_id]))
    return 'Товар ID '.$product_id.' добавлен в корзину по цене '.$_SESSION['cart'][$product_id]['cost'];
  else
    return 'Товар ID '.$product_id.' добавлен в корзину по цене ';
}

//пересчет товаров и стоимости
function updateCart() {
    global $go;
    $go->makeWorkingPricesSet();
	//количество НАИМЕНОВАНИЙ в корзине считаем как количество элементов в массиве
  if (array_key_exists('cart', $_SESSION))
	  $_SESSION['cart_count']=count($_SESSION['cart']);
  else
    $_SESSION['cart_count']=0;
	//сначала обнулим стоимость:
	$_SESSION['cart_cost']=0;
	$_SESSION['cart_total']=0;
	//стоимость корзины (перемножаем цены на количество и складываем):
  if (array_key_exists('cart', $_SESSION)) {
  	foreach ($_SESSION['cart'] as $key=>$value) {
  	    $_SESSION['cart'][$key]['cost']=$go->prices[$go->mapping[$key]];
  		$_SESSION['cart_cost'] += $_SESSION['cart'][$key]['cost'] * $_SESSION['cart'][$key]['count'];
  		$_SESSION['cart_total'] += $_SESSION['cart'][$key]['count'];
  	}
  }
	recordCart();
	}

//изменение количества
function updateProductCount($product_id,$count) {
	$_SESSION['cart'][$product_id]['count']=$count;
	return 'Количество изменено!';
	}

//удаление позиции
function deletePosition($product_id) {
	unset($_SESSION['cart'][$product_id]);
	return 'Позиция удалена';
	}

//очистка корзины
function eraseCart(){
	unset($_SESSION['cart']);
	return 'Корзина очищена!';
	}

function recordCart(){
    global $go;
    //$content=serialize($_SESSION['cart']); //возможно тут происходит повторная ненужная сериализация
    //if (unserialize($_SESSION['cart'])==false) {
    if (array_key_exists('cart', $_SESSION)) {
      if (is_array($_SESSION['cart']))
        $content=serialize($_SESSION['cart']);
      else
        $content=$_SESSION['cart'];
    } else {
      $content="";
    }

    /*если юзер залогинен, пишем корзину под userID
    если нет, проверяем куку с ключом к корзине
    если куки нет, создаем новую корзину и куку для нее*/

    if (isset($_SESSION['user_id'])) {
        $token=$_SESSION['user_id'];
        $query='INSERT INTO `carts`(`userID`,`content`) VALUES (?s,?s) ON DUPLICATE KEY UPDATE content=?s';
        }
    else {
        if (isset($_COOKIE['cartToken'])) $token=$_COOKIE['cartToken'];
        else {
            $token=uniqid();
            setcookie('cartToken',$token,time()+2592000,'/');
            }
        $query='INSERT INTO `carts`(`token`,`content`) VALUES (?s,?s) ON DUPLICATE KEY UPDATE content=?s';
        }

    $go->query($query,$token,$content,$content);
    }
