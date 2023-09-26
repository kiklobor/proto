<?php
namespace URL;

class CategoryParams extends Category {

  public static $catarr = array();

  public $firsturl = 'params';

  public $type = 'service';

  public function __construct($go) {
    if (count(self::$catarr) == 0) {
      self::$catarr = $this->getCatArr($go);
      self::$catarr = $this->set_cat_path(self::$catarr, 0);
    }
  }

  function getCatArr($go) {
    $groups = $go->getAll("SELECT c.uID uID, c.ID ID, c.name Name, c.parent parent, c.url url
      FROM category_params c
      WHERE c.active=1 AND c.type=?s
      ORDER BY c.route, c.name", $this->type);
    $catarr = $this->dbtocatarr($groups);
    return $catarr;
  }

  function calc_param_cat($go, $cats, $parent_id) {
      if (is_array($cats) and isset($cats[$parent_id])) {
        $sum = 0;
        foreach ($cats[$parent_id] as $key => $cat) {
          $query = 'SELECT p.ID FROM params p WHERE p.active=1 AND p.parent=?s';
          $productsAll = $go->getAll($query, $cat['uID']);
          $productsCountAll = $go->affectedRows();
          $cats[$parent_id][$key]['productcount'] = $productsCountAll;
          $sum += $productsCountAll;
          if (isset($cats[$cat['uID']])) {
            $cats = $this->calc_param_cat($go, $cats, $cat['uID']);
            $subsum = 0;
            foreach ($cats[$cat['uID']] as $key2 => $cat2) {
              if (array_key_exists('productcount', $cat2)) {
                $subsum += $cat2['productcount'];
              }
            }
            $sum += $subsum;
          }
        }
        foreach ($cats as $key1 => $cat1) {
          if (array_key_exists($parent_id, $cat1)) {
            $cats[$key1][$parent_id]['productcount'] += $sum;
          }
        }
      } else {
        return null;
      }
      return $cats;
  }

}
