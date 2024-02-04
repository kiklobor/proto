<?php

/**
 * Sample Yandex.Market Yml generator
 */

class ImigeYmlGeneratorADV extends YmlGeneratorADV {

    /// переопределяем свойсва-методы
    //public $shopInfoElements = array('name','company','url','platform','version','agency','email');
    /**/
    public function run() {
        $this->beforeWrite();
        
        $this->writeShopInfo();
        $this->writeCurrencies();
        $this->writeDeliveryoptions();
        $this->writePickupoptions();
        $this->writeCategories();
        $this->writeOffers();
        
        $this->afterWrite();
    }
    /**/
    ///end переопределяем свойсва-методы
    
    
    protected function shopInfo() {
        return array(
            'name'=>'Центр полиграфии "Имидж"',
            'company'=>'ООО "Имидж"',
            'url'=>'https://imige.ru',
            'platform'=>'PHP',
            'version'=>'7',
            'agency'=>'ООО "Имидж"',
            'email'=>'zakaz@imige.ru',
            'delivery-options' => array('cost'=>550, 'days'=>'1-3', 'orderbefore'=>18),
            'pickup-options' => array('cost'=>0, 'days'=>'1-3', 'orderbefore'=>18)
      );
    }

    protected function writeDeliveryoptions() {        
        $engine = $this->getEngine();
        $si = $this->shopInfo();
        //var_dump($si);     
        $engine->startElement('delivery-options');       
        $this->addDeliveryoptions($si['delivery-options']['cost'], $si['delivery-options']['days'], $si['delivery-options']['orderbefore']);
        $engine->fullEndElement();
    }
    
    protected function writePickupoptions() {        
        $engine = $this->getEngine();
        $si = $this->shopInfo();
        //var_dump($si);   
        $engine->startElement('pickup-options');       
        $this->addDeliveryoptions($si['pickup-options']['cost'], $si['pickup-options']['days'], $si['pickup-options']['orderbefore']);
        $engine->fullEndElement();
    }
    
    protected function addDeliveryoptions($cost = 0, $days = 1, $orderbefore = 18) {
        $engine = $this->getEngine();
        $engine->startElement('option');
        $engine->writeAttribute('cost', $cost);
        $engine->writeAttribute('days', $days);
		$engine->writeAttribute('order-before', $orderbefore);
        $engine->endElement();
    }
    
    protected function currencies() {
		$this->addCurrency('RUR');
    }
    
    protected function categories() {
		global $go;
		$query='SELECT * FROM `groups` WHERE active=1';
        $root_groups=$go->getAll($query);
		foreach($root_groups as $c) {
			$this->showTree($c['uID'], null, null, $c);
		}
    }
    
    protected function offers() {
		global $go;
		$from='products p';
		$where='p.active=1 AND p.yml_active=1';
		$groupby='p.ID';
		$pquery='SELECT p.* FROM '.$from.' WHERE '.$where.' GROUP BY '.$groupby.' ORDER BY p.name'; 
		$products=$go->getAll($pquery);
		$productsCount=$go->affectedRows();
		$offers = array();
		if ($productsCount>0) {
			foreach ($products as $row) {
				$cellsPrepare['name']=$row['name'];
				$cellsPrepare['id']=$row['ID'];
				$cellsPrepare['url'] = 'https://imige.ru/product/' . $row['url'];
				$cellsPrepare['article']=$row['articleFull'];

				if ($row['cost'] > 0) $cellsPrepare['cost'] = $row['cost'];
				else continue;

				$images=glob(FPATH.$row['uID'].'*');
				if (count($images)!=0) {
					usort($images,'imgSort');
					$cellsPrepare['photo']='https://imige.ru/' . $images[0];
				} else {
					$cellsPrepare['photo']='';
				}
				
				$params_query = "SELECT prop.name AS param_name, p.name AS param_value FROM `relProductsToValues` pv
					LEFT JOIN `relPropertiesToValues` propv ON pv.vuID = propv.vuID
					LEFT JOIN `properties` prop ON propv.puID = prop.uID
					LEFT JOIN `propertiesValues` p ON p.uID = pv.vuID
					WHERE pv.puID = '" . $row['uID'] . "'";
				$product_params = $go->getAll($params_query);
				$ym_params = array();
				foreach ($product_params as $p) {
					$ym_params[] = array(
						0 => $p['param_name'],
						2 => $p['param_value'],
					);
				}
                
        //$cellsPrepare['pickupoptions'] = TRUE;
        //$deliveryd = FALSE;
        //$availabled = false;
        
        //if (mb_strtolower($row['availability']) == mb_strtolower('Товар в наличии')) {
        if (strtolower($row['availability']) == strtolower('Товар в наличии')) {
          $cellsPrepare['available'] = true;
        } 
        else {
          $cellsPrepare['available'] = false;
        }
        
				$offers[] = array(
					'id' => $cellsPrepare['id'],
					'data' => array(
						'name' => $cellsPrepare['name'],
						'url' => $cellsPrepare['url'],
						'price' => $cellsPrepare['cost'],
						'currencyId' => 'RUR',
						'categoryId' => crc32($row['parent']),
						'picture' => $cellsPrepare['photo'],
						'delivery' => true,
						'pickup' => true,
						'vendor' => 'ООО "Имидж"',
						'description' => strip_tags($row['description']),
					),
					'params' => $ym_params,
					'type' => null,
					'available' => $cellsPrepare['available']
				//	'sales_notes'=>'При заказе от 5000 рублей - доставка бесплатно.'
				);
			
			}

       
			foreach($offers as $offer) {
				$this->addOffer(
					$offer['id'],         //1
					$offer['data'],       //2
          '',
					$offer['params'],     //3
					$offer['available'],  //4
				//	$offer['sales_notes'],//?
					null,                 //5
					null,				  //6
					null,                 //7
			        null                  //8
				);
			}
			
			
		}

    }
	
