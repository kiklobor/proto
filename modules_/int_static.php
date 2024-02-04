<?
switch ($page) {
    case '404':$file='int_404.html';$crumbs='404';break;
    case 'confidence':$file='int_confidence.html';$crumbs='Соглашение на обработку персональных данных';break;
    case 'cookies':$file='int_cookies.html';$crumbs='Информация об использовании файлов cookies';break;
    case 'contacts':$file='int_contacts.html';$crumbs='Контакты';break;
    case 'payments':$file='int_payments.html';$crumbs='Оплата';break;
    case 'delivery':$file='int_delivery.html';$crumbs='Доставка';break;
    default:$file='int_404.html';
    }
?>

<div class="bread"><a href="/">Главная</a> / 
<?=$crumbs?>
</div>

<div class="w-100">
<?include('static/'.$file);?>
</div>