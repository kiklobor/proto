<?
$catalog_meta_title_tpl = "Купить #CATEGORY_NAME# по выгодной цене оптом и в розницу в интернет-магазине ООО «Имидж»";
$catalog_meta_description_tpl = "✓ #CATEGORY_NAME# - купить и изготовить, а также разработать дизайн с доставкой на дом и в офис в Москве и регионах. Интернет-магазин ООО «Имидж». ☎ Наш телефон: 7 (499) 707-17-91";

$filter_meta_title_tpl = "#CATEGORY_NAME# (#FILTERS#) по выгодной цене оптом и в розницу в интернет-магазине ООО «Имидж»";
$filter_meta_description_tpl = "✓ #CATEGORY_NAME# (#FILTERS#) - купить, изготовить в Москве в интернет-магазине ООО «Имидж». ☎ Наш телефон: 7 (499) 707-17-91";

$meta='';
$canonical='';

$pagenumtext = '';
if (isset($_GET['pagenumber']) AND is_numeric($_GET['pagenumber']) AND ($_GET['pagenumber']>1)) {
 $pagenumtext = " | Страница ".$_GET['pagenumber'];
}

if($page == 'catalog' && isset($_GET['id']) && $_GET['target'] == 'products' && isset($_GET['level']) && !isset($_GET['filters'])) {
	$category_table_name = ($_GET['level'] == 1)?'groups':'subgroups';
	$query = 'SELECT g.name FROM ' . $category_table_name . ' g WHERE g.id=' . (int)$_GET['id'];
	$group = $go->getRow($query);

	if(!empty($group)) {
		$meta_title = str_replace("#CATEGORY_NAME#", mb_strtolower($group['name'], 'UTF-8'), $catalog_meta_title_tpl);
		$meta_description = str_replace("#CATEGORY_NAME#", $group['name'], $catalog_meta_description_tpl);
		$meta.='<title>'.$meta_title.$pagenumtext.'</title>';
		$meta.='<meta name="description" content="'.$meta_description.$pagenumtext.'"/>';
	}

} elseif($page == 'catalog' && isset($_GET['id']) && $_GET['target'] == 'products' && isset($_GET['level']) && isset($_GET['filters'])) {
	$category_table_name = ($_GET['level'] == 1)?'groups':'subgroups';
	$query = 'SELECT g.name FROM ' . $category_table_name . ' g WHERE g.id=' . (int)$_GET['id'];
	$group = $go->getRow($query);

	$filter_arr = array_map("intval", explode(',', $_GET['filters']));
	$query = 'SELECT GROUP_CONCAT(name) as filters FROM propertiesValues pv WHERE pv.id IN (' . implode(',', $filter_arr) . ')';
	$filters = $go->getRow($query);
	if(!empty($group) && !empty($filters)) {
		$meta_title = str_replace(
			array(
				"#CATEGORY_NAME#",
				"#FILTERS#"
			),
			array(
				$group['name'],
				mb_strtolower($filters['filters'], 'UTF-8')
			),
			$filter_meta_title_tpl
		);

		$meta_description = str_replace(
			array(
				"#CATEGORY_NAME#",
				"#FILTERS#"
			),
			array(
				$group['name'],
				mb_strtolower($filters['filters'], 'UTF-8')
			),
			$filter_meta_description_tpl
		);

		$meta.='<title>'.$meta_title.$pagenumtext.'</title>';
		$meta.='<meta name="description" content="'.$meta_description.$pagenumtext.'"/>';
	}

    /*$productUrl=$_GET['product'];
    $query="SELECT * FROM `products` WHERE url=?s AND active=1 LIMIT 1";
    $product=$go->getRow($query,$productUrl);
    if ($go->affectedRows()==1) {
        $meta.='<title>'.$product['name'].'</title>';
        }*/
} elseif ($page == 'catalog' && isset($_GET['target']) && $_GET['target'] == 'products' AND isset($_GET['filters'])) {
	$filter_arr = array_map("intval", explode(',', $_GET['filters']));
	$query = 'SELECT GROUP_CONCAT(name) as filters FROM propertiesValues pv WHERE pv.id IN (' . implode(',', $filter_arr) . ')';
	$filters = $go->getRow($query);
	if(!empty($filters)) {
		$meta = $meta.'<title>Купить '.mb_strtolower($filters['filters'], 'UTF-8').' по выгодной цене оптом и в розницу в интернет-магазине &quot;Имидж&quot;</title>';
		$meta = $meta.'<meta name="description" content="'.mb_strtolower($filters['filters'], 'UTF-8').' - купить, изготовить в Москве в интернет-магазине ООО «Имидж». ☎ Наш телефон: 7 (499) 707-17-91"/>';
    } else {
		$meta.='<title>Каталог товаров — интернет-магазин &quot;Имидж&quot;</title>';
		$meta.='<meta name="description" content="Полный каталог товаров и выгодная цена в интернет-магазине &quot;Имидж&quot;. Товары для офиса, дома и бизнеса. ✆ Наш телефон- 8 (800) 555-80-54"/>';
    }
} elseif($page == 'catalog' && isset($_GET['target']) && $_GET['target'] == 'products') {
		$meta.='<title>Каталог товаров — интернет-магазин &quot;Имидж&quot;</title>';
		$meta.='<meta name="description" content="Полный каталог товаров и выгодная цена в интернет-магазине &quot;Имидж&quot;. Товары для офиса, дома и бизнеса. ✆ Наш телефон- 8 (800) 555-80-54"/>';
} else {
/*	if ($page!='') {
	print_r ($meta_title);
	//print_r ($_GET['id');
	} */
    $metaArr=$go->getRow("SELECT * FROM meta WHERE page=?s",$page);
   // print_r ($page);
    if ($go->affectedRows()==1) {
        if ($metaArr['title']!='') $meta.='<title>'.htmlspecialchars($metaArr['title']).$pagenumtext.'</title>';
        if ($metaArr['keywords']!='') $meta.='<meta name="keywords" content="'.htmlspecialchars($metaArr['keywords']).'">';
        if ($metaArr['description']!='') $meta.='<meta name="description" content="'.htmlspecialchars($metaArr['description']).$pagenumtext.'">';
    }

    //if ($_GET['page']=='main') $canonical='<link rel="canonical" href="https://'.$_SERVER['HTTP_HOST'].'">';
    //elseif ($page=='catalog') {}
    //else $canonical='<link rel="canonical" href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">';
}


