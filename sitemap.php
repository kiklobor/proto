<?
header('Content-type: text/xml'); //определяем вывод содержимого как xml
require('scripts/setup.php'); //подключаемся к БД
define('LROOT', $_SERVER['DOCUMENT_ROOT']);

global $go;
$go=new SafeMySQL();

include_once(LROOT.'/scripts/prg_catalog_hierarchy.php');
global $category;
$category = new \URL\Category($go);

// echo '<pre>';
// var_dump($category::$catarr);
// echo '</pre>';

include_once('scripts'.DIRECTORY_SEPARATOR.'prg_catalog_hierarchy_service.php');
global $categoryservices;
$categoryservices = new \URL\CategoryServices($go);

$news='';
$products='';
$samples='';
//$samplesgroups='';
$groups='';
$subgroups='';
$groupsservices = '';
$services = '';

//сборка для раздела news
$arr=$go->getCol('SELECT ID FROM news ORDER BY date DESC');
foreach ($arr as $val) $news.='<url><loc>https://imige.ru/news/'.$val.'</loc></url>';

//сборкa для разделов категорий
//$arr=$go->getCol('SELECT ID FROM groups WHERE active=1 AND ind!=1');
//foreach ($arr as $val) $groups.='<url><loc>https://imige.ru/catalog?target=products&amp;level=1&amp;id='.$val.'</loc></url>';
// $arrguid = array();
// $arrg=$go->getAll('SELECT url, uID FROM groups WHERE active=1 AND ind!=1');
// foreach ($arrg as $val) {
//  $groups.='<url><loc>https://imige.ru/catalog/'.$val['url'].'/</loc></url>';
//  $arrguid[$val['uID']]['url'] = $val['url'];
// }

//сборкa для разделов подкатегорий
//$arr=$go->getCol('SELECT ID FROM subgroups WHERE active=1');
//foreach ($arr as $val) $groups.='<url><loc>https://imige.ru/catalog?target=products&amp;level=2&amp;id='.$val.'</loc></url>';
// $arr=$go->getAll('SELECT ID, parent, url FROM subgroups WHERE active=1');
// foreach ($arr as $val) $subgroups.='<url><loc>https://imige.ru/catalog/'.$arrguid[$val['parent']]['url'].'/'.$val['url'].'/</loc></url>';

//сборкa для категорий и подготовка массива для отобора товаров uIDs
$parent_uids = array();
foreach ($category::$catarr as $key => $val) {
  foreach ($val as $parentuid => $chldA) {
     $groups.='<url><loc>https://imige.ru/catalog/'.implode('/', $chldA['catpath']).'/</loc></url>';
     array_push($parent_uids, $chldA['uID']);
  }
}

//сборкa для карточек продуктов
// $arr=$go->getCol('SELECT p.url FROM groups g,subgroups sg, products p WHERE p.active=1 AND p.parent=sg.uID AND sg.parent=g.uID AND sg.active=1 AND g.active=1 AND g.ind=0');
$arr=$go->getCol('SELECT p.url FROM products p WHERE p.active=1 AND p.parent IN (?a)', $parent_uids);
foreach ($arr as $val) $products.='<url><loc>https://imige.ru/product/'.$val.'</loc></url>';

// услуги сборкa для категорий и подготовка массива для отобора услуг по  uID категорий
$parent_services_uids = array();
foreach ($categoryservices::$catarr as $key => $val) {
  foreach ($val as $parentuid => $chldA) {
     $groupsservices.='<url><loc>https://imige.ru/services/'.implode('/', $chldA['catpath']).'/</loc></url>';
     array_push($parent_services_uids, $chldA['uID']);
  }
}
//сборкa для самих услуг
// $arr=$go->getCol('SELECT p.url FROM groups g,subgroups sg, products p WHERE p.active=1 AND p.parent=sg.uID AND sg.parent=g.uID AND sg.active=1 AND g.active=1 AND g.ind=0');
$arr=$go->getCol('SELECT p.url FROM services p WHERE p.active=1 AND p.parent IN (?a)', $parent_services_uids);
foreach ($arr as $val) $services.='<url><loc>https://imige.ru/service/'.$val.'</loc></url>';

//сборкa для карточек кастомок
$arr=$go->getCol('SELECT p.url FROM groups g,subgroups sg, products p WHERE p.active=1 AND p.parent=sg.uID AND sg.parent=g.uID AND sg.active=1 AND g.active=1 AND g.ind=1');
foreach ($arr as $val) $samples.='<url><loc>https://imige.ru/sample/'.$val.'</loc></url>';

//сборкa для групп кастомок
//$arr=$go->getCol('SELECT ID FROM groups WHERE active=1 AND ind=1');
//foreach ($arr as $val) $samplesgroups.='<url><loc>http://imige.ru/ccatalog?level=1&amp;id='.$val.'</loc></url>';

echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc>https://imige.ru</loc></url>
<url><loc>https://imige.ru/login</loc></url>
<url><loc>https://imige.ru/customs</loc></url>
<url><loc>https://imige.ru/about</loc></url>
<url><loc>https://imige.ru/callback</loc></url>
<url><loc>https://imige.ru/registration</loc></url>
<url><loc>https://imige.ru/news</loc></url>
<url><loc>https://imige.ru/contacts</loc></url>
<url><loc>https://imige.ru/payments</loc></url>
<url><loc>https://imige.ru/delivery</loc></url>
'.$news.'
<url><loc>https://imige.ru/catalog/</loc></url>

'.$groups.'

'.$subgroups.'

'.$products.'

<url><loc>https://imige.ru/services/</loc></url>

'.$groupsservices.'

'.$services.'

'.$samples.'
</urlset>';
