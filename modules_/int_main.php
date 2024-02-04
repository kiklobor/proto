<?
$usesGroups = $go->getAll("SELECT * FROM usesGroups WHERE active=1 ORDER BY name");

$groups=$go->getAll("SELECT sg.ID sgID, sg.name sgName, sg.parent parent, g.name gName, g.ID gID, sg.url sgurl, g.url gurl FROM subgroups sg, groups g WHERE sg.active=1 AND g.active=1 AND sg.parent=g.uID AND g.ind=0 ORDER BY g.route,g.name,sg.route,sg.name");
$groupsArr=array();
$sgCheck='';
foreach($groups as $val) {
    if($val['parent']!=$sgCheck) {
        $sgCheck=$val['parent'];
        array_push($groupsArr,'<div class="catalogCell"><div class="cItem" g="'.$val['gID'].'">'.$val['gName'].'</div></div>');
    }
    //array_push($groupsArr,'<div class="catalogCell"><a href="/catalog?target=products&level=2&id='.$val['sgID'].'"><div class="cSubitem" g="'.$val['gID'].'">'.$val['sgName'].'</div></a></div>');
    array_push($groupsArr,'<div class="catalogCell"><a href="/catalog/'.$val['gurl'].'/'.$val['sgurl'].'/"><div class="cSubitem" g="'.$val['gID'].'">'.$val['sgName'].'</div></a></div>');
}

$inds=$go->getAll("SELECT g.name gName, g.ID gID FROM groups g WHERE g.active=1 AND g.ind=1 ORDER BY g.name");
$indsArr=array();
foreach($inds as $val) array_push($indsArr,'<div class="catalogCell" style="display:block;"><a href="/ccatalog?id='.$val['gID'].'&level=1"><div class="cSubitem" style="text-align:center;padding: 20px 30px;font-size: .9rem;display:block;">'.$val['gName'].'</div></a></div>');
/*
$query='SELECT ID FROM products WHERE active=1';
$products=$go->getCol($query);
if(count($products)>0) {
        shuffle($products);
        $products=array_slice($products,0,20);
        $productsArr=implode(',',$products);
        $query='SELECT * FROM products WHERE active=1 AND ID IN ('.$productsArr.')';
        $products=$go->getAll($query);
        $productsArr=array();
        $prImg=array();
        foreach ($products as $val) {
            $prImg=glob('files/'.$val['uID'].'*');
            if(isset($prImg[0])) $img=$prImg[0];
            else $img='styles/images/noPhoto.png';
            unset($prImg);
            array_push($productsArr,'
            <a href="/product/'.$val['url'].'" class="d-flex row text-center flex-column m-1 p-1" style="height:300px;background-color:white;box-shadow:0 0 2px #a8afb1;overflow:hidden;display:flex;text-align:center;justify-content: center;flex-direction: column!important;padding:.25rem;flex-wrap: wrap;margin:0.2rem;">
            <img class="d-flex" style="width:100%;max-height:200px;" src="'.$img.'">
            <div class="d-flex">'.$val['name'].'</div>
            </a>');
            //array_push($productsArr,'<div class="">'.$val['name'].'</div>');
            }
        }
else $productsArr=false;*/
//<div class="mt-2 mb-4 p-0 owl-carousel owl-theme">

/*$query='SELECT uID FROM products WHERE active=1';
$products=$go->getCol($query);
if(count($products)>0) {
        shuffle($products);
        $productsArr=array();
        $missed++;
        foreach ($products as $val) {
            if (count(glob('files/'.$val.'*'))>0) array_push($productsArr,'"'.$val.'"');
            else $missed++;
            if ((count($productsArr))>=20 || $missed>=500) break;
            }

        $productsArr=implode(',',$productsArr);
        $query='SELECT * FROM products WHERE active=1 AND uID IN ('.$productsArr.')';
        $products=$go->getAll($query);
        $productsArr=array();

        foreach ($products as $val) {
            $prImg=glob('files/'.$val['uID'].'*');
            if (isset($prImg[0])) $img=$prImg[0];
            else $img='styles/images/noPhoto.png'; //на всякий случай все равно оставил проверку на наличие фоток
            unset($prImg);
            array_push($productsArr,'
            <a href="/product/'.$val['url'].'" class="d-flex row text-center flex-column m-1 p-1" style="height:300px;background-color:white;box-shadow:0 0 2px #a8afb1;overflow:hidden;display:flex;text-align:center;justify-content: center;flex-direction: column!important;padding:.25rem;flex-wrap: wrap;margin:0.2rem;">
            <img class="d-flex" style="width:100%;max-height:200px;" src="'.$img.'">
            <div class="d-flex">'.$val['name'].'</div>
            </a>');
            }
        }
else $productsArr=false;*/
$limit=20; //сколько товаров выбирать в слайдер
$missLimit=1000; //сколько товаров перебирать максимум
//выбираем товары за последнюю неделю

