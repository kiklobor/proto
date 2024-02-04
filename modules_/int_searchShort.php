<div class="bread">
<a href="/">Главная</a> / Поиск
</div>

<h1>Поиск</h1>

<? 
if ($searchEmpty) echo 'Вы не ввели запрос.';
elseif ($groupsByNameCount+$subgroupsByNameCount+$productsByNameCount==0) echo '<h2>"'.$what.'"</h2><h4>Нет результатов.</h4>'; //если сумма результатов 0, значит ничего не нашли, ничего не выводим
else {
    echo'<h2>"'.$what.'"</h2>';
    
    if ($groupsByNameCount>0) {
        echo '<h4>Группы ('.$groupsByNameCount.'):</h4><div>';
        foreach ($groupsByName as $key=>$row) echo '<a href="/catalog?target=products&level=1&id='.$row['ID'].'"><div>'.$row['name'].'</div></a>';
        echo '</div>';
        }
    
    if ($subgroupsByNameCount>0) {
        echo '<h4>Подгруппы ('.$subgroupsByNameCount.'):</h4><div>';
        foreach ($subgroupsByName as $key=>$row) echo '<a href="/catalog?target=products&level=2&id='.$row['ID'].'"><div>'.$row['name'].'</div></a>';
        echo '</div>';
        }
    
    if ($productsByNameCount>0) {
        echo '<h4>Товары ('.$productsByNameCount.'):</h4><div>';
        foreach ($productsByName as $key=>$row) echo '<a href="/product/'.$row['url'].'"><div>'.$row['name'].'</div></a>';
        echo '</div>';
        }
    }

?>



