<?


//echo count($urlArrf);

// Set active tab styles based on the 'target' GET parameter
$target = isset($_GET['target']) ? $_GET['target'] : '';

if ($target === 'uses') {
    $style1 = ' activeTab';
    $style2 = '';
} elseif ($target === 'products') {
    $style1 = '';
    $style2 = ' activeTab';
} else {
    $style1 = '';
    $style2 = '';
}



//var_dump($categoryparams);
$catarr = $categoryparams::$catarr;

// Recursive function to build a hierarchical category structure
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
									$tree .= '<div class="category-parent-wrp"><div class="category-name"><a href="/params/'.$urltmp.$cat['url'].'/">'.$cat['Name'].'</a></div>';
									array_push($path, $cat['url']);
									$tree .=  build_tree($cats,$cat['uID'], false, $path);
									array_pop($path);
									$tree .= '</div>';
								} else {
									$tree .= '<div class="category-name"><a href="/params/'.$urltmp.$cat['url'].'/">'.$cat['Name'].'</a></div>';
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

$menu = build_tree($catarr, 0);

?>

<div class="bread">
    <a href="/">Главная</a> / Образцы
</div>

<div class="contentContainer col-12 col-md-9 p-0">
    <div class="catalog w-100">
        <div class="mcatalogBlock t1">
            <?php echo $menu; ?>
        </div>
    </div>
</div>