// Услуги

$services_meta_title_tpl = "#CATEGORY_NAME# на заказ в Москве в компании ООО «Имидж»";
$services_meta_description_tpl = "#CATEGORY_NAME# - по доступным ценам в производственном комбинате ООО «Имидж» в Москве. Оперативно и качественно, любые объемы заказа. 100% гарантия качества. Доставка по всей России. ☎ Наш телефон: 7 (499) 707-17-91";

if ($page == 'services' && !isset($_GET['filters'])) {
  global $urlArrf;
  global $categoryservices;
  $category = $categoryservices->getlastcat($urlArrf);
  if (count($category)>0) {
    $meta_title = str_replace("#CATEGORY_NAME#", $category['Name'], $services_meta_title_tpl);
    $meta_description = str_replace("#CATEGORY_NAME#", $category['Name'], $services_meta_description_tpl);
    $meta.='<title>'.$meta_title.$pagenumtext.'</title>';
    $meta.='<meta name="description" content="'.$meta_description.$pagenumtext.'"/>';
  } else {
    $meta.='<title>Каталог услуг &quot;Имидж&quot;</title>';
		$meta.='<meta name="description" content="Полный каталог услуг &quot;Имидж&quot;. ✆ Наш телефон- 8 (800) 555-80-54"/>';
  }
} elseif ($page == 'mservices' OR $page == 'services') {
  $metaArr=$go->getRow("SELECT * FROM meta WHERE page=?s",$page);
  // print_r ($page);
  if ($go->affectedRows()==1) {
      if ($metaArr['title']!='') $meta.='<title>'.htmlspecialchars($metaArr['title']).$pagenumtext.'</title>';
      if ($metaArr['keywords']!='') $meta.='<meta name="keywords" content="'.htmlspecialchars($metaArr['keywords']).'">';
      if ($metaArr['description']!='') $meta.='<meta name="description" content="'.htmlspecialchars($metaArr['description']).$pagenumtext.'">';
  }
}

// canonical urls
global $isSemanticUrl;
global $canonical;
global $is404;

