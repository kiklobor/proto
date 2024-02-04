<?
//точим стили
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

//вырезаем номер страницы из ссылки и формируем базовую ссылку
$curl=$_SERVER['REQUEST_URI'];
$output1=explode('?',$curl);
parse_str($output1[1],$output2);
$curl='?';
$furl='?';
$i=1;
foreach($output2 as $key=>$value) {
    if ($key!='pagenumber' && $key!='filters') {
        $i===1 ? $curl=$curl.$key.'='.$value : $curl=$curl.'&'.$key.'='.$value;
        $i++;
    }
    if ($key!='pagenumber') {
        $i===1 ? $furl=$furl.$key.'='.$value : $furl=$furl.'&'.$key.'='.$value;
        $i++;
    }
}
$GLOBALS['basiclink']=$curl;

//echo $curl;
//СБОР ЗАПРОСА (каталог и название группы\подгруппы)
$link='';
/*
//по группам
if($_GET['target']=='products' && $_GET['level']=='1' && isset($_GET['id'])) {
	$query='SELECT g.name FROM groups g WHERE g.id='.$_GET['id'].' ORDER BY g.name';
	$group=$go->getRow($query);
	$link.=$group['name'];
	
	$from='products p, subgroups sg, groups g';
	$where='p.parent=sg.uid AND sg.parent=g.uid AND g.id='.$_GET['id'].' AND p.active=1';
	$groupby='p.ID';
	}
//по подгруппам
elseif($_GET['target']=='products' && $_GET['level']=='2' && isset($_GET['id'])) {
	$query='SELECT sg.* FROM subgroups sg, groups g WHERE sg.id='.$_GET['id'].' AND sg.parent=g.uid';
	$subgroup=$go->getRow($query);
	$query='SELECT g.name,g.ID FROM groups g WHERE g.uID="'.$subgroup['parent'].'" ORDER BY name';
    $group=$go->getRow($query);
    $link.='<a href="/catalog?target=products&level=1&id='.$group['ID'].'">'.$group['name'].'</a> / '.$subgroup['name'];
    
    $from='products p, subgroups sg';
    $where='p.parent=sg.uid AND sg.id='.$_GET['id'].' AND p.active=1';
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
    }*/

if (isset($_GET['id'])) {
    $query='SELECT g.name FROM groups g WHERE g.id='.$_GET['id'].' AND g.ind=1 ORDER BY g.name';
$group=$go->getRow($query);
$link.=$group['name'];
$from='products p, subgroups sg, groups g';
$where='p.parent=sg.uid AND sg.parent=g.uid AND g.id='.$_GET['id'].' AND g.ind=1 AND p.active=1';
$groupby='p.ID';
    }
else {
$query='SELECT g.name FROM groups g WHERE g.ind=1 ORDER BY g.name';
$group=$go->getRow($query);
$from='products p, subgroups sg, groups g';
$where='p.parent=sg.uid AND sg.parent=g.uid AND g.ind=1 AND p.active=1';
$groupby='p.ID';
}


//РАБОТАЕМ С ФИЛЬТРАМИ
if ($output2['filters']) {

    $filters=explode(',',$output2['filters']);
    $filtersIN=implode(',',$filters); //эти две операции сгенерят фейл, если со входным массивом лажа

    $ptcquery='SELECT * FROM relPropertiesToValues rpv, propertiesValues pv WHERE pv.active=1 AND rpv.active=1 AND pv.uID=rpv.vuID AND pv.ID IN ('.$filtersIN.') GROUP BY rpv.puID';
    $ptc=$go->numRows($go->query($ptcquery)); //считаем количество типов установленных фильтров, чтобы установить границу вывода через HAVING COUNT(p.ID)

    $from.=', propertiesValues pv, relProductsToValues rpv';
    $where.=' AND pv.active=1 AND rpv.active=1 AND rpv.vuID=pv.uid AND p.uID=rpv.puID  AND pv.ID IN ('.$filtersIN.')';
    $groupby.=' HAVING COUNT(p.ID)>='.($ptc);
    }

