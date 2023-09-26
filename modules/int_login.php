<?php
if (!isset($loginResult)) $loginResult = "";
?>
<div class="bread">
<a href="/">Главная</a> / Вход
</div>

<div class="loginWrapper">
<h1>Вход</h1>
<div id="loginResult"><?echo($loginResult);?></div>
<form id="login" method="post">
<input type="text" name="login" form="login" placeholder="Электронная почта"><br>
<input type="password" name="pass" form="login" placeholder="Пароль"><br>
<input type="hidden" name="action" form="login" value="login">
<button type="submit" form="login" class="greenGradient">Войти</button>
</form>
<a href="/registration">Зарегистрироваться</a><br>
<a href="/forget">Забыли пароль?</a><br>
</div>