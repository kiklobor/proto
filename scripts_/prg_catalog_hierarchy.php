<?php
namespace URL;

class Category {

  public static $catarr = array();

  public $firsturl = 'catalog';

  public function __construct($go) {
    if (count(self::$catarr)==0) {
      self::$catarr = $this->getCatArr($go);
      self::$catarr = $this->set_cat_path(self::$catarr,0);
    }
  }

  function checkurlpath($urlArrf) {
    $urlArrf = array_filter($urlArrf);
    $exist = FALSE;
    $cat = array_shift($urlArrf);
    if ($cat != $this->firsturl) array_unshift($urlArrf, $cat);
    $urltmp1 = implode('/', $urlArrf);
    foreach (static::$catarr as $key => $val) {
      foreach ($val as $parentuid => $chldA) {
        /*
        echo '<pre>';
        var_dump(implode('/', $chldA['catpath']));
        echo '</pre>';
        /**/
        if ($urltmp1 == implode('/', $chldA['catpath'])) {
          $exist = TRUE;
          break;
        }
      }
      if ($exist) break;
    }
    return $exist;
  }

  function getcatid($urlArrf) {
    $urlArrf = array_filter($urlArrf);
    $catid = 0;
    $caturl = array_pop($urlArrf);
    if ($caturl == $this->firsturl) return $catid;
    foreach (static::$catarr as $key => $val) {
      foreach ($val as $parentuid => $chldA) {
        /*
        echo '<pre>';
        var_dump(implode('/', $chldA['catpath']));
        echo '</pre>';
        /**/
        if ($caturl == $chldA['url']) {
          $catid =  $chldA['ID'];
          break;
        }
      }
      if ($catid<>0) break;
    }
    return $catid;
  }

  function getlastcat($urlArrf) {
    $urlArrf = array_filter($urlArrf);
    $cat = array_shift($urlArrf);
    if ($cat != $this->firsturl) array_unshift($urlArrf, $cat);
    $cat = array();
    $caturl = array_pop($urlArrf);
    if ($caturl == $this->firsturl) return $cat;
    foreach (static::$catarr as $key => $val) {
      foreach ($val as $parentuid => $chldA) {
        /*
        echo '<pre>';
        var_dump(implode('/', $chldA['catpath']));
        echo '</pre>';
        /**/
        if ($caturl == $chldA['url'] AND ((count($urlArrf)+1)==count($chldA['catpath']))) {
          $cat =  $chldA;
          break;
        }
      }
      if (count($cat)<>0) break;
    }
    return $cat;
  }

  function getcatbyuid($uid) {
    $cat = array();
    foreach (static::$catarr as $key => $val) {
      foreach ($val as $parentuid => $chldA) {
        /*
        echo '<pre>';
        var_dump(implode('/', $chldA['catpath']));
        echo '</pre>';
        /**/
        if ($uid == $chldA['uID']) {
          $cat =  $chldA;
          break;
        }
      }
      if (count($cat)<>0) break;
    }
    return $cat;
  }

  /*
  function checkurlpath1($urlArrf) {
    $urlArrf = array_filter($urlArr);
    $exist = TRUE;
    $cat = array_pop($urlArrf);
    $parentuid = '';
    foreach (self::$catarr as $key => $val) {
      if ($cat == $val['url']) {
        $parentuid = $val['parent'];
        break;
      }
    }
    if ($parentuid == '') {
      return FALSE;
    }
    while (count($urlArrf)>0) {
      $cat = array_pop($urlArrf);
      foreach (self::$catarr as $key => $val) {
        if ($cat == $val['url']) {
          $parentuidbyurl = $val['parent'];
          break;
        }
      }
      if (self::$cararr[$] == $parentuidbyurl) {

      }
    }
    return $exist;
  }
  /**/

  function dbtocatarr($groups, $catarr = array()) {
    foreach($groups as $val) {
      $catarr[$val['parent']][$val['uID']] = $val;
    }
    return $catarr;
  }

