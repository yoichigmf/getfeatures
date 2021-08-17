<?php
require_once __DIR__ . '/vendor/autoload.php';

//  Google Spread Sheet 用クライアント作成
function getGoogleSheetClient() {


   $auth_str = getenv('authstr');

   $json = json_decode($auth_str, true);


     $client = new Google_Client();

    $client->setAuthConfig( $json );


    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);



    $client->setApplicationName('ReadSheet');

    return $client;


}

function renameGoogleUrl( $imgUrl ){

   $arr = array();
   $arr = explode('/', $imgUrl);

   $fid = $arr[5];
   

   $newurl = 'https://drive.google.com/uc?export=view&id=' . $fid;

    return $newurl;

}




function  OutputPlacemark( $cols, $style_url ){


     
$dated = $cols[0];
$timed = $cols[1];

$dist = $cols[2];
$addr = $cols[3];
$descript = $cols[4];

$user = $cols[5];
$isource = $cols[6];

$purl = $cols[7];

$lon = $cols[8];
$lat = $cols[9];

$log->addWarning("url  ${purl}");
$log->addWarning("lat  ${lat}");
$log->addWarning("lon  ${lon}");
#echo "\nurl  ${purl}";

# echo "\nlat ${lat}  ";  //////
# echo "\nlon ${lon}  ";  //////
if ( ! empty($lon) && ! empty($lat) ){   //  座標がはいっている場合のみKML出力


    echo  "<Placemark>\n";



    $desc_str = '<![CDATA[';

#  $dssc_str = $desc_str . '<table>';

#  $dsc_str  = $desc_str . '</table>';
    $desc_str  =  $desc_str . $dated . ' ' . $timed;

    $desc_str  =   $desc_str . '<BR><BR>';



    $desc_str  = $desc_str . '報告者:'. $user. '<BR><BR>';
    if ( ! empty($isource)  ){

        $desc_str  = $desc_str . '情報ソース:'. $isource. '<BR><BR>';
      
    }

 if ( ! empty($purl)  ){  //  url 文字列が null でない場合
        $pstr = str_replace(array("\r\n", "\r", "\n"), "\n", $purl);
        $arr = explode("\n", $pstr);

        foreach ( $arr as $iurl ){
          $nurl = renameGoogleUrl( $iurl );
          $desc_str  = $desc_str . "<img src=\"${nurl}\" height=\"200\" width=\"auto\" /><BR><BR>";

        }  //  foreach
   } // if

 $desc_str  =  $desc_str . $descript ;
 
  $desc_str  = $desc_str . ']]>';


   //  $log->addWarning("desc ${desc_str}");

    echo  "<description>${desc_str}</description>\n";
    echo  "<styleUrl>${style_url}</styleUrl>\n";      
    echo  "<name>${dist}</name>\n";
    echo "<Point>\n";
    echo "<coordinates>\n";
    echo  "${lon},${lat}";
    echo  "</coordinates>\n";
    echo  "</Point>\n";
    echo  "</Placemark>\n";

  }


}


function GetSheet( $sheetid, $sheetname, $client ) {
//  $client = getGoogleSheetClient();


    $client->addScope(Google_Service_Sheets::SPREADSHEETS);
    $client->setApplicationName('ReadSheet');

    $service = new Google_Service_Sheets($client);

    $response = $service->spreadsheets_values->get($sheetid, $sheetname);

    $values = $response->getValues();

    return $values;
    //var_dump( $values );

}

function GetFirstSheetName( $spreadsheetID, $client ){

  $service = new Google_Service_Sheets($client);

  $response = $service->spreadsheets->get($spreadsheetID);
  foreach($response->getSheets() as $s) {
       $sheets[] = $s['properties']['title'];
   }

   $ret = $sheets[0];
   return $ret ;

}

function GetsheetNames($spreadsheetID, $client) {
    $sheets = array();


    $sheetService = new Google_Service_Sheets($client);
    $response = $sheetService->spreadsheets->get($spreadsheetID);

    foreach($response->getSheets() as $s) {
        $sheets[] = $s['properties']['title'];
    }
 
   
    return $sheets;
}


function Getsheets($spreadsheetID, $client) {
    $sheets = array();


    $sheetService = new Google_Service_Sheets($client);
    $spreadSheet = $sheetService->spreadsheets->get($spreadsheetID);
    $sheets = $spreadSheet->getSheets();
    foreach($sheets as $sheet) {
        $sheets[] = $sheet->properties->sheetId;
    }
    return $sheets;
}
