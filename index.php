<?php
session_start();

//require("scripts/timer.php");
//Timerw::start();

$paths=parse_url($_SERVER['REQUEST_URI']);

/*if (preg_match('%index%',$paths['path'])) {
    header("Location: /",TRUE,301);
    exit();
    }*/

if ($paths===FALSE) {
  $page = '404';
  $is404 = TRUE;
  $urlArr = Array('404', '404'); // fix array length error
} elseif (isset($_GET['page'])) $page='404';
else {
    $urlArr=explode('/',$paths['path']);
    switch($urlArr[1]) {
        case '':$page='main';break;
        case 'news':$page='news';break;
        case 'articles':$page='articles';break;
        case 'guide':$page='guide';break;
        case 'products':$page='products';break;
        case 'customs':$page='customs';break;
        case 'catalog':$page='catalog';break;
        case 'ccatalog':$page='ccatalog';break;
        case 'contacts':$page='contacts';break;
        case 'delivery':$page='delivery';break;
        case 'mcatalog':$page='mcatalog';break;
        case 'cart':$page='basket';break;
        case 'orderProcess':$page='orderProcess';break;
        case 'orderComplete':$page='orderComplete';break;
        case 'login':$page='login';break;
        case 'registration':$page='registration';break;
        case 'forget':$page='forget';break;
        case 'orders':$page='orders';break;
        case 'pwdchng':$page='pwdchng';break;
        case 'agents':$page='agents';break;
        case 'newagent':$page='newagent';break;
        case 'callback':$page='callback';break;
        case 'callbackSuccess':$page='callbackSuccess';break;
        case 'customSuccess':$page='customSuccess';break;
        case 'search':$page='searchShort';break;
        case 'payments':$page='payments';break; // информация об оплате
        case 'product':$page='product';break;
        case 'sample':$page='sample';break;
        case 'ymarket':$page='ymarket';break;
        case 'about':$page='about';break;
        case 'exit':$page='exit';break;
        case 'confidence':$page='confidence';break;
        case 'cookies-policy':$page='cookies';break;
        case 'payment':$page='payment';break; // сама оплата, взаимодействие с paymaster
        case 'services':$page='services';break;
        case 'service':$page='service';break;
        case 'params':$page='params';break;
		case 'param':$page='param';break;
		case 'update_session':$page='update_session';break;
        default:$page='404';break;
    	/*
    	case("delivery"):require("modules/int_deliveryAndPayment.php");break;
    	case("uses"):require("modules/int_uses.php");break;
    	case("searchFull"):require("modules/int_searchFull.php");break;*/
        }
    }

 //var_dump($page);

// временно наверно. 301 от некорретного формирования
// имеет смысл только для GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  //$paths = parse_url($_SERVER['REQUEST_URI']);
  //$path = $paths['path'];
  //$path = \Utilsw\URL\URL::url($path);
  include_once($_SERVER['DOCUMENT_ROOT'].'/modules/utils/url.php');
  switch ($page) {
    case 'catalog':
       $clean_url = \Utilsw\URL\URL::url($_SERVER['REQUEST_URI']);
       if ($_SERVER['REQUEST_URI'] !== $clean_url) {
         header("Location: ".$clean_url, TRUE, 301);
         exit();
       }
       //без чпу пока в prg_meta через canonical
       if (!array_key_exists('target',$_GET)) {
       }
      break;

    default: break;
  }
}

 //var_dump($page);
// строгое чпу
// имеет смысл только для GET
if (($_SERVER['REQUEST_METHOD'] == 'GET') AND $page!='404' AND $_SERVER['REQUEST_URI']!='') {
// if (($_SERVER['REQUEST_METHOD'] == 'GET') AND $page!='404' AND $_SERVER['REQUEST_URI']!='' AND FALSE) {
  //$paths = parse_url($_SERVER['REQUEST_URI']);
  //$path = $paths['path'];
  //$path = \Utilsw\URL\URL::url($path);
  include_once($_SERVER['DOCUMENT_ROOT'].'/modules/utils/url.php');
  //var_dump($_SERVER['REQUEST_URI']);
  $chkurl = \Utilsw\URL\URL::checkurl($_SERVER['REQUEST_URI']);
  //if (!$chkurl) {
    //$page = '404';
  //}
  // switch ($page) {
  //   case 'catalog':
  //      $clean_url = \Utilsw\URL\URL::checkurl($_SERVER['REQUEST_URI']);
  //      if ($_SERVER['REQUEST_URI'] !== $clean_url) {
  //        header("Location: ".$clean_url, TRUE, 301);
  //        exit();
  //      }
  //      //без чпу пока в prg_meta через canonical
  //      if (!array_key_exists('target',$_GET)) {
  //      }
  //     break;
  //
  //   default: break;
  // }
}

/*
echo '<pre>';
var_dump($page);
var_dump($paths);
var_dump($_SERVER);
echo '</pre>';
/**/


