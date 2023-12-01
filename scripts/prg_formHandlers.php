<?
//промежуточная страница для обработки заказа
if ($page==='orderProcess') {
	$url='/orderComplete?order='.$_GET['order'];
	header('Location: '.$url);
	}

//поиск
if ($page==='searchShort') {
    $inSearch=$what=preg_replace('/[^,a-zа-я0-9-\,\.\/ ]/ui', '',htmlspecialchars(trim($_GET['what'])));

	$searchEmpty=true;

	if ($what!='') {
	      $searchEmpty=false;

        $lroot = $_SERVER['DOCUMENT_ROOT'];
        include_once($lroot.'/modules/stemmer/LinguaStemRu.php');
        $stemmer = new \Stem\LinguaStemRu();

        require_once( $lroot.'/modules/morphy/src/common.php');

        // Укажите путь к каталогу со словарями
        $dir = $lroot.'/modules/morphy/dicts';

        // Укажите, для какого языка будем использовать словарь.
        // Язык указывается как ISO3166 код страны и ISO639 код языка,
        // разделенные символом подчеркивания (ru_RU, uk_UA, en_EN, de_DE и т.п.)

        $lang = 'ru_RU';

        // Укажите опции
        // Список поддерживаемых опций см. ниже
        $opts = array(
            'storage' => PHPMORPHY_STORAGE_FILE,
        );

        // создаем экземпляр класса phpMorphy
        // обратите внимание: все функции phpMorphy являются throwable т.е.
        // могут возбуждать исключения типа phpMorphy_Exception (конструктор тоже)
        //$morphy = new phpMorphy($dir, $lang, $opts);

        try {
            $morphy = new phpMorphy($dir, $lang, $opts);
        } catch(phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
        }

        //echo $stemmer->stem_word('Автомобиль') . "<br/>";
        //echo $stemmer->stem_text('Любовь к Родине – это очень сильное чувство.');

        //$whatz = preg_replace('/(\s+|^)(за|в(о|не)?|по(д)?|к(о)?|и|а|так|же|на|у)(\s+|$)/iu',' ',$what);
        $whatz = preg_replace('/(\bза\b|\bв(о|не)?\b|\bпо(д)?\b|\bк(о)?\b|\bи\b|\bа\b|\bтак\b|\bже\b|\bна\b|\bу\b)/iu', ' ', $what);
        //$whatz = preg_replace('/(\s+|^)(за|в(о|не)?|по(д)?|к(о)?|и|а|так|же|на|у)(\s+|$)/i',' ',$what);
        /*
        $whatz = preg_replace_callback('/(\s+|^)(за|в(о|не)?|по(д)?|к(о)?|и|а|так|же|на|у)(\s+|$)/i',
                 function ($matches) {
                   return ' ';
                 }, $what);
        /**/
        $whatz = preg_replace('/\s+/', ' ', $whatz);
        //echo $whatz;
        $whatz = explode(' ',$whatz);
        $sgquery="SELECT * FROM ?n WHERE active=1";
        $pquery="SELECT * FROM ?n WHERE active=1";

        //echo '<pre>';
        //var_dump($morphy);
        //var_dump($morphy->getEncoding());
        //echo '</pre>';\

        $whatznorm = array();

        foreach ($whatz as $key=>$val) {
            //$val = $stemmer->stem_word($val);
            $val = mb_strtoupper($val);
            $psroot = $morphy->getPseudoRoot($val, phpMorphy::NORMAL);//, phpMorphy::NORMAL
            if ($psroot === FALSE) {
              $whatznorm[] = mb_strtolower($stemmer->stem_word($val));
            } else {
              $pred = $morphy->isLastPredicted();
              foreach ($psroot as $key=>$valpsroot) {
                $whatznorm[] = mb_strtolower($valpsroot);
                $needcheckval = mb_strtolower($stemmer->stem_word($valpsroot));
                $wasinarray = !in_array($needcheckval, $whatznorm);
                if ($pred AND $wasinarray) {
                  $whatznorm[] = $needcheckval;
                }
              }
            }
        }

        foreach ($whatznorm as $key=>$val) {
            //echo $val;
            //echo '<pre>';
            //var_dump($val);
            //echo '</pre>';
            if (mb_strlen($val)>1) {
              if (preg_match("/\./i", $val)) {
                $val1 = preg_replace('/\./i', ',', $val);
                $pquery.=' AND ((name LIKE "%'.$val1.'%" OR articleFull LIKE "%'.$val1.'%") OR (name LIKE "%'.$val.'%" OR articleFull LIKE "%'.$val.'%"))';
                $sgquery.=' AND ((name LIKE "%'.$val1.'%") OR (name LIKE "%'.$val.'%"))';
              } else {
                $sgquery.=' AND name LIKE "%'.$val.'%"';
                $pquery.=' AND (name LIKE "%'.$val.'%" OR articleFull LIKE "%'.$val.'%")';
              }
            }
        }

        //echo $pquery.'<br />';
        //echo $sgquery.'<br />';

        //ищем товары по названию
        $productsByName=$go->getAll($pquery,'products');
        $productsByNameCount=$go->affectedRows();

        //ищем группы по названию
        $groupsByName=$go->getAll($sgquery,'groups');
        $groupsByNameCount=$go->affectedRows();

        //ищем подгруппы по названию
        $subgroupsByName=$go->getAll($sgquery,'subgroups');
        $subgroupsByNameCount=$go->affectedRows();
    }

  /*
	if ($what!='') {
	      $searchEmpty=false;

        $lroot = $_SERVER['DOCUMENT_ROOT'];
        include_once($lroot.'/modules/stemmer/LinguaStemRu.php');

        $stemmer = new \Stem\LinguaStemRu();
        //echo $stemmer->stem_word('Автомобиль') . "<br/>";
        //echo $stemmer->stem_text('Любовь к Родине – это очень сильное чувство.');

        $whatz = preg_replace('/\s+(за|в(о|не)?|по(д)?|к(о)?|(и|а)|(так|же))\s+/i',' ',$what);
        $whatz = preg_replace('/\s+/', ' ', $whatz);
        //echo $whatz;
        $whatz = explode(' ',$whatz);
        $sgquery="SELECT * FROM ?n WHERE active=1";
        $pquery="SELECT * FROM ?n WHERE active=1";

        foreach ($whatz as $key=>$val) {
            $val = $stemmer->stem_word($val);
            //echo $val;
            if (mb_strlen($val)>1) {
              if (preg_match("/\./i", $val)) {
                $val1 = preg_replace('/\./i', ',', $val);
                $pquery.=' AND ((name LIKE "%'.$val1.'%" OR articleFull LIKE "%'.$val1.'%") OR (name LIKE "%'.$val.'%" OR articleFull LIKE "%'.$val.'%"))';
                $sgquery.=' AND ((name LIKE "%'.$val1.'%") OR (name LIKE "%'.$val.'%"))';
              } else {
                $sgquery.=' AND name LIKE "%'.$val.'%"';
                $pquery.=' AND (name LIKE "%'.$val.'%" OR articleFull LIKE "%'.$val.'%")';
              }
            }
        }
        echo $pquery.'<br />';
        echo $sgquery.'<br />';

        //ищем товары по названию
        $productsByName=$go->getAll($pquery,'products');
        $productsByNameCount=$go->affectedRows();

        //ищем группы по названию
        $groupsByName=$go->getAll($sgquery,'groups');
        $groupsByNameCount=$go->affectedRows();

        //ищем подгруппы по названию
        $subgroupsByName=$go->getAll($sgquery,'subgroups');
        $subgroupsByNameCount=$go->affectedRows();
    }
    */
}









