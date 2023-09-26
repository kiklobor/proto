<h1>Мои контрагенты</h1>
<?
$query='SELECT * FROM `customers` WHERE user=?i';
$agents=$go->getAll($query,$_SESSION['user_id']);

if ($go->affectedRows()>0) {
	$result=array();
	$i=0;
	foreach ($agents as $value) {
		$i++;
		$result[$i]['name']=$value['name'];
		$result[$i]['phone']=$value['phone'];
		$result[$i]['mail']=$value['mail'];
		switch ($value['type']) {
			case '1':$result[$i]['type']='физлицо';break;
			case '2':$result[$i]['type']='юрлицо';break;
			}
		}
	echo '<div>';
	foreach ($result as $key=>$value) {
		echo $key.') '.$value['name'].', '.$value['type'].', <a href="#" class="textlink">редактировать</a><br>';
		}
	echo '</div>';
	}
else echo 'К вашей учетной записи не привязано ни одного контрагента.';

?>

<br>
<a class="textlink goback">Назад</a> <a href="/newagent" class="textlink">Создать контрагента</a>

