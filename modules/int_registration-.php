<div class="bread">
<a href="/">Главная</a> / Регистрация
</div>

<div class="loginWrapper">
<h1>Регистрация</h1>
<span class="redText">
<?
if ($check!=4) foreach ($registerResult as $key=>$value) {
	echo $value.'<br>';
	}
?>
</span><br>
<form id="register" method="post">
<input type="text" name="login" form="register" value="<?echo($regData['login']);?>" placeholder="Электронная почта*"><br>
<input type="text" name="name" form="register" value="<?echo($regData['name']);?>" placeholder="ФИО*"><br>
<input type="password" name="pass" form="register" placeholder="Пароль*"><br>
<input type="password" name="pass2" form="register" placeholder="Пароль еще раз*"><br>
<input type="hidden" name="action" form="register" value="register">
<button type="submit" form="register" class="greenGradient">Зарегистрироваться</button>
</form>
<br> * поля, обязательные для заполнения
</div>