//прием кастомного заказа (переменные криво называются)
if (array_key_exists('action', $_POST) AND $_POST['action']==='customorder') {
    $cbName=$_POST['name'];
	$cbPhone=$_POST['phone'];
	$cbMail=$_POST['mail'];
	$cbText=$_POST['text'];
	$captcha=$_POST['g-recaptcha-response'];
	
	$article=$_POST['article'];
	$productname=$_POST['productname'];
	if ($article!='') $article = 'Артикул: '.$article;
	if ($productname!='') $productname = 'Название услуги: '.$productname;
	
	unset($_POST);
	$counter=0;
	$callbackResult='';

	if ($cbName==='') $callbackResult=$callbackResult.'Введите имя.<br>';
        elseif (preg_match("/href|url|http|https|www|.ru|.com|.net|.info|.org|.pro/i", $cbName)) $callbackResult=$callbackResult.'Запрещено размещать ссылки.<br>'; else $counter++;
	if ($cbPhone==='' && $cbMail==='') $callbackResult=$callbackResult.'Введите хотя бы один контакт.<br>'; else $counter++;
    if ($cbText!='') $counter++; else $callbackResult=$callbackResult.'Введите текст сообщения.<br>';
    if (preg_match("/href|url|http|https|www|.ru|.com|.net|.info|.org|.pro|.cyou/i", $cbText)) $callbackResult=$callbackResult.'Запрещено размещать ссылки.<br>'; else $counter++;

    if (!isset($captcha) || $captcha==='') $callbackResult=$callbackResult.'Проверка не пройдена.<br>';
    else {
      if (checkRecaptchaResponseCurl($captcha)===TRUE) $counter++;
      else $callbackResult=$callbackResult.'Проверка не пройдена.<br>';
    }

	if ($counter===5) {
	    //$callbackResult='Успех!';

		$query='INSERT INTO `customOrders`( `name`, `phone`, `mail`, `text`) VALUES (?s,?s,?s,?s)';
		$go->query($query,$cbName,$cbPhone,$cbMail,$cbText);
		$callbackID=$go->insertId();

		$date=date("d.m.Y, H:i:s");
		$mailText='Поступил новый запрос на индивидуальный заказ.<br><br>
		Номер запроса: '.$callbackID.'<br>
		Имя: '.$cbName.'<br>
		Телефон: '.$cbPhone.'<br>
		Почта: '.$cbMail.'<br>
		Дата и время: '.$date.'<br><br>

		Текст: '.$cbText.'<br>'.$article.'<br>'.$productname.'<br>';

$telegramMessage=array(
    'method'=>'sendMessage',
    'text'=>'Новый запрос на индивидуальный заказ.'.PHP_EOL.'Номер запроса: '.$callbackID.PHP_EOL.'Имя: '.$cbName.PHP_EOL.'Телефон: '.$cbPhone.PHP_EOL.'Почта: '.$cbMail.PHP_EOL.$article.PHP_EOL.$productname.PHP_EOL.'Дата и время: '.$date.PHP_EOL.$cbText,
    'chat_id'=>'',
    'disable_notification'=>'true'
    );
$telegramMessage['disable_notification']= (isWorkingHours()) ? 'false' : 'true';

$query='SELECT * FROM adminDelivery WHERE active=1';
$delivery=$go->getAll($query);

foreach($delivery as $key=>$value) {
	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	$mail->IsSendmail();
	$mail->CharSet = 'UTF-8';
	try {
		$mail->AddAddress($value['mail'], $value['name']);
		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
		$mail->Subject = 'Новый запрос на индивидуальный заказ №'.$callbackID;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($mailText);
		$mail->Send();
		}
	catch (\PHPMailer\PHPMailer\Exception $e) {
    //echo $e->errorMessage();
    $e->errorMessage();
    //die('1  ');
  }
	catch (Exception $e) {$e->getMessage();}

	if ($value['telegramID']!=0) {
	    $telegramMessage['chat_id']=$value['telegramID'];
	    $tg->sendj($telegramMessage);
	    }
	}
//админам написали, пишем менеджерам
$telegramMessage['reply_markup']=array(
        "inline_keyboard" => array(
            array(array('text' => 'Принять', 'callback_data' => 'action=accept&what=custom&id='.$callbackID))
            )
        );

$query='SELECT * FROM managers WHERE active=1';
$delivery=$go->getAll($query);
foreach($delivery as $key=>$value) {
	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	$mail->IsSendmail();
	$mail->CharSet = 'UTF-8';
	try {
		$mail->AddAddress($value['mail'], $value['name']);
		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
		$mail->Subject = 'Новый запрос на индивидуальный заказ №'.$callbackID;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($mailText);
		$mail->Send();
		}
	catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
	catch (Exception $e) {$e->getMessage();}

	if ($value['telegramID']!=0) {
	    $telegramMessage['chat_id']=$value['telegramID'];
	    $tg->sendj($telegramMessage);
	    }
	}
		$url='/customSuccess?id='.$callbackID;
		//header('Location: '.$url);
		}
}




