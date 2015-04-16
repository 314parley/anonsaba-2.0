var mod_set = false;
var ispage;

/* IE/Opera fix, because they need to go learn a book on how to use indexOf with arrays */
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function(elt /*, from*/) {
	var len = this.length;

	var from = Number(arguments[1]) || 0;
	from = (from < 0)
		 ? Math.ceil(from)
		 : Math.floor(from);
	if (from < 0)
	  from += len;

	for (; from < len; from++) {
	  if (from in this &&
		  this[from] === elt)
		return from;
	}
	return -1;
  };
}

var utf8 = {

	// public method for url encoding
	encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// public method for url decoding
	decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}
function checkhighlight() {
	var match;

	if(match=/#i([0-9]+)/.exec(document.location.toString()))
	if(!document.forms.postform.message.value)
	insert(">>" + match[1] + "\n");

	if(match=/#([0-9]+)/.exec(document.location.toString()))
	highlight(match[1]);
}

function highlight(post, checknopage) {

	if ((checknopage && ispage) || ispage) {
		// Uncomment the following line to always send the user to the thread if the link was clicked on the board page.
		//return;
	}

	var cells = document.getElementsByTagName("td");
	for(var i=0;i<cells.length;i++) if(cells[i].className == "highlight") cells[i].className = "reply";

	var reply = document.getElementById("reply" + post);
	var replytable = reply.parentNode;
	while (replytable.nodeName != 'TABLE') {
		replytable = replytable.parentNode;
	}

	if((reply || document.postform.replythread.value == post) && replytable.parentNode.className != "reflinkpreview") {
		if(reply) {
			reply.className = "highlight";
		}
		var match = /^([^#]*)/.exec(document.location.toString());
		document.location = match[1] + "#" + post;
		return false;
	}
	
	return true;
}
function addreflinkpreview(e) {
	var e_out;
	var ie_var = "srcElement";
	var moz_var = "href";
	this[moz_var] ? e_out = this : e_out = e[ie_var];
	ainfo = e_out.className.split('|');
	
	var previewdiv = document.createElement('div');
	
	previewdiv.setAttribute("id", "preview" + e_out.className);
	previewdiv.setAttribute('class', 'reflinkpreview');
	previewdiv.setAttribute('className', 'reflinkpreview');
	if (e.pageX) {
		previewdiv.style.left = '' + (e.pageX + 50) + 'px';
	} else {
		previewdiv.style.left = (e.clientX + 50);
	}
	var previewdiv_content = document.createTextNode('');
	previewdiv.appendChild(previewdiv_content);
	var parentelement = e_out.parentNode;
	var newelement = parentelement.insertBefore(previewdiv, e_out);
	new Ajax.Request(ku_boardspath + '/read.php?b=' + ainfo[1] + '&t=' + ainfo[2] + '&p=' + ainfo[3] + '&single',
	{
		method:'get',
		onSuccess: function(transport){
			var response = transport.responseText || _("something went wrong (blank response)");
			newelement.innerHTML = response;
		},
		onFailure: function(){ alert('wut'); }
	});
}

function delreflinkpreview(e) {
	var e_out;
	var ie_var = "srcElement";
	var moz_var = "href";
	this[moz_var] ? e_out = this : e_out = e[ie_var];

	var previewelement = document.getElementById("preview" + e_out.className);
	if (previewelement) {
		previewelement.parentNode.removeChild(previewelement);
	}
}

function addpreviewevents() {
	var aelements = document.getElementsByTagName('a');
	var aelement;
	var ainfo;
	for(var i=0;i<aelements.length;i++){
		aelement = aelements[i];
		if (aelement.className) {
			if (aelement.className.substr(0, 4) == "ref|") {
				if (aelement.addEventListener){
					aelement.addEventListener("mouseover", addreflinkpreview, false);
					aelement.addEventListener("mouseout", delreflinkpreview, false);
				}
				else if (aelement.attachEvent){
					aelement.attachEvent("onmouseover", addreflinkpreview);
					aelement.attachEvent("onmouseout", delreflinkpreview);
				}
			}
		}
	}
}
function get_password(name) {
	var pass = getCookie(name);
	if(pass) return pass;

	var chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	var pass='';

	for(var i=0;i<8;i++) {
		var rnd = Math.floor(Math.random()*chars.length);
		pass += chars.substring(rnd, rnd+1);
	}
	set_cookie(name, pass, 365);
	return(pass);
}
function replaceString( str, from, to ) {
	var idx = str.indexOf( from );
	while ( idx > -1 ) {
		str = str.replace( from, to );
		idx = str.indexOf( from );
	}
	return str;
}

function getCookie(name) {
	with(document.cookie) {
		var regexp=new RegExp("(^|;\\s+)"+name+"=(.*?)(;|$)");
		var hit=regexp.exec(document.cookie);
		if(hit&&hit.length>2) {
			return utf8.decode(unescape(replaceString(hit[2],'+','%20')));
		} else {
		return '';
		}
	}
}

function set_cookie(name,value,days) {
	if(days) {
		var date=new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires="; expires="+date.toGMTString();
	} else expires="";
	document.cookie=name+"="+value+expires+"; path=/";
}
function del_cookie(name) {
	document.cookie = name +'=; expires=Thu, 01-Jan-70 00:00:01 GMT; path=/';
} 
function togglePassword() {
	/* Now IE/Opera safe */
	var bSaf = (navigator.userAgent.indexOf('Safari') != -1);
	var bOpera = (navigator.userAgent.indexOf('Opera') != -1);
	var bMoz = (navigator.appName == 'Netscape');
	var passwordbox = document.getElementById("passwordbox");
	if (passwordbox) {
		var passwordbox1_html;
		
		if ((bSaf) || (bOpera) || (bMoz))
			passwordbox_html = passwordbox.innerHTML;
		else passwordbox_html = passwordbox.text;
		
		passwordbox_html = passwordbox_html.toLowerCase();
		var newhtml = '<td></td><td></td>';

		if (passwordbox_html == newhtml) {
			var newhtml = '<td class="postblock">Mod</td><td><input type="text" name="modpassword" size="28" maxlength="75">&nbsp;<acronym title="Display staff status (Mod/Admin)">D</acronym>:&nbsp;<input type="checkbox" name="displaystaffstatus" checked>&nbsp;<acronym title="Lock">L</acronym>:&nbsp;<input type="checkbox" name="lock">&nbsp;&nbsp;<acronym title="Sticky">S</acronym>:&nbsp;<input type="checkbox" name="sticky">&nbsp;&nbsp;<acronym title="Raw HTML">RH</acronym>:&nbsp;<input type="checkbox" name="rawhtml">&nbsp;&nbsp;<acronym title="Name">N</acronym>:&nbsp;<input type="checkbox" name="usestaffname"></td>';
		}
		
		if ((bSaf) || (bOpera) || (bMoz))
			passwordbox.innerHTML = newhtml;
		else passwordbox.text = newhtml;
	}
	return newhtml;
}
function delandbanlinks() {
	if (!mod_set) return;
	togglePassword();
	var dnbelements = document.getElementsByTagName('span');
	var dnbelement;
	var dnbinfo;
	var xmlhttp = new XMLHttpRequest();
	for(var i=0;i<dnbelements.length;i++){
		dnbelement = dnbelements[i];
		if (dnbelement.getAttribute('id')) {
			if (dnbelement.getAttribute('id').substr(0, 3) == 'dnb') {
				dnbinfo = dnbelement.getAttribute('id').split('-');
				xmlhttp.open("GET","/management/index.php?action=getip&board="+dnbinfo[1]+"&id="+dnbinfo[2],false);
				xmlhttp.send();
				var ip = xmlhttp.responseText;
				dnbelements[i].innerHTML = " [IP: "+ip.replace('::ffff:', '') +" <a href='/management/index.php?side=mod&action=delip&ip="+xmlhttp.responseText+"' title='Delete all posts by this IP' target='_blank'>D</a>] [<a href='/management/index.php?side=mod&action=del&id="+dnbinfo[2]+"&boardname="+dnbinfo[1]+"' target='_blank'>D</a> <a href='/management/index.php?side=mod&action=delban&ip="+xmlhttp.responseText+"&boardname="+dnbinfo[1]+"' target='_blank'>&</a> <a href='/management/index.php?side=mod&action=bans&do=ban&ip="+xmlhttp.responseText+"&boardname="+dnbinfo[1]+"&id="+dnbinfo[2]+"' target='_blank'>B</a>]";
			}
		}
	}
}
function set_inputs(id) {
      if (document.getElementById(id)) {
           with(document.getElementById(id)) {
                if(!name.value) name.value = getCookie("name");
                if(!em.value) em.value = getCookie("email");
                if(!password.value) password.value = get_password("postpassword");
           }
      }
 }
function set_delpass(id) {
	if (document.getElementById(id).postpassword) {
		with(document.getElementById(id)) {
			if(!postpassword.value) postpassword.value = get_password("postpassword");
		}
	}
}

function addreflinkpreview(e) {
	var e_out;
	var ie_var = "srcElement";
	var moz_var = "href";
	this[moz_var] ? e_out = this : e_out = e[ie_var];
	ainfo = e_out.className.split('|');
	
	var previewdiv = document.createElement('div');
	
	previewdiv.setAttribute("id", "preview" + e_out.className);
	previewdiv.setAttribute('class', 'reflinkpreview');
	previewdiv.setAttribute('className', 'reflinkpreview');
	if (e.pageX) {
		previewdiv.style.left = '' + (e.pageX + 50) + 'px';
	} else {
		previewdiv.style.left = (e.clientX + 50);
	}
	var previewdiv_content = document.createTextNode('');
	previewdiv.appendChild(previewdiv_content);
	var parentelement = e_out.parentNode;
	var newelement = parentelement.insertBefore(previewdiv, e_out);
	new Ajax.Request('/read.php?b=' + ainfo[1] + '&t=' + ainfo[2] + '&p=' + ainfo[3] + '&single',
	{
		method:'get',
		onSuccess: function(transport){
			var response = transport.responseText || _("something went wrong (blank response)");
			newelement.innerHTML = response;
		},
		onFailure: function(){ alert('wut'); }
	});
}

function delreflinkpreview(e) {
	var e_out;
	var ie_var = "srcElement";
	var moz_var = "href";
	this[moz_var] ? e_out = this : e_out = e[ie_var];

	var previewelement = document.getElementById("preview" + e_out.className);
	if (previewelement) {
		previewelement.parentNode.removeChild(previewelement);
	}
}

function addpreviewevents() {
	var aelements = document.getElementsByTagName('a');
	var aelement;
	var ainfo;
	for(var i=0;i<aelements.length;i++){
		aelement = aelements[i];
		if (aelement.className) {
			if (aelement.className.substr(0, 4) == "ref|") {
				if (aelement.addEventListener){
					aelement.addEventListener("mouseover", addreflinkpreview, false);
					aelement.addEventListener("mouseout", delreflinkpreview, false);
				}
				else if (aelement.attachEvent){
					aelement.attachEvent("onmouseover", addreflinkpreview);
					aelement.attachEvent("onmouseout", delreflinkpreview);
				}
			}
		}
	}
}
window.onload=function() {
    if (getCookie("mod") == "allboards") {
		mod_set = true;
        delandbanlinks();
    }
    /*else if(getCookie("mod") != "") {
        var listofboards = getCookie("mod").split('|');
        var thisboard = document.getElementById("postform").board.value;
        for (var cookieboard in listofboards) {
            if (listofboards[cookieboard] == thisboard) {
               var mod_set = true;
                break;
            }
        }
    }	*/
	document.onkeydown = keypress;
}
function reppop(a) {
	day=new Date();
	id=day.getTime();
	window.open(a,id,"toolbar=0,scrollbars=0,location=0,status=1,menubar=0,resizable=1,width=685,height=200");
	return false;
}
