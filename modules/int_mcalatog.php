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
 //die(11111111111111);
/*
//формируем запрос и выполняем
if ($_GET['target']==='products') {
	$query='SELECT * FROM groups WHERE active=1 ORDER BY name';
	$query2='SELECT * FROM subgroups WHERE active=1 ORDER BY name';
	$target='products';
	}
elseif ($_GET['target']==='uses') {
	$query='SELECT * FROM usesGroups WHERE active=1 ORDER BY name';
	$query2='SELECT * FROM uses WHERE active=1 ORDER BY name';
	$target='uses';
	}

$items=$go->getAll($query);
$itemsCount=$go->affectedRows();

$subitems=$go->getAll($query2);
$subitemsCount=$go->affectedRows();

//парсим
//сперва детей
$z=array();
if ($subitemsCount>0) {
	$i=1;
	foreach ($subitems as $row) {
		$SIPrepare['name']=$row['name'];
		$SIPrepare['id']=$row['ID'];
		$SIPrepare['parent']=$row['parent'];
		$SIPrepare['uid']=$row['uID'];

		if(!array_key_exists($SIPrepare['parent'],$z)) $i=1;
		$z[$SIPrepare['parent']][$i]=$SIPrepare;
		$i++;
		}
	}
else echo 'Нет дочернего содержимого.';

//потом родителей и собираем массив

$cells=array();
if ($itemsCount>0) {
	$i=1;
	foreach ($items as $row) {
		$cellsPrepare['name']='<a name="'.$row['ID'].'"><a href="/catalog?target='.$target.'&level=1&id='.$row['ID'].'"><div class="mcItem">'.$row['name'].'</div></a>';
		//$cellsPrepare['photo']='files/product_'.$row['ID'].'.png';
		$cellsPrepare['id']=$row['ID'];
		array_push($cells,$cellsPrepare);
		if(array_key_exists($row['uID'],$z)) foreach ($z[$row['uID']] as $row2) {
			$cellsPrepare['name']='<a href="/catalog?target='.$target.'&level=2&id='.$row2['id'].'"><div class="mcSubitem">'.$row2['name'].'</div></a>';
			$cellsPrepare['ID']=$row2['ID'];
			array_push($cells,$cellsPrepare);
			}
		}
	}
else echo 'Нет содержимого.';
*/

/*
if ($_GET['target']==='products' && !isset($_GET['id'])) {
	$query='SELECT * FROM groups WHERE active=1 ORDER BY name';
	$query2="SELECT g.*,sg.* FROM groups g,subgroups sg WHERE g.uID=sg.parent AND g.active=1 AND sg.active=1 AND g.ind=0 ORDER BY g.name, sg.name";
	$target='products';
	}
elseif ($_GET['target']==='products' && isset($_GET['id'])) {
    $query='SELECT * FROM groups WHERE active=1 AND ID='.$_GET['id'].' ORDER BY name';
	$query2="SELECT g.*,sg.* FROM groups g,subgroups sg WHERE g.uID=sg.parent AND g.active=1 AND sg.active=1 AND g.ID=".$_GET['id']." ORDER BY g.name, sg.name";
	$target='products';
    }
elseif ($_GET['target']==='uses') {
	$query='SELECT * FROM usesGroups WHERE active=1 ORDER BY name';
	$query2='SELECT * FROM uses WHERE active=1 ORDER BY name';
	$target='uses';
	}

//создаем массив из групп а[uid]=название, по нему будем определять переход между подгруппами
$items=$go->getAll($query);
$itemsCount=$go->affectedRows();
unset($groups);
foreach($items as $row) {
    $groups[$row['uID']]['name']=$row['name'];
    $groups[$row['uID']]['ID']=$row['ID'];
    $groups[$row['uID']]['url']=$row['url'];
}

$subitems=$go->getAll($query2);
$subitemsCount=$go->affectedRows();
unset($subgroups);
$groupCheck='';
$cells=array();
$i=1;
foreach($subitems as $row) {
	if ($row['parent']!=$groupCheck) {
	    $groupCheck=$row['parent'];
	    //$toPush='<a href="/catalog?target='.$target.'&level=1&id='.$groups[$row['parent']]['ID'].'"><div class="mcItem">'.$groups[$row['parent']]['name'].'</div></a>';
     $toPush='<a href="/catalog/'.$groups[$row['parent']]['url'].'/"><div class="mcItem">'.$groups[$row['parent']]['name'].'</div></a>';
	    array_push($cells,$toPush);
	}
 //$toPush='<a href="/catalog?target='.$target.'&level=2&id='.$row['ID'].'"><div class="mcSubitem">'.$row['name'].'</div></a>';
 $toPush='<a href="/catalog/'.$groups[$row['parent']]['url'].'/'.$row['url'].'/"><div class="mcSubitem">'.$row['name'].'</div></a>';
	array_push($cells,$toPush);
}
*/