//создание контрагента
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='createAgent') {
	$check=0;
	$agentType=$_POST['agentType'];
	if ($agentType===1) {
		$name=$_POST['name'];
		$phone=$_POST['phone'];
		$mail=$_POST['mail'];
		}
	elseif ($agentType===2) {
		}
	else $check=0;
	}

//запрос на обратный звонок
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='callback') {
	$cbName=$_POST['name'];
	$cbPhone=$_POST['phone'];
	$captcha=$_POST['g-recaptcha-response'];
	unset($_POST);
	$counter=0;
	$callbackResult='';
	if ($cbName!='') $counter++; else $callbackResult=$callbackResult.'Введите имя.<br>';
	if ($cbPhone!='') $counter++; else $callbackResult=$callbackResult.'Введите телефон.<br>';

	if (!isset($captcha) || $captcha==='') $callbackResult=$callbackResult.'Проверка не пройдена.<br>';
  else {
    if (checkRecaptchaResponseCurl($captcha)===TRUE) $counter++;
    else $callbackResult=$callbackResult.'Проверка не пройдена.<br>';
  }

  if ($counter>2) {
  	$query='SELECT * FROM `callbacks` WHERE phone=?s AND status=0';
  	$callback=$go->getRow($query,$cbPhone);
  	if ($go->affectedRows()===0) $counter++; else $callbackResult=$callbackResult.'Заявка с такими данными уже в очереди.<br>';
  }

	if ($counter===4) {
		$query='INSERT INTO `callbacks`(`name`, `phone`) VALUES (?s,?s)';
		$go->query($query,$cbName,$cbPhone);
		$callbackID=$go->insertId();

		$date=date("d.m.Y, H:i:s");
		$mailText='Поступил новый запрос на обратный звонок.<br><br>
		Номер запроса: '.$callbackID.'<br>
		Имя: '.$cbName.'<br>
		Телефон: '.$cbPhone.'<br>
		Дата и время: '.$date;

//рассылка админам
$telegramMessage=array(
    'method'=>'sendMessage',
    'text'=>'Просит перезвонить (imige.ru): '.$cbName.PHP_EOL.'Телефон: '.$cbPhone,
    'chat_id'=>'',
    'disable_notification'=>'true'
    );
$telegramMessage['disable_notification']= (isWorkingHours()) ? 'false' : 'true';

$query='SELECT * FROM adminDelivery WHERE active=1';
$delivery=$go->getAll($query);

foreach($delivery as $key=>$value) {
	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	$mail->IsSendmail();
	$mail->CharSet = 'UTF-8';
	try {
		$mail->AddAddress($value['mail'], $value['name']);
		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
		$mail->Subject = 'Запрос обратного звонка №'.$callbackID;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($mailText);
		$mail->Send();
		}
	catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
	catch (Exception $e) {$e->getMessage();}

	if ($value['telegramID']!=0) {
	    $telegramMessage['chat_id']=$value['telegramID'];
	    $tg->sendj($telegramMessage);
	    }
	}
//админам написали, пишем менеджерам
$telegramMessage['reply_markup']=array(
        "inline_keyboard" => array(
            array(array('text' => 'Принять', 'callback_data' => 'action=accept&what=callback&id='.$callbackID))
            )
        );

$query='SELECT * FROM managers WHERE active=1';
$delivery=$go->getAll($query);

foreach($delivery as $key=>$value) {
	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
	$mail->IsSendmail();
	$mail->CharSet = 'UTF-8';
	try {
		$mail->AddAddress($value['mail'], $value['name']);
		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
		$mail->Subject = 'Запрос обратного звонка №'.$callbackID;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($mailText);
		$mail->Send();
		}
	catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
	catch (Exception $e) {$e->getMessage();}

	if ($value['telegramID']!=0) {
	    $telegramMessage['chat_id']=$value['telegramID'];
	    $tg->sendj($telegramMessage);
	    }
	}

		$url='/callbackSuccess?id='.$callbackID;
		header('Location: '.$url);
		}
	}