define('RESIZEPATH','files/resize');
define('LROOT', $_SERVER['DOCUMENT_ROOT']);
define('FPATH','files/');
define('FPATHSERVICE', FPATH.'services/');
define('FPATHPARAMS', FPATH.'params/');
define('NOPH','/styles/images/noPhoto.png');
define('MPICS','files/managers/');

/*
if (!isset($_GET['page']) || $_GET['page']=='') {
    $page='main';
    //$canonical='';
    }
else {
    $page=$_GET['page'];
    //if ($page=='main') $canonical='<link rel="canonical" href="http://imige.ru">';
    //else $canonical='';
    }
*/
//$GLOBALS['paymentsOn'];
$GLOBALS['basiclink']=0;
/*тут должна быть проверка, вызывающая окно логина, если незалогиненный пользователь пытается вызывать страницу для залогиненных*/

if (array_key_exists('user_in', $_SESSION) AND $page==='login' && $_SESSION['user_in']) {
	$url='/';
	header('Location: '.$url);
	}

if ($page==='exit') {
	unset($_SESSION['user_mail']);
	unset($_SESSION['user_name']);
	unset($_SESSION['user_id']);
	unset($_SESSION['user_phone']);
	$_SESSION['user_in']=false;
	$url='/';
	header('Location: '.$url);
	exit();
	}

if (!require('scripts'.DIRECTORY_SEPARATOR.'setup.php')) {echo 'Ошибка соединения с БД.';exit;}
else $go=new SafeMySQL();

$telegramClassPath = 'scripts'.DIRECTORY_SEPARATOR.'cls_telegram.php';
if (include_once($telegramClassPath)) $tg=new telegram(false);

//if(!require('scripts/PHPM/PHPMailerAutoload.php')) echo('Ошибка загрузки модуля отправки писем. Могут наблюдаться перебои в доставке уведомлений.');
if(!require('scripts'.DIRECTORY_SEPARATOR.'PHPM6'.DIRECTORY_SEPARATOR.'PHPMailerAutoload.php')) echo('Ошибка загрузки модуля отправки писем. Могут наблюдаться перебои в доставке уведомлений.6');
if(!require('scripts'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'api.php')) echo ('Ошибка подключения API. Некоторые функции могут работать направильно.');
else $api = new api();

//die($page);

/*********** ЧПУ ********************/
include_once(LROOT.'/scripts/prg_catalog_hierarchy.php');
global $category;
$category = new \URL\Category($go);

include_once('scripts'.DIRECTORY_SEPARATOR.'prg_catalog_hierarchy_service.php');
global $categoryservices;
$categoryservices = new \URL\CategoryServices($go);
// var_dump($categoryservices);
// var_dump($categoryservices->catarr);


include_once('scripts'.DIRECTORY_SEPARATOR.'prg_catalog_hierarchy_params.php');
global $categoryparams;
$categoryparams = new \URL\CategoryParams($go);



global $urlArrf;
$urlArrf = array_filter($urlArr);
global $isSemanticUrl;
$isSemanticUrl = FALSE;
switch($urlArr[1]) {

	

	
    case 'catalog':
      if (!array_key_exists('target',$_GET)) {
        // Похоже на чпу
        //die('4pu');

        $_GET['target'] = 'products';

        /*
        echo '<pre>';
        var_dump($urlArrf);
        echo '</pre>';
        /**/

        if (count($urlArrf)>3) {
          $_GET['level'] = 2;
          if ($category->checkurlpath($urlArrf)) {
          //if (FALSE) {
            $_GET['id'] = $category->getcatid($urlArrf);
            /*
            echo '<pre>';
            var_dump($_GET['id']);
            echo '</pre>';
            /**/
          } else {
            $_GET['id'] = -1;
          }
          /*
          echo '<pre>';
          var_dump($category::$catarr);
          echo '</pre>';
          /**/
        } elseif (count($urlArrf)>2) {
          // subgroups
          //die('subgroups');
          $_GET['level'] = 2;
          //может работать и упращённый, но чтобы не чудили, пробуем делать 2 уровня обязательно(возможно переработается при 404)
          //$query = "SELECT sg.id FROM subgroups sg WHERE sg.url='".$urlArrf[3]."'";
          //$group = $go->getRow($query);
          //$group['uID'];
          if ($category->checkurlpath($urlArrf)) {
            $query = "SELECT sg.id FROM subgroups sg WHERE sg.url='".$urlArrf[3]."' "
                      ." AND sg.parent IN (SELECT g.uID FROM groups g WHERE g.url='".$urlArrf[2]."')";  //parent
            $group = $go->getRow($query);
            if (is_null($group) OR $group===FALSE) {
             //т.к. не существует
             $_GET['id'] = -1;
            } else $_GET['id'] = $group['id'];
          } else {
            // die('not subgroups');
            $_GET['id'] = -1;
          }
        } elseif (count($urlArrf)>1) {
          // groups
          // die('groups');
          $_GET['level'] = 1;

          if ($category->checkurlpath($urlArrf)) {
            $query = "SELECT g.id FROM groups g WHERE g.url='".$urlArrf[2]."'";
            $group = $go->getRow($query);
            if (is_null($group) OR $group===FALSE) {
             //т.к. не существует
             $_GET['id'] = -1;
            } else $_GET['id'] = $group['id'];
          } else {
            // die('not group');
            $_GET['id'] = -1;
          }
        } elseif (count($urlArrf)==1) {
          parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$urlQuery);
          if (array_key_exists('filters', $urlQuery)) {
            //установлен фильтр значит страница отображения каталог с товаром
          } else {
            // category list
            $page = 'mcatalog';
          }
        }
        $isSemanticUrl = TRUE;
      }
      break;
    case 'mcatalog':
      break;

	   case 'params':
      if (count($urlArrf)>1) {
        $page = 'params';
      } else {
        $page = 'mparams';
      }
      $isSemanticUrl = TRUE;
      break;


    case 'services':
      if (count($urlArrf)>1) {
        $page = 'services';
      } else {
        $page = 'mservices';
      }
      $isSemanticUrl = TRUE;
      break;
	  
	  


    default: break;
}

