<?
$mngrs=array();
array_push($mngrs,'
<div class="d-flex flex-column">
');

$query='SELECT * FROM managers WHERE display=1 ORDER BY name';
$mngrsOb=$go->getAll($query);

foreach($mngrsOb as $key=>$val) {
    $images=glob(MPICS.$val['ID'].'*');
            if (count($images)!=0) {
                usort($images,'imgSort');
                $mpic=$images[0];
                }
		    else $mpic='styles/images/noAvatar.png';
    $row='<div class="d-flex flex-row row mt-2">
    <div class="col-3 col-md-2 d-flex align-items-center"><img class="w-100 rounded-circle" src="/'.$mpic.'"></div>
    <div class="col-9 col-md-10 d-flex flex-column justify-content-center align-items-start">
        <span style="font-size:1.5rem">'.$val['name'].'</span>
        <a href="tel:'.$val['phonehref'].'">'.$val['phone'].'</a>
        <a href="mailto:'.$val['mail'].'">'.$val['mail'].'</a>
    </div>
    </div>
			';
	array_push($mngrs,$row);
    }
    
array_push($mngrs,'
</div>
');
?>


<div class="bread">
<a href="/">Главная</a> / О компании
</div>
<h1>О компании</h1>
<div class="infoBlock overflow-auto row">
	<div class="infoBlockTitle text-center col-5 col-md-3">О компании</div>
	<div class="infoBlockChisel col-7 col-md-9"></div>
	<div class="infoBlockContent col-12 pt-3 pb-3">
<span itemscope itemtype="http://schema.org/Organization">
<span itemprop="name">Компания ООО "Имидж"</span> является полиграфическим предприятием.<br><br>

Компания "Имидж" начала свою деятельность в 1998 году. Производственная фирма с небольшим офисом в Москве и собственными производственными площадями, находящимися в Подмосковье. Производимая продукция - архивные короба, обложки на документы из различных материалов и прочее.
<br>
Общее количество работников 140. Из них занято на производстве 90 человек. Отдел контроля качества - 3 человека. Отдел дизайна и издательских разработок - 7 человек, инженерное обеспечение запуска новой продукции - 3 человека.
<br>
Компания ООО "Имидж" является обладателем нескольких патентов, часть наших разработок стала уже стандартом в области архивирования и хранения документов. Собственный дизайнерский отдел позволяет регулярно запускать новую продукцию, аналогов которой нет на рынке.

<ul>
<li>Архивные короба</li>
<li>Адресные папки из различных материалов, открытки.</li>
<li>Обложки на документы из различных материалов.</li>
<li>Любые виды полиграфии, переплета, обложки на документы на заказ.</li>
</ul>


Нашим партнером является московская фабрика "Гознак".<br><br>

Компания "Имидж"- официальный распространитель бланков "Трудовая книжка" и "Вкладыш в трудовую книжку" производства Московской фабрики "Гознак" согласно договору №4 от 30.12.2005 года между нашими организациями.<br>
Обеспечиваем комплектом официальных документов на бланки "Трудовая книжка" и "Вкладыш в трудовую книжку", включая копию сертификата качества на данные бланки выданного нам фабрикой "Гознак".

<ul>
<li>Книга учета движения бланков "Трудовая книжка" и "Вкладыш в трудовую книжку" (для отдела кадров предприятия)</li>
<li>Приходно-расходная книга по учету бланков "Трудовая книжка" и "Вкладыш в трудовую книжку" (для бухгалтерии предприятия)</li>
<li>Правила ведения и заполнения бланков "Трудовая книжка" и "Вкладыш в трудовую книжку".</li>
</ul>
Юридические данные:</br>
ООО "Имидж"</br>
Юридический адрес: 143306, Московская обл, г. Наро-Фоминск, ул. Ленина, д. 28, оф. 1<br>
ОГРН 1047796408802</br>
ИНН 7716508694</br>
КПП 503001001</br>
р/с 40702810538260106530</br>
в ПАО СБЕРБАНК</br>
БИК 044525225</br>
к/с 30101810400000000225</br>

<!--Адрес: 143306, Московская область, Наро-Фоминский р-он, г. Наро-Фоминск, ул. Ленина, д. 28, офис 3 -->
<br>

Телефоны: <a href="tel:88005558054" itemprop="telephone">8 (800) 555-80-54</a>, <a href="tel:+74991101741" itemprop="telephone">+7 (499) 110-17-41</a>, <a href="tel:+74997071791" itemprop="telephone">+7 (499) 707-17-91</a><br>

Факс: <a href="tel:+74956471070" itemprop="faxNumber">+7 (495) 647-10-70</a><br>

E-mail: <span itemprop="email">imige@imige.ru</span><br>

Наши менеджеры:<br>
<?foreach($mngrs as $val)echo($val);?>

<br>
Адрес: <span itemprop="address">109428, г. Москва, Ленинский проспект, дом 42. В здании "Комбинат Питания", 1 этаж, офис 1-13</span><br>

<div class="mt-2 border">
<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A7edc48e6d81271d93c52c0a70938b89f19950e0a2ed33d00ed9ed12eda9f9436&amp;width=100%25&amp;height=400&amp;lang=ru_RU&amp;scroll=true"></script>
</div>	
</span>
</div>
</div>