//вход на сайт
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='login') {
	$tryLogin=$_POST['login'];
	$tryPass=$_POST['pass'];
	$loginSuccess=false;
	unset($_POST);
	$query='SELECT * FROM `users` WHERE mail=?s';
	$user=$go->getRow($query,$tryLogin);
	if ($go->affectedRows()===1) {
		$originPass=$user['pass'];
		$salt=$user['salt'];
		$newTryPass=hash('sha512',$tryPass.$salt);
		if ($newTryPass===$originPass) {
      $loginSuccess=true;
    } else {
      //Наруже знать не обязательно логин или пароль.
      $loginResult = "Ошибка авторизации. Неверный логин или пароль.";
    }
	} else {
    //Наруже знать не обязательно логин или пароль. Юзеров тоже искать не надо помогать.
    $loginResult = "Ошибка авторизации. Неверный логин или пароль.";
  }
	if ($loginSuccess) {
		$_SESSION['user_mail']=$user['mail'];
		$_SESSION['user_name']=$user['name'];
		$_SESSION['user_id']=$user['ID'];
		$_SESSION['user_in']=true;

		//складываем корзины
		if (isset($_COOKIE['cartToken'])) {
	    $token=$_COOKIE['cartToken'];
	    $query='SELECT content FROM carts WHERE token=?s ORDER BY lastUpdate DESC LIMIT 1';
          $c1=$go->getOne($query,$_COOKIE['cartToken']);
          if ($go->affectedRows()!=1 || $c1=='') $c1=array();
          else $c1=unserialize($c1);
					unset($_COOKIE['cartToken']);
			    setcookie('cartToken', '', time() - 3600, '/'); // empty value and old timestamp
	    } else $token='';
		
		$userHasCart=false;
	    $query='SELECT content FROM carts WHERE userID=?i AND token="" ORDER BY lastUpdate DESC LIMIT 1';
	    $userID=(integer)$user['ID'];
        $c2=$go->getOne($query,$userID);
        if ($go->affectedRows()!=1 || $c2=='') $c2=array();
        else {
            $c2=unserialize($c2);
            $userHasCart=true;
            }
		combineCarts($c1,$c2); //суммируем корзины
		/*
		$userHasCart=false;
	    $query='SELECT content FROM carts WHERE userID=?s ORDER BY lastUpdate DESC LIMIT 1';
        $c2=$go->getOne($query,$_SESSION['user_id']);
        if ($go->affectedRows()!=1 || $c2=='') $c2=array();
        else {
            $c2=unserialize($c2);
            $userHasCart=true;
            }

		combineCarts($c1,$c2); //суммируем корзины
		$cart=serialize($c1);
        recountCart($cart); //пересчитываем корзину и записываем в сессию


// 		print_r($c1);
// 		echo '<br>';
// 		print_r($_SESSION['cart']);
// 		echo '<br>';
// 		print_r(serialize($cart));

		if ($userHasCart) $query='UPDATE `carts` SET `token`="",`content`=?s WHERE userID=?i';
		else $query='INSERT INTO `carts`(`token`,`content`,`userID`) VALUES ("",?s,?i)';
		$go->query($query,serialize($cart),$_SESSION['user_id']);

		$query='UPDATE `carts` SET `content`="" WHERE userID=0 AND token=?s'; //обнуляем т-корзину
		$go->query($query,$token);*/
		$query='INSERT INTO `carts`(`token`,`content`,`userID`) VALUES ("",?s,?i) ON DUPLICATE KEY UPDATE `token`="",`content`=?s';
		$go->query($query,serialize($c1),$userID,serialize($c1));
		$query='UPDATE `carts` SET `content`="" WHERE userID=0 AND token=?s'; //обнуляем т-корзину
		$go->query($query,$token);

// 		конец работы с корзиной

		$url='/';
		header('Location: '.$url);
	}	else {
		unset($_SESSION['user_mail']);
		unset($_SESSION['user_name']);
		unset($_SESSION['user_id']);
		$_SESSION['user_in']=false;
		}
}

