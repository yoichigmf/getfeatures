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
   $sheetid = $_GET['sheetid'];
}

$download_f = 0;

if( isset($_GET['download'])){
   $download_f = $_GET['download'];
}

#$sheetname = filter_input(INPUT_POST,"sheetname"); //変数の出力。jQueryで指定したキー値optを用いる

$log->addWarning("sheet name 1  ${sheetname}");

#$sheetid= filter_input(INPUT_POST,"sheetid"); //変数の出力。jQueryで指定したキー値optを用いる


//$download_f= filter_input(INPUT_POST,"download");

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


echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
echo '<kml xmlns="http://earth.google.com/kml/2.2">';
echo "\n";
echo ' <Document>';
echo "\n";
echo  "<name>${sheetname}</name>";
echo "\n";
echo '<description/>';


$fp = fopen('styles/style_mappin.xml','r');

while (!feof($fp)){

 $txt = fgets($fp);
 echo $txt;
}
echo '<Folder>';




$style_url = '#icon-1899-0288D1';

echo  "<name>${sheetname}</name>\n";

foreach ($sheetd as $index => $cols) {

//echo "\nindex ${index}  ";  //////
//echo "\ndate ${dated}  ";  //////

   if ( $index > 1 ){

   OutputPlacemark( $cols, $style_url );
   

    }


  
     }  //  foreach

 
    
    

     echo '</Folder>';

     echo ' </Document>';
     echo '</kml>';


   //  $retjson = json_encode( $geojson  );      // make json
    // echo $retjson;

?>