// die($page);
/*********** 404 ********************/
global $is404;
$is404 = FALSE;
//var_dump($urlArr);
switch($urlArr[1]) {
    case '':break;
    case 'news':
     if (count($urlArrf)>2) {
     //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
     }
      if (isset($urlArr[2]) AND $urlArr[2]!=='') {
        //var_dump($urlArr[2]);
        //die('111');
        $newsID=(integer)$urlArr[2];
        $query='SELECT * FROM news WHERE ID=?i';
        $news=$go->getRow($query,$newsID);
        if (is_null($news) OR $news===FALSE) {  //OR $group['url'] != '') {
          $is404 = TRUE;
        }
      } elseif (isset($urlArr[2]) AND $urlArr[2]==='') {
        //
      }
    break;
    case 'articles':
     if (count($urlArrf)>2) {
     //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
     }
      if (isset($urlArr[2]) AND $urlArr[2]!=='') {
        //var_dump($urlArr[2]);
        //die('111');
        $articlesID=(integer)$urlArr[2];
        $query='SELECT * FROM articles WHERE ID=?i';
        $articles=$go->getRow($query,$articlesID);
        if (is_null($articles) OR $articles===FALSE) {  //OR $group['url'] != '') {
          $is404 = TRUE;
        }
      } elseif (isset($urlArr[2]) AND $urlArr[2]==='') {
        //
      }
    break;
    case 'guide':
     if (count($urlArrf)>2) {
     //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
     }
      if (isset($urlArr[2]) AND $urlArr[2]!=='') {
        //var_dump($urlArr[2]);
        //die('111');
        $guideID=(integer)$urlArr[2];
        $query='SELECT * FROM guide WHERE ID=?i';
        $guide=$go->getRow($query,$guideID);
        if (is_null($guide) OR $guide===FALSE) {  //OR $group['url'] != '') {
          $is404 = TRUE;
        }
      } elseif (isset($urlArr[2]) AND $urlArr[2]==='') {
        //
      }
    break;
    case 'products':
     //хрень какая-то
     $is404 = TRUE;
    break;
    case 'customs':
      if (isset($urlArr[2]) AND $urlArr[2]!=='') {
       $is404 = TRUE;
      }
    break;
    case 'catalog':
      /*
     if (count($urlArrf)>3) {
     //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
     }
     /**/
     /*
     echo '<pre>';
     var_dump($is404);
     echo '</pre>';
     /**/

     if (isset($_GET['id']) && $_GET['target'] == 'products' && isset($_GET['level'])) {
      if ($_GET['level'] == 1) {
        $query = 'SELECT g.url FROM groups g WHERE g.id=' . (int)$_GET['id'];
        $group = $go->getRow($query);
        if (is_null($group) OR $group===FALSE) {  //OR $group['url'] != '') {
          $is404 = TRUE;
        }
      } elseif ($_GET['level'] == 2) {
        if (count($urlArrf)>3) {
          if ($category->checkurlpath($urlArrf)) {
          } else {
            // die('error url');
            $is404 = TRUE;
          }
        } else {
          $query = 'SELECT sg.url sgurl, g.url gurl  FROM subgroups sg, groups g WHERE sg.id='.(int)$_GET['id'].' AND sg.parent=g.uid';
          $group = $go->getRow($query);
          if (is_null($group) OR $group===FALSE) { //AND $group['sgurl'] != '' AND $group['gurl'] != '') {
            $is404 = TRUE;
          }
        }
      }
     }

    break;
    case 'ccatalog':break;

    case 'cookies-policy'://break;
    case 'confidence'://break;
    case 'payments'://break; // информация об оплате
    case 'about'://break;
    case 'contacts'://break;
    case 'delivery':
      //var_dump($paths);
      if ((isset($urlArr[2]) AND $urlArr[2]!=='') OR (isset($paths['query']))) { //AND $paths['query']!=='')) {
       $is404 = TRUE;
      }
    break;

    case 'mcatalog':
     if (isset($_GET['id']) && $_GET['target'] == 'products') {
      $query='SELECT * FROM groups WHERE active=1 AND ID='.$_GET['id'];
      // это в int_mcatalog тупусть $query2="SELECT g.*,sg.* FROM groups g,subgroups sg WHERE g.uID=sg.parent AND g.active=1 AND sg.active=1 AND g.ID=".$_GET['id'];
      $group = $go->getRow($query);
      if (is_null($group) OR $group===FALSE) {  //OR $group['url'] != '') {
        $is404 = TRUE;
      }
     }
    break;

    //case 'agents':break;
    //case 'newagent':break;
    //case 'search':break;
    case 'product':
      if (count($urlArrf)>2) {
      //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
      }

      $productUrl=$urlArr[2];
      $query="SELECT p.* FROM products p, subgroups sg WHERE p.parent=sg.uID AND p.url=?s AND p.active=1 LIMIT 1";
      $product=$go->getRow($query,$productUrl);
      if (is_null($product) OR $product===FALSE) {  //OR $group['url'] != '') {
        $is404 = TRUE;
        break;
      }

      //маразм, но на всякий случай
      if (count($category->getcatbyuid($product['parent'])) == 0) {
        $is404 = TRUE;
        break;
      }
      /*
      $productUrl=$urlArr[2];
      $query="SELECT p.* FROM products p,groups g,subgroups sg WHERE p.parent=sg.uID AND sg.parent=g.uID AND g.ind=0 AND p.url=?s AND p.active=1 LIMIT 1";
      $product=$go->getRow($query,$productUrl);
      if (is_null($product) OR $product===FALSE) {  //OR $group['url'] != '') {
        $is404 = TRUE;
      }
      /**/
    break;
    case 'services':
      if (!($categoryservices->checkurlpath($urlArrf)) AND ($page == 'services')) {
        $is404 = TRUE;
      }
      break;
    case 'service':
      if (count($urlArrf)>2) {
      //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
      }

      $serviceUrl=$urlArr[2];
      $query="SELECT s.* FROM services s WHERE s.url=?s AND s.active=1 LIMIT 1";
      $service=$go->getRow($query,$serviceUrl);
      if (is_null($service) OR $service===FALSE) {  //OR $group['url'] != '') {
        $is404 = TRUE;
        break;
      }

      //маразм, но на всякий случай
      if (count($categoryservices->getcatbyuid($service['parent'])) == 0) {
        $is404 = TRUE;
        break;
      }
      //die('service');
      // var_dump($is404);
      // die($is404);
    break;

    case 'params':
      if (!($categoryparams->checkurlpath($urlArrf)) AND ($page == 'params')) {
        $is404 = TRUE;
      }
      break;
    case 'param':
      if (count($urlArrf)>2) {
      //if (count($urlArr)>4) {
       $is404 = TRUE;
       break;
      }

      $serviceUrl=$urlArr[2];
      $query="SELECT s.* FROM params s WHERE s.url=?s AND s.active=1 LIMIT 1";
      $service=$go->getRow($query,$serviceUrl);
      if (is_null($service) OR $service===FALSE) {  //OR $group['url'] != '') {
        $is404 = TRUE;
        break;
      }

      //маразм, но на всякий случай
      if (count($categoryparams->getcatbyuid($service['parent'])) == 0) {
        $is404 = TRUE;
        break;
      }
      //die('service');
      // var_dump($is404);
      // die($is404);
    break;
	
	
    //case 'sample':break;

    default:break;
}

