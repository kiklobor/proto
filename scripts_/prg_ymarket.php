<?php
error_reporting(E_ALL);
$lroot = $_SERVER['DOCUMENT_ROOT'];
require($lroot."/scripts/YmlGeneratorDBS.php");
require($lroot."/scripts/ImigeYmlGeneratorDBS.php");
$generator = new ImigeYmlGeneratorDBS;
$generator->encoding = 'utf-8';
$generator->outputFile = $lroot.'/yml_catalog_DBS.xml';
$generator->run();
echo "DBS - OK";
echo '<br/>';

require("scripts/YmlGeneratorADV.php");
require("scripts/ImigeYmlGeneratorADV.php");
$generator = new ImigeYmlGeneratorADV;
$generator->encoding = 'utf-8';
$generator->outputFile = 'yml_catalog_ADV.xml';
$generator->run();
echo "ADV - OK";
exit();