	public function showTree($category_id, $parent_id, $level, $info ) {
		global $go;
		$this->addCategory($info['name'], crc32($category_id), crc32($parent_id));
		$query='SELECT * FROM `subgroups` WHERE active=1 AND parent="' . $category_id . '"';
        $categories=$go->getAll($query);
		if (!empty($categories)) {
			foreach ($categories as $result) {
				$this->showTree($result['uID'], $result['parent'], null, $result);
			}
		} else {
			return;
		}
			
	}
    /*
		$id,
        $data,
        $availability,
		$params = array(),
		$available = false
    */
    
    // 4,5 
    protected function addOffer(
		$id,
        $data,
        $availability,
		$params = array(),
//		$type = null,
		$available = false
//		$bid = null,
//		$cbid = null
//		$group_id = null,
//		$selling_type = null,
    //$delivery = FALSE,
    //$pickupoptions = FALSE
	) {
        $engine = $this->getEngine();
        $engine->startElement('offer');
        $engine->writeAttribute('id', $id);
//        if ($type) 
//            $engine->writeAttribute('type', $type);
//		if ($group_id) 
//            $engine->writeAttribute('group_id', $group_id);
//		if ($selling_type)  --------------------------------------------------------------
//            $engine->writeAttribute('selling_type', $selling_type);
        $engine->writeAttribute('available', $available ? 'true' : 'false');
	    $engine->writeElement('sales_notes', "При заказе от 5000 рублей - доставка бесплатно.");
//        if ($bid) {
//            $engine->writeAttribute('bid', $bid);
//            if ($cbid) 
//                $engine->writeAttribute('cbid', $cbid);
        //}
        foreach($data as $elm=>$val) {
            if (in_array($elm,$this->offerElements)) {
                if (!is_array($val)) {
                    $val = array($val);
                }
                foreach($val as $value) {
                    $engine->writeElement($elm, $value);
                }
            }
        }
 /*       if ($delivery !== FALSE) { //-------------------------------------
          if ($delivery[0] !== TRUE) {
            $engine->startElement('delivery-options'); 
            $this->addDeliveryoptions($delivery['cost'], $delivery['days'], $delivery['order-before']);
            $engine->endElement();
            $engine->startElement('pickup-options'); 
            $this->addDeliveryoptions($pickupoptions['cost'], $pickupoptions['days'], $delivery['order-before']);
            $engine->endElement();
          }
        } */
        

        /*
        $engine->startElement('delivery-options'); {
            $engine->startElement('option'); {
                $engine->writeAttribute('cost', 550);
                $engine->writeAttribute('days', $days); }
            $engine->endElement(); }
        $engine->endElement();
        /**/

        foreach($params as $param) {
             $engine->startElement('param');
             $engine->writeAttribute('name', $param[0]);
             //if ($param[1])
             //    $engine->writeAttribute('unit', $param[1]);
             $engine->text($param[2]);
             $engine->endElement();
        }
        $engine->fullEndElement();
    }

	
}