//регистрация на сайте
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='register') {
    //подготовка
    $check=0;
    $regData['name']=$_POST['name'];
    $regData['login']=$_POST['login'];
    $regData['phone']=$_POST['phone'];
    $regData['city']=$_POST['city'];
    $regData['address']=$_POST['address'];
    $regData['pass']=$_POST['pass'];
    $regData['pass2']=$_POST['pass2'];
	unset($_POST);
	$registerResult=array();

	$regData['name']==='' ? array_push($registerResult,'ФИО не заполнено.') : $check++;

	if (filter_var($regData['login'], FILTER_VALIDATE_EMAIL)) {
		if ($go->numRows($go->query('SELECT * FROM `users` WHERE mail=?s',$regData['login']))===0) $check++;
		else array_push($registerResult,'Этот адрес электронной почты занят. Попробуйте ввести другой адрес.');
		}
	else array_push($registerResult,'Адрес электронной почты не заполнен или указан в неверном формате.');

	$regData['city']==='' ? array_push($registerResult,'Поле "Город" не заполнено.') : $check++;

	if ($regData['pass']==='') array_push($registerResult,'Пароль не заполнен.');
	else {
		if (preg_match("/[а-я]/i", $regData['pass'])) array_push($registerResult,'Пароль может содержать только латинские буквы и цифры.');
		else $check++;
		}
	$regData['pass']!=$regData['pass2'] ? array_push($registerResult,'Пароли не совпадают.') : $check++;
	//если проверка пройдена - прописываем юзера
	if($check===5) {
		array_push($registerResult,'Регистрируем!');
		$salt=hash('sha512',uniqid());
		$pass=hash('sha512',$regData['pass'].$salt);
		$query='INSERT INTO `users`(`name`, `mail`, `phone`, `city`, `address`, `pass`, `salt`) VALUES (?s,?s,?s,?s,?s,?s,?s)';
		$go->query($query,$regData['name'],$regData['login'],$regData['phone'],$regData['city'],$regData['address'],$pass,$salt);

        $telegramMessage=array(
            'method'=>'sendMessage',
            'text'=>'Зарегистрирован новый пользователь.'.PHP_EOL.$regData['name'].PHP_EOL.$regData['login'].PHP_EOL.$regData['phone'].PHP_EOL.$regData['city'].PHP_EOL.$regData['address'],
            'chat_id'=>'',
            'disable_notification'=>'true'
            );
        $telegramMessage['disable_notification']= (isWorkingHours()) ? 'false' : 'true';
        $adminsTelegramIDs=$go->getCol('SELECT telegramID FROM adminDelivery WHERE active=1 AND telegramID!=0');
		foreach($adminsTelegramIDs as $telegramID) {
            $telegramMessage['chat_id']=$telegramID;
            $tg->sendj($telegramMessage);
		    }

		$url='/login';
		header('Location: '.$url);
		}
	}

