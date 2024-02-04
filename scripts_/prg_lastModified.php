<?
$lastModified=0;

if ($page=='product' || $page=='sample') {
    $lastModified=strtotime($product['lastModified']);
    }
else switch($page) {
	
	case("main"):$lastModified=filemtime("modules/int_main.php");break;
	case("news"):$lastModified=filemtime("modules/int_news.php");break;
	case("about"):$lastModified=filemtime("modules/int_about.php");break;
	case("delivery"):$lastModified=filemtime("modules/int_deliveryAndPayment.php");break;
	case("products"):$lastModified=filemtime("modules/int_products.php");break;
	case("uses"):$lastModified=filemtime("modules/int_uses.php");break;
	case("customs"):$lastModified=filemtime("modules/int_customs.php");break;
	case("catalog"):$lastModified=filemtime("modules/int_catalog.php");break;
	case("mcatalog"):$lastModified=filemtime("modules/int_mcalatog.php");break;
	case("basket"):$lastModified=filemtime("modules/int_basket.php");break;
	case("orderProcess"):$lastModified=filemtime("modules/int_orderProcess.php");break;
	case("orderComplete"):$lastModified=filemtime("modules/int_orderComplete.php");break;
	case("login"):$lastModified=filemtime("modules/int_login.php");break;
	case("registration"):$lastModified=filemtime("modules/int_registration.php");break;
	case("orders"):$lastModified=filemtime("modules/int_myOrders.php");break;
	case("agents"):$lastModified=filemtime("modules/int_myAgents.php");break;
	case("newagent"):$lastModified=filemtime("modules/int_myAgentsCreate.php");break;
	case("callback"):$lastModified=filemtime("modules/int_callback.php");break;
	case("callbackSuccess"):$lastModified=filemtime("modules/int_callbackSuccess.php");break;
	case("customSuccess"):$lastModified=filemtime("modules/int_customSuccess.php");break;
	case("searchShort"):$lastModified=filemtime("modules/int_searchShort.php");break;
	case("searchFull"):$lastModified=filemtime("modules/int_searchFull.php");break;
	case("product"):$lastModified=filemtime("modules/int_product.php");break;
	}
	
//function filemtime($file) {
//    return date("D, d M Y H:i:s \G\M\T",filemtime($file));
//    }
    
if ($lastModified!=0) {
    $lastModified = gmdate("D, d M Y H:i:s \G\M\T", $lastModified);
    $IfModifiedSince = false;
    if (isset($_ENV['HTTP_IF_MODIFIED_SINCE'])) $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));  
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
    if ($IfModifiedSince && $IfModifiedSince >= $lastModified) header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
    else header('Last-Modified: '. $lastModified);
    }