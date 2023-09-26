<?php

/**
 * Sample Yandex.Market Yml generator
 */

class ImigeYmlGenerator extends YmlGenerator {
    
    protected function shopInfo() {
        return array(
            'name'=>'Центр полиграфии "Имидж"',
            'company'=>'ООО "Имидж"',
            'url'=>'https://imige.ru',
            'platform'=>'PHP',
            'version'=>'7',
            'agency'=>'ООО "Имидж"',
            'email'=>'zakaz@imige.ru'
      );
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
				if ($row'availability' = 'Товар в наличии') $cellsPrepare['days']=$row['availability'];
				else $cellsPrepare['days']=$row['availability'];

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
				/*	'delivery-options' => */
					'params' => $ym_params,
					'available' => true,
					'type' => null,
					//'group_id' => $offer_data['product_id'],
				);
			
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
					$offer['group_id']
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
	
}
