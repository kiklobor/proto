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

if (isset($_GET['pagenumber']) && is_numeric($_GET['pagenumber'])) $pagenumber=$_GET['pagenumber'];
else $pagenumber=1;



//создаем блок со страницами
$query='SELECT * FROM products WHERE active=1';
$pagesBase=$go->getAll($query);
$pagesCount=ceil(($go->affectedRows())/52);
if ($pagenumber>$pagesCount) $pagenumber=1;

echo $pagenumber;
$pagesBlock='';
for($i=1;$i<=$pagesCount;$i++) {
	if ($i==$pagenumber) $pagesBlock=$pagesBlock.'<div class="pageNumberSelect selected">'.$i.'</div>';
	else $pagesBlock=$pagesBlock.'<div class="pageNumberSelect">'.$i.'</div>';
	}


$startFrom=($pagenumber-1)*52; //точка начала запроса, исходя из номера страницы
//формируем запрос и выполняем
$query='SELECT * FROM products WHERE active=1 AND ID>?i LIMIT 52';
$products=$go->getAll($query,$startFrom);
$productsCount=$go->affectedRows();

//парсим
$cells=array();
if ($productsCount>0) {
	$i=1;
	foreach ($products as $row) {
		$cellsPrepare['name']=$row['name'];
		$cellsPrepare['photo']='files/product_'.$row['ID'].'.png';
		$cellsPrepare['id']=$row['ID'];
		if (!file_exists($cellsPrepare['photo'])) $cellsPrepare['photo']='styles/images/noPhoto.png';
		array_push($cells,$cellsPrepare);
		}
	}
else echo 'Нет содержимого.';


?>

<div class="bread">
<a href="/">Главная</a>/<!--<a href="/catalog?target=products">Продукция</a>/-->
</div>

<div class="contentContainer">
	<div class="catalog">
		<div class="tabContainer">
			<div class="usesBlockTab tabBlock<?echo($style1);?>">
			<a href="/catalog?target=uses"><div class="tabText">Назначения</div></a>
			</div>
	
			<div class="productsBlockTab tabBlock<?echo($style2);?>">
			<!--<a href="/catalog?target=products"><div class="tabText">Продукция</div></a>-->
			</div>
	
			<div class="customBlockTab tabBlock">
			<a href="/customs"><div class="tabText">Продукция<br>по параметрам<br>заказчика</div></a>
			</div>
		</div>
		
		<div class="catalogBlock">

		<?
		foreach ($cells as $row) {
			if (isset($_SESSION['cart'][$row['id']])) $text='В корзине!';
			else $text='Купить';
			echo '<div class="productCell"><img src="'.$row['photo'].'" class="productCellImg"><br>'.$row['name'].'<br><button class="buy greenGradient" product="'.$row['id'].'">'.$text.'</button></div>';
			}
		?>

		</div>
		<div class="pagesBlock">
			<?echo($pagesBlock);?>
		</div>
	</div>
	
	<div class="textBlock1">
	<div class="textBlockInner">Текст</div>
	</div>

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