// var_dump($page);
// die($service);

if ($is404) {
 header("HTTP/1.1 404 Not Found");
 $page = '404';
}
// die($page);
require('scripts/prg_formHandlers.php'); //обработчики всех форм
include('scripts/prg_meta.php'); //потом надо будет распихать сбор меты в разные скрипты
require('scripts/prg_pricelists.php'); //определение и установка прайслиста
//die($page);
/*новые кукисы
если юзер залогинен - достаем корзину по user_id, если она есть
если не залогинен - смотрим, есть ли у него кука с токеном*/

if (isset($_SESSION['user_id'])) {
    $query='SELECT content FROM carts WHERE userID=?s AND token="" ORDER BY lastUpdate DESC LIMIT 1';
    $cart=$go->getOne($query,$_SESSION['user_id']);
    if ($cart!='') recountCart($cart);
    }
elseif (isset($_COOKIE['cartToken'])) {
    $query='SELECT content FROM carts WHERE token=?s AND userID=0 ORDER BY lastUpdate DESC LIMIT 1';
    $cart=$go->getOne($query,$_COOKIE['cartToken']);
    if ($cart!='') recountCart($cart);
    }
//конец кукисов

switch($page) {
	case("param"):
    include_once(LROOT.'/modules/utils/image.php');
    require("scripts/prg_param.php");
    break;
	case("product"):
   include_once(LROOT.'/modules/utils/image.php');
   require("scripts/prg_product.php");
   break;
	case("sample"):require("scripts/prg_individualSampleCard.php");break;
	case("ymarket"):require("scripts/prg_ymarket.php");break;
	case("news"):require("scripts/prg_news.php");break;
	case("articles"):require("scripts/prg_articles.php");break;
  case("guide"):require("scripts/prg_guide.php");break;
	case("404"):header("HTTP/1.1 404 Not Found");break;
  case("catalog"):
  case("params"):
   include_once(LROOT.'/modules/utils/url.php');
   break;
  case("services"):
   include_once(LROOT.'/modules/utils/url.php');
   break;
   case("service"):
    include_once(LROOT.'/modules/utils/image.php');
    require("scripts/prg_service.php");
    break;

}
 //var_dump($page);

