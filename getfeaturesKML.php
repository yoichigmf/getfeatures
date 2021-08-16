<?php

require_once __DIR__ . '/vendor/autoload.php';

require 'functions.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

date_default_timezone_set('Asia/Tokyo');

$sheetname = filter_input(INPUT_POST,"sheetname"); //変数の出力。jQueryで指定したキー値optを用いる

$sheetid= filter_input(INPUT_POST,"sheetid"); //変数の出力。jQueryで指定したキー値optを用いる


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

     $dated = $cols[0];
     $userd = $cols[1];

     $kind = $cols[2];
     $url  = $cols[3];

     $stext = $cols[4];
     
     $client_name = $cols[7];
     
     
$log->addWarning("client_name = ${client_name}\n");
 
 ###  add 20210712  native client data
 if ( strcmp( $client_name ,'reportpost') ==0 ) {
 
        $xcod = (double)$cols[6];    //  coordinate
        $ycod = (double)$cols[5];
         $log->addWarning("reportpost\n");
         
         
           if (array_key_exists( $userd, $uid_ar)){   //  is the user id in the array ?

            $ckey = $uid_ar[$userd] + 1;
           $uid_ar[$userd] = $ckey;
               }
        else   {
           $ckey = 0;
          $uid_ar[$userd] = $ckey;

            //$non_loc_ar[$userd] = array();
            }

       $arkey = $userd . "_" . $ckey ;
       
       
              $atrar = array();
              
              
                   $atrar = array();
         
                      $atrdata = array(
                       'date'=> $dated,
                       'user' => $userd,
                       'kind' => $kind,
                       'text' => $stext,
                       'url'=> $url
                     );
                     
              
              array_push(  $atrar , $atrdata );
              

              //             $log->addWarning("feature id == ${arkey}  user == ${userd}");
         $feature = array(
           'id' => $arkey,
           'type' => 'Feature',
           'geometry' => array(
           'type' => 'Point',
       # Pass Longitude and Latitude Columns here
             'coordinates' => array((double)$xcod, (double)$ycod)
              ),
   # Pass other attribute columns here
           'properties' => array(
              'user' => $userd,
              'date' => $dated,
              'kind' => $kind,
              'text' => $stext,
              'url' => $url,
       'proplist' => $atrar
       )
   );

        array_push($geojson['features'], $feature);

    continue;
 }
 


 if ( strcmp( $kind ,'location' ) == 0 ) {   //  if record is location data
 
          $log->addWarning("line\n");

   //  echo "\nkind ${kind}  ";  sample



        $xcod = (double)$cols[6];    //  coordinate
        $ycod = (double)$cols[5];

        if (array_key_exists( $userd, $uid_ar)){   //  is the user id in the array ?

            $ckey = $uid_ar[$userd] + 1;
            $uid_ar[$userd] = $ckey;
               }
        else   {
            $ckey = 0;
            $uid_ar[$userd] = $ckey;

            //$non_loc_ar[$userd] = array();
            }

         $arkey = $userd . "_" . $ckey ;

    
           $atrar = array();
           

              //             $log->addWarning("feature id == ${arkey}  user == ${userd}");
         $feature = array(
           'id' => $arkey,
           'type' => 'Feature',
           'geometry' => array(
           'type' => 'Point',
       # Pass Longitude and Latitude Columns here
             'coordinates' => array((double)$xcod, (double)$ycod)
              ),
   # Pass other attribute columns here
           'properties' => array(
              'user' => $userd,
              'date' => $dated,
              'kind' => $kind,
              'text' => $stext,
              'url' => $url,
       'proplist' => $atrar
       )
   );

         array_push($geojson['features'], $feature);

       }    // location
       else  {



       if ( $index > 0 ){


           if (array_key_exists( $userd, $uid_ar)){


                   $ukeyd = $uid_ar[$userd];
                   $ukey = $userd . "_" . $ukeyd ;
                   //$arkey = $ukey;
                  }
            else  {


                  //  $output_ar[$arkey]['attribute'] = array();

                     $ukey = $arkey;

                  }
                  $attr = array();

   

                     $atrdata = array(
                       'date'=> $dated,
                       'user' => $userd,
                       'kind' => $kind,
                       'text' => $stext,
                       'url'=> $url
                     );

                     $log->addWarning("attribute add  ${ukey}");
                     foreach ( $geojson['features'] as &$feat){

                          $fkey = $feat["id"];

                        //   $log->addWarning("fkey == ${fkey}");

                           if ( $feat["id"] === $ukey ){
                             $log->addWarning("add attribute success ============== ${ukey}");

                              array_push(  $feat["properties"]["proplist"], $atrdata );
                           }

                     }

          }
       }

     }  //  foreach

 
     $kmlOutput = join("\n", $kml_hd);


     header('Content-type: application/vnd.google-earth.kml+xml');
     echo $kmlOutput;

     $fp = fopen('styles/style_mappin.txt','r');

     while (!feof($fp)){

      $txt = fgets($fp);
      echo $txt;
     }


     echo ' </Document>';
     echo '</kml>';


   //  $retjson = json_encode( $geojson  );      // make json
    // echo $retjson;

?>