//СОБИРАЕМ ЗАПРОС
$pquery='SELECT p.* FROM '.$from.' WHERE '.$where.' GROUP BY '.$groupby.' ORDER BY p.name';

//ЗАПРАШИВАЕМ
$products=$go->getAll($pquery);
$productsCount=$go->affectedRows();

//ПАРСИМ
$cells=array();
if ($productsCount>0) {
	$i=1;
	foreach ($products as $row) {
		$cellsPrepare['name']=$row['name'];
		$cellsPrepare['id']=$row['ID'];
		$cellsPrepare['url']=$row['url'];
		$cellsPrepare['article']=$row['articleFull'];

		$images=glob(FPATH.$row['uID'].'*');
        if (count($images)!=0) {
            usort($images,'imgSort');
            $cellsPrepare['photo']=$images[0];
            }
		else $cellsPrepare['photo']=NOPH;
		
		$cells[$i]=$cellsPrepare;
		$i++;
		}
	
	//РАБОТАЕМ СО СТРАНИЦАМИ
	//определяем номер текущей страницы
	if (isset($_GET['pagenumber']) && is_numeric($_GET['pagenumber'])) $pagenumber=(integer)$_GET['pagenumber'];
    else $pagenumber=1;
	$pagesCount=ceil($productsCount/52); //считаем общее количество страниц
    if ($pagenumber>$pagesCount) $pagenumber=1; //если номер текущей страницы больше общего, ставим на 1

    $pagesBlock='';
    for($i=1;$i<=$pagesCount;$i++) if ($i===$pagenumber) $pagesBlock=$pagesBlock.'<a href="'.$furl.'&pagenumber='.$i.'"><div class="pageNumberSelect selected"><span>'.$i.'</span></div></a>';
    else $pagesBlock=$pagesBlock.'<a href="'.$furl.'&pagenumber='.$i.'"><div class="pageNumberSelect"><span>'.$i.'</span></div></a>';
    	
    $startFrom=($pagenumber-1)*52+1;
    //echo $startFrom;
    //КОНЕЦ РАБОТЫ СО СТРАНИЦАМИ
	}
?>

<div class="bread">
<a href="/">Главная</a> / Образцы по индивидуальному заказу
</div>

<div class="contentContainer col-12 col-md-9 p-0">
	<div class="catalog w-100">
		
		<div class="pagesBlock">
			Всего образцов: <b><?echo($productsCount);?></b>
		</div>
		
		<div class="catalogBlock">

		<?
		if ($productsCount>0) 
		for($i=$startFrom;$i<=($startFrom+51);$i++) {
            if(isset($cells[$i])) {
                isset($_SESSION['cart'][$cells[$i]['id']]) ? $text='В корзине!' : $text='Купить';
                echo '<div class="productCell col-6 col-md-4 col-lg-3">
                <a href="/sample/'.$cells[$i]['url'].'">
                <img src="'.$cells[$i]['photo'].'" class="productCellImg">
                </a>
                <a href="/sample/'.$cells[$i]['url'].'">
                <div><div class="productCellName">'.$cells[$i]['name'].'</div></div>
                <!--<span>Арт.: <b>'.$cells[$i]['article'].'</b></span>-->
                </a>
                </div>';
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
			<?echo($pagesBlock);?>
		</div>
	</div>
	
	<!--<div class="textBlock1">
	<div class="textBlockInner">Текст</div>
	</div>-->
</div>

<div class="col-12 col-md-3 order-1 order-md-2">
    <div class="optionsSticky position-sticky">
	<div class="optionsText">ФИЛЬТРЫ</div>
	<div class="optionsList">
		<?include('int_options.php');?>
	</div>
	<a href="/callback" style="text-decoration:none;"><div class="makeCall">Заказать звонок</div></a>
	</div>
</div>