include('scripts/prg_lastModified.php');
//$name=htmlspecialchars(trim(strip_tags(stripslashes($_POST['name'])))); для проверки

global $cartCount;
//последние приготовления
$cartCount=(!empty($_SESSION['cart']) && count($_SESSION['cart'])>0) ? $_SESSION['cart_count'] : 0;

if (array_key_exists('user_in', $_SESSION) AND $_SESSION['user_in']) $userBlock='<div class="userBar p-1">'.$_SESSION['user_name'].'<div class="userMenu"><a class="userMenuLink" href="/orders">Заказы</a><a class="userMenuLink" href="/pwdchng">Изменить пароль</a><a class="userMenuLink" href="/exit">Выход</a></div></div>';
else $userBlock='<a href="/login"><div class="entry p-1">Вход</div></a>';

if (!isset($inSearch)) $inSearch = "";

global $canonical;

// var_dump($is404);
// die($is404);

//Старт буферизации для отложенного вывода
ob_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?=$meta?>
<?=$canonical?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WWHB3JH');</script>
<!-- End Google Tag Manager -->

<link rel="shortcut icon" href="/favicon.ico">
<!-- 20201202 4.3.1  integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous" --><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<script type="text/javascript" src="https://yastatic.net/jquery/2.2.3/jquery.min.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>

<link  href="/styles/fotorama.css" rel="stylesheet">
<script src="/scripts/fotorama.js"></script>
<link rel="stylesheet" href="/styles/owl.carousel.min.css">
<link rel="stylesheet" href="/styles/owl.theme.default.min.css">
<script src="/scripts/owl.carousel.min.js"></script>

<!-- integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous" --><script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<!-- 20201202 4.3.1  integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous" --><script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127638054-1"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'UA-127638054-1');
</script>
-->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-194280260-1">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-194280260-1');
</script>

<meta name="google-site-verification" content="LrAHLloxP-YUnAQv1lQqALr170Y9BBzNGZQw6W-Qcyo" />
<meta name="yandex-verification" content="cd557ceb705f6c82" />

<link href="/styles/main.css" rel="stylesheet" type="text/css">
<script src="//code.jivosite.com/widget.js" jv-id="7por5QPLLn" async></script>

</head>

<body class="w-100 h-100">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WWHB3JH"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="mainWrapper">
<div class="contentWrapper">



<div class="cap position-sticky fixed-top p-1 d-print-none">
    <div class="header">

    	<div class="logoContainer">
    	    <a href="/" class=""><div class="logo"></div></a>
    	</div>

    	<div class="navContainer">
    	    <div class="burger"><span></span><span></span><span></span></div>
        	<div class="nav">
                <a href="/news"><div class="p-1">Новости</div></a>
                <a href="/articles"><div class="p-1">Статьи</div></a>
 <!--               <a href="/guide"><div class="p-1">Путеводитель кадровика</div></a> -->
                <a href="/payments"><div class="p-1">Оплата</div></a>
                <a href="/delivery"><div class="p-1">Доставка</div></a>
        	    <a href="/contacts"><div class="p-1">Контакты</div></a>
                <a href="/about"><div class="p-1">О нас</div></a>
        		<?=$userBlock?>
        	</div>
    	</div>

      <!--cartlazyload-->

        <div class="phonesContainer">
            Тел: <a href="tel:88005558054">8 (800) 555-80-54</a>, <a href="tel:+74997071791">+7 (499) 707-17-91</a>
        </div>

        <div class="searchContainer">
	      <form id="search" method="get" action="/search">
	          <input type="text" form="search" name="what" value="<?=$inSearch?>" class="searchInput" placeholder='Поиск, например "папка адресная"'>
	          <button type="submit" form="search" class="searchButton">&nbsp;</button>
	      </form>
    	</div>
    </div>
</div>



<!-- <h3><p align="center"><strong><font color='red'>"ВНИМАНИЕ!!! В связи с заменой оборудования, сегодня (04.10.2022 г.) у нас не работают телефоны до 14:00 (МСК). Просьба обращаться по электронной почте. Приносим извинения за неудобство."</font></strong></p></h3> -->




