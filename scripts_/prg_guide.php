<?
$guideText='';
$meta='';
if (isset($urlArr[2]) AND $urlArr[2]!=='') {
    $guideID=(integer)$urlArr[2];
    $query='SELECT * FROM guide WHERE ID=?i';
    $guide=$go->getRow($query,$guideID);
    
    if ($go->affectedRows()==1) {
        $date=bDate(strtotime($guide['date']));
        $guideText.='
        <div class="bread"><a href="/">Главная</a> / <a href="/guide">Путеводитель кадровика</a> / '.$guide['title'].'</div>
        <div class="w-100"><h4>'.$guide['title'].'</h4><div class="newsRowDate">'.$date.'</div></div>
        <div class="w-100">'.html_entity_decode($guide['text']).'</div>
        <a href="/guide"><button class="greyGradient">Назад</button></a>';
        
        $meta.='<title>'.htmlspecialchars($guide['title']).'</title>';
        $meta.='<meta name="keywords" content="'.htmlspecialchars($guide['keywords']).'">';
        $meta.='<meta name="description" content="'.htmlspecialchars($guide['description']).'">';
        }
    else {
        header("HTTP/1.1 404 Not Found");
        $page='404';
        $guideText.='Статья не найдена';
        }
    }
else {
    $guideText.='<div class="bread">
<a href="/">Главная</a> / Путеводитель кадровика
</div>';
$guideText.='<h1>Путеводитель кадровика</h1>';
    $query='SELECT * FROM guide WHERE active=1 ORDER BY date DESC';
    $guide=$go->getAll($query);
    
    if ($go->affectedRows()>0) {
        foreach ($guide as $key=>$val) {
            $date=bDate(strtotime($val['date']));
            
            $guideText.='<div class="newsRow d-flex flex-nowrap flex-row"><a href="/guide/'.$val['ID'].'"><div>'.$val['title'].'</div></a><div class="newsRowDate">'.$date.'</div></div>';
            }
        }
    else $guideText.='Ничего нового';
    
    
    $metaArr=$go->getRow("SELECT * FROM meta WHERE page=?s",'guide');

    if ($go->affectedRows()==1) {
        if ($metaArr['title']!='') $meta.='<title>'.htmlspecialchars($metaArr['title']).'</title>';
        if ($metaArr['keywords']!='') $meta.='<meta name="keywords" content="'.htmlspecialchars($metaArr['keywords']).'">';
        if ($metaArr['description']!='') $meta.='<meta name="description" content="'.htmlspecialchars($metaArr['description']).'">';
        }
}