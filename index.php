<?php

require_once __DIR__ . '/vendor/autoload.php';

require 'functions.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$log = new Logger('name');
$log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

date_default_timezone_set('Asia/Tokyo');




if (!session_id()) {
    session_start();
}


//    id token の取得


//$client_secret = getenv("CLIENT_SECRET");

$title_string = getenv("TITLE");

if ( empty($title_string )) {
 $title_string = "災害情報地図データ出力";
}


$sheet_id = getenv("SPREADSHEET_ID");
$client_str =   getGoogleSheetClient();

$sheet_names = GetsheetNames($sheet_id, $client_str);

//$log->addWarning( "client_secret ${client_secret}");

if ( (!empty($client_id)) && (!empty($redirect_uri)) && (!empty($client_secret ))){

  $code = $_GET['code'];

  $state = $_GET['state'];

  $session_state = $_SESSION['_line_state'];
//unset($_SESSION['_line_state']);

  $log->addWarning( "session state =${session_state}");


  $log->addWarning( "state =${state}");
  if ( !isset($code) or !isset($state) or !isset($session_state) ){
    $loginm = urlencode("地図閲覧のためにはLINEアカウントのログインが必要です");
    header( "Location:login.php?message=${loginm}" );

      exit;
    }
    if ($session_state !== $state) {
        $loginm = urlencode("地図閲覧のためにはLINEアカウントのログインが必要です");
        header( "Location:login.php?message=${loginm}" ) ;

        exit;
      }


  //  認証ありの場合

  $url = "https://api.line.me/oauth2/v2.1/token";

//----------------------------------------
// POSTパラメータの作成
//----------------------------------------
  $query = "";
  $query .= "grant_type=" . urlencode("authorization_code") . "&";
  $query .= "code=" . urlencode($code) . "&";
  $query .= "redirect_uri=" . urlencode($redirect_uri) . "&";
  $query .= "client_id=" . urlencode($client_id) . "&";
  $query .= "client_secret=" . urlencode($client_secret) . "&";

  $header = array(
    "Content-Type: application/x-www-form-urlencoded",
    "Content-Length: " . strlen($query),
  );

  $context = array(
    "http" => array(
        "method"        => "POST",
        "header"        => implode("\r\n", $header),
        "content"       => $query,
        "ignore_errors" => true,
    ),
  );
  $res_json = file_get_contents($url, false, stream_context_create($context));

  $res = json_decode($res_json);

  if (isset($res->error)) {

    $loginm = urlencode("ログインエラーが発生しました。<br />" . $res->error . '<br />'.$res->error_description);
    header( "Location:login.php?message=${loginm}" );

      exit;

  }
//id_token(JWT)を分解
  $val = explode(".", $res->id_token);
  $data_json = base64_decode($val[1]);
  $data = json_decode($data_json);

//echo '$data= ';
//print_r($data);
//echo '<br /><br />';

//取得したデータを表示
//print("[sub]:[" . $data->sub . "][対象ユーザーの識別子]<br />\n");

  $uname = GetUserNameUsingID( $data->sub );

//print("[username]:[" . $uname. "][対象ユーザーの名前]<br />\n");

  if ( isset($uname )) {
  print( "<!DOCTYPE html>\n");
  print("<html>");
  print("<head>");
  print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">");
  print("<title>" . $title_string  . "</title>");

  print("<script>\n");
  print("title_string =\"" . $title_string ."\";\n");
  print("</script>\n");


   // readfile(__DIR__ . '/pg/map.html');
  }
  else {
     $loginm = urlencode("情報調査LINEボットと友達になっていないと地図は閲覧できません");
     header( "Location:login.php?message=${loginm}" );
   }
}
else {   //  認証無しの場合

print( "<!DOCTYPE html>\n");
print("<html>");
print("<head>");
print("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">");
print("<title>" . $title_string  . "</title>");

print("<script>\n");
print("title_string =\"" . $title_string ."\";\n");
print("</script>\n");
print("</head>");
print("<body>");
print("<H1>");
print( $title_string);
print("</H1>");

$download = "1";

//var_dump($sheet_names);

print "<a href=\"getfeaturesKML.php?sheetname=all&sheetid=${sheet_id}&download=${download}\">";
print "全部";
print "</a>";
print "<BR>";

for ( $i=0; $i < count($sheet_names); $i++){
  $sn = $sheet_names[$i];
  //print $sn;
  $pos = strpos( $sn, "避難所");

   if ( $pos === false){
          print "<a href=\"getfeaturesKML.php?sheetname=${sn}&sheetid=${sheet_id}&download=${download}\">";
          print $sn;
          print "</a>";
          print "<BR>";

   }

}

print("</body>");



//    readfile(__DIR__ . '/pg/map.html');
}
