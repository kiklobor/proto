<?
// работа с прайслистами

// настраиваем класс
require_once(__DIR__.'/cls_prices.php');
$prc=new prices();
$prc->setVat(); // установка НДС, переключател, vatKeys
$pricesD=$prc->defaultPrices(); // дефолтные цены
$prices=$prc->makeWorkingPricesSet(); // массив с рабочими ценами

    
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// фактический пересчет корзины
$_SESSION['cart_cost']=0;
$_SESSION['cart_total']=0;
//var_dump($_SESSION);
if (array_key_exists('cart', $_SESSION) AND isset($_SESSION['cart'])) {
foreach ($_SESSION['cart'] as $key=>$value) {
        $_SESSION['cart'][$key]['cost']=$prices[$prc->mapping[$key]];
        $_SESSION['cart_cost'] += $_SESSION['cart'][$key]['cost'] * $_SESSION['cart'][$key]['count'];
		    $_SESSION['cart_total'] += $_SESSION['cart'][$key]['count'];
}
}
// echo '<br>L1 = '.$prc->l1;
// echo '<br>L2 = '.$prc->l2;
// echo '<br>NVLF = '.$prc->nvlf;
// echo '<br>L2 (nv) = '.$prc->l2;
// echo '<br>';
// echo '<br>Позиций в прайсе РОЗНИЦА: '.count($pricesD[1]);
// echo '<br>Позиций в прайсе МЕЛКИЙ ОПТ С НДС: '.count($pricesD[2]);
// echo '<br>Позиций в прайсе ОПТ С НДС: '.count($pricesD[3]);
// echo '<br>Позиций в прайсе МЕЛКИЙ ОПТ БЕЗ НДС: '.count($pricesD[4]);
// echo '<br>Позиций в прайсе ОПТ БЕЗ НДС: '.count($pricesD[5]);
// echo '<br>Позиций в индивидуальном прайсе: '.count($pricesPrep);
// echo '<br>Позиций в конечном наборе: '.count($prices);
// echo '<br>';
// echo '<br>Корзина по рознице: '.$prc->s1;
// echo '<br>Корзина по мелкому опту: '.$prc->s2;
// echo '<br>'.$cartSumF;
// echo '<br><br>';