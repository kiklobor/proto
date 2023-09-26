<?
$result=array();
$result['status']=1;
$result['content']=array();

if (require('../scripts/setup.php')) $go=new SafeMySQL();
else {
    $result['status']=0;
    print_r(json_encode($result));
    die();
    }
mb_internal_encoding('UTF-8');




if (isset($_GET['g']) && $_GET['g']!='') {
    $g=$_GET['g'];
    $filters=mb_substr($g,1); //удаляем запятую в начале
    $filtersArr=explode(',',$filters); //делаем массив с фильтрами
    //print_r($filtersArr);
    $filters=implode(',',$filtersArr);
    $types=count($filtersArr);
    }
else {
    $result['status']=0;
    print_r(json_encode($result));
    die();
    }

/*
$query='SELECT pv.ID FROM groups g,subgroups sg,products p,relProductsToValues rpv,propertiesValues pv
WHERE g.active=1
AND sg.active=1
AND p.active=1
AND rpv.active=1
AND pv.active=1
AND g.ID IN ('.$filters.')
AND p.parent=sg.uID
AND sg.parent=g.uID
AND rpv.puID=p.uID
AND rpv.vuID=pv.uID
GROUP BY pv.ID
';
*/
$query='SELECT pv.ID FROM groups g,subgroups sg,products p,relProductsToValues rpv,propertiesValues pv
WHERE g.active=1
AND sg.active=1
AND p.active=1
AND rpv.active=1
AND pv.active=1
AND g.ID IN ('.$filters.')
AND p.parent=sg.uID
AND sg.parent=g.uID
AND rpv.puID=p.uID
AND rpv.vuID=pv.uID
';

$props=$go->getCol($query);

if ($go->affectedRows()==0) {
    $result['status']=0;
    print_r(json_encode($result));
    die();
    }
else {
    $props2=array();
    foreach ($props as $val) {
        if (!in_array($val,$props2)) array_push($props2,$val);
    }
    $result['content']=$props2;
    }




//print_r($types);
//print_r($props);


print_r(json_encode($result));
