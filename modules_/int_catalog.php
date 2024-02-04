<?php
//Timerw::start();
//точим стили

/*
// $paths $urlArr
echo '<pre>';
var_dump($paths);
echo '</pre>';

echo '<pre>';
var_dump($urlArr);
echo '</pre>';
/**/
global $urlArrf;
global $category;
global $isSemanticUrl;

$paths = parse_url($_SERVER['REQUEST_URI']);
$path = $paths['path'];
$path = \Utilsw\URL\URL::url($path);

/*
echo '<pre>';
var_dump($path);
var_dump($paths);
var_dump($_SERVER);
echo '</pre>';
/**/

if ($_GET['target']==='uses') {
	$style1=' activeTab';
	$style2='';
	}
elseif ($_GET['target']==='products') {
	$style1='';
	$style2=' activeTab';
	}
else {
	$style1='';
	$style2='';
	}

// новая работа со ссылкой
parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY),$urlQuery);
if (array_key_exists('filters', $urlQuery))
  $filtersStr=$urlQuery['filters'];
else
  $filtersStr="";
$curlArr=$furlArr=$urlQuery;
unset($curlArr['pagenumber']);
unset($furlArr['pagenumber']);
unset($curlArr['filters']);

$curl=urldecode(http_build_query($curlArr));
$furl=urldecode(http_build_query($furlArr));

$GLOBALS['basiclink']='?'.$curl;

//СБОР ЗАПРОСА (каталог и название группы\подгруппы)
$link='';
$h1_group_name = '';
$seo_text = '';
$seo_text2 = '';
$from='products p';
$where='p.active=1';
$groupby='p.ID';
//по группам
if(array_key_exists('level', $_GET) AND $_GET['target']=='products' && $_GET['level']=='1' && isset($_GET['id'])) {
	$query='SELECT g.* FROM groups g WHERE g.id='.$_GET['id'].' ORDER BY g.name';
	$group=$go->getRow($query);

	if (!is_null($group) AND $group!==FALSE) {
		$link.=$group['name'];
		$h1_group_name=$group['name'];
		$seo_text=$group['text'];
		$seo_text2=$group['text2'];
	}

	if ($isSemanticUrl) {
		$urlArrfl = $urlArrf;
		$cat = array_shift($urlArrfl);
		if ($cat != 'catalog') array_unshift($urlArrfl, $cat);
		$cat =	$category->getlastcat($urlArrfl);
		$catss = $category->getallhierarchycatuids($category::$catarr, $cat['uID']);
		$catss = implode("','", $catss);

		$from='products p';
		$where= "p.parent IN ('".$catss."') AND p.active=1";
		$groupby='p.ID';
	} else {
		$from='products p, subgroups sg, groups g';
		$where='p.parent=sg.uid AND sg.parent=g.uid AND g.id='.$_GET['id'].' AND p.active=1';
	}
	$groupby='p.ID';
}
//по подгруппам
elseif(array_key_exists('level', $_GET) AND $_GET['target']=='products' && $_GET['level']=='2' && isset($_GET['id'])) {
	if (count($urlArrf)>3) {
		$urlArrfl = $urlArrf;
		$cat = array_shift($urlArrfl);
    if ($cat != 'catalog') array_unshift($urlArrfl, $cat);
		$query = 'SELECT sg.* FROM subgroups sg WHERE sg.id='.$_GET['id'];
		$subgroup =$go->getRow($query);
		if (!is_null($subgroup) AND $subgroup!==FALSE) {
			$cat = $category->getlastcat($urlArrfl);
			array_pop($urlArrfl);
			$link=$cat['Name'];
			while (count($urlArrfl)<>0) {
				$cat = $category->getlastcat($urlArrfl);
				$link='<a href="/catalog/'.implode('/', $cat['catpath']).'/">'.$cat['Name'].'</a> / '.$link;
				$cat = array_pop($urlArrfl);
			}

			$h1_group_name=$subgroup['name'];
		  $seo_text=$subgroup['text'];
		  $seo_text2=$subgroup['text2'];
		}
	} else {
		$query='SELECT sg.* FROM subgroups sg, groups g WHERE sg.id='.$_GET['id'].' AND sg.parent=g.uid';
		$subgroup=$go->getRow($query);
		if (!is_null($subgroup) AND $subgroup!==FALSE) {
		  $query='SELECT g.name, g.ID, g.url FROM groups g WHERE g.uID="'.$subgroup['parent'].'" ORDER BY name';
		  $group=$go->getRow($query);
		  //$link.='<a href="/catalog?target=products&level=1&id='.$group['ID'].'">'.$group['name'].'</a> / '.$subgroup['name'];
		  if ($group['url'] != '')
		  	$link.='<a href="/catalog/'.$group['url'].'/">'.$group['name'].'</a> / '.$subgroup['name'];
		  else
		  	$link.='<a href="/catalog?target=products&level=1&id='.$group['ID'].'">'.$group['name'].'</a> / '.$subgroup['name'];

		  $h1_group_name=$subgroup['name'];
		  $seo_text=$subgroup['text'];
		  $seo_text2=$subgroup['text2'];
		}
	}
	if ($isSemanticUrl) {
		$urlArrfl = $urlArrf;
		$cat = array_shift($urlArrfl);
		if ($cat != 'catalog') array_unshift($urlArrfl, $cat);
		$cat =	$category->getlastcat($urlArrfl);
		$catss = $category->getallhierarchycatuids($category::$catarr, $cat['uID']);
		$catss = implode("','", $catss);

		$from='products p';
		$where= "p.parent IN ('".$catss."') AND p.active=1";
		$groupby='p.ID';
	} else {
		$from='products p, subgroups sg';
		$where='p.parent=sg.uid AND sg.id='.$_GET['id'].' AND p.active=1';
	}
	$groupby='p.ID';
}
//по назначениям elseif($_GET['target']=='uses' && $_GET['level']=='1' && isset($_GET['id'])) $pquery='SELECT p.* FROM products p WHERE active=1';
//по группам назначений elseif($_GET['target']=='uses' && $_GET['level']=='2' && isset($_GET['id'])) $pquery='SELECT p.* FROM products p WHERE active=1';
//стандартный запрос (выводим EEEEVERRRYYYTTHIIIING)
else {
    $link.='Все';
    $from='products p';
    $where='p.active=1';
    $groupby='p.ID';
}

