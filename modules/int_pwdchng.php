<?php
if (!isset($check)) $check = 0;
if (!isset($pwdchngResult)) $pwdchngResult = array();
if (!isset($pwdData)) $pwdData = array();
?>
<div class="bread">
<a href="/">Главная</a> / Изменение пароля
</div>

<div class="loginWrapper">
<h1>Изменение пароля</h1>
<span class="redText">
<?
if ($check!=4) foreach ($pwdchngResult as $key=>$value) {
	echo $value.'<br>';
	}
?>
</span><br>
<form id="pwdchng" method="post">
<input type="password" name="pass" form="pwdchng" placeholder="Новый пароль*"><br>
<input type="password" name="pass2" form="pwdchng" placeholder="Новый пароль еще раз*"><br><br>
<div class="g-recaptcha d-flex justify-content-center" data-sitekey="6LeIt0wUAAAAAE5bKMeMw29Gor0AguFj8lOSHpEZ"></div>
<input type="hidden" name="action" form="pwdchng" value="pwdchng">
<button type="submit" form="pwdchng" class="greenGradient">Изменить пароль</button>
</form>
<br> * поля, обязательные для заполнения
</div>