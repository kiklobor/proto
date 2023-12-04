<?php
if( $buttonText=="Заказать")$buttonText="Взять за образец";
if (!isset($cbName)) $cbName = "";
if (!isset($cbPhone)) $cbPhone = "";
if (!isset($cbMail)) $cbMail = "";
if (!isset($cbText)) $cbText = "";
if (!isset($callbackResult)) $callbackResult = "";
$code=$product['articleFull'];
$supply=$product['availability'];
?>

<div class="bread">
<a href="/">Главная</a> / <a href="/params/">Параметры</a> / <?=$crumbs?><!--mcatalog?target=products-->
</div>

<div class="d-flex flex-row flex-wrap w-100 single-product-wrp" itemscope itemtype="https://schema.org/Product">
    <div class="col-12 col-md-6 text-center text-md-left">
        <div id="fotorama" class="fotorama" data-width="100%" data-ratio="4/3" data-nav="thumbs">
            <?=$gallery?>
        </div>
    </div>


    <div class="col-12 col-md-6 text-left text-md-left">
        <h1 itemprop="name"><?=$product['name']?></h1>
        <?=$description?><br>
        <?=$props?>
        Артикул: <b><?=$code?></b><br>
        <span itemprop="offers" itemscope itemtype="https://schema.org/Offer">
        <span itemprop="price">
        <?=$pricesBlock?>
        </span>
        <meta itemprop="priceCurrency" content="RUB">
        </span>
<!--        <div><?=$supply?></div><br> -->

<!--
        <div class="d-inline-block"><div class="pb-0 d-flex align-items-center justify-content-center counter-wrp"><button class="ProductCount" action="0" product="<?=$product['ID']?>">-</button><input class="ProductCount" type="text" value="<?=$ProductCountInCart?>" autocomplete="off" product="<?=$product['ID']?>"><button class="ProductCount" action="1" product="<?=$product['ID']?>">+</button></div></div>&nbsp;
        <button class="buy greenGradient" product="<?=$product['ID']?>" data-prdincart="<?=$prdincart?>"><?=$buttonText;?></button>&nbsp;
        <button class="greyGradient" onclick="window.print();">Печать</button>
-->

        <form id="pvn-switch" method="post"><input type="hidden" name="action" value="pvn-switch"></form>
    </div>
<div class="catalog w-100">
        <div class="mcatalogBlock">
            <form id="custom" method="post">
        <input type="text" form="custom" name="name" placeholder="ФИО" value="<?echo($cbName);?>"><br>
        <input type="text" form="custom" name="phone" placeholder="Телефон для связи" value="<?echo($cbPhone);?>"><br>
        <input type="text" form="custom" name="mail" placeholder="E-mail для связи" value="<?echo($cbMail);?>"><br>
        <input type="hidden" form="custom" name="action" value="customorder">
        <textarea form="custom" name="text" placeholder="Описание заказа"><?echo($cbText);?></textarea><br>
        
        <input type="hidden" form="custom" name="article" value="<?=$code?>">
        <input type="hidden" form="custom" name="productname" value="<?=$product['name']?>">
        
        <div class="g-recaptcha" data-sitekey="6LeIt0wUAAAAAE5bKMeMw29Gor0AguFj8lOSHpEZ"></div>
        <span class="redText"><?echo($callbackResult);?></span><br>
        <button type="submit" form="custom" class="greenGradient">Отправить заказ</button>
        </form>
    </div>
    </div>

</div>

<?=$similarSlider?>

<script>
    $(document).ready(function (){
        $('#fotorama').fotorama({allowfullscreen: true});
    });
</script>
