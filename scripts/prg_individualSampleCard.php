<?
//$productUrl=$_GET['product'];
if (isset($_GET['product'])) $productUrl=$_GET['product'];
else $productUrl=$urlArr[2];

//$query="SELECT * FROM `products` WHERE url=?s AND active=1 LIMIT 1";
$query="SELECT p.* FROM products p,groups g,subgroups sg WHERE p.parent=sg.uID AND sg.parent=g.uID AND g.ind=1 AND p.url=?s AND p.active=1 LIMIT 1";
$product=$go->getRow($query,$productUrl);
$product['cost']=$prices[$product['uID']];
//print_r($product);

if ($go->affectedRows()==1) {
    $productSafe=htmlspecialchars($product['name']);
    
    $images=array();
    $preimages=array();
    
    if($product['video']!='') array_push($images,'<a href="https://youtube.com/watch?v='.$product['video'].'&autoplay=1&version=3&loop=1&playlist='.$product['video'].'">ВИДЕО</a>');

	foreach (glob(FPATH.$product['uID'].'*') as $filename) array_push($preimages,'/'.$filename);
    if (count($preimages)==0) array_push($images,'<img title="'.htmlspecialchars($product['name']).'" alt="Цена: '.$product['cost'].' руб. '.$productSafe.'" src="styles/images/noPhoto.png">');
    else {
        usort($preimages,'imgSort');
        foreach ($preimages as $filename) array_push($images,'<img title="'.htmlspecialchars($product['name']).'" alt="Цена: '.$product['cost'].' руб. '.$productSafe.'" src="'.$filename.'">');
    }
    
    
	if ($product['articleFull']==='') $article='не указан';
	else $article=$product['articleFull'];
	isset($_SESSION['cart'][$product['ID']]) ? $text='В корзине!' : $text='Купить';
	
	//достаем характеристики
	$props=array();
	$props=$go->getCol("SELECT pv.name FROM relProductsToValues rpv,propertiesValues pv WHERE pv.active=1 AND rpv.active=1 AND rpv.puID=?s AND rpv.vuID=pv.uID GROUP BY pv.ID",$product['uID']);
	
    //пилим крошки
    $query='SELECT g.name gname, g.ID gID, sg.name sgname, sg.ID sgID FROM products p, groups g, subgroups sg WHERE p.ID=?i AND p.parent=sg.uID AND sg.parent=g.uID';
    $parents=$go->getRow($query,$product['ID']);
    
    //мета
    $meta='';
    $meta.='<title>'.$product['name'].'</title>';
    $meta.='<meta name="description" content="Цена: '.$product['cost'].' руб. '.$productSafe.'">';
    $canonical='<link rel="canonical" href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">';

    //пилим ключевые слова
    $kw=$parentSafe.' '.$productSafe;
    $kwArr=explode(' ',$kw);
    $kwArr=array_unique($kwArr);
    foreach($kwArr as $key=>$val) {
        if(mb_strlen($val,'UTF-8')<=3) unset($kwArr[$key]);
        }
    $kw=mb_strtolower(implode(', ',$kwArr),'UTF-8');
    $meta.='<meta name="keywords" content="'.$kw.'">';
    
    //похожие продукты: той же подгруппы
    $query='SELECT ID FROM products WHERE active=1 AND ID NOT IN (?i) AND parent=?s';
    $similar=$go->getCol($query,$product['ID'],$product['parent']);
    if(count($similar)>0) {
        shuffle($similar);
        $similar=array_slice($similar,0,8);
        $similarArr=implode(',',$similar);
        $query='SELECT * FROM products WHERE active=1 AND ID IN ('.$similarArr.')';
        $similar=$go->getAll($query);
        $similarArr=array();
        $simImg=array();
        foreach ($similar as $val) {
            $simImg=glob(FPATH.$val['uID'].'*');
            if (count($simImg)!=0) {
                usort($simImg,'imgSort');
                $img='/'.$simImg[0];
                }
            else $img='styles/images/noPhoto.png';
            unset($simImg);
            //array_push($similarArr,'<a href="/sample/'.$val['url'].'" style="display:flex;height:auto;min-height:200px;width:12.5%"><div class="simPr" style="width:100%;background-color:white;text-align:center;margin:0 1px;border:1px solid #bcbebd;padding:1px;overflow:hidden;"><img src="'.$img.'" style="width:100%">'.$val['name'].'</div></a>');
            $block='
            <a href="/product/'.$val['url'].'" class="d-flex flex-column justify-content-between align-items-center p-1 simPr text-center m-1" style="min-height:300px;background-color:white;border:1px solid #bcbebd;overflow:hidden;">
            <img src="'.$img.'" style="width:100%">
            <div>'.$val['name'].'</div>
            <div>'.$val['cost'].' руб.</div>
            </a>';
            array_push($similarArr,$block);
            
        }
        }
        
    }
else {
    //$url='/404';
	//header('Location: '.$url);
	$page='404';
	header("HTTP/1.1 404 Not Found");
    }
?>