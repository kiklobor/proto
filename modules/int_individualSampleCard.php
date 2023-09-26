<div class="bread">
<a href="/">Главная</a> / <a href="/ccatalog?target=products">Образцы по индивидуальному заказу</a> / <?echo($product['name']);?>
<?
//if ($parents) echo '<a href="/catalog?target=products&level=1&id='.$parents['gID'].'">'.$parents['gname'].'</a> / <a href="/catalog?target=products&level=2&id='.$parents['sgID'].'">'.$parents['sgname'].'</a> / ';
//else echo 'Без категории / ';
//echo $product['name'];
?>
</div>
<div style="width:100%;display:flex;flex-direction:row;">
    <div style="width:60%;padding:5px;">
                <div id="fotorama" class="fotorama" data-width="100%" data-ratio="4/3" data-nav="thumbs">
                    <?foreach($images as $val) echo $val;?>
                </div>
    </div>
    <div style="width:40%;padding:5px;"><h1><?echo($product['name']);?></h1>
    <?echo($product['description']);?>
    <?
    if (count($props)>0) {
        echo '<h4>Характеристики:</h4>';
        foreach ($props as $val) echo ' - '.$val.'<br>';
        echo '<br>';
    }
    ?>
    Артикул: <b><?echo($article);?></b><br>
    <button class="greyGradient" onclick="window.print();" style="height:25px">Печать</button>
    </div>
</div>

<?if ($similarArr) {?>
Похожие продукты:
<div class="owl-carousel p-0 container-fluid">
<?foreach ($similarArr as $row) echo $row;?>
</div>
<?}?>

<script>
    $(document).ready(function (){
    
    
    /*$('#fotorama').on('fotorama:ready ' + 'fotorama:show ',
            function (e, fotorama, extra) {
              console.log('active frame', fotorama.activeFrame.i);
              frame=fotorama.activeFrame.i-1;
                $('.thumbs').css({'border-color':'#d7dfe1'});
                $('.thumbs[index='+frame+']').css({'border-color':'#000'});
            }
        )*/
    var $fotoramaDiv = $('#fotorama').fotorama({allowfullscreen: true});
   // var fotorama = $fotoramaDiv.data('fotorama');
 
       /*$('.thumbs').click(function (){
          showPlease=$(this).attr('index');
          $('.thumbs').css({'border-color':'#d7dfe1'});
          $(this).css({'border-color':'#000'});
          fotorama.show({
            index:showPlease,
            time:500
            });
       });*/
    });
</script>