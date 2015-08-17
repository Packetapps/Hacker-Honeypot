<?php
require_once("config.inc");
require_once("globals.inc");
require_once("notices.inc");
global $config;
$conf = json_decode(file_get_contents('/usr/local/appsbypacketapps/hhp/honey.conf'),true);

$message = "";

if($conf['subject'] <> "") {
	$subject = $conf['subject'];
}


$subject = str_replace("[hostname]",$config['system']['hostname'],$subject);



$in = file("php://stdin");
foreach($in as $line){
	if( strstr($line,'[hostname]') ){
		$line = str_replace('[hostname]',$config['system']['hostname'],$line);
	}
	if( strstr($line,"Interface") ){
		$line_explode = explode(':',$line);
		$int = trim($line_explode[1]);
		$subject = str_replace("[interface]",$int,$subject);
	}
	
	if( strstr($line,"Probed port") ){
		$line_explode = explode(':',$line);
		$port = trim($line_explode[1]);
		$subject = str_replace("[port]",$port,$subject);
	}
	
	if( strstr($line,"Source IP") ){
		$line_explode = explode(':',$line);
		$ip = trim($line_explode[1]);
		$subject = str_replace("[ip]",$ip,$subject);
	}
	
	$message .= "$line";
}

send_smtp_message($message, $subject,true);
?>