$canonical = '';
$canonicalurl = '';

if (!$isSemanticUrl) {
  if ($page == 'catalog' && isset($_GET['id']) && $_GET['target'] == 'products' && isset($_GET['level'])) {
    //$canonicalurl = '/catalog/';
    if ($_GET['level'] == 1) {
      $query = 'SELECT g.url FROM groups g WHERE g.id=' . (int)$_GET['id'];
      $group = $go->getRow($query);
      if ($group['url'] != '') {
        $canonicalurl = $group['url'];
      }
    } elseif ($_GET['level'] == 2) {
      $query = 'SELECT sg.url sgurl, g.url gurl  FROM subgroups sg, groups g WHERE sg.id='.(int)$_GET['id'].' AND sg.parent=g.uid';
      $group = $go->getRow($query);
      if ($group['sgurl'] != '' AND $group['gurl'] != '') {
        $canonicalurl = $group['gurl'].'/'.$group['sgurl'];
      }
    }
    if ($canonicalurl != '') {
      $canonicalurl = '/catalog/'.$canonicalurl.'/';
    }
  } elseif ($page == 'mcatalog' && $_GET['target'] == 'products') {
    $canonicalurl = '/catalog/';
  } elseif ($page == 'catalog' && $_GET['target'] == 'products' AND array_key_exists('filters', $_GET)) {
    //parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$urlQuery); if (array_key_exists('filters', $urlQuery)) {
    //установлен фильтр на главной каталога в не чпу - /catalog?target=products&filters=636  надо в такой /catalog/?filters=636
    $paths = parse_url($_SERVER['REQUEST_URI']);
    $path = $paths['path'];
    $path = \Utilsw\URL\URL::url($path);
    if (mb_substr($path, -1) != '/') {
      $path = $path.'/';
    }
    if (array_key_exists('query', $paths)) {
      parse_str($paths['query'], $urlQuery);
      //little paranoic
      if (array_key_exists('target', $urlQuery))
        unset($urlQuery['target']);
      $furl=http_build_query($urlQuery);
      $path = $path.'?'.urldecode($furl);
      /**/
    }
    $canonicalurl = $path;
  }

  if (($page == 'catalog' OR $page == 'mcatalog') AND array_key_exists('filters', $_GET) AND !$is404 AND $canonicalurl=='') {
    $canonical='<link rel="canonical" href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">';
  }

  if ($canonicalurl != '') {
    $canonical = '<link rel="canonical" href="'.$canonicalurl.'">';
  }
}

if (($page == 'catalog' OR $page == 'mcatalog') AND array_key_exists('filters', $_GET) AND !$is404 AND $canonicalurl=='') {
  $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
  $canonical='<link rel="canonical" href="https://'.$_SERVER['HTTP_HOST'].$uri_parts[0].'">';
}

/*/
echo '<pre>';
var_dump($isSemanticUrl);
echo '</pre>';
/**/

/*
echo ($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
switch($page) {
	case("main"):require("modules/int_main.php");break;
	case("about"):require("modules/int_about.php");break;
	case("delivery"):require("modules/int_deliveryAndPayment.php");break;
	case("products"):require("modules/int_products.php");break;
	case("uses"):require("modules/int_uses.php");break;
	case("customs"):require("modules/int_customs.php");break;
	case("catalog"):require("modules/int_catalog.php");break;
	case("mcatalog"):require("modules/int_mcalatog.php");break;
	case("basket"):require("modules/int_basket.php");break;
	case("orderProcess"):require("modules/int_orderProcess.php");break;
	case("orderComplete"):require("modules/int_orderComplete.php");break;
	case("login"):require("modules/int_login.php");break;
	case("registration"):require("modules/int_registration.php");break;
	case("orders"):require("modules/int_myOrders.php");break;
	case("agents"):require("modules/int_myAgents.php");break;
	case("newagent"):require("modules/int_myAgentsCreate.php");break;
	case("callback"):require("modules/int_callback.php");break;
	case("callbackSuccess"):require("modules/int_callbackSuccess.php");break;
	case("customSuccess"):require("modules/int_customSuccess.php");break;
	case("searchShort"):require("modules/int_searchShort.php");break;
	case("searchFull"):require("modules/int_searchFull.php");break;
	case("product"):require("modules/int_product.php");break;
	}*/
