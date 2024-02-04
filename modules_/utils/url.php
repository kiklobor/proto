<?php

namespace Utilsw\URL;

class URL {

  public static function url(string $url, bool $fullurl = FALSE, string $baseurl = ""): string {
    //preg_split('/\//', '/files/1.jpg', -1, PREG_SPLIT_NO_EMPTY);
    //include_once(LROOT.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ImageResize'.DIRECTORY_SEPARATOR.'ImageResize.php');
    $url = str_replace('?&', '?', $url);
    $url = str_replace('//', '/', $url);
    if (mb_substr($url, -1) == '?') {
      $url = mb_substr($url, 0, -1);
    }
    return $url;
  }

  public static function checkurl(string $url) {
    // var_dump($url);
    include_once('urlmask.php');
    // var_dump($maskurl);
    $urlfinded = FALSE;
    foreach ($maskurl as $key => $value) {
      $matches = array();
      if (preg_match('/^\/'.$value.'$/i', $url, $matches)) {
        // var_dump($matches[0]);
        if ($matches[0]==$url) {
          $urlfinded = TRUE;
          break;
        }
      }
    }
    return $urlfinded;
  }

}
