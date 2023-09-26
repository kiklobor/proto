<?
//Developed by Sergejs Germanovs
//https://germanovs.com
//
class telegram {
    private $url;
    private $token;
    private $record;
    
    private $contentRaw; //raw input
    public  $content; //decoded input
    
    public  $requestType;
    public  $cb; //callback query object
    public  $message; //message object
    public  $answer; //telegram answer to send command
    
    
    function __construct($record){
        $this->record=$record;
        $this->token='799513981:AAHdtCIqAtoVP8-P869C4tBSf4by8xirfEE'; //test
        // $this->token='745535068:AAGsc90rwDFEQzZ4kwS6RSV8moq_2EM3xto'; //live
        $this->url='https://api.telegram.org/bot'.$this->token.'/';
        }
    
    public function processRequest(){
            
        $this->contentRaw=file_get_contents("php://input");
        $this->content=json_decode($this->contentRaw, true);
        if (isset($this->content['callback_query'])) {
            $this->requestType='cbk';
            $this->cb=$this->content['callback_query'];
            $this->message=$this->content['callback_query']['message'];
            }
        else {
            $this->requestType='msg';
            $this->message=$this->content['message'];
            }
        
        if($this->record) $this->recordRequest();
        }
    
    public function send($text){
        $options=array(
            'chat_id' => $this->message['chat']['id'],
            'parse_mode' => 'HTML',
            'text' => $text
            );
        $method='sendMessage';
        $url = $this->url.$method.'?'.http_build_query($options);
        if($this->record) $this->recordAnswer('Answer url: '.$url);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);

        return $this->request($handle);
        }
    
    public function sendj($parameters){
        if (!$parameters) $parameters = array();
        else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
            }
        
        if (!isset($parameters['method']) || $parameters['method']=='') $parameters['method']='sendMessage';
        
        if($this->record) $this->recordAnswer('Content to send: '.json_encode($parameters));
        $handle = curl_init($this->url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        
        return $this->request($handle);
        }
    
    
    private function request($handle){
        $response = curl_exec($handle);
        if($this->record) {
            $this->recordAnswer('Answer content: '.$response);
            $this->answer=$response;
            }
        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
            }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
        
        if ($http_code >= 500) { //anti DDOS
            sleep(10);
            return false;
            } 
        elseif ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) throw new Exception('Invalid access token provided');
            return false;
            } 
        else {
            $response = json_decode($response, true);
            if (isset($response['description'])) error_log("Request was successfull: {$response['description']}\n");
            $response = $response['result'];
            }
        return $response;
        }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function record($text){
        file_put_contents('telegram_class_report.txt',$text.PHP_EOL,FILE_APPEND);
        }
    
    private function recordRequest(){
        $now=PHP_EOL.PHP_EOL.date("Y-m-d H:i:s").PHP_EOL;
        file_put_contents('telegram_class_request.txt',$now,FILE_APPEND);
        file_put_contents('telegram_class_request.txt',$this->contentRaw,FILE_APPEND);
        }
    
    private function recordAnswer($text){
        $now=PHP_EOL.PHP_EOL.date("Y-m-d H:i:s").PHP_EOL;
        file_put_contents('telegram_class_answer.txt',$now,FILE_APPEND);
        file_put_contents('telegram_class_answer.txt',$text.PHP_EOL,FILE_APPEND);
        }
        
    public function webhook($url){ //set webhook, can be used only once
    //$url="https://api.telegram.org/bot".$this->token."/setWebhook?url='.$url;
    //https://api.telegram.org/bot819277643:AAEPnrxgA1FdvnygyCuRsyw9tPHC5I3C_HY/getWebhookInfo
        }
}
?>