<div class="container main-container d-flex flex-row flex-wrap">
<?
switch($page) {
	case("main"):
    include_once(LROOT.'/modules/utils/image.php');
    require("modules/int_main.php");
    break;
	case("about"):require("modules/int_about.php");break;
	case("news"):require("modules/int_news.php");break;
	case("articles"):require("modules/int_articles.php");break;
  case("guide"):require("modules/int_guide.php");break;
	case("products"):require("modules/int_products.php");break;
	case("uses"):require("modules/int_uses.php");break;
	case("customs"):require("modules/int_customs.php");break;
	case("catalog"):
    include_once(LROOT.'/modules/utils/image.php');
    //die('1111111111111111');
    require("modules/int_catalog.php");
    break;
	case("ccatalog"):require("modules/int_customCatalog.php");break;
	case("mcatalog"):require("modules/int_mcalatog.php");break;
	case("basket"):
    include_once(LROOT.'/modules/utils/image.php');
    require("modules/int_basket.php");
    break;
	case("orderProcess"):require("modules/int_orderProcess.php");break;
	case("orderComplete"):require("modules/int_orderComplete.php");break;
	case("login"):require("modules/int_login.php");break;
	case("registration"):require("modules/int_registration.php");break;
	case("forget"):require("modules/int_forget.php");break;
	case("orders"):require("modules/int_myOrders.php");break;
	case("pwdchng"):require("modules/int_pwdchng.php");break;
	case("agents"):require("modules/int_myAgents.php");break;
	case("newagent"):require("modules/int_myAgentsCreate.php");break;
	case("callback"):require("modules/int_callback.php");break;
	case("callbackSuccess"):require("modules/int_callbackSuccess.php");break;
	case("customSuccess"):require("modules/int_customSuccess.php");break;
	case("searchShort"):
    include_once(LROOT.'/modules/utils/image.php');
    require("modules/int_searchShort.php");
    break;
	case("searchFull"):require("modules/int_searchFull.php");break;
	case("product"):require("modules/int_product.php");break;
	case("sample"):require("modules/int_individualSampleCard.php");break;
	case("payment"):require("modules/int_paymentsProcess.php");break;
	case("404");
	case("confidence");
	case("cookies");
	case("payments");
	case("contacts");
	case("delivery"):require("modules/int_static.php");break;
	case 'params':
	//die('1111111111111111');
	include_once(LROOT.'/modules/utils/image.php');
        require('modules/int_params.php');
        break;
    case 'mparams':
        require('modules/int_mparams.php');
        break;
  case("mservices"): require("modules/int_mservices.php");break;
  case("services"):
    //die('1111111111111111');
    include_once(LROOT.'/modules/utils/image.php');
    require("modules/int_services.php");
    break;
  case("service"):require("modules/int_service.php"); break;
  case("param"):require("modules/int_par.php"); break;
  case("update_session"):require("modules/update_session.php"); break;

	default:require("modules/int_main.php");break;
	}
?>
</div>
</div>

<footer>
    © 2012-<?=date('Y');?> Имидж<br>
    Тел.: <a href="tel:88005558054">8 (800) 555-80-54</a>, <a href="tel:+74997071791">+7 (499) 707-17-91</a><br>
    E-mail: <span class="contactMail"></span><br>
    Московская область, г. Наро-Фоминск, ул. Ленина, д. 28, офис 3<br>
</footer>