//РАБОТАЕМ С ФИЛЬТРАМИ

if ($filtersStr!='') {
    $noFilter=false;
    $filters=explode(',',$filtersStr);
    $filtersIN=implode(',',$filters); //эти две операции сгенерят фейл, если со входным массивом лажа

    $ptcquery='SELECT * FROM relPropertiesToValues rpv, propertiesValues pv WHERE pv.active=1 AND rpv.active=1 AND pv.uID=rpv.vuID AND pv.ID IN ('.$filtersIN.') GROUP BY rpv.puID';
    $ptc=$go->numRows($go->query($ptcquery)); //считаем количество типов установленных фильтров, чтобы установить границу вывода через HAVING COUNT(p.ID)

    $from.=', propertiesValues pv, relProductsToValues rpv';
    $where.=' AND pv.active=1 AND rpv.active=1 AND rpv.vuID=pv.uid AND p.uID=rpv.puID  AND pv.ID IN ('.$filtersIN.')';
    $groupby.=' HAVING COUNT(p.ID)>='.($ptc);
    }
else $noFilter=true;

$pquery = 'SELECT p.ID FROM '.$from.' WHERE '.$where.' GROUP BY '.$groupby.' ORDER BY p.name'; //СОБИРАЕМ ЗАПРОС
//var_dump($pquery);
$productsAll = $go->getAll($pquery);
$productsCountAll = $go->affectedRows();
//var_dump($productsCount);

//echo $pquery;

//ПАРСИМ
$cells=array();

//определяем номер текущей страницы
$pagenumber= (isset($_GET['pagenumber']) && is_numeric($_GET['pagenumber'])) ? (integer)$_GET['pagenumber'] : 1;
$pagesBlock='';

