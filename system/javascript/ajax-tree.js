/*
	Knowledgeroot
	Frank Habermann
	This file includes functions that are used to display and work with the ajaxtree
*/

	function AjaxMenuOpen(id,move,path,editor) {
		ShowMessage(msgboxloading, "loading");

		script = path + "ajax-xml.php";

		var ajaxopen = new Ajax.Request(
			script,
			{
				method:'post',
				postBody: 'ajaxopen='+id+'&move='+move+'&editor='+editor,
				onComplete:showOpen,
				onFailure:showError
			}
		);

		document.getElementById("menuimg_"+id).src = path + "images/minus.jpg";
		document.getElementById("linkid_"+id).onclick = function () { AjaxMenuClose(id,move,path,editor); };
	}

	function AjaxMenuClose(id,move,path,editor) {
			ShowMessage(msgboxloading, "loading");

			script = path + "ajax-xml.php";

			var ajaxopen = new Ajax.Request(
				script,
				{
					method:'post',
					postBody: 'ajaxclose='+id+'&move='+move+'&editor='+editor,
					onComplete:showClose,
					onFailure:showError
				}
			);

			document.getElementById("menuimg_"+id).src = path + "images/plus.jpg";
			document.getElementById("linkid_"+id).onclick = function () { AjaxMenuOpen(id,move,path,editor); };
	}

	var showOpen = function(r) {
		try {
			//alert(r.responseText);
			var root = r.responseXML.getElementsByTagName("root");

			var id = root[0].getElementsByTagName("parentid")[0].firstChild.nodeValue;

			if(navigator.appName == "Opera" || navigator.appName == "opera") {
				var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
				new Insertion.After('menu_'+id, data);
			} else if (document.all) {
				var data = unescape(r.responseXML.getElementsByTagName("html")[0].firstChild.nodeValue);
				new Insertion.After('menu_'+id, data);
			} else {
				var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
				new Insertion.After('menu_'+id, data);
			}

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number + "#" + e.file, "error");
			setTimeout("HideMessage()", 7000);
			//alert(e.message + "#" + e.name + "#" + e.number);
		}
	}

	var showClose = function(r) {
		try {
			//alert(r.responseText);
			var root = r.responseXML.getElementsByTagName("root");
			var elements = root[0].getElementsByTagName("element");

			for(var i=0; i<elements.length; i++) {
				var id = root[0].getElementsByTagName("element")[i].firstChild.data;
				//var node = document.getElementById("menu_"+id);
				//var temp = document.getElementById("menu_"+id).removeChild(node.firstChild);

				//Element.remove("menu_"+id);
				// extra function because fucking ie is so stupid
				removeElement("menu_"+id);
			}

			// remove content elements
			var contents = root[0].getElementsByTagName("content");

			for(var i=0; i<contents.length; i++) {
				var id = root[0].getElementsByTagName("content")[i].firstChild.data;
				removeElement("contenttreeid_"+id);
			}

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number, "error");
			setTimeout("HideMessage()", 7000);
			//alert(e.message + "#" + e.name + "#" + e.number);
		}
	}

	var removeElement = function(id) {
		try {
			Element.remove(id);
		} catch (e) {

		}
	}

	var showError = function(r) {
	}

	var ShowTree = function() {
		$("tree").style.display = "block";
		$("treeopener").style.display = "none";
	}

	var HideTree = function() {
		$("tree").style.display = "none";
		$("treeopener").style.display = "block"
	}

	var TreeFixed = function() {
		$("tree").style.position = "static";
		$("treeslide").style.display = "block";
		$("treefixed").style.display = "none";
	}

	var TreeSlide = function() {
		$("tree").style.position = "absolute";
		$("treeslide").style.display = "none";
		$("treefixed").style.display = "block";
	}

	var TreeExpand = function(move) {
		ShowMessage(msgboxloading, "loading");
		var ajaxopen = new Ajax.Request(
			"ajax-xml.php",
			{
				method:'post',
				postBody: 'expandtree=yes&move='+move,
				onComplete:showReloadTree,
				onFailure:showError
			}
		);
	}

	var TreeCollapse = function(move) {
		ShowMessage(msgboxloading, "loading");
		var ajaxopen = new Ajax.Request(
			"ajax-xml.php",
			{
				method:'post',
				postBody: 'collapsetree=yes&move='+move,
				onComplete:showReloadTree,
				onFailure:showError
			}
		);
	}

	var TreeReload = function(move) {
		ShowMessage(msgboxloading, "loading");
		var ajaxopen = new Ajax.Request(
			"ajax-xml.php",
			{
				method:'post',
				postBody: 'reloadtree=yes&move='+move,
				onComplete:showReloadTree,
				onFailure:showError
			}
		);
	}

	var showReloadTree = function(r) {
		try {
			var root = r.responseXML.getElementsByTagName("root");
			if(navigator.appName == "Opera" || navigator.appName == "opera") {
				var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
			} else if (document.all) {
				var data = unescape(r.responseXML.getElementsByTagName("html")[0].firstChild.nodeValue);
			} else {
				var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
			}

			Element.remove("treeelementtable");
			new Insertion.Top('treeelements', data);
			//alert(data);
			//$("treeanchor").nodeValue = data;

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number, "error");
			setTimeout("HideMessage()", 7000);
		}
	}

	var AjaxMoveTree = function(source, destination) {
		if(source != destination) {
			ShowMessage(msgboxloading, "loading");

			script = "ajax-xml.php";

			var ajaxopen = new Ajax.Request(
				script,
				{
					method:'post',
					postBody: 'action=ajaxmove&source='+source+'&destination='+destination,
					onComplete:showReloadTree,
					onFailure:showError
				}
			);
		}
	}

	var AjaxMoveContent = function(source, destination) {
		ShowMessage(msgboxloading, "loading");

		script = "ajax-xml.php";

		var ajaxopen = new Ajax.Request(
			script,
			{
				method:'post',
				postBody: 'action=ajaxmovecontent&source='+source+'&destination='+destination,
				onComplete:showMoveTree,
				onFailure:showError
			}
		);
	}

	var showMoveTree = function(r) {
		try {
			var root = r.responseXML.getElementsByTagName("root");
			if(navigator.appName == "Opera" || navigator.appName == "opera") {
				var action = unescape(root[0].getElementsByTagName("action")[0].textContent);
			} else if (document.all) {
				var action = unescape(r.responseXML.getElementsByTagName("action")[0].firstChild.nodeValue);
			} else {
				var action = unescape(root[0].getElementsByTagName("action")[0].textContent);
			}

			if(action == "contentremove") {
				if(navigator.appName == "Opera" || navigator.appName == "opera") {
					var contentid = unescape(root[0].getElementsByTagName("element")[0].textContent);
				} else if (document.all) {
					var contentid = unescape(r.responseXML.getElementsByTagName("element")[0].firstChild.nodeValue);
				} else {
					var contentid = unescape(root[0].getElementsByTagName("element")[0].textContent);
				}

				Element.remove("contentid_" + contentid);
			}

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number, "error");
			setTimeout("HideMessage()", 7000);
		}
	}
