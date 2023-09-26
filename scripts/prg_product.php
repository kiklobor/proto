<?
$productUrl=$urlArr[2];

//$query="SELECT p.* FROM products p,groups g,subgroups sg WHERE p.parent=sg.uID AND sg.parent=g.uID AND g.ind=0 AND p.url=?s AND p.active=1 LIMIT 1";
$query="SELECT p.* FROM products p,groups g,subgroups sg WHERE p.parent=sg.uID AND p.url=?s AND p.active=1 LIMIT 1";
$product=$go->getRow($query,$productUrl);
if (isset($prices[$product['uID']]) && $prices[$product['uID']]!=0) $product['cost']=$prices[$product['uID']];
else $product['cost']='не установлена';

if ($go->affectedRows()==1) {
    $productSafe=htmlspecialchars($product['name']);

    // собираем галерею
    $images=array();
    $preimages=array();
    if($product['video']!='') array_push($images,'<a href="https://youtube.com/watch?v='.$product['video'].'&autoplay=1&version=3&loop=1&playlist='.$product['video'].'">ВИДЕО</a>');
    //if($product['video']!='') array_push($images,'<iframe width="560" height="315" src="https://www.youtube.com/embed/'.$product['video'].'?rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>');
	foreach (glob(FPATH.$product['uID'].'*') as $filename) array_push($preimages,'/'.$filename);
    if (count($preimages)==0) array_push($images,'<img title="'.htmlspecialchars($product['name']).'" alt="Цена: '.$product['cost'].' руб. '.$productSafe.'" src="'.NOPH.'">');
    else {
        usort($preimages,'imgSort');
        foreach ($preimages as $filename) array_push($images,'<img title="'.htmlspecialchars($product['name']).'" alt="Цена: '.$product['cost'].' руб. '.$productSafe.'" src="'.$filename.'" itemprop="image">');
    }

    $gallery=implode('',$images);

    // цены
    $cellTemplateUnlogged='
    <div class="w-50 p-2">
    <div class="d-flex flex-row justify-content-between"><div><b>Цена:&nbsp;</b></div><div></div></div>
    <div class="d-flex flex-row justify-content-between"><div>Розничная:&nbsp;</div><div>#priceR1#</div></div>
    <div class="d-flex flex-row justify-content-between"><div>Co скидкой:&nbsp;</div><div><a class="blue" href="/login">войти</a></div></div>
    <div class="d-flex flex-row justify-content-between"><div>Оптовая:&nbsp;</div><div><a class="blue" href="/login">войти</a></div></div>
    </div>

    <!--noindex-->
    <div> <!-- <div class="w-50 i-tooltip-parent arrow-on-left"> -->
    Цена указана в рублях #pvn#
    <!-- <div class="i-tooltip">Вы сможете работать без НДС, если зарегистрируетесь и войдете.</div> -->
    </div>
    <!--/noindex-->
    ';

    $cellTemplateLogged='
    <div class="w-100 p-2">
    <div class="d-flex flex-row justify-content-between"><div><b>Ваша цена:&nbsp;</b></div><div><b>#price#</b></div></div>
    <div class="d-flex flex-row justify-content-between i-tooltip-parent"><div class="arrow-on-left">Розничная:&nbsp;</div><div>#priceR1#</div>
    <!-- <div class="i-tooltip">Как только сумма ваших покупок достигнет '.$prc->l1.', начинает действовать цена со скидкой. При заказе от одной коробки.</div> --></div>
    <div class="d-flex flex-row justify-content-between i-tooltip-parent"><div class="arrow-on-left">Co скидкой:&nbsp;</div><div>#priceR2#<!-- #pvn#--></div>
    <!-- <div class="i-tooltip">Как только сумма ваших покупок достигнет '.$prc->l2.', начинает действовать оптовая цена. При заказе от одной коробки.</div>--></div>
    <div class="d-flex flex-row justify-content-between i-tooltip-parent"><div class="arrow-on-left">Оптовая:&nbsp;</div><div>#priceR3#<!-- #pvn#--></div><div class="i-tooltip">Для обсуждения дополнительной скидки свяжитесь с менеджером.<br><a class="blue" href="/about">Контакты.</a></div></div>
    </div>

    <div> <!-- <div class="w-50 i-tooltip-parent arrow-on-left"> -->
    Цена указана в рублях #pvn#
    <!-- <div class="i-tooltip text-center"><button type="submit" form="pvn-switch" class="greyGradient">Переключить</button></div> -->
    </div>';

    $pvnText= ($_SESSION['pvn']==false) ? 'без НДС' : 'с НДС';

    $cell= (array_key_exists('user_in', $_SESSION) AND $_SESSION['user_in']) ? $cellTemplateLogged : $cellTemplateUnlogged;
    $cell=str_replace('#pvn#',$pvnText,$cell);
    $cell=str_replace('#price#',$product['cost'],$cell);
    if (array_key_exists($product['uID'], $pricesD[1])) $prs = $pricesD[1][$product['uID']];
    else $prs = 'не установлено';
    $cell=str_replace('#priceR1#',$prs,$cell);
    if ($_SESSION['pvn']==false) {
      if (array_key_exists($product['uID'], $pricesD[4])) $prs4 = $pricesD[4][$product['uID']];
      else $prs4 = 'не установлено';
      if (array_key_exists($product['uID'], $pricesD[5])) $prs5 = $pricesD[5][$product['uID']];
      else $prs5 = 'не установлено';
      $cell=str_replace('#priceR2#',$prs4,$cell);
      $cell=str_replace('#priceR3#',$prs5,$cell);
    } else {
      if (array_key_exists($product['uID'], $pricesD[2])) $prs2 = $pricesD[2][$product['uID']];
      else $prs2 = 'не установлено';
      if (array_key_exists($product['uID'], $pricesD[3])) $prs3 = $pricesD[3][$product['uID']];
      else $prs3 = 'не установлено';
      $cell=str_replace('#priceR2#',$prs2,$cell);
      $cell=str_replace('#priceR3#',$prs3,$cell);
    }
    $pricesBlock=$cell;

    // мелочевка
	$code= ($product['articleFull']==='') ? 'не указан' : $product['articleFull'];
	if (strtolower($product['availability']) == strtolower('Товар в наличии')) {
        $supply=($product['availability'].'.</br>
            Доставка по г. Москва:</br>
         Срок доставки 1-3 дня</br>
         Стоимость доставки 750 рублей.');
    }
    else {
        $supply=($product['availability']==='') ? 'Срок поставки не указан' : $product['availability'];
    }

  $ProductCountInCart = 1;
	if (isset($_SESSION['cart'][$product['ID']])) {
    $buttonText='В корзине!';
    $prdincart = 1;
    if (array_key_exists('cart', $_SESSION) AND array_key_exists($product['ID'], $_SESSION['cart'])) {
      $ProductCountInCart = $_SESSION['cart'][$product['ID']]['count'];
    }
  } else {
    $buttonText='Купить';
    $prdincart = 0;
  }

	// блок характеристик
	$props='';
	$propsArr=$go->getCol("SELECT pv.name FROM relProductsToValues rpv,propertiesValues pv WHERE pv.active=1 AND rpv.active=1 AND rpv.puID=?s AND rpv.vuID=pv.uID GROUP BY pv.ID",$product['uID']);
	if (count($propsArr)>0) {
	    $props.='<h4 class="mt-2">Характеристики:</h4><ul>';
        foreach ($propsArr as $val) $props.='<li>'.$val.'</li>';
        $props.='</ul>';
    	}



    // строка крошек
    $query='SELECT g.name gname, g.ID gID, sg.name sgname, sg.ID sgID, g.url gurl, sg.url sgurl FROM products p, groups g, subgroups sg WHERE p.ID=?i AND p.parent=sg.uID AND sg.parent=g.uID AND p.active=1 AND g.active=1 AND sg.active=1';
    $parents=$go->getRow($query,$product['ID']);
    //if ($parents) $crumbs='<a href="/catalog?target=products&level=1&id='.$parents['gID'].'">'.$parents['gname'].'</a> / <a href="/catalog?target=products&level=2&id='.$parents['sgID'].'">'.$parents['sgname'].'</a> / ';
    if ($parents) $crumbs='<a href="/catalog/'.$parents['gurl'].'/">'.$parents['gname'].'</a> / <a href="/catalog/'.$parents['gurl'].'/'.$parents['sgurl'].'/">'.$parents['sgname'].'</a> / ';
    else {
      $query='SELECT sg.name sgname, sg.ID sgID, sg.url sgurl, sg.uID AS uID FROM products p, subgroups sg WHERE p.ID=?i AND p.parent=sg.uID AND p.active=1 AND sg.active=1';
      $parents=$go->getRow($query,$product['ID']);

      if ($parents) {
        $cat = $category->getcatbyuid($parents['uID']);
        if (count($cat)<>0) {
          $catpath = $cat['catpath'];
    			$link='';
    			while (count($catpath)<>0) {
    				$cat = $category->getlastcat($catpath);
    				$link='<a href="/catalog/'.implode('/', $cat['catpath']).'/">'.$cat['Name'].'</a> / '.$link;
    				$cat = array_pop($catpath);
    			}
          $crumbs=$link;
          /**/
        } else {
          $crumbs='Без категории / ';
        }

      } else {
        $crumbs='Без категории / ';
      }
    }
    $crumbs.=$product['name'];

    // блок описания
    $description= ($product['description']!='') ? '<span itemprop="description">'.$product['description'].'</span>' : '<meta itemprop="description" content="нет описания">';

    // мета
    $meta='';
    $meta.='<title>'.$product['name'].' - '.$parents['sgname'].' - Компания "Имидж"'.'</title>'; // было $product['name']
    //$meta.='<meta name="description" content="Цена: '.$product['cost'].' руб. '.$productSafe.'">';
    $meta.='<meta name="description" content="&#10003; '.$productSafe.' по выгодной цене: '.$product['cost'].' руб. купить с доставкой по Москве и МО в интернет-магазине &quot;Имидж&quot;. &#9990; Наш телефон- 8 (800) 555-80-54">';

    $canonical='<link rel="canonical" href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">';

    // мета: ключевые слова
    if (!isset($parentSafe)) $parentSafe = "";
    $kw=$parentSafe.' '.$productSafe;
    $kwArr=explode(' ',$kw);
    $kwArr=array_unique($kwArr);
    foreach($kwArr as $key=>$val) {
        if(mb_strlen($val,'UTF-8')<=3) unset($kwArr[$key]);
        }
    $kw=mb_strtolower(implode(', ',$kwArr),'UTF-8');
    $meta.='<meta name="keywords" content="'.$kw.'">';

    // похожие продукты: той же подгруппы
    $similarSlider='';
    $query='SELECT ID FROM products WHERE active=1 AND ID NOT IN (?i) AND parent=?s';
    $similar=$go->getCol($query,$product['ID'],$product['parent']);
    if(count($similar)>0) {
        shuffle($similar);
        $similar=$go->getAll('SELECT * FROM products WHERE active=1 AND ID IN (?a)',array_slice($similar,0,15));
        $simImg=array();
        foreach ($similar as $val) {
            $simImg=glob(FPATH.$val['uID'].'*');
            if (count($simImg)!=0) {
                usort($simImg,'imgSort');
                $img='/'.$simImg[0];
                $img = \Utilsw\Image\Image::getResizeImg($img);
                }
            else $img=NOPH;
            unset($simImg);
            if (array_key_exists($val['uID'], $prices)) $prc = $prices[$val['uID']].' руб.';
            else $prc = 'не установлена';
            $block='
            <a href="/product/'.$val['url'].'" class="d-flex flex-column align-items-center p-1 simPr text-center m-1" style="min-height:300px;background-color:white;border:1px solid #bcbebd;overflow:hidden;">
            <div class="img-carousel-sim-wrap"><div class="img-carousel-sim-innerwrap"><img src="'.$img.'" ></div></div>
            <div>'.$val['name'].'</div>
            <div class="mt-auto">'.$prc.'</div>
            </a>';
            $similarSlider.=$block;
            }
        $similarSlider='Похожие продукты: <div class="owl-carousel similar p-0 container-fluid">'.$similarSlider.'</div>';
    }



} else {
	$page='404';
	header("HTTP/1.1 404 Not Found");
    }
?>
