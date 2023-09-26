<?php
if (!isset($cbName)) $cbName = "";
if (!isset($cbPhone)) $cbPhone = "";
if (!isset($callbackResult)) $callbackResult = "";
?>
<div class="bread">
<a href="/">Главная</a> / Заказ звонка
</div>

<div class="loginWrapper">
<h1>Заказ звонка</h1>
<form id="callback" method="post">
<input type="text" name="name" form="callback" placeholder="Как к вам обращаться?" value="<?echo($cbName);?>"><br>
<input type="text" name="phone" form="callback" placeholder="Телефон" value="<?echo($cbPhone);?>"><br>
<input type="hidden" name="action" form="callback" value="callback">
<div class="g-recaptcha" style="margin: 15px auto 12px 49px;" data-sitekey="6LeIt0wUAAAAAE5bKMeMw29Gor0AguFj8lOSHpEZ"></div>
<div id="loginResult" class="redText"><?echo($callbackResult);?></div>
<button type="submit" form="callback" class="greenGradient">Заказать звонок</button>
</form>
</div>