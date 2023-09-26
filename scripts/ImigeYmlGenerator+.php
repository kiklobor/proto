<?php

/**
 * Sample Yandex.Market Yml generator
 */

class ImigeYmlGenerator extends YmlGenerator {

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
            'delivery-options' => array('cost'=>300, 'days'=>'1-3'),
            'pickup-options' => array('cost'=>0, 'days'=>1)
      );
    }

    protected function writeDeliveryoptions() {        
        $engine = $this->getEngine();
        $si = $this->shopInfo();
        //var_dump($si);     
        $engine->startElement('delivery-options');       
        $this->addDeliveryoptions($si['delivery-options']['cost'], $si['delivery-options']['days']);
        $engine->fullEndElement();
    }
    
    protected function writePickupoptions() {        
        $engine = $this->getEngine();
        $si = $this->shopInfo();
        //var_dump($si);   
        $engine->startElement('pickup-options');       
        $this->addDeliveryoptions($si['pickup-options']['cost'], $si['pickup-options']['days']);
        $engine->fullEndElement();
    }
    
    protected function addDeliveryoptions($cost = 0, $days = 1) {
        $engine = $this->getEngine();
        $engine->startElement('option');
        $engine->writeAttribute('cost', $cost);
        $engine->writeAttribute('days', $days);
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
		/*
		$categories = [
			[
				'name' => 'Детская текстильная обувь',
				'id' => 1,
				'parentId' => null,
			],
		];
        foreach($categories as $category) {
            $this->addCategory($category['name'],$category['id'],$category['parentId']);
        }  */
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
				//$cellsPrepare['delivery']=$row['availability']; 

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
                
        $cellsPrepare['pickupoptions'] = TRUE;
        $deliveryd = FALSE;
        
        //if (mb_strtolower($row['availability']) == mb_strtolower('Товар в наличии')) {
        if (strtolower($row['availability']) == strtolower('Товар в наличии')) {
        
          $cellsPrepare['delivery'] = array(TRUE);
          $deliveryd = TRUE;
          //$deliveryd = TRUE;
          //die('1');
          //var_dump($cellsPrepare);
          //die('1');
          
        } elseif (strpos(strtolower($row['availability']),strtolower('Срок поставки'))!==FALSE) {
          $matches = array();
          if (preg_match("/\d+/i", $row['availability'], $matches)!== false) {
            $dayss = $matches[0];
            $cellsPrepare['delivery'] = array('cost'=>300, 'days'=>($dayss+1).'-'.($dayss+3));
            $cellsPrepare['pickupoptions'] = array('cost'=>0, 'days'=>($dayss+1));
          } else
            $cellsPrepare['delivery'] = array('cost'=>300, 'days'=>'20'); //нечто а вдруг?
          $deliveryd = TRUE;
        } else {
          $cellsPrepare['delivery'] = FALSE;
        }
        //***************************************
        //$deliveryd = $row['availability'];
        //*************************************** 
        
				$offers[] = array(
					'id' => $cellsPrepare['id'],
					'data' => array(
						'name' => $cellsPrepare['name'],
						'url' => $cellsPrepare['url'],
						'price' => $cellsPrepare['cost'],
						'currencyId' => 'RUR',
						'categoryId' => crc32($row['parent']),
						'picture' => $cellsPrepare['photo'],
						'delivery' => $deliveryd,
						'pickup' => true,
						'vendor' => 'ООО "Имидж"',
						'description' => strip_tags($row['description']),
					),
					'params' => $ym_params,
					'available' => true,
					'type' => null,
					//'group_id' => $offer_data['product_id'],
          'delivery' => $cellsPrepare['delivery'],
          'pickupoptions' => $cellsPrepare['pickupoptions']
				);
			
			}

      /*
		1 $id,
    2    $data,
        //$d_cost,
        //$days,
        //$availability,
		3 $params = array(),
		4 $available=true,
		5 $type = 'vendor.model',
		6 $bid = null,
		7 $cbid = null,
		8 $group_id = null,
		9 $selling_type = null
    */
      
			foreach($offers as $offer) {
				$this->addOffer(
					$offer['id'],         //1
					$offer['data'],       //2
					$offer['params'],     //3
					$offer['available'],  //4
					$offer['type'],       //5
					null,                 //6
					null,                 //7
					$offer['group_id'],   //8
          null,                          //9
          $offer['delivery'],     //10
          $offer['pickupoptions'] //11
				);
			}
			
			
		}
		/*$controller = Yii::app()->controller;
		$offers = [];
		$connectionNew = Yii::app()->db_new;
		$command = $connectionNew->createCommand("
    		SELECT s.ID AS offer_id,
			 s.ID_goods AS product_id,
			 g.Name AS product_name,
			 pc.id AS category_id,
			 s.Price AS price,
			 s.DiscountPrice AS discount_price,
			 f.Name as vendor,
			 g.Description AS description,
			 g.Articul AS articul,
			 g.GUID AS product_guid,
			 s.Size AS offer_size,
			 s.Color AS offer_color,
			 pc.seo_url AS category_seo_url,
			 IF (pc.custom_name IS NOT NULL && pc.custom_name != '', pc.custom_name, pc.default_name) AS category_name
			FROM `Sizes` s
			LEFT JOIN Goods g ON s.ID_goods = g.ID 
			LEFT JOIN Catalog c ON c.ID = g.ID_Catalog 
			LEFT JOIN centropt_clean.product_categories pc ON pc.guid = c.PathKEY
			LEFT JOIN Fabrics f ON f.ID = g.ID_Fabrics
			WHERE Residue > 0
			GROUP BY s.Kod_1c
    	");
        
    	$data = $command->queryAll();
		foreach($data as $offer_data) {
			$detail_link = "https://centropt.ru" . $controller->createUrl('site/goods', array(
            	'cat_id' => $offer_data['category_id'],
            	's_cat_alias' => $offer_data['category_seo_url']?$offer_data['category_seo_url']:$offer_data['category_name'],
            	'good_id' => $offer_data['product_id'],
            	's_good_name' => $offer_data['product_name']
            ));
			$pictures = TemplateHelper::getGoodSourceImages($offer_data['product_guid']);
			
			if(!empty($pictures)) {
				$product_image_link = "https://centropt.ru" . $pictures[0];
			}
			if(isset($product_image_link)) {
				$offers[] = [
				'id' => $offer_data['offer_id'],
				'data' => [
					'name' => $offer_data['product_name'],
					'url' => $detail_link,
					'price' => (($offer_data['discount_price'] < $offer_data['price'])?$offer_data['discount_price']:$offer_data['price']),
					'currencyId' => 'RUR',
					'categoryId' => $offer_data['category_id'],
					'picture' => $product_image_link,
					'delivery' => true,
					'pickup' => true,
					'vendor' => $offer_data['vendor'],
					'description' => (($offer_data['description'])?$offer_data['description']:$offer_data['product_name']),
				],
				'params' => [
					[0 => 'Размер', 2 => $offer_data['offer_size']],
					[0 => 'Цвет', 2 => $offer_data['offer_color']],
					[0 => 'Артикул', 2 => $offer_data['articul']],
					[0 => 'Производитель', 2 => $offer_data['vendor']],
				],
				'available' => true,
				'type' => null,
				'group_id' => $offer_data['product_id'],
			];
			}
			
		}
        
        foreach($offers as $offer) {
            $this->addOffer(
				$offer['id'],
				$offer['data'],
				$offer['params'],
				$offer['available'],
				$offer['type'],
				null, 
				null,
				$offer['group_id'],
				'w'
			);
        }*/
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
    // 4,5 
    protected function addOffer(
		$id,
        $data,
        //$d_cost,
        //$days,
        //$availability,
		$params = array(),
		$available=true,
		$type = 'vendor.model',
		$bid = null,
		$cbid = null,
		$group_id = null,
		$selling_type = null,
    $delivery = FALSE,
    $pickupoptions = FALSE
	) {
        $engine = $this->getEngine();
        $engine->startElement('offer');
        $engine->writeAttribute('id', $id);
        if ($type) 
            $engine->writeAttribute('type', $type);
		if ($group_id) 
            $engine->writeAttribute('group_id', $group_id);
		if ($selling_type) 
            $engine->writeAttribute('selling_type', $selling_type);
        $engine->writeAttribute('available', $available ? 'true' : 'false');
        if ($bid) {
            $engine->writeAttribute('bid', $bid);
            if ($cbid) 
                $engine->writeAttribute('cbid', $cbid);
        }
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
        if ($delivery !== FALSE) {
          if ($delivery[0] !== TRUE) {
            $engine->startElement('delivery-options'); 
            $this->addDeliveryoptions($delivery['cost'], $delivery['days']);
            $engine->endElement();
            $engine->startElement('pickup-options'); 
            $this->addDeliveryoptions($pickupoptions['cost'], $pickupoptions['days']);
            $engine->endElement();
          }
        } 
        

        /*
        $engine->startElement('delivery-options'); {
            $engine->startElement('option'); {
                $engine->writeAttribute('cost', 300);
                $engine->writeAttribute('days', $days); }
            $engine->endElement(); }
        $engine->endElement();
        /**/

        foreach($params as $param) {
             $engine->startElement('param');
             $engine->writeAttribute('name', $param[0]);
             if ($param[1])
                 $engine->writeAttribute('unit', $param[1]);
             $engine->text($param[2]);
             $engine->endElement();
        }
        $engine->fullEndElement();
    }

	
}
