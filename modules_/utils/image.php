<?php

namespace Utilsw\Image;

class Image {

  public static function getResizeImg(string $filepath, string $imgresizesubfolder=""): string {
    //preg_split('/\//', '/files/1.jpg', -1, PREG_SPLIT_NO_EMPTY);
    if (empty($filepath) OR $filepath=="") return $filepath;
    $pathinfo = implode('/',array_filter(explode('/', $filepath)));
    $pathinfo = pathinfo($filepath);
    $imgname = $pathinfo['basename'];

    if ($imgresizesubfolder!="") $imgresize = '/'.RESIZEPATH . '/' . $imgresizesubfolder .'/'. $imgname;
    else $imgresize = '/'.RESIZEPATH .'/'. $imgname;

    $lroot = $_SERVER['DOCUMENT_ROOT'];
    include_once(LROOT.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ImageResize'.DIRECTORY_SEPARATOR.'ImageResize.php');

    //use Martijnvdb\ImageResize\ImageResize;
    $dirresize = LROOT.DIRECTORY_SEPARATOR.RESIZEPATH;
    $resizefilecheck = LROOT.DIRECTORY_SEPARATOR.RESIZEPATH.DIRECTORY_SEPARATOR.$imgname;
    if ($imgresizesubfolder!="") {
      $dirresize = LROOT.DIRECTORY_SEPARATOR.RESIZEPATH.DIRECTORY_SEPARATOR.$imgresizesubfolder;
      $resizefilecheck = LROOT.DIRECTORY_SEPARATOR.RESIZEPATH.DIRECTORY_SEPARATOR.$imgresizesubfolder.DIRECTORY_SEPARATOR.$imgname;
    }

    if (!file_exists($resizefilecheck)) {
      $image = \Martijnvdb\ImageResize\ImageResize::get(LROOT.DIRECTORY_SEPARATOR.$filepath)
          ->setWidth(300)
          //->setHeight(500)
          ->setQuality(50)
          ->export($dirresize .DIRECTORY_SEPARATOR. $imgname);
      $filepath = $imgresize;
    } else {
      $filepath = $imgresize;
    }
    return $filepath;
  }

}