$query='SELECT uID FROM products WHERE active=1 AND startDate>=DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH) ORDER BY startDate DESC LIMIT ?i';
$products=$go->getCol($query,$limit);
$productsArr = array();
$usedUIDs = array();
$productsArrFinal = array();
if(count($products)>0) {
    $missed=0;
    foreach ($products as $val) {
            if (count(glob(FPATH.$val.'*'))>0) array_push($productsArr,'"'.$val.'"');
            else $missed++;
            if ((count($productsArr))>=$limit || $missed>=$missLimit) break;
            }
    if (count($productsArr)>0) {
        $usedUIDs=$productsArr;
        $productsArr=implode(',',$productsArr);
        $query='SELECT * FROM products WHERE active=1 AND uID IN ('.$productsArr.')';
        $products=$go->getAll($query);
        $productsArr=array();

        foreach ($products as $val) {
            $prImg=glob(FPATH.$val['uID'].'*');
            if (count($prImg)>0) {
                usort($prImg,'imgSort');
                $img=$prImg[0];
                $img = \Utilsw\Image\Image::getResizeImg($img);

            } else $img='styles/images/noPhoto.png';
            unset($prImg);
            // if ($val['cost']==0) $cost='<div>&nbsp;</div>';
            // else $cost='<div style="font-size:1rem">'.$val['cost'].' руб.</div>';
            $cost = (isset($prices[$val['uID']]) && ($prices[$val['uID']]>0))? '<div class="mt-auto" style="font-size:1rem">'.$prices[$val['uID']].' руб.</div>' : '<div>&nbsp;</div>';
            array_push($productsArrFinal,'
            <a href="/product/'.$val['url'].'" class="d-flex text-center flex-column flex-nowrap m-1 p-1 overflow-hidden" style="min-height:320px;background-color:white;box-shadow:0 0 2px #a8afb1;text-decoration:none;"><!-- justify-content-between -->
            <div class="newItemBar">Новинка</div>
            <div class="img-carousel-wrap"><div class="img-carousel-inner-wrap"><img style="" src="'.$img.'"></div></div>
            <div>'.$val['name'].'</div>'.$cost.'
            </a>');
            }
        }
    }

//довыбираем случайные продукты
$limit=$limit-count($productsArrFinal);
$missed=0;
$productsArr=array();
$query='SELECT uID FROM products WHERE active=1';
$products=$go->getCol($query);
if(count($products)>0) {
        shuffle($products);
        $missed++;
        foreach ($products as $val) {
            if ((count(glob(FPATH.$val.'*'))>0) && !in_array($val,$usedUIDs)) array_push($productsArr,'"'.$val.'"');
            else $missed++;
            if ((count($productsArr))>=$limit || $missed>=$missLimit) break;
            }
        if (count($productsArr)>0) {
        $productsArr=implode(',',$productsArr);
        $query='SELECT * FROM products WHERE active=1 AND uID IN ('.$productsArr.')';
        $products=$go->getAll($query);
        $productsArr=array();

        foreach ($products as $val) {
            $prImg=glob(FPATH.$val['uID'].'*');
            if (count($prImg)>0) {
                usort($prImg,'imgSort');
                $img=$prImg[0];
                $img = \Utilsw\Image\Image::getResizeImg($img);

            } else $img='styles/images/noPhoto.png';
            unset($prImg);
            // if ($val['cost']==0) $cost='<div>&nbsp;</div>';
            // else $cost='<div style="font-size:1rem">'.$val['cost'].' руб.</div>';
            $cost = (isset($prices[$val['uID']]) && ($prices[$val['uID']]>0))? '<div class="mt-auto" style="font-size:1rem">'.$prices[$val['uID']].' руб.</div>' : '<div>&nbsp;</div>';
            array_push($productsArrFinal,'
            <a href="/product/'.$val['url'].'" class="d-flex text-center flex-column flex-nowrap m-1 p-1 overflow-hidden" style="min-height:320px;background-color:white;box-shadow:0 0 2px #a8afb1;text-decoration:none;"><!-- justify-content-between -->
            <div class="img-carousel-wrap"><div class="img-carousel-inner-wrap"><img src="'.$img.'"></div></div>
            <div>'.$val['name'].'</div>'.$cost.'
            </a>');
            }
        }
        }
else $productsArr=false;

//вывод назначений
/*foreach ($usesGroups as $row){
			echo '<a href="/mcatalog?target=uses#'.$row['ID'].'"><div class="blockListRow">'.$row['name'].'</div></a>';
			}*/

if (count($productsArrFinal)>0) {
    echo '<div class="col-12 mt-2 mb-1 p-0 owl-carousel owl-theme main" style="box-sizing:border-box">';
    foreach ($productsArrFinal as $row) echo $row;
    echo '</div>';
    }

  //$catarr = getCatArr($go);
  //$category = new \URL\Category($go);
  global $category;
  $catarr = $category::$catarr;

  $catarr = $category->calc_prod_cat($go, $catarr, 0);

  /*
  echo '<pre>';
  var_dump($catarr);
  echo '</pre>';
  /**/

  function build_tree($cats,$parent_id,$only_parent = false){
      if(is_array($cats) and isset($cats[$parent_id])){
          $tree = '<ul>';
          if($only_parent==false){
              foreach($cats[$parent_id] as $cat){
                  $tree .= '<li>'.$cat['Name'].' #'.$cat['uID'];
                  $tree .=  build_tree($cats,$cat['uID']);
                  $tree .= '</li>';
              }
          }elseif($only_parent !==""){
              $cat = $cats[$parent_id][$only_parent];
              $tree .= '<li>'.$cat['Name'].' #'.$cat['uID'];
              $tree .=  build_tree($cats,$cat['uID']);
              $tree .= '</li>';
          }
          $tree .= '</ul>';
      }
      else return null;
      return $tree;
  }
  //$menu = build_tree($catarr,0);
  //echo $menu;

  function build_tree1($cats, $parent_id, $only_parent = false, $path = array(), $starturl = "catalog"){
      if(is_array($cats) and isset($cats[$parent_id])){
          //$tree = '<div>';
          $tree = '';
          if($only_parent==false){
              foreach($cats[$parent_id] as $cat){
                  if (isset($cats[$cat['uID']])) {
                    $tree .= '<div class="category-parent-wrp"><div class="catalogCell"><div class="cItem" g="'.$cat['ID'].'">'.$cat['Name'].'</div></div>';
                    array_push($path, $cat['url']);
                    $tree .=  build_tree1($cats,$cat['uID'], false, $path, $starturl);
                    array_pop($path);
                    $tree .= '</div>';
                  } else {
                    $urltmp = '';
                    if (count($path)>0) {
                      $urltmp = implode('/',$path).'/';
                    }
                    $tree .= '<div class="catalogCell"><a href="/'.$starturl.'/'.$urltmp.$cat['url'].'/"><div class="cSubitem" g="'.$cat['ID'].'">'.$cat['Name'].'</div></a></div>';
                  }

              }
          }elseif($only_parent !==""){
              $cat = $cats[$parent_id][$only_parent];
              $tree .= '<li>'.$cat['Name'].' #'.$cat['uID'];
              $tree .=  build_tree1($cats,$cat['uID'], FALSE, array(), $starturl);
              $tree .= '</li>';
          }
          //$tree .= '</div>';
      }
      else return null;
      return $tree;
  }

  function build_tree2($cats, $parent_id, $only_parent = false, $path = array(), $starturl = "catalog"){
      if(is_array($cats) and isset($cats[$parent_id])){
          //$tree = '<div>';
          $tree = '';
          if($only_parent==false){
              if ($parent_id!=0) {
                $tree .= '<div class="category-wrp a1">';
                if (array_key_exists($parent_id, $cats[0])) { $tree .= '<span>&times;</span>'; }
              }
              foreach ($cats[$parent_id] as $cat) {
                  if (isset($cats[$cat['uID']])) {
                    if ($cat['parent']!=0) {
                      $urltmp = '';
                      if (count($path)>0) {
                        $urltmp = implode('/',$path).'/';
                      }
                      $tree .= '<div class="category-parent-wrp a2"><div class="catalogCell"><a href="/'.$starturl.'/'.$urltmp.$cat['url'].'/"><div class="cSubitem" g="'.$cat['ID'].'">'.$cat['Name'].' ('.$cat['productcount'].')</div></a></div>';
                      array_push($path, $cat['url']);
                      $tree .=  build_tree2($cats,$cat['uID'], false, $path, $starturl);
                      array_pop($path);
                      $tree .= '</div>';
                    } else {
                      if (array_key_exists($cat['uID'], $cats))
                        $tree .= '<div class="category-parent-wrp a3"><div class="catalogCell"><div class="cItem" g="'.$cat['ID'].'">'.$cat['Name'].' ('.$cat['productcount'].')</div></div>';
                      else
                        $tree .= '<div class="category-parent-wrp a3 sce"><div class="catalogCell"><div class="cItem" g="'.$cat['ID'].'">'.'<a href="/'.$starturl.'/'.$cat['url'].'/">'.$cat['Name'].' ('.$cat['productcount'].')</a>'.'</div></div>';
                      array_push($path, $cat['url']);
                      $tree .=  build_tree2($cats,$cat['uID'], false, $path, $starturl);
                      array_pop($path);
                      $tree .= '</div>';
                    }
                  } else {
                    $urltmp = '';
                    if (count($path)>0) {
                      $urltmp = implode('/',$path).'/';
                    }
                    if ($parent_id==0) {
                      $tree .= '<div class="category-parent-wrp a3 sce"><div class="catalogCell"><div class="cItem" g="'.$cat['ID'].'">'.'<a href="/'.$starturl.'/'.$cat['url'].'/">'.$cat['Name'].' ('.$cat['productcount'].')</a>'.'</div></div></div>';
                    } else {
                      $tree .= '<div class="catalogCell"><a href="/'.$starturl.'/'.$urltmp.$cat['url'].'/"><div class="cSubitem" g="'.$cat['ID'].'">'.$cat['Name'].' ('.$cat['productcount'].')</div></a></div>';
                    }
                  }
              }
              if ($parent_id!=0) { $tree .= '</div>'; }
          }elseif($only_parent !==""){
              $cat = $cats[$parent_id][$only_parent];
              $tree .= '<li>'.$cat['Name'].' #'.$cat['uID'];
              $tree .=  build_tree1($cats,$cat['uID'], FALSE, array(), $starturl);
              $tree .= '</li>';
          }
          //$tree .= '</div>';
      }
      else return null;
      return $tree;
  }


  //$parenta = find_parent($catarr, "cf168e62-7a11-11ec-a7e3-bc97e1eed341");
  //$catarr = set_cat_path($catarr,0);
  $menu = build_tree2($catarr, 0, FALSE, array(), 'catalog');
//array_push($groupsArr,'<div class="catalogCell"><div class="cItem" g="'.$val['gID'].'">'.$val['gName'].'</div></div>');
//array_push($groupsArr,'<div class="catalogCell"><a href="/catalog/'.$val['gurl'].'/'.$val['sgurl'].'/"><div class="cSubitem" g="'.$val['gID'].'">'.$val['sgName'].'</div></a></div>');

  /*
  echo '<pre>';
  var_dump($parenta);
  echo '</pre>';
  /**/
  /*
  echo '<pre>';
  var_dump($catarr[0]);
  echo '</pre>';
  /**/

  ?>


  <div class="col-12 col-md-9 p-0 order-2 order-md-1">
  	<div class="contentBlock maincat d-flex flex-column flex-sm-row flex-wrap">
    	<div class="productsBlock col-12 col-sm-12 pb-1 pb-sm-4">
    		<a href="/catalog/"><div class="blockText">КАТАЛОГ ПРОДУКЦИИ</div></a>
    		<div class="blockList maincatwrp">
    		<?php
          //foreach ($groupsArr as $row) echo $row;
          echo $menu;
        ?>
    		</div>
    	</div>
  	</div>

  	<!--<div class="textBlock1">
  	<div class="textBlockInner">Текст</div>
  	</div>

  	<div class="textBlock2">
  	<div class="textBlockInner">О сайте</div>
  	</div>-->
    <div class="contentBlock maincat-service d-flex flex-column flex-sm-row flex-wrap"><!-- style="display:none  !important;" -->
      <div class="productsBlock col-12 col-sm-12 pb-1 pb-sm-4">
        <a href="/services/"><div class="blockText">КАТАЛОГ УСЛУГ</div></a>
        <div class="blockList maincatservicewrp">
          <?php
           //var_dump($categoryservices::$catarr);
           //var_dump($categoryservices);
          global $categoryservices;
          $catservarr = $categoryservices::$catarr;
          $catservarr = $categoryservices->calc_serv_cat($go, $catservarr, 0);
          $servicemenu = build_tree2($catservarr, 0, FALSE, array(), 'services');
          echo $servicemenu;
          ?>
        </div>
      </div>
    </div>
	
	
	
	
	    <div class="contentBlock maincat-service d-flex flex-column flex-sm-row flex-wrap"><!-- style="display:none  !important;" -->
      <div class="productsBlock col-12 col-sm-12 pb-1 pb-sm-4">
        <a href="/params/"><div class="blockText">КАТАЛОГuuuuuu ПРОДУКЦИИ ПО ПАРАМЕТРАМ ЗАКАЗЧИКА</div></a>
        <div class="blockList maincatservicewrp">
          <?php
		      global $categoryparams;
           //var_dump($categoryparams::$catarr);
//var_dump($categoryparams);
      
          $catservarr = $categoryparams::$catarr;
          $catservarr = $categoryparams->calc_param_cat($go, $catservarr, 0);
          $servicemenu = build_tree2($catservarr, 0, FALSE, array(), 'params');
          echo $servicemenu;
          ?>
        </div>
      </div>
    </div>
  </div>

<!--div class="col-12 col-md-9 p-0 order-2 order-md-1">
	<div class="contentBlock d-flex flex-column flex-sm-row flex-wrap">

	<div class="productsBlock col-12 col-sm-12 pb-1 pb-sm-4">
		<a href="/catalog/"><div class="blockText">КАТАЛОГ ПРОДУКЦИИ</div></a>
		<div class="blockList">
		<?//foreach ($groupsArr as $row) echo $row;?>
		</div>
	</div>

	</div>
</div-->

<div id="optionsBlock" class="col-12 col-md-3 order-1 order-md-2 p-0 pb-1">


  <div class="optionsSticky position-sticky">
  <!--<a class="d-block d-sm-none" data-toggle="collapse" href="#optionsList" role="button" aria-expanded="true" aria-controls="optionsList"><div class="optionsText">ФИЛЬТРЫ</div></a>-->
	<div class="optionsText d-block" data-target="#optionsList" data-toggle="collapse">ФИЛЬТРЫ</div>
	<div class="optionsList" id="optionsList">
		<?include('int_options.php');?>
	</div>
	<div class="customBlock rightside col-12">
		<div class="customBlockText1">Продукция<br>по параметрам<br>заказчика</div>
		<?if ($indsArr) foreach ($indsArr as $row) echo $row;?>
		<a href="/customs"><div class="makeCall col-11 col-md-9 p-0 mb-2 mb-sm-3">Оставить заявку</div></a>
		<!--<div class="customBlockText3">Составить описание</div>
		<input class="customBlockInput" type="text">-->
	</div>
	<a href="/callback" style="text-decoration:none;"><div class="makeCall">Заказать звонок</div></a>
	</div>
</div>

<script>
    jQuery(document).ready(function (){

        if($('#optionsBlock').css('order')=='1') {
            $('#optionsList').addClass('collapse'); //.addClass('hide');
        }
        //else $('#optionsList').show();

        $('.cItem1').click(function (){
            group=$(this).attr('g');
            $(this).toggleClass('cItemOpened');
            gStr='g=';
            $('.cItemOpened').each(function (){
                gStr=gStr+','+$(this).attr('g');
            });
            console.log(gStr);
        		jQuery.ajax({
        			type: "GET",
        			dataType: 'json',
        			cache: false,
        			url: "/ajax/ajax_filtersMain.php",
        			data: gStr
        			})
        			.done(function(result) {
        			    //console.log(result.status);
        				if (result.status==1) {
          				optionsArr=result.content;
          				$('.optionsListSubrow').each(function (){
          				    if (optionsArr.indexOf($(this).attr('propid'))==-1) $(this).hide().removeClass('selectedSubrow').attr('available','0');
          				    else $(this).show(500).attr('available','1');
          				    });
          				findEmptyOptionsContainer('');
        				}
        				else {
        				    $('.optionsListRow[basicshow=1]').show();
        				    $('.optionsListRow[basicshow=0]').hide();
        				}
        			});
            $('.cSubitem[g='+group+']').toggle(500);
        });

        jQuery('.maincatwrp > .category-parent-wrp > .category-wrp > span').on("click", function (){
          th = jQuery(this);
          console.log(th);
          pwrp = th.closest('.category-parent-wrp');
          console.log(pwrp);
          pwrp.children('.catalogCell').find('.cItem').click();
          clearmainfilter();
        });

        jQuery('.maincatwrp .category-parent-wrp .cItem').click(function (){
            th = jQuery(this);
            pwrp = th.closest('.category-parent-wrp');
            catblock = th.closest('.maincatwrp');

            group=th.attr('g');
            wasactive = th.hasClass('cItemOpened');

            catblock.find('.category-parent-wrp .cItem').removeClass('cItemOpened');
            catblock.find('.category-parent-wrp').removeClass('wrpopened');

            if (!wasactive) {
              th.toggleClass('cItemOpened');
              pwrp.toggleClass('wrpopened');
            } else {
              clearmainfilter();
            }
            ccahref = th.closest('.catalogCell').next().find('.cSubitem');
            //if (ccahref.length>0) ccahref.toggle(500);
            //pwrp.children('.category-parent-wrp').toggle(500);
            gStr='g=';
            $('.cItemOpened').each(function (){
                gStr=gStr+','+$(this).attr('g');
            });
            console.log(gStr);
        		jQuery.ajax({
        			type: "GET",
        			dataType: 'json',
        			cache: false,
        			url: "/ajax/ajax_filtersMain.php",
        			data: gStr
        			})
        			.done(function(result) {
        			    //console.log(result.status);
        				if (result.status==1) {
          				optionsArr=result.content;
          				$('.optionsListSubrow').each(function (){
          				    if (optionsArr.indexOf($(this).attr('propid'))==-1) $(this).removeClass('selectedSubrow').addClass('hidebycategory hide').attr('available','0');
          				    else $(this).attr('available','1').removeClass('hidebycategory hide');
                  });
          				findEmptyOptionsContainer('hidebycategory');
        				}
        				else {
        				    $('.optionsListRow[basicshow=1]').removeClass('hide');
        				    $('.optionsListRow[basicshow=0]').addClass('hide');
        				}
              });
            //  th.find('.cSubitem[g='+group+']').toggle(500);
        });

        function clearmainfilter() {
          jQuery('.optionsListRow').each(function (){
            jQuery(this).removeClass('hide hidebycategory');
          });
          jQuery('.optionsListSubrow').each(function (){
            jQuery(this).removeClass('hide hidebycategory');
          });
          findEmptyOptionsContainer('');
        }

        jQuery('.maincatservicewrp .category-parent-wrp .cItem').click(function (){
            th = jQuery(this);
            pwrp = th.closest('.category-parent-wrp');
            catblock = th.closest('.maincatservicewrp');

            group=th.attr('g');
            wasactive = th.hasClass('cItemOpened');

            catblock.find('.category-parent-wrp .cItem').removeClass('cItemOpened');
            catblock.find('.category-parent-wrp').removeClass('wrpopened');

            if (!wasactive) {
              th.toggleClass('cItemOpened');
              pwrp.toggleClass('wrpopened');
            } else {
              //clearmainfilter();
            }
        });
        jQuery('.maincatservicewrp > .category-parent-wrp > .category-wrp > span').on("click", function (){
          th = jQuery(this);
          // console.log(th);
          pwrp = th.closest('.category-parent-wrp');
          // console.log(pwrp);
          pwrp.children('.catalogCell').find('.cItem').click();
          // clearmainfilter();
        });


    });
</script>
