<div class="bread">
<a href="/">Главная</a> / <a href="/params/">Образцы</a> / <?=$crumbs?><!--mcatalog?target=products-->
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
        <div><?=$supply?></div><br>
        <div class="d-inline-block"><div class="pb-0 d-flex align-items-center justify-content-center counter-wrp"><button class="ProductCount" action="0" product="<?=$product['ID']?>">-</button><input class="ProductCount" type="text" value="<?=$ProductCountInCart?>" autocomplete="off" product="<?=$product['ID']?>"><button class="ProductCount" action="1" product="<?=$product['ID']?>">+</button></div></div>&nbsp;
        <button class="buy greenGradient" product="<?=$product['ID']?>" data-prdincart="<?=$prdincart?>"><?=$buttonText;?></button>&nbsp;
        <button class="greyGradient" onclick="window.print();">Печать</button>
        <form id="pvn-switch" method="post"><input type="hidden" name="action" value="pvn-switch"></form>
    </div>
</div>

<?=$similarSlider?>

<script>
    $(document).ready(function (){
        $('#fotorama').fotorama({allowfullscreen: true});
    });
</script>