if ($productsCountAll>0) {
	  //РАБОТАЕМ СО СТРАНИЦАМИ
    $productsOnPage=32;
    //определяем номер текущей страницы
    $pagenumber= (isset($_GET['pagenumber']) && is_numeric($_GET['pagenumber'])) ? (integer)$_GET['pagenumber'] : 1;
	  $pagesCount=ceil($productsCountAll/$productsOnPage); //считаем общее количество страниц
    if ($pagenumber>$pagesCount) $pagenumber=1; //если номер текущей страницы больше общего, ставим на 1
    $startnum = ($pagenumber-1)*$productsOnPage;
    //$endnum =  ($pagenumber)*$productsOnPage;

    $pagesBlock='';
    if ($pagenumber==1) $pagesBlock='<div class="pageNumberSelect selected"><span>1</span></div>';
    else $pagesBlock='<a href="'.\Utilsw\URL\URL::url($path.'?'.$furl).'"><div class="pageNumberSelect"><span>1</span></div></a>';
    for($i=2;$i<=$pagesCount;$i++) if ($i===$pagenumber) $pagesBlock=$pagesBlock.'<div class="pageNumberSelect selected"><span>'.$i.'</span></div>';
    else $pagesBlock=$pagesBlock.'<a href="'.\Utilsw\URL\URL::url($path.'?'.$furl.'&pagenumber='.$i).'"><div class="pageNumberSelect"><span>'.$i.'</span></div></a>';

    //$startFrom=($pagenumber-1)*$productsOnPage+1;
    //КОНЕЦ РАБОТЫ СО СТРАНИЦАМИ

    $ppgquery='SELECT p.* FROM '.$from.' WHERE '.$where.' GROUP BY '.$groupby.' ORDER BY p.name LIMIT ?i,?i'; //СОБИРАЕМ ЗАПРОС

    //var_dump($ppgquery);

    //ЗАПРАШИВАЕМ
    $products=$go->getAll($ppgquery, $startnum, $productsOnPage);
    $productsCount=$go->affectedRows();


  //var_dump(Timerw::finish());
  //var_dump($productsCount);
	/*
	echo '<pre>';
  var_dump($products);
	echo '</pre>';
	/**/

	$i=1;
	foreach ($products as $row) {
		$cellsPrepare['name']=$row['name'];
		$cellsPrepare['id']=$row['ID'];
		$cellsPrepare['url']=$row['url'];
		$cellsPrepare['article']=$row['articleFull'];
		$cellsPrepare['uid']=$row['uID'];
		$cellsPrepare['lastMod']=$row['lastModified'];
		$cellsPrepare['supply']=$row['availability'];

		if (isset($prices[$row['uID']]) && $prices[$row['uID']]!=0) $cellsPrepare['cost']=$prices[$row['uID']];
		else $cellsPrepare['cost']='не установлена';

		/*
		echo '<pre>';
		var_dump($cellsPrepare['cost']);
		echo '</pre>';
		/**/

		$images=glob(FPATH.$row['uID'].'*');
	  if (count($images)!=0) {
	      usort($images,'imgSort');
	      $cellsPrepare['photo']=$images[0];
	      //die($cellsPrepare['photo']); files/e7e1a49c-cc50-11e7-9d9d-00505601212a-8396d8a2-763a-11e9-811a-0050569b570f-1.jpg
	      $cellsPrepare['photo'] = \Utilsw\Image\Image::getResizeImg($cellsPrepare['photo']);
	  } else $cellsPrepare['photo']=NOPH;

		$cells[$i]=$cellsPrepare;
		$i++;
	}
}

//работаем с сео
$seo_text_compiled = "";
if ($pagenumber==1 && $noFilter) { //отображаем тесты только на первой странице и если нет фильтров
    $seo_text_compiled='<div class="p-2">'.$seo_text;
    if ($seo_text2!='') $seo_text_compiled.='<button class="greyGradient" data-toggle="collapse" data-target="#seo_2">Узнать подробности.</button><div id="seo_2" class="collapse">'.$seo_text2.'</div>';
    $seo_text_compiled.='</div>';
    }

$cellTemplateUnlogged='
    <div class="productCell col-6 col-md-4 col-lg-3">
    <a href="/product/#url#">
    <div class="imgWrap"><img src="#photourl#" class="productCellImg"></div>
    </a>
    <a class="d-block pt-md-2" href="/product/#url#">
    <div class="productNameWrap"><div class="productCellName">#name#</div></div>
    </a>

    <div class="w-100 p-2">
    <div class="d-flex flex-row justify-content-between"><div><b>Цена:&nbsp;</b></div><div></div></div>
    <div class="d-flex flex-row justify-content-between"><div>Розничная:&nbsp;</div><div>#priceR1#</div></div>
    <div class="d-flex flex-row justify-content-between"><div>Co скидкой:&nbsp;</div><div><a class="blue" href="/login">войти</a></div></div>
    <div class="d-flex flex-row justify-content-between"><div>Оптовая:&nbsp;</div><div><a class="blue" href="/login">войти</a></div></div>
    </div>

    <div>
      <div class="articleWrap"><div class="inner-article-wrap">Арт.:<b>#code#</b></div></div>
			<div class="d-flex flex-row flex-wrap w-100 no-gutters p-2">
				<div class="col-12 col-md-6"><div class="pr-0 pr-md-1 pb-1 pb-md-0 d-flex align-items-center justify-content-center counter-wrp"><button class="ProductCount" action="0" product="#id#">-</button><input class="ProductCount" type="text" value="#ProductCountInCart#" autocomplete="off" product="#id#"><button class="ProductCount" action="1" product="#id#">+</button></div></div>
      	<div class="col-12 col-md-6"><div class="buybtnWrap"><button class="buy greenGradient" product="#id#" data-prdincart="#prdincart#">#buttonText#</button></div></div>
			</div>
    </div>

    <!--noindex-->
    <div> <!-- <div class="i-tooltip-parent arrow-on-left"> -->
    Цена указана в рублях #pvn#
    <!-- <div class="i-tooltip">Вы сможете работать без НДС, если зарегистрируетесь и войдете.</div> -->
    </div>
    <!--/noindex-->
    <div class="d-flex flex-row justify-content-between align-self-center"><div>#supply#</div></div>
    </div>';

