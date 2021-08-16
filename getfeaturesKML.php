<?php

require_once __DIR__ . '/vendor/autoload.php';

require 'functions.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

date_default_timezone_set('Asia/Tokyo');


$sheetname = "";
if( isset($_GET['sheetname'])){
    $sheetname = $_GET['sheetname'];
}

$sheetid = "";

if( isset($_GET['sheetid'])){
   $sheetid = $_GET['sheetname'];
}


#$sheetname = filter_input(INPUT_POST,"sheetname"); //変数の出力。jQueryで指定したキー値optを用いる

$log->addWarning("sheet name 1  ${sheetname}");

#$sheetid= filter_input(INPUT_POST,"sheetid"); //変数の出力。jQueryで指定したキー値optを用いる


$download_f= filter_input(INPUT_POST,"download");

//変数の出力。jQueryで指定したキー値optを用いる
//$env_name  = getenv('SHEET_NAME');
//$envid= getenv('SPREADSHEET_ID');
 //$sheetname = 'シート1';
 $spreadsheetId = getenv('SPREADSHEET_ID');

 if ( ! empty($sheetid)  ){
     $spreadsheetId = $sheetid;
 }

$client = getGoogleSheetClient();
 if( empty($sheetname)  ) {
     $sheetname  = getenv('SHEET_NAME');
     if( empty($sheetname)  ) {
          //$sheetname = 'シート1';
          $sheetname = GetFirstSheetName(  $spreadsheetId, $client );
     }
 }

 $fname = "${sheetname}.kml";


 $log->addWarning("sheet name   ${sheetname}");

 if ( ! empty($download_f)  ){
    if ( $download_f > 0 ){
   header("Content-Type:application/vnd.google-earth.kml+xml; charset=UTF-8"); //ヘッダー情報の明記。必須。
   header("Content-Disposition: attachment; filename=${fname}");
   header("Content-Transfer-Encoding: binary");
    }
    else {
       
   header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
    }
 }
 else {

   header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
 }
 
 header('Access-Control-Allow-Origin: *');
 header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
 

$sheetd = GetSheet( $spreadsheetId, $sheetname, $client );



$isdone = false;


$kml_hd = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml_hd[] = '<kml xmlns="http://earth.google.com/kml/2.2">';
$kml_hd[] = ' <Document>';
$kml_hd[] =  "<name>${sheetname}</name>";
$kml_hd[] =  '<description/>';



/*
$kml_hd[] = ' <Style id="restaurantStyle">';
$kml[] = ' <IconStyle id="restuarantIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon63.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
$kml[] = ' <Style id="barStyle">';
$kml[] = ' <IconStyle id="barIcon">';
$kml[] = ' <Icon>';
$kml[] = ' <href>http://maps.google.com/mapfiles/kml/pal2/icon27.png</href>';
$kml[] = ' </Icon>';
$kml[] = ' </IconStyle>';
$kml[] = ' </Style>';
*/


$geojson = array(
   'type'      => 'FeatureCollection',
   'features'  => array()
);


$output_ar = array();    // array of output data

$uid_ar = array();   //  array of user id

$non_loc_ar = array();  // array of non location data

$ckey = 0;

$non_locr = array();    //  arrray of non location data for a user

foreach ($sheetd as $index => $cols) {

//echo "\nindex ${index}  ";  //////
//echo "\ndate ${dated}  ";  //////

if ( $index > 2 ){
     $dated = $cols[0];
     $timed = $cols[1];

     $dist = $cols[2];
     $addr = $cols[3];
     $descript = $cols[4];

     $user = $cols[5];
     $isource = $cols[6];

     $purl = $cols[7];

     $lon = $cols[8];
     $lat = $cols[8];

     echo "\nurl  ${purl}";

     echo "\nlat ${lat}  ";  //////
     echo "\nlon ${lon}  ";  //////



 
}

  
     }  //  foreach

 
     $kmlOutput = join("\n", $kml_hd);


     //header('Content-type: application/vnd.google-earth.kml+xml');
     echo $kmlOutput;

     $fp = fopen('styles/style_mappin.xml','r');

     while (!feof($fp)){

      $txt = fgets($fp);
      echo $txt;
     }


     echo ' </Document>';
     echo '</kml>';


   //  $retjson = json_encode( $geojson  );      // make json
    // echo $retjson;

?>
