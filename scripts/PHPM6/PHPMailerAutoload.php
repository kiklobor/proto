<?php

//define('ROOTPMA',  $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR);

function PHPMailerAutoload($class)
{
    //Can't use __DIR__ as it's only in PHP 5.3+
    //$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'class.'.strtolower($classname).'.php';
    //if (is_readable($filename)) {
    //    require $filename;
    //}
    
    //$path = str_replace('\\',DIRECTORY_SEPARATOR, $class.'.php');
    $lroot = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;    
    if ($class=='') {
      return;
    }
    $pa = explode ('\\', $class);
    if (count($pa)==0) {
      return;
    }
    $cll = array_pop($pa);
    array_push($pa, 'src');
    array_push($pa, $cll);
    $path = implode(DIRECTORY_SEPARATOR, $pa);
    $path = $path.'.php';
    //echo $class.'<br>';
    //echo ROOT.$path.'<br>';
    //die('111');
    $rf = $lroot.'scripts'.DIRECTORY_SEPARATOR.'PHPM6'.DIRECTORY_SEPARATOR.$path;
    if (file_exists($rf)) {
      if (is_readable($rf)) {
        require $rf;
      }
      //echo '<br>include '.$rf.'<br>';
    } else {
      //echo '<br>not include '.$rf.'<br>';
    }
    
}

spl_autoload_register('PHPMailerAutoload');


/*
if ( !function_exists("mpdf_worknmn_autoloader") )
{
  function mpdf_worknmn_autoloader($class) {
      //savetologw($class);
      //if ($class 'PhpOffice\PhpSpreadsheet')
      
      $file = __DIR__ . '/../../vendor/' . str_replace("\\","/",$class) . '.php';
      //savetologw($file);
      //var_dump($file);
      //die('worknmn_autoloader');
      include_once $file;
  }
}
spl_autoload_register('mpdf_worknmn_autoloader');


spl_autoload_register(function($class) {
    //$path = str_replace('\\',DIRECTORY_SEPARATOR, $class.'.php');    
    if ($class=='') {
      return;
    }
    $pa = explode ('\\', $class);
    if (count($pa)==0) {
      return;
    }
    if (array_first($pa) == 'Mini') {
      array_shift($pa);
    }
    array_unshift($pa, 'application');
    $path = implode(DIRECTORY_SEPARATOR, $pa);
    $path = $path.'.php';
    //echo $class.'<br>';
    //echo ROOT.$path.'<br>';
    //die('111');
    if (file_exists(ROOT.$path)) {
      require ROOT.$path;
      //echo '<br>include<br>';
    } else {
      //echo '<br>not include<br>';
    }
});

function array_first($array, $default = null)
{
   foreach ($array as $item) {
       return $item;
   }
   return $default;
}

*/