  function getCatArr ($go) {
    $groups=$go->getAll("SELECT 0 as parent, g.name Name, g.uID uID, g.ID ID, g.url url FROM groups g WHERE g.active=1 AND g.ind=0 ORDER BY g.route,g.name");
    $catarr = $this->dbtocatarr($groups);
    $groups=$go->getAll("SELECT sg.uID uID, sg.ID ID, sg.name Name, sg.parent parent, sg.url url FROM subgroups sg WHERE sg.active=1 ORDER BY sg.route,sg.name");
    $catarr = $this->dbtocatarr($groups, $catarr);
    return $catarr;
  }

  function find_parent ($tmp, $cur_id){
      if($tmp[$cur_id]['parent']!=0){
          return $this->find_parent($tmp,$tmp[$cur_id]['parent']);
      }
      return $tmp[$cur_id]['uID'];
  }

  function set_cat_path($cats, $parent_id, $path = array()){
      if(is_array($cats) and isset($cats[$parent_id])){
              foreach($cats[$parent_id] as $key=>$cat){
                  if (isset($cats[$cat['uID']])) {
                    array_push($path, $cat['url']);
                    $cats[$parent_id][$key]['catpath'] = $path;
                    $cats = $this->set_cat_path($cats,$cat['uID'], $path);
                    array_pop($path);
                  } else {
                    array_push($path, $cat['url']);
                    $cats[$parent_id][$key]['catpath'] = $path;
                    array_pop($path);
                  }
              }
      }
      else return null;
      return $cats;
  }

  function calc_prod_cat($go, $cats, $parent_id) {
      if(is_array($cats) and isset($cats[$parent_id])){
        $sum = 0;
        foreach($cats[$parent_id] as $key=>$cat){
          $query = 'SELECT p.ID FROM products p WHERE p.active=1 AND p.parent=?s';
          //var_dump($pquery);
          $productsAll = $go->getAll($query, $cat['uID']);
          $productsCountAll = $go->affectedRows();
          $cats[$parent_id][$key]['productcount'] = $productsCountAll;
          $sum = $sum + $productsCountAll;
          if (isset($cats[$cat['uID']])) {
            //array_push($suba, $sum);
            //$cats[$parent_id][$key]['catpath'] = $path;
            $cats = $this->calc_prod_cat($go, $cats, $cat['uID']);
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

  function getlastcatuids($cats, $parent_uid, $catids = array()){
      if(is_array($cats) and isset($cats[$parent_uid])){
              foreach($cats[$parent_uid] as $key=>$cat){
                  if (isset($cats[$cat['uID']])) {
                    $catids = $this->set_cat_path($cats,$cat['uID'], $catids);
                  } else {
                    array_push($catids, $cat['uID']);
                  }
              }
      }
      else return array($parent_uid);
      return $catids;
  }

  function getallhierarchycatuids($cats, $parent_uid, $catids = array()){
      if (count($catids)==0) array_push($catids, $parent_uid);
      if(is_array($cats) and isset($cats[$parent_uid])){
              foreach($cats[$parent_uid] as $key=>$cat){
                  array_push($catids, $cat['uID']);
                  if (isset($cats[$cat['uID']])) {
                    $catids = $this->getallhierarchycatuids($cats,$cat['uID'], $catids);
                  }
              }
      }
      else return array($parent_uid);
      return $catids;
  }

  function getcatuidbyid($id) {
    $catuid = '';
    foreach (static::$catarr as $key => $val) {
      foreach ($val as $parentuid => $chldA) {
        /*
        echo '<pre>';
        var_dump(implode('/', $chldA['catpath']));
        echo '</pre>';
        /**/
        if ($id == $chldA['ID']) {
          $catuid =  $chldA['uID'];
          break;
        }
      }
      if ($catuid<>'') break;
    }
    return $catuid;
  }

}
