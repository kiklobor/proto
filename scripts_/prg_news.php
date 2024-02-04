<?
$newsText='';
$meta='';
if (isset($urlArr[2]) AND ($urlArr[2]!=='')) {
    $newsID=(integer)$urlArr[2];
    $query='SELECT * FROM news WHERE ID=?i';
    $news=$go->getRow($query,$newsID);
    
    if ($go->affectedRows()==1) {
        $date=bDate(strtotime($news['date']));
        $newsText.='
        <div class="bread"><a href="/">Главная</a> / <a href="/news">Новости</a> / '.$news['title'].'</div>
        <div class="w-100"><h4>'.$news['title'].'</h4><div class="newsRowDate">'.$date.'</div></div>
        <div class="w-100">'.html_entity_decode($news['text']).'</div>
        <a href="/news"><button class="greyGradient">Назад</button></a>';
        
        $meta.='<title>'.htmlspecialchars($news['title']).'</title>';
        $meta.='<meta name="keywords" content="'.htmlspecialchars($news['keywords']).'">';
        $meta.='<meta name="description" content="'.htmlspecialchars($news['description']).'">';
        }
    else {
        header("HTTP/1.1 404 Not Found");
        $page='404';
        $newsText.='Новость не найдена';
        }
    }
else {
    $newsText.='<div class="bread">
<a href="/">Главная</a> / Новости
</div>';
$newsText.='<h1>Новости</h1>';
    $query='SELECT * FROM news WHERE active=1 ORDER BY date DESC';
    $news=$go->getAll($query);
    
    if ($go->affectedRows()>0) {
        foreach ($news as $key=>$val) {
            $date=bDate(strtotime($val['date']));
            
            $newsText.='<div class="newsRow d-flex flex-nowrap flex-row"><a href="/news/'.$val['ID'].'"><div>'.$val['title'].'</div></a><div class="newsRowDate">'.$date.'</div></div>';
            }
        }
    else $newsText.='Ничего нового';
    
    
    $metaArr=$go->getRow("SELECT * FROM meta WHERE page=?s",'news');

    if ($go->affectedRows()==1) {
        if ($metaArr['title']!='') $meta.='<title>'.htmlspecialchars($metaArr['title']).'</title>';
        if ($metaArr['keywords']!='') $meta.='<meta name="keywords" content="'.htmlspecialchars($metaArr['keywords']).'">';
        if ($metaArr['description']!='') $meta.='<meta name="description" content="'.htmlspecialchars($metaArr['description']).'">';
        }
}