$cellTemplateLogged='
    <div class="productCell col-6 col-md-4 col-lg-3">
    <a href="/product/#url#">
    <div class="imgWrap"><img src="#photourl#" class="productCellImg"></div>
    </a>
    <a class="d-block pt-md-2" href="/product/#url#">
    <div class="productNameWrap"><div class="productCellName">#name#</div></div>
    </a>

    <div class="w-100 p-2">
    <div class="d-flex flex-row justify-content-between"><div><b>Ваша цена:&nbsp;</b></div><div><b>#price#</b></div></div>
    <div class="d-flex flex-row justify-content-between i-tooltip-parent"><div class="arrow-on-left">Розничная:&nbsp;</div><div>#priceR1#</div>
    <!-- <div class="i-tooltip">Как только сумма ваших покупок достигнет '.$prc->l1.', начинает действовать цена со скидкой. При заказе от одной коробки.</div> --></div>
    <div class="d-flex flex-row justify-content-between i-tooltip-parent"><div class="arrow-on-left">Co скидкой:&nbsp;</div><div>#priceR2#<!-- #pvn#--></div>
    <!-- <div class="i-tooltip">Как только сумма ваших покупок достигнет '.$prc->l2.', начинает действовать оптовая цена. При заказе от одной коробки.</div>--></div>
    <div class="d-flex flex-row justify-content-between i-tooltip-parent"><div class="arrow-on-left">Оптовая:&nbsp;</div><div>#priceR3#<!-- #pvn#--></div><div class="i-tooltip">Для обсуждения дополнительной скидки свяжитесь с менеджером.<br><a class="blue" href="/about">Контакты.</a></div></div>
    </div>

    <div>
      <div class="articleWrap"><div class="inner-article-wrap">Арт.:<b>#code#</b></div></div>
			<div class="d-flex flex-row flex-wrap w-100 no-gutters p-2">
				<div class="col-12 col-md-6"><div class="pr-0 pr-md-1 pb-1 pb-md-0 d-flex align-items-center justify-content-center counter-wrp"><button class="ProductCount" action="0" product="#id#">-</button><input class="ProductCount" type="text" value="#ProductCountInCart#" autocomplete="off" product="#id#"><button class="ProductCount" action="1" product="#id#">+</button></div></div>
      	<div class="col-12 col-md-6"><div class="buybtnWrap"><button class="buy greenGradient" product="#id#" data-prdincart="#prdincart#" >#buttonText#</button></div></div>
			</div>
    </div>

    <div> <!-- <div class="i-tooltip-parent arrow-on-left"> -->
    Цена указана в рублях #pvn#
    <!-- <div class="i-tooltip"><button type="submit" form="pvn-switch" class="greyGradient">Переключить</button></div> -->
    </div>
    <div class="d-flex flex-row justify-content-between align-self-center"><div>#supply#</div></div>
    </div>';

$pvnText= ($_SESSION['pvn']==false) ? 'без НДС' : 'с НДС';

if (!isset($h1_group_name)) $h1_group_name = "";

?>

<div class="bread">
<a href="/">Главная</a> / <a href="/catalog/">Категории</a><!--mcatalog?target=products--> / <?=$link?>
</div>

<div id="optionsBlock" class="col-12 col-md-3 order-0 order-md-2 p-0">
    <div class="optionsSticky position-sticky">
	<div class="optionsText">ФИЛЬТРЫ</div>
	<div class="optionsList">
		<?include('int_options.php');?>
	</div>
	<a href="/callback" style="text-decoration:none;"><div class="makeCall">Заказать звонок</div></a>
	</div>
</div>