<div id="settings" style="display:none;" discountstatus="<?=$prc->discountLevel?>" basiclink="<?=$GLOBALS['basiclink']?>">
</div>
<script>
$(document).ready(function (){
	part1 = 'imige';
	part2 = 'imige.ru';
	final = part1+'@'+part2;
	mailto = 'mailto:'+final;
	$('.contactMail').html('<a href="'+mailto+'">'+final+'</a>');

	var productid;

	$('.owl-carousel.main').owlCarousel({
    loop:true,
    margin:0,nav:true,autoplay:true,autoplayTimeout:3000,autoplayHoverPause:true,
    responsiveClass:true,
    responsive:{
        0:{items:1},
        300:{items:2},
        600:{items:3},
        1000:{items:5}
        }
  });

	$('.owl-carousel.similar').owlCarousel({
    loop:true,
    margin:0,nav:true,autoplay:true,autoplayTimeout:3000,autoplayHoverPause:true,
    responsiveClass:true,
    responsive:{
        0:{items:1},
        300:{items:2},
        600:{items:3},
        1000:{items:5}
        }
  });

	$('.owl-carousel').owlCarousel({
    loop:true,
    margin:0,nav:false,autoplay:true,autoplayTimeout:3000,autoplayHoverPause:true,
    responsiveClass:true,
    responsive:{
        0:{items:1},
        300:{items:2},
        600:{items:3},
        1000:{items:5}
        }
  });

    $('.burger').click(function (){
        $('.nav').toggleClass('opened');
        $('.burger').toggleClass('opened');
        });

	$('.buy').click(function (){
		productid=$(this).attr('product');
		button=$(this);

    count = 1;
    inpCount = jQuery('input[product='+productid+']');
    if (inpCount.length > 0) {
      count = inpCount.val();
    }

    animateBuy(button);

		dataToSent='action=add&productid='+productid+'&count='+count;
		$.ajax({
			type: "POST",
			url: "/scripts/api/basket.php",
			dataType: 'json',
			cache: false,
			data: dataToSent,
			success: function(result){
				//console.log(result.answer);
				button.html('В корзине!');
        button.data('prdincart', 1);
				$('#cartcount').html(result.cart_count);
        ne = 'not-empty';
        cb = jQuery('.cartContainer');
        if (cb.hasClass(ne)) {
        } else {
          cb.addClass(ne);
        }
        //animateBuy(button);
				// console.log(result.discountStatus);
				// console.log($('#settings').attr('discountstatus'));
				if (result.discountStatus!=$('#settings').attr('discountstatus')) location.reload();
				}
			});
		});

	$('#erase').click(function (){
	    if (confirm('Очистить корзину?')) {
    		dataToSent='action=erase';
    		$.ajax({
    			type: "POST",
    			url: "/scripts/api/basket.php",
    			dataType: 'json',
    			cache: false,
    			data: dataToSent,
    			success: function(result){
    				//alert(result.answer);
    				location.reload();
    				}
    			});
	        }
		});

	$('.deleteFromCart').click(function (){
	    if (confirm('Удалить товар из корзины?')) {
    		productid=$(this).attr('product');
    		dataToSent='action=delete&productid='+productid;
    		$.ajax({
    			type: "POST",
    			url: "/scripts/api/basket.php",
    			dataType: 'json',
    			cache: false,
    			data: dataToSent,
    			success: function(result){
    				$('.cartRow[product='+productid+']').hide(300);
    				$('#cartcount').html(result.cart_count);
    				$('#cartcount2').html(result.cart_count);
    				$('#carttotal').html(result.cart_total);
    				$('#cartcost').html(result.cart_cost);
    				//if (result.cart_count=='0') location.reload();
    				location.reload();
    				}
    			});
	        }
		});

	$('button.cartProductCount').click(function (){
		productid=$(this).attr('product');
		action=Number($(this).attr('action'));
		if (action==1) newValue=Number($('input[product='+productid+']').val())+1;
		else {
			newValue=Number($('input[product='+productid+']').val());
			if (newValue>1) newValue--;
			else newValue=1;
			}
		$('input[product='+productid+']').val(newValue);
		dataToSent='action=update&productid='+productid+'&count='+newValue;
		$.ajax({
			type: "POST",
			url: "/scripts/api/basket.php",
			dataType: 'json',
			cache: false,
			data: dataToSent,
			success: function(result){
				$('#cartcount').html(result.cart_count);
				$('#cartcount2').html(result.cart_count);
				$('#carttotal').html(result.cart_total);
				$('#cartcost').html(result.cart_cost);
				Object.keys(result.cart).forEach(key => { $('.cart-price[data-id='+key+']').html(result.cart[key]['cost']+' руб/шт'); });
				//console.log(result.answer);
				}
			});
		});

	$('input.cartProductCount').change(function (){
		if ($.isNumeric($(this).val()) && $(this).val()>0) newValue=$(this).val();
		else {
			newValue=1;
			$(this).val(1);
			}

		productid=$(this).attr('product');
		dataToSent='action=update&productid='+productid+'&count='+newValue;
		$.ajax({
			type: "POST",
			url: "/scripts/api/basket.php",
			dataType: 'json',
			cache: false,
			data: dataToSent,
			success: function(result){
				$('#cartcount').html(result.cart_count);
				$('#cartcount2').html(result.cart_count);
				$('#carttotal').html(result.cart_total);
				$('#cartcost').html(result.cart_cost);
				Object.keys(result.cart).forEach(key => { $('.cart-price[data-id='+key+']').html(result.cart[key]['cost']+' руб/шт'); });
				}
			});
		});

    $('button.ProductCount').click(function (){
  		productid=$(this).attr('product');
      button = jQuery('button.buy[product='+productid+']');
  		action=Number($(this).attr('action'));
  		if (action==1) newValue=Number($('input[product='+productid+']').val())+1;
  		else {
  			newValue=Number($('input[product='+productid+']').val());
  			if (newValue>1) newValue--;
  			else newValue=1;
  			}
  		$('input[product='+productid+']').val(newValue);
      //$('input[product='+productid+']').change();
      if (button.data('prdincart')==1) {
        setprdincart (productid, newValue);
      }
  	});

    $('input.ProductCount').change(function (){

        productid = $(this).attr('product');
        button = jQuery('button.buy[product='+productid+']');

    		if ($.isNumeric($(this).val()) && $(this).val()>0) newValue=$(this).val();
    		else {
    			newValue=1;
    			$(this).val(1);
    			}

        count = Number($(this).val());
        if (button.data('prdincart')==1) {
          setprdincart(productid,count);
        }
    });

  function setprdincart (productid, count) {
    button = jQuery('button.buy[product='+productid+']');

    if (button.data('prdincart')==0) {
      animateBuy(button);
    } else {
      if (!jQuery('.cartContainer').hasClass('animate add')) {
        jQuery('.cartContainer').addClass('animate add');
        setTimeout(function () {
          jQuery('.cartContainer').removeClass('animate add');
        }, 500);
      }
    }

    dataToSent='action=add&productid='+productid+'&count='+count;
    $.ajax({
      type: "POST",
      url: "/scripts/api/basket.php",
      dataType: 'json',
      cache: false,
      data: dataToSent,
      success: function(result){
        //console.log(result.answer);
        if (button.data('prdincart')==0) {
          button.html('В корзине!');
          button.data('prdincart', 1);
        }
        $('#cartcount').html(result.cart_count);
        ne = 'not-empty';
        cb = jQuery('.cartContainer');
        if (cb.hasClass(ne)) {
        } else {
          cb.addClass(ne);
        }
        //animateBuy(button);
        // console.log(result.discountStatus);
        // console.log($('#settings').attr('discountstatus'));
        if (result.discountStatus!=$('#settings').attr('discountstatus')) location.reload();
        }
      });
  }

	jQuery('.cartButton2').click(function(){
		jQuery('.cartFormWrapper').slideDown(300);
    jQuery(this).hide();
		});
  if (jQuery('.form-result .error').length>0) {
    jQuery('.cartButton2').click();
    jQuery('.cartFormWrapper').slideDown(300);
  }

  jQuery('.cartFormWrapper #makeOrder').submit(function () {

    jQuery(this).find('.cartButton1').prop('disabled', true).addClass('wait');
  });

	$('.goback').click(function (){
		window.history.back();
		});

  function animateBuy(btnin) {

    var btn = $(btnin);
    var flyimg = $(btn).closest('.productCell').find('.imgWrap img');
    var param = {'width':0,'height':0};

    if (flyimg.length>0) {
      param['width'] = flyimg.width();
      param['height'] = flyimg.height();
      flyToElement($(flyimg), $('.cartContainer'), param);

      // Автопрокрутка
      //$('html, body').animate({
      //  'scrollTop' : $("body").position().top
      //});
    } else {
      //flyimg = $(btn).closest('.productCell').find('.imgWrap img');
      $(btn).append('<div class="animtocart"></div>');
      flyimg = jQuery($(btn).find('.animtocart'));
      flyimg.css({
        'position' : 'absolute',
        'background' : '#339933',
        'width' :  '25px',
        'height' : '25px',
        'border-radius' : '50%',
        'z-index' : '9999999999',
        'right' : 0,
        'top' : 0,
      });
      param['width'] = 25;
      param['height'] = 25;
      flyToElement(flyimg, jQuery('.cartContainer'), param);
      flyimg.remove();
    }
  }
  function flyToElement(flyer, flyingTo, param) {
  	//var $func = $(this);
  	var divider = 3;
  	var flyerClone = $(flyer).clone();
  	$(flyerClone).css({position: 'absolute', top: $(flyer).offset().top + "px", left: $(flyer).offset().left + "px", opacity: 1, 'z-index': 'calc(1 + var(--basetopzindex))', 'width':param['width']+'px', 'height':param['height']+'px'});
  	$('body').append($(flyerClone));
  	var gotoX = $(flyingTo).offset().left + ($(flyingTo).width() / 2) - ($(flyer).width()/divider)/2;
  	var gotoY = $(flyingTo).offset().top + ($(flyingTo).height() / 2) - ($(flyer).height()/divider)/2;

    $(flyingTo).addClass('animate add');

    $(flyerClone).animate({
    		opacity: 0.4,
    		left: gotoX,
    		top: gotoY,
    		width: $(flyer).width()/divider,
    		height: $(flyer).height()/divider
      },
      700,
    	function () {
        /*
    		$(flyingTo).fadeOut('fast', function () {
    			$(flyingTo).fadeIn('fast', function () {
    				$(flyerClone).fadeOut('fast', function () {
    					$(flyerClone).remove();
    				});
    			});
    		});
        */
        //$(flyerClone).remove();
        //$(flyingTo).addClass('animate add');
        setTimeout(function () {
          $(flyingTo).removeClass('animate add');
          $(flyerClone).remove();
        }, 500);
     }
    );
  }

});
</script>
<script type="text/javascript" >
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter48177671 = new Ya.Metrika({
                    id:48177671,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true,
                    ut:"noindex"
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/48177671?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(67598599, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/67598599" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<?php //var_dump(Timerw::finish());?>
</body>
</html><?php
//Функционал отложенных функций
$output = ob_get_contents();
ob_end_clean(); 	//очищаем буфер

// Актуальная корзина
//
$baskethtml = '<div class="cartContainer';
if ($cartCount>0) $baskethtml .= " not-empty";
$baskethtml .= '"><a href="/cart">';
$baskethtml .= "Корзина";
$baskethtml .= ' <b id="cartcount">';
$baskethtml .= $cartCount;
$baskethtml .= '</b></a></div>';

//Общее с заделом, что может быть больше, чем корзина
$patterns = array(
    "/<!--cartlazyload-->/"
  );
$replacements = array(
    $baskethtml
  );
$output = preg_replace($patterns, $replacements, $output);

echo $output;
