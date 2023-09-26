<?
define('LROOT', $_SERVER['DOCUMENT_ROOT']);

if (require('../scripts/setup.php')) $go=new SafeMySQL();
else die('Ошибка.');
mb_internal_encoding('UTF-8');

include_once(LROOT.'/scripts/prg_catalog_hierarchy.php');
///global $category;
$category = new \URL\Category($go);

$result=array();
$result['status']=1;
$result['content']=array();

$filters=$_GET['filters']; //для лайва

if ($filters=='') {
    $result['status']=0;
    print_r($result);
    die();
}

$url=$_GET['url']; //для лайва

//$filters=',5,11,143'; //для теста
$group = array();
if ($url!='') {
 $parsed_url = parse_url(base64_decode($url));
 $query    = isset($parsed_url['query']) ? $parsed_url['query'] : '';
 $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
 $isSemanticUrl = FALSE;
 if ($query!='') {
  parse_str($query, $output);
  // чпу?
  if (!array_key_exists('target', $output)) {
    $isSemanticUrl = TRUE;
  }
 }
 // если главное с выбранной группой
 if (array_key_exists('g', $_GET) AND $_GET['g']!='') {
   $cgids = explode(',', $_GET['g']);
   //var_dump($cgids);
   foreach ($cgids as $cgid) {
     $cuid = $category->getcatuidbyid($cgid);
     //var_dump($cuid);
     if ($cuid!='') {
       $catmp = $category->getallhierarchycatuids($category::$catarr, $cuid);
       //var_dump($catmp);
       if (count($catmp)>0) {
         $group = array_merge($group, $catmp);
       }
     }
   }
   //var_dump($group);
 } elseif (!$isSemanticUrl) {
    $level = isset($output['level']) ? $output['level'] : '';
    $id = isset($output['id']) ? $output['id'] : '';
    if ($level==2 AND $id!='') {
          $query = "SELECT sg.uID FROM subgroups sg WHERE sg.active=1 AND sg.id=".$id;
          $group = $go->getCol($query);
    } elseif ($level==1 AND $id!='') {
          $query = "SELECT sg.uID FROM subgroups sg WHERE "
                    ." sg.active=1 AND sg.parent IN (SELECT g.uID FROM groups g WHERE g.active=1 AND g.id=".$id.")";  //parent
          $group = $go->getCol($query);
    }
 } else {
  $urlArr=explode('/', $path);
  $urlArr = array_filter($urlArr);
  $urlArrfl = $urlArr;
	$cat = array_shift($urlArrfl);
	if ($cat != 'catalog') array_unshift($urlArrfl, $cat);
  $subcat = $category->getlastcat($urlArrfl);
  //$group = $category->getlastcatuids($category::$catarr, $subcat['uID']);
  $group = $category->getallhierarchycatuids($category::$catarr, $subcat['uID']);
  /*
  if (count($urlArr)>3) {

    $subcat = $category->getlastcat($urlArrfl);
    //$group = $category->getlastcatuids($category::$catarr, $subcat['uID']);
    $group = $category->getallhierarchycatuids($category::$catarr, $subcat['uID']);

  } elseif (count($urlArr)>2) {
          $query = "SELECT sg.uID FROM subgroups sg WHERE sg.url='".$urlArr[3]."' "
                    ." AND sg.active=1";  //parent
                    //." AND sg.active=1 AND sg.parent IN (SELECT g.uID FROM groups g WHERE g.active=1 AND g.url='".$urlArr[2]."')";  //parent
          $group = $go->getCol($query);
  } elseif (count($urlArr)>1) {
          $query = "SELECT sg.uID FROM subgroups sg WHERE "
                    ." sg.active=1 AND sg.parent IN (SELECT g.uID FROM groups g WHERE g.active=1 AND g.url='".$urlArr[2]."')";  //parent
          $group = $go->getCol($query);
  }
  /**/
 }
 //var_dump(parse_url(base64_decode($url)));
 //parse_str($str, $output);
 //var_dump($urlArr);
 //var_dump($group);
 //die();
}

//$filters=mb_substr($filters,1); //удаляем запятую в начале
$filtersArr=explode(',',$filters); //делаем массив с фильтрами
$filtersArr = array_filter($filtersArr);//убрать пустое
//print_r($filtersArr);
$filters=implode(',',$filtersArr);

$types=count($filtersArr);

//$query='SELECT * FROM propertiesValues pv WHERE ID IN ('.$filters.') AND pv.active=1 ORDER BY ID';
//$query='SELECT rpv.puID FROM propertiesValues pv, relProductsToValues rpv WHERE pv.ID IN ('.$filters.') AND pv.active=1 AND rpv.active=1 AND rpv.vuID=pv.uID GROUP BY rpv.puID';
//$query='SELECT rpv.puID FROM propertiesValues pv, relProductsToValues rpv WHERE pv.active=1 AND rpv.active=1 AND rpv.vuID=pv.uid AND pv.ID IN ('.$filters.') GROUP BY rpv.puID HAVING COUNT(rpv.puID)>=?i'; //это верный запрос, надо считать аргумент для HAVING COUNT

// определяем "подгруппы свойств", чтобы определить сколько у товаров должно быть совпадений(сгруппируем по ID и подсчитать кол-во групп)
$query='SELECT rpv.puID AS e_properties_uID, pv.ID AS pvID, pv.name AS pv_name  FROM relPropertiesToValues rpv, propertiesValues pv WHERE rpv.active=1 AND pv.active=1 AND pv.uID=rpv.vuID AND pv.ID in ('.$filters.')';
//ЗАПРАШИВАЕМ
$tmpa=$go->getAll($query);
$asa = array();
//$productsCount=$go->affectedRows();
foreach ($tmpa as $row) {
 $asa[$row['e_properties_uID']][] = 'pv.ID='.$row['pvID'];
}
$types=count($asa);

if (count($group)>0) {
 $groups = "'".implode("','",$group)."'";
 $query='SELECT rpv.puID FROM products p, propertiesValues pv, relProductsToValues rpv WHERE p.active=1 AND pv.active=1 AND rpv.active=1 AND rpv.puID=p.uID AND rpv.vuID=pv.uid AND pv.ID IN ('.$filters.') AND p.parent IN ('.$groups.') GROUP BY rpv.puID HAVING COUNT(rpv.puID)>=?i';
} else {
 $query='SELECT rpv.puID FROM propertiesValues pv, relProductsToValues rpv WHERE pv.active=1 AND rpv.active=1 AND rpv.vuID=pv.uid AND pv.ID IN ('.$filters.') GROUP BY rpv.puID HAVING COUNT(rpv.puID)>=?i';
}
//$result['sql'] = $query;
$prods=$go->getCol($query,$types);

if ($go->affectedRows()>0) {
  foreach ($prods as $key=>$val) {
      $prods[$key]='"'.$val.'"';
      }

  $prods=implode(',',$prods);

  //$query='SELECT pv.ID FROM relProductsToValues rpv, propertiesValues pv WHERE rpv.active=1 AND pv.active=1 AND rpv.puID IN ('.$props.') GROUP BY pv.ID';
  $query='SELECT pv.ID FROM relProductsToValues rpv, propertiesValues pv WHERE rpv.active=1 AND pv.active=1 AND rpv.vuID=pv.uID AND rpv.puID IN ('.$prods.') GROUP BY pv.ID';

  //echo $query;
  //echo '<br>';
  //echo '<br>';

  $props=$go->getCol($query);

  //$props=json_encode($props);
  $result['content']=$props;
}
else {
    $result['status']=0;
}

print_r(json_encode($result));
