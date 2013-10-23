	function show_field(id) {
		document.getElementById(id).style.display = "none";
		document.getElementById("input_"+id).style.display = "block";
		document.getElementById("cancel_"+id).style.display = "block";
	}

	function hide_field(id) {
		document.getElementById(id).style.display = "block";
		document.getElementById("input_"+id).style.display = "none";
		document.getElementById("cancel_"+id).style.display = "none";
	}

	function show_edit(id) {
		if(document.getElementById("input_"+id).style.display == "none") {
			document.getElementById(id).style.display = "block";
		}
	}

	function hide_edit(id) {
		document.getElementById(id).style.display = "none";
	}

	function saveConfig(path,value) {
		//ShowMessage(msgboxloading, "loading");
		var ajaxopen = new Ajax.Request(
			"index.php",
			{
				method:'post',
				postBody: 'ext=admin_config&action=save_config&config_path='+ path +'&config_value='+value,
				onComplete:showOpen,
				onFailure:showError
			}
		);
	}

	var showError = function(r) {
		alert("Error: " + r.status + "/t" + r.statusText);
	}

	var showOpen = function(r) {
		try {
			var root = r.responseXML.getElementsByTagName("root");
			var error = 0;
			var errormsg = "";

			if (document.all) {
				var value = unescape(r.responseXML.getElementsByTagName("value")[0].firstChild.nodeValue);
				try {
					var error = unescape(r.responseXML.getElementsByTagName("error")[0].firstChild.nodeValue);
					var errormsg = unescape(r.responseXML.getElementsByTagName("errormsg")[0].firstChild.nodeValue);
				} catch(e) {
				}
			} else {
				var value = unescape(root[0].getElementsByTagName("value")[0].textContent);
				try {
					var error = unescape(root[0].getElementsByTagName("error")[0].textContent);
					var errormsg = unescape(root[0].getElementsByTagName("errormsg")[0].textContent);
				} catch(e) {
				}
			}
			var path = root[0].getElementsByTagName("name")[0].firstChild.nodeValue;

			if(error == "1") {
				ShowMessage(errormsg,"error");
				setTimeout("HideMessage()", 7000);
				document.getElementById('value_' + path).firstChild.nodeValue = value;
			} else {
				document.getElementById('value_' + path).firstChild.nodeValue = value;
			}
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number + "#" + e.file, "error");
			setTimeout("HideMessage()", 7000);
		}
	}
