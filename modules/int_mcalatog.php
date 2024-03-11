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
<a href="/">Главная</a> <!-- / Категории -->
</div>

<div id="optionsBlock" class="col-12 col-md-3 order-0 order-md-2 p-0">
    <div class="optionsSticky position-sticky">
	<div class="optionsText d-block" data-target="#optionsList" data-toggle="collapse">ФИЛЬТРЫ</div>
	<div class="optionsList" id="optionsList">
		<?include('int_options.php');?>
	</div>
	<a href="/callback" style="text-decoration:none;"><div class="makeCall">Заказать звонок</div></a>
	</div>
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

<script>
    jQuery(document).ready(function (){

        if($('#optionsBlock').css('order')=='0') {
            $('#optionsList').addClass('collapse'); //.addClass('hide');
        }
    });
</script>


