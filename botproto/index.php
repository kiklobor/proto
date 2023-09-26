<?
//token c810b024-6bf5-472b-ab32-26b138bcad4c
//imigetestbot: 799513981:AAHdtCIqAtoVP8-P869C4tBSf4by8xirfEE
//$token='c810b024-6bf5-472b-ab32-26b138bcad4c'; пока не используется

// $url='https://api.telegram.org/bot799513981:AAHdtCIqAtoVP8-P869C4tBSf4by8xirfEE/sendMessage?chat_id=302711179&disable_notification=true&text='.urlencode('test');
// file_get_contents($url);

echo 'bot';

require('../scripts/cls_telegram_proto.php');
$tg=new telegram(true);
$tg->processRequest();

require('setup.php');
$go=new SafeMySQL();

switch ($tg->requestType) {
    case 'msg':include('prg_process_message.php');break;
    case 'cbk':include('prg_process_callback.php');break;
    default:include('prg_process_message.php');
    }

echo '<br>end';
?>