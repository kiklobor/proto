<?php
if (isset($_SESSION['user_name'])) {
$cbName = $_SESSION['user_name'];
if (isset($_SESSION['user_phone'])) $cbPhone = $_SESSION['user_phone'];
if (isset($_SESSION['user_mail'])) $cbMail = $_SESSION['user_mail'];}
else {$cbName = "";$cbPhone = "";$cbMail = "";}
if (!isset($cbText)) $cbText = "";
if (!isset($callbackResult)) $callbackResult = "";
?>
<div class="bread">
<a href="/">Главная</a> / Продукция по параметрам заказчика
</div>

<div class="contentContainer col-12 col-md-9 p-0">
	<div class="catalog w-100">
		<div class="tabContainer">

			<div class="tabBlock col-6 col-md-9 d-flex justify-content-center align-items-center">
			<a href="/mcatalog?target=products"><div class="tabText">Продукция</div></a>
			</div>
	
			<div class="tabBlock col-6 col-md-3 d-flex justify-content-center align-items-center activeTab">
			<a href="/customs"><div class="tabText">Продукция<br>по параметрам<br>заказчика</div></a>
			</div>
		</div>
		
		<div class="mcatalogBlock">
		Если вы не нашли нужного вам товара или хотите приобрести что-то особенное, здесь вы можете составить описание необходимого вам товара и оставить заявку на составление индивидуального заказа. После рассмотрения вашей заявки мы свяжемся с вами для обсуждения его выполнения.
		<form id="custom" method="post">
		<input type="text" form="custom" name="name" placeholder="ФИО" value="<?echo($cbName);?>"><br>
		<input type="text" form="custom" name="phone" placeholder="Телефон для связи" value="<?echo($cbPhone);?>"><br>
		<input type="text" form="custom" name="mail" placeholder="E-mail для связи" value="<?echo($cbMail);?>"><br>
		<input type="hidden" form="custom" name="action" value="customorder">
		<textarea form="custom" name="text" placeholder="Описание заказа"><?echo($cbText);?></textarea><br>
		<div class="g-recaptcha" data-sitekey="6LeIt0wUAAAAAE5bKMeMw29Gor0AguFj8lOSHpEZ"></div>
		<span class="redText"><?echo($callbackResult);?></span><br>
		<button type="submit" form="custom" class="greenGradient">Оставить заявку</button>
		</form>
		</div>
	</div>
</div>

<div class="col-12 col-md-3 order-1 order-md-2 p-0">
    <div class="optionsSticky position-sticky">
	<a href="/callback" style="text-decoration:none;"><div class="makeCall">Заказать звонок</div></a>
	</div>
</div>