<div class="contentContainer col-12 col-md-9 p-0">
	<div class="catalog w-100">
	    <h1 itemprop="name"><?=$h1_group_name?></h1>
		<div class="tabContainer">
			<div class="tabBlock col-6 col-md-9 d-flex justify-content-center align-items-center<?=$style2?>">
			<a href="/catalog/"><div class="tabText">Продукция</div></a><!--mcatalog?target=products-->
			</div>

			<div class="tabBlock col-6 col-md-3 d-flex justify-content-center align-items-center">
			<a href="/customs"><div class="tabText">Продукция<br>по параметрам<br>заказчика</div></a>
   </div>
		</div>
		<div class="pagesBlock">
			Всего товаров: <b><?=$productsCountAll?></b>
		</div>

		<div class="catalogBlock row no-gutters align-content-md-stretch">

		<?
		if ($productsCountAll>0) {
		    //for($i=$startFrom;$i<=($startFrom+$productsOnPage-1);$i++)
        foreach($cells as $cellval)
                //if(isset($cells[$i])) {
                if(TRUE) {
                    $buttonText= isset($_SESSION['cart'][$cellval['id']]) ? 'В корзине!' : 'Купить';
										$prdincart = isset($_SESSION['cart'][$cellval['id']]) ? 1 : 0;

                    $cell= (array_key_exists('user_in', $_SESSION) AND $_SESSION['user_in']) ? $cellTemplateLogged : $cellTemplateUnlogged;
                    $cell=str_replace('#url#',$cellval['url'],$cell);
                    $cell=str_replace('#photourl#',$cellval['photo'],$cell);
                    $cell=str_replace('#name#',$cellval['name'],$cell);

                    $cell=str_replace('#code#',$cellval['article'],$cell);
                    //$cell=str_replace('#code#',formatarticle($cellval['article']),$cell);

                    $cell=str_replace('#id#',$cellval['id'],$cell);
                    $cell=str_replace('#buttonText#',$buttonText,$cell);
                    $cell=str_replace('#pvn#',$pvnText,$cell);
                    $cell=str_replace('#supply#',$cellval['supply'],$cell);

                    $cell=str_replace('#price#',$cellval['cost'],$cell);
										if (array_key_exists($cellval['uid'], $pricesD[1])) $prs = $pricesD[1][$cellval['uid']];
										else $prs = 'не установлено';
                    $cell=str_replace('#priceR1#',$prs,$cell);
                    if ($_SESSION['pvn']==false) {
											if (array_key_exists($cellval['uid'], $pricesD[4])) $prs4 = $pricesD[4][$cellval['uid']];
											else $prs4 = 'не установлено';
											if (array_key_exists($cellval['uid'], $pricesD[5])) $prs5 = $pricesD[5][$cellval['uid']];
											else $prs5 = 'не установлено';
                      $cell=str_replace('#priceR2#',$prs4,$cell);
                      $cell=str_replace('#priceR3#',$prs5,$cell);
                    } else {
											if (array_key_exists($cellval['uid'], $pricesD[2])) $prs2 = $pricesD[2][$cellval['uid']];
											else $prs2 = 'не установлено';
											if (array_key_exists($cellval['uid'], $pricesD[3])) $prs3 = $pricesD[3][$cellval['uid']];
											else $prs3 = 'не установлено';
                      $cell=str_replace('#priceR2#',$prs2,$cell);
                      $cell=str_replace('#priceR3#',$prs3,$cell);
                    }
										$ProductCountInCart = 1;
  									if (array_key_exists('cart', $_SESSION) AND array_key_exists($cellval['id'], $_SESSION['cart'])) {
											$ProductCountInCart = $_SESSION['cart'][$cellval['id']]['count'];
										}
										$cell=str_replace('#ProductCountInCart#',$ProductCountInCart,$cell);
										$cell=str_replace('#prdincart#',$prdincart,$cell);
                    echo $cell;
                }
		    }
        else echo 'По заданным параметрам товаров не найдено. <br><br>
        Попробуйте следующее: <br>
        1) cмягчите условия фильтрации<br>
        2) поищите в других категориях<br><br>

        Если вы не можете найти подходящий товар, вы можете оставить заявку на изготовление продукции по вашим параметрам.';
		?>

		</div>
		<div class="pagesBlock">
			<?=$pagesBlock?>
		</div>
		<form id="pvn-switch" method="post"><input type="hidden" name="action" value="pvn-switch"></form>
	</div>
<?=$seo_text_compiled?>
</div>

<?php

//var_dump(Timerw::finish());

function formatarticle($article) {
  $ahtml = $article;
  if (mb_strlen($article)>21) {
    $ahtml = mb_strcut($article, 0, 17).'<span class="dotarticle">...</span><div class="hide">'.$article."</div>";
  }
  return $ahtml;
}
?>