//Изменение пароля
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='pwdchng') {
    if (isset($_SESSION) AND array_key_exists('user_in', $_SESSION) AND $_SESSION['user_in'] === TRUE) {
      $check=0;
      $pwdData['login']=$_SESSION['user_mail'];
      $pwdData['ID']=(integer)$_SESSION['user_id'];
      $pwdData['pass']=$_POST['pass'];
      $pwdData['pass2']=$_POST['pass2'];
      $captcha=$_POST['g-recaptcha-response'];
      unset($_POST);
      $pwdchngResult=array();

      if (!isset($captcha) || $captcha==='') array_push($pwdchngResult,'Проверка каптчи не пройдена.');
      else {
      if (checkRecaptchaResponseCurl($captcha)===TRUE) $check++;
      else array_push($pwdchngResult,'Проверка каптчи не пройдена.');
      }

      // На всякий случай - а есть ли юзер?
      if ($pwdData['login']==='') array_push($pwdchngResult,'Не выполнен вход!'); else $check++;
      if ($pwdData['ID']==0) array_push($pwdchngResult,'Не выполнен вход!'); else $check++;

      if ($pwdData['pass']==='') array_push($pwdchngResult,'Пароль не заполнен.');
      else {
          if (preg_match("/[а-я]/i", $pwdData['pass'])) array_push($pwdchngResult,'Пароль может содержать только латинские буквы и цифры.');
          else $check++;
          }
      $pwdData['pass']!=$pwdData['pass2'] ? array_push($pwdchngResult,'Пароли не совпадают.') : $check++;
      //если проверка пройдена - прописываем юзера
      if($check===5) {
          //$query = "SELECT mail, salt FROM users WHERE mail=?s LIMIT 1";

          $query = "SELECT `mail`, `salt` FROM `users` WHERE `ID`=?i LIMIT 1";
          //$userData = $go->getRow($query, $pwdData['login']);
          $userData = $go->getRow($query, $pwdData['ID']);
          if ($userData!='') {
              $salt = $userData['salt'];
              $pass=hash('sha512',$pwdData['pass'].$salt);
              $query = 'UPDATE `users` SET `pass`=?s WHERE `ID`=?i';
              $go->query($query, $pass, $pwdData['ID']);
              }

          $url='/login';
          header('Location: '.$url);
          }
    }
}

//Восстановление пароля
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='forget') {
	//подготовка
  $Success = FALSE;
	$check=0;
  $Data = Array();
	$Data['login'] = $_POST['login'];
  $captcha=$_POST['g-recaptcha-response'];
	unset($_POST);
	$sentResult=array();

	if (!isset($captcha) || $captcha==='') array_push($sentResult,'Проверка каптчи не пройдена.');
  else {
    if (checkRecaptchaResponseCurl($captcha)===TRUE) $check++;
    else array_push($sentResult,'Проверка каптчи не пройдена.');
  }

	if (filter_var($Data['login'], FILTER_VALIDATE_EMAIL)) {
    if ($check>0){
  		if ($go->numRows($go->query('SELECT * FROM `users` WHERE mail=?s',$Data['login']))!==0) $check++;
  		else array_push($sentResult,'Адрес электронной почты не найден.');
    }
	}	else array_push($sentResult,'Адрес электронной почты не заполнен или указан в неверном формате.');

	//если проверка пройдена - прописываем юзера
	if($check===2) {
    $query = "SELECT ID, name, phone, address, mail, salt FROM users WHERE mail=?s LIMIT 1";
		$userData = $go->getRow($query, $Data['login']);
    //$userData=$go->getAll($q, $_SESSION['user_id']);
    //var_dump($userData);
		if ($userData!='') {
		  $salt = $userData['salt'];
      $newpass = substr(md5(uniqid(rand())), 0, 8);
      $userData['ID'] = (integer)$userData['ID'];
		  $pass = hash('sha512',$newpass.$salt);
		  $query = 'UPDATE `users` SET `pass`=?s WHERE `ID`=?i';
		  $go->query($query, $pass, $userData['ID']);
      //var_dump($newpass);
      //Логин: '.$Data['login'].'<br>
  		$mailText='Сгенерирован новый пароль.<br><br>
  		Пароль: '.$newpass.'<br><br>
      	<br>
        Вы можете изменить его на сайте <a href="imige.ru/pwdchng">imige.ru</a>.';

    	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    	$mail->IsSendmail();
    	$mail->CharSet = 'UTF-8';
    	try {
    		$mail->AddAddress($Data['login'], '');
    		$mail->SetFrom('no_reply@imige.ru', 'ООО "Имидж"');
    		$mail->Subject = 'Новый пароль для imige.ru';
    		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
    		$mail->MsgHTML($mailText);
    		$mail->Send();
    		}
    	catch (\PHPMailer\PHPMailer\Exception $e) {$e->errorMessage();}
    	catch (Exception $e) {$e->getMessage();}
      $Success = TRUE;
    }
    /*
		$salt=hash('sha512',uniqid());
		$pass=hash('sha512',$regData['pass'].$salt);
		$query='INSERT INTO `users`(`name`, `mail`, `phone`, `address`, `pass`, `salt`) VALUES (?s,?s,?s,?s,?s,?s)';
		$go->query($query,$regData['name'],$regData['login'],$regData['phone'],$regData['address'],$pass,$salt);

        $telegramMessage=array(
            'method'=>'sendMessage',
            'text'=>'Зарегистрирован новый пользователь.'.PHP_EOL.$regData['name'].PHP_EOL.$regData['login'].PHP_EOL.$regData['phone'].PHP_EOL.$regData['address'],
            'chat_id'=>'',
            'disable_notification'=>'true'
            );
        $telegramMessage['disable_notification']= (isWorkingHours()) ? 'false' : 'true';
        $adminsTelegramIDs=$go->getCol('SELECT telegramID FROM adminDelivery WHERE active=1 AND telegramID!=0');
		foreach($adminsTelegramIDs as $telegramID) {
            $telegramMessage['chat_id']=$telegramID;
            $tg->sendj($telegramMessage);
		    }

		$url='/login';
		header('Location: '.$url);
    */
	}
}

