<?php 

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use Parallax\UtilsBundle\Utils\GenericDataManager;

$gdm = new GenericDataManager();
 
$arr = ["name" => "ron" , "last" => "fridman"];

echo 'result:' . $gdm->GetValueFromArray($arr,'name');