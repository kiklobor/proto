<?php
namespace URL;
//
class CategoryServices extends Category {

  public static $catarr = array();

  public $firsturl = 'services';

  // public static $lang = '';

  public $type = 'service';

  public function __construct($go) {
    if (count(self::$catarr)==0) {
      // global $lang;
      // self::$lang = $lang['lang'];
      // $this->type;
      self::$catarr = $this->getCatArr($go);
      // var_dump(self::$catarr);
      self::$catarr = $this->set_cat_path(self::$catarr,0);
    }
  }

  function getCatArr($go) {
    // $groups = $go->getAll("SELECT c.uID uID, c.ID ID, c.name Name, ct.name AS ?n, c.parent parent, c.url url
    //   FROM category c
    //   LEFT JOIN categorytranslate ct
    //   ON c.uID=ct.categoryuID AND ct.lang=?s
    //   WHERE c.active=1 AND c.type=?s
    //   ORDER BY c.route, c.name", "Name_".self::$lang, self::$lang, $this->type);
    $groups = $go->getAll("SELECT c.uID uID, c.ID ID, c.name Name, c.parent parent, c.url url
      FROM category c
      WHERE c.active=1 AND c.type=?s
      ORDER BY c.route, c.name", $this->type);
    // var_dump($groups);
    $catarr = $this->dbtocatarr($groups);
    // $catarr = $this->settranslate($catarr);
    // echo '<pre>';
    // var_dump($catarr);
    // echo '</pre>';
    // $catarr = array();
    return $catarr;
  }

  function calc_serv_cat($go, $cats, $parent_id) {
      if(is_array($cats) and isset($cats[$parent_id])){
        $sum = 0;
        foreach($cats[$parent_id] as $key=>$cat){
          $query = 'SELECT s.ID FROM services s WHERE s.active=1 AND s.parent=?s';
          // var_dump($query);
          $productsAll = $go->getAll($query, $cat['uID']);
          $productsCountAll = $go->affectedRows();
          $cats[$parent_id][$key]['productcount'] = $productsCountAll;
          $sum = $sum + $productsCountAll;
          if (isset($cats[$cat['uID']])) {
            //array_push($suba, $sum);
            //$cats[$parent_id][$key]['catpath'] = $path;
            $cats = $this->calc_serv_cat($go, $cats, $cat['uID']);
            //$sum = array_pop($suba);
            $subsum = 0;
            foreach($cats[$cat['uID']] as $key2=>$cat2) {
              if (array_key_exists('productcount', $cat2)) {
                $subsum = $subsum + $cat2['productcount'];
              }
            }
            $sum = $sum + $subsum;
          } else {
            //array_push($suba, $cat['url']);
            //$cats[$parent_id][$key]['catpath'] = $path;
            //array_pop($suba);
          }
        }

        foreach($cats as $key1=>$cat1) {
          if (array_key_exists($parent_id, $cat1)) {
            $cats[$key1][$parent_id]['productcount'] = $cats[$key1][$parent_id]['productcount'] + $sum;
          }
        }

      }
      else return null;
      return $cats;
  }

}