//прием и запись заказа
if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']==='confirmOrder') {
	$orderData['name']=$_POST['oFIO'];
	$orderData['phone']=$_POST['oPhone'];
	$orderData['mail']=$_POST['oMail'];
	$orderData['mngr']=(integer)$_POST['mngr'];
	$captcha=$_POST['g-recaptcha-response'];

	unset($_POST);
	$check=0;
	$sentResult = array();
  $ErrorFields = array();
	if ($orderData['name']==='') {
    array_push($sentResult,'Поле ФИО не заполнено.');
    array_push($ErrorFields,'name');
  } else $check++;
	if (!filter_var($orderData['mail'], FILTER_VALIDATE_EMAIL)) {
    array_push($sentResult,'Адрес электронной почты не заполнен или указан в неверном формате.');
    array_push($ErrorFields,'mail');
  } else $check++;
	if ($orderData['phone']==='') {
    array_push($sentResult,'Необходимо указать номер телефона.');
    array_push($ErrorFields,'phone');
  } else $check++;
	//((!isset($_SESSION['cart']['count'])||($_SESSION['cart']['count']==0))) ? array_push($sentResult,'Корзина пустая, невозможно записать заказ.') : $check++;

	if (!isset($captcha) || $captcha==='') array_push($sentResult,'Проверка каптчи не пройдена.');
    else {
        if (checkRecaptchaResponseCurl($captcha)===TRUE) $check++;
        else array_push($sentResult,'Проверка каптчи не пройдена.');
    }

	if ($check===4) {
		//записываем покупателя
		$query='INSERT INTO `customers`(`name`, `phone`, `mail`, `user`) VALUES (?s,?s,?s,?i)';
		if (isset($_SESSION['user_id'])) $userID=$_SESSION['user_id'];
		else $userID=0;
		$go->query($query,$orderData['name'],$orderData['phone'],$orderData['mail'],$userID);
		$customerID=$go->insertId();
		//записываем заказ
		$query='INSERT INTO `orders`(`customer`, `status`,`manager`) VALUES (?i,?i,?i)';
		$go->query($query,$customerID,0,$orderData['mngr']);
		$orderID=$go->insertId();
		//записываем состав заказа
		foreach ($_SESSION['cart'] as $key=>$value) {
			$productID=$key;
			$count=$value['count'];
			$cost=$value['cost'];
			$query='INSERT INTO `ordersContent`(`orderID`, `productID`, `count`,`cost`) VALUES (?i,?i,?i,?s)';
			$go->query($query,$orderID,$productID,$count,$cost);
			}
		//отправляем на промежуточную
		$url='/orderProcess?order='.$orderID;
		header('Location: '.$url);
		}
}

function imgSort($a,$b) {
	if (strrchr($a,'-')>strrchr($b,'-')) return(1);
    elseif (strrchr($a,'-')<strrchr($b,'-')) return(-1);
    else return(0);
    }

