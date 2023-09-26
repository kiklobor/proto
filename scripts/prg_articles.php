<?
$articlesText='<a href="/guide"></br><u><div class="h3">Путеводитель кадровика</div></u></a>';
$meta='';
if (isset($urlArr[2]) AND $urlArr[2]!=='') {
    $articlesID=(integer)$urlArr[2];
    $query='SELECT * FROM articles WHERE ID=?i';
    $articles=$go->getRow($query,$articlesID);
    
    if ($go->affectedRows()==1) {
        $date=bDate(strtotime($articles['date']));
        $articlesText.='
        <div class="bread"><a href="/">Главная</a> / <a href="/articles">Статьи</a> / '.$articles['title'].'</div>
        <div class="w-100"><h4>'.$articles['title'].'</h4><div class="newsRowDate">'.$date.'</div></div>
        <div class="w-100">'.html_entity_decode($articles['text']).'</div>
        <a href="/articles"><button class="greyGradient">Назад</button></a>';
        
        $meta.='<title>'.htmlspecialchars($articles['title']).'</title>';
        $meta.='<meta name="keywords" content="'.htmlspecialchars($articless['keywords']).'">';
        $meta.='<meta name="description" content="'.htmlspecialchars($articles['description']).'">';
        }
    else {
        header("HTTP/1.1 404 Not Found");
        $page='404';
        $articlesText.='Статья не найдена';
        }
    }
else {
    $articlesText.='<div class="bread">
<a href="/">Главная</a> / Статьи
</div>';
$articlesText.='<h1>Статьи</h1>';
    $query='SELECT * FROM articles WHERE active=1 ORDER BY date DESC';
    $articles=$go->getAll($query);
    
    if ($go->affectedRows()>0) {
        foreach ($articles as $key=>$val) {
            $date=bDate(strtotime($val['date']));
            
            $articlesText.='<div class="newsRow d-flex flex-nowrap flex-row"><a href="/articles/'.$val['ID'].'"><div>'.$val['title'].'</div></a><div class="newsRowDate">'.$date.'</div></div>';
            }
        }
    else $articlesText.='Ничего нового';
    
    
    $metaArr=$go->getRow("SELECT * FROM meta WHERE page=?s",'articles');

    if ($go->affectedRows()==1) {
        if ($metaArr['title']!='') $meta.='<title>'.htmlspecialchars($metaArr['title']).'</title>';
        if ($metaArr['keywords']!='') $meta.='<meta name="keywords" content="'.htmlspecialchars($metaArr['keywords']).'">';
        if ($metaArr['description']!='') $meta.='<meta name="description" content="'.htmlspecialchars($metaArr['description']).'">';
        }
}