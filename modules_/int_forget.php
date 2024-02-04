<?php
if (!isset($Success)) $Success = FALSE;
if (!isset($check)) $check = 0;
if (!isset($sentResult)) $sentResult = array();
if (!isset($Data)) $Data = array();
?>
<div class="bread">
<a href="/">Главная</a> / Восстановить пароль
</div>

<div class="forgetWrapper">
<h1>Восстановление доступа</h1>
<div>
<?php if (!$Success) { ?>
<span class="redText">
<?
if ($check!==2) foreach ($sentResult as $key=>$value) {
	echo $value.'<br>';
	}
?>
</span>
</div>
<form id="forget" method="post">
<input required type="text" name="login" form="forget" <?php if(array_key_exists('login', $Data) AND $Data['login']!='') { echo 'value="'.$Data['login'].'"'; }?> placeholder="Электронная почта*"><br>
<div class="g-recaptcha d-flex justify-content-center" data-sitekey="6LeIt0wUAAAAAE5bKMeMw29Gor0AguFj8lOSHpEZ"></div>
<input type="hidden" name="action" form="forget" value="forget">
<button type="submit" form="forget" class="greenGradient">Выслать новый пароль</button>
</form>
<a href="/registration">Зарегистрироваться</a><br>
<!--<a href="">Забыли пароль?</a><br>-->
<?php } else { ?>
<div class="h3 text-success">Новый пароль выслан на почту.</div>
<!--<div>Вы так же можете <a href="/registration">Зарегистрироваться</a><br></div>-->
<?php } ?>
</div>