function bDate($d) {
    $month=date("m",$d);
    if ($month=="01") {$month="января";}
    elseif ($month=="02") {$month="февраля";}
    elseif ($month=="03") {$month="марта";}
    elseif ($month=="04") {$month="апреля";}
    elseif ($month=="05") {$month="мая";}
    elseif ($month=="06") {$month="июня";}
    elseif ($month=="07") {$month="июля";}
    elseif ($month=="08") {$month="августа";}
    elseif ($month=="09") {$month="сентября";}
    elseif ($month=="10") {$month="октября";}
    elseif ($month=="11") {$month="ноября";}
    elseif ($month=="12") {$month="октября";}

    return date("d",$d).' '.$month.' '.date("Y",$d);
    }

function isWorkingHours(){
    $now=(int)date('H');
    if ($now>=8 && $now<=19) return true;
    else return false;
    }

function combineCarts(&$c1,&$c2) {
    if ($c2 != null) {
    foreach ($c2 as $key=>$val)
        if (isset($c1[$key])) $c1[$key]['count']+=$c2[$key]['count'];
        else $c1[$key]=$c2[$key];
    }
}

function recountCart($cart){
    global $go;
    global $prices;

    if (isset($cart) AND $cart != null) {
      $cart=unserialize($cart);
      if (isset($cart) AND $cart != null) {
      foreach($cart as $key=>$val) {
          // $query='SELECT * FROM products WHERE ID=?i AND active=1';
          // $product=$go->getRow($query,$key);
          // $cart[$key]['cost']=$product['cost'];
          $query='SELECT uID FROM products WHERE ID=?i AND active=1';
          $productuID=$go->getOne($query,$key);
          $cart[$key]['cost']= (isset($prices[$productuID])) ? $prices[$productuID] : 0;
          }
      $_SESSION['cart']=$cart;
      $_SESSION['cart_count']=count($_SESSION['cart']);
  	  //сначала обнулим стоимость:
      $_SESSION['cart_cost']=0;
      $_SESSION['cart_total']=0;
      //стоимость корзины (перемножаем цены на количество и складываем):
      foreach ($_SESSION['cart'] as $key=>$value) {
      	$_SESSION['cart_cost'] += $_SESSION['cart'][$key]['cost'] * $_SESSION['cart'][$key]['count'];
      	$_SESSION['cart_total'] += $_SESSION['cart'][$key]['count'];
          }
      } else {
        $_SESSION['cart_cost']=0;
        $_SESSION['cart_total']=0;
      }
    } else {
      $_SESSION['cart_cost']=0;
      $_SESSION['cart_total']=0;
    }

    return 0;
}

function checkRecaptchaResponseCurl($captcha)
{
    //$captcha = $request->getPost("g_recaptcha_response");
    if (isset($captcha) AND !empty($captcha)) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array('secret' => '6LeIt0wUAAAAAL8DVF5FhxMgzCsRxf_1cwBDZllH', 'response' => $captcha, 'remoteip' => $_SERVER["REMOTE_ADDR"]);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        //if(!empty($response)) $decoded_response = json_decode($response);
        //$success = false;
        if ($response AND !empty($response)) {
            $decoded_response = json_decode($response);
            //if ($decoded_response && $decoded_response->success && $decoded_response->action == $captcha_action && $decoded_response->score > 0.5) {
            //    $success = $decoded_response->success;
            //    // обрабатываем данные формы, которая защищена капчей
            //} else {
            //    // прописываем действие, если пользователь оказался ботом
            //    return FALSE;
            //}
            if ($decoded_response->success===TRUE) {
              return TRUE;
            }
        }
    }
    return FALSE;
    /*
        $url='https://www.google.com/recaptcha/api/siteverify';

        //$params = array('secret' => '6LeIt0wUAAAAAL8DVF5FhxMgzCsRxf_1cwBDZllH', 'response' => $captcha, 'remoteip' => $_SERVER["REMOTE_ADDR"]);
        //$result = file_get_contents($url, false, stream_context_create(array('http' => array('method'  => 'POST', 'content' => http_build_query($params)))));
        //$parse=json_decode($result);

        $params = array('secret' => '6LeIt0wUAAAAAL8DVF5FhxMgzCsRxf_1cwBDZllH', 'response' => $captcha, 'remoteip' => $_SERVER["REMOTE_ADDR"]);
        $query = http_build_query($params);
        $options = array(
          'http' => array(
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
            "Content-Length: ".strlen($query)."\r\n".
            "User-Agent:MyAgent/1.0\r\n",
            'method'  => "POST",
            'content' => $query,
          ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $parse=json_decode($result);
    */
}
