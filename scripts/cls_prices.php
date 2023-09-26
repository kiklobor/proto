<?
class prices extends SafeMySQL{
    public $pricesD=array();
    public $prices=array();
    public $l1=10000;
    public $l2=50000;
    public $nvlf=0.95; // 95%, NO VAT LIMIT FACTOR
    public $s1=0; // стоимость корзины по рознице
    public $s2=0; // стоимость корзины по мелкому опту
    public $mapping=array();
    public $discountLevel=0;
    protected $vatKeys=array(2,3);
    
    function __construct(){
		parent::__construct();
		$this->productMapping();
		}
    
    // создает массив вида $a[ID товара]=[uID товара], необходим для замены цен в итоговом наборе в последующем
    protected function productMapping(){
        $products=$this->getAll('SELECT ID, uID FROM products WHERE active=1');
        foreach($products as $val) $this->mapping[$val['ID']]=$val['uID']; // маппинг продуктов по ID и uID
        }
    
    public function setVat(){
        if (!isset($_SESSION['pvn'])) $_SESSION['pvn']=true;
        if (isset($_POST) AND array_key_exists('action', $_POST) AND $_POST['action']=='pvn-switch') { // переключатель
            $_SESSION['pvn']= ($_SESSION['pvn']==false) ? true : false; 
            unset($_POST['action']);
            header('Location: '.$_SERVER['REQUEST_URI']);
            }
        $this->vatKeys= ($_SESSION['pvn']) ? array(2,3) : array(4,5);
        }
        
    public function defaultPrices(){
        /* собираем массив массивов с дефолтными ценами, где
        ключ 1 - розничные цены
        ключ 2 - мелкий опт с НДС
        ключ 3 - опт с НДС
        ключ 4 - мелкий опыт без НДС
        ключ 5 - опт без НДС
        каждый массив имеет вид: $a[uID товара]=(float) цена */
        $pricesD=array(
            1 => array(),
            2 => array(),
            3 => array(),
            4 => array(),
            5 => array()
            );

        $pricesPrep=$this->getAll('SELECT * FROM pricelists pl, prices pr WHERE pr.luID=pl.uID AND pl.asDefault>0 AND pl.active=1 AND pr.price>0');
        foreach ($pricesPrep as $item) $pricesD[$item['asDefault']][$item['puID']]=$item['price'];
        $this->pricesD=$pricesD;
        return $pricesD;
        }
    
    /* эта функция создаем массив рабочих цен, по которым непосредственно идет взаимодействие с покупателем. Значения этого массива отражаются в поле "ваша цена" и являются окончательными для расчетов */
    public function makeWorkingPricesSet(){
        $prices=array();
        foreach($this->pricesD[1] as $uid=>$price) $prices[$uid]=$price;
        
        if (isset($_SESSION) AND array_key_exists('user_in', $_SESSION) AND $_SESSION['user_in']) {
            if (!$_SESSION['pvn']) $this->l2*=$this->nvlf;
            $this->s1=$this->calcCartBy1(); // стоимость корзины по рознице
            $this->s2=$this->calcCartBy2(); // стоимость корзины по мелкому опту
            
            /* оптимальное сравнение цен: сначала верхний лимит, потом нижний 
            if ($this->s2>=$this->l2) {
                $this->discountLevel=2;
                foreach ($this->pricesD[$this->vatKeys[1]] as $uid=>$price) $prices[$uid]=$price; // замещение розничных цен оптовыми ценами
                }
            elseif ($this->s1>=$this->l1) {
                $this->discountLevel=1;
                foreach ($this->pricesD[$this->vatKeys[0]] as $uid=>$price) $prices[$uid]=$price; // замещение розничных цен мелкооптовыми ценами
                }*/
                
            
            if ($this->s1>=$this->l1) {
                $this->discountLevel=1;
                foreach ($this->pricesD[$this->vatKeys[0]] as $uid=>$price) $prices[$uid]=$price; // замещение розничных цен мелкооптовыми ценами
                }
            if ($this->s2>=$this->l2) {
                $this->discountLevel=2;
                foreach ($this->pricesD[$this->vatKeys[1]] as $uid=>$price) $prices[$uid]=$price; // замещение розничных цен оптовыми ценами
                }
            // ищем ИПЛ: если он существует, заменяем цены в наборе ценами из ИПЛ
            $pricesPrep=$this->getAll('SELECT pr.puID, pr.price FROM pricelists pl, prices pr, users u WHERE u.pricelist=pl.ID AND u.ID=?i AND pl.active=1 AND pr.luID=pl.uID',$_SESSION['user_id']);
            foreach($pricesPrep as $item) $prices[$item['puID']]=$item['price'];
            }
        $this->prices=$prices;
        return $prices;
        }
        
    // обсчет корзины по розничным ценам
    public function calcCartBy1(){
        $s=0;
        foreach ($_SESSION['cart'] as $key=>$value) {
            $s+=$this->pricesD[1][$this->mapping[$key]] * $_SESSION['cart'][$key]['count'];
		    }
		return $s;
        }
    
    // обсчет корзины по мелкому опту
    public function calcCartBy2(){
        $s=0;
        foreach ($_SESSION['cart'] as $key=>$value) {
            $s+= $this->pricesD[$this->vatKeys[0]][$this->mapping[$key]] * $_SESSION['cart'][$key]['count'];
		    }
		return $s;
        }
    
    // обсчет корзины по крупному опту
    public function calcCartBy3(){
        }
}