global $category;
$catarr = $category::$catarr;

function build_tree($cats, $parent_id, $only_parent = false, $path = array()){
		if(is_array($cats) and isset($cats[$parent_id])){
				//$tree = '<div>';
				$tree = '';
				if($only_parent==false){
						if ($parent_id!=0) { $tree .= '<div class="cat-wrp">'; }
						foreach ($cats[$parent_id] as $cat) {
								$urltmp = '';
								if (count($path)>0) {
									$urltmp = implode('/',$path).'/';
								}
								if (isset($cats[$cat['uID']])) {
									$tree .= '<div class="category-parent-wrp"><div class="category-name"><a href="/catalog/'.$urltmp.$cat['url'].'/">'.$cat['Name'].'</a></div>';
									array_push($path, $cat['url']);
									$tree .=  build_tree($cats,$cat['uID'], false, $path);
									array_pop($path);
									$tree .= '</div>';
								} else {
									$tree .= '<div class="category-name"><a href="/catalog/'.$urltmp.$cat['url'].'/">'.$cat['Name'].'</a></div>';
								}
						}
						if ($parent_id!=0) { $tree .= '</div>'; }
				}elseif($only_parent !==""){
						$cat = $cats[$parent_id][$only_parent];
						$tree .= '<li>'.$cat['Name'].' #'.$cat['uID'];
						$tree .=  build_tree($cats,$cat['uID']);
						$tree .= '</li>';
				}
				//$tree .= '</div>';
		}
		else return null;
		return $tree;
}

$menu = build_tree($catarr,0);

?>

<div class="bread">
<a href="/">Главная</a> / Категории
</div>

<div class="contentContainer col-12 col-md-9 p-0">
	<div class="catalog w-100">
		<div class="tabContainer d-flex flex-row flex-nowrap">

			<div class="tabBlock col-6 col-md-9 d-flex justify-content-center align-items-center<?echo($style2);?>">
			<a href="/catalog/"><div class="tabText">Продукция</div></a><!--mcatalog?target=products-->
			</div>

			<div class="tabBlock col-6 col-md-3 d-flex justify-content-center align-items-center">
			<a href="/customs"><div class="tabText">Продукция<br>по параметрам<br>заказчика</div></a>
			</div>
		</div>

		<div class="mcatalogBlock t1">
			<?php echo $menu; ?>
		</div>

		<!--<div class="mcatalogBlock t1">
        <?php //foreach ($cells as $row) echo '<div class="mcatalogCell">'.$row.'</div>';?>
		</div>-->
	</div>

	<!--<div class="textBlock1">
	<div class="textBlockInner">Текст</div>
	</div>-->

</div>

<div class="col-12 col-md-3 order-1 order-md-2 p-0">
    <div class="optionsSticky position-sticky">
	<div class="optionsText">ФИЛЬТРЫ</div>
	<div class="optionsList">
		<?include('int_options.php');?>
	</div>
	<a href="/callback" style="text-decoration:none;"><div class="makeCall">Заказать звонок</div></a>
	</div>
</div>
