<?php
// ==========================================
// Ideamart : TRANSLATER SMS API
// ==========================================
// Author : Jayalath JDC , Bandara HMAPK , Jayawardhana JLMM , Harischandra MG 
// ==========================================

ini_set('error_log', 'sms-app-error.log');
require_once 'lib/Log.php';
require_once 'lib/SMSReceiver.php';
require_once 'lib/SMSSender.php';


define('SERVER_URL', 'https://api.dialog.lk/sms/send');	
define('APP_ID', 'APP_009655');
define('APP_PASSWORD', 'c07c9cc67c79f3f923e1cd7932af54e8');

$logger = new Logger();

try{

	// Creating a receiver and intialze it with the incomming data
	$receiver = new SMSReceiver(file_get_contents('php://input'));
	
	//Creating a sender
	$sender = new SMSSender( SERVER_URL, APP_ID, APP_PASSWORD);
	
	$message = $receiver->getMessage(); // Get the message sent to the app
	$address = $receiver->getAddress();	// Get the phone no from which the message was sent 

	$logger->WriteLog($receiver->getAddress());
    

/*    mysql_connect("localhost", "root", "" )or die(mysql_error()); 
	mysql_select_db("translationdb") or die(mysql_error()); 
    $sql_sessions="INSERT INTO `translation` (`tel`) VALUES ('".$address."')";
    $quy_sessions=mysql_query($sql_sessions);
  */
    
    
    // keywd <space> string
    $kk = substr($message , 5 , 1 );
    
    if($kk == "s" || $kk == "S" || $kk == "e" || $kk == "E"){
       // list($key,$text) = explode("*#", $message);// change
        
        $key =  substr($message , 5 , 1 );
        $stringlength = strlen($message);
        $text = substr($message , 7 , $stringlength );
        
        
    
    $newstring = str_ireplace(" ", "+", $text); //  change the spaces with +
    $url = "https://translate.google.com/translate_a/single?client=t&sl=auto&tl=si&hl=en&dt=bd&dt=ex&dt=ld&dt=md&dt=qc&dt=rw&dt=rm&dt=ss&dt=t&dt=at&ie=UTF-8&oe=UTF-8&prev=btn&rom=1&ssel=0&tsel=0&tk=517882|775443&q=$newstring"; // call to the google translate
    ///// get the response ......................................................................
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $your_var = curl_exec($ch);
    $length = strlen ($message);
    curl_close($ch);
        
    /// spliting ----------------------------------------------------------------
   
function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}


$sl = get_string_between($your_var, "[\"\",,\"", "\"]]");
$singlish = str_ireplace("\",\"", "--", $sl);

$pa = get_string_between($your_var, "[[[\"", "\",\"");
$sinhala = str_ireplace("\",\"", "--", $pa);


///----------------------------------------------------------- sending
    
    if($key == "s" || $key == "S" ){
        $response=$sender->sms($sinhala, $address);
    }else{
        $response=$sender->sms($singlish, $address);
    }
    
    
    
        
    }else{
    
        $response=$sender->sms("Error !!!
use correct message format >>  
for unicode - s*#Your content   

or for singlish - e*#Your content ", $address);
        
    }
    
    
}catch(SMSServiceException $e){
	$logger->WriteLog($e->getErrorCode().' '.$e->getErrorMessage());
}

?>