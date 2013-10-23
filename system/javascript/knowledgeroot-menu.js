/**
 * Javascript to show menu on tree elements or on content elements
 * Knowledgeroot
 * Frank Habermann
 * 28.10.2007
 */

var KnowledgerootMenu = {
	mouseX: 0,
	mouseY: 0,
	id: '',

	init: function() {
		Event.observe(document, "mousemove", this.getPosition, false);
	},

	show: function(id, menuname, pageid, contentid) {
		// set id
		this.id = id;

		// get and build menu
		this.getMenu(id,menuname, pageid, contentid);

		//Event.stopObserving(document, "mousemove", KnowledgerootMenu.getPosition);
	},

	showMenu: function(id) {
		$(id).style.left = KnowledgerootMenu.mouseX-1 + 'px';
		$(id).style.top = KnowledgerootMenu.mouseY-1 + 'px';
		$(id).style.zIndex = 999;
		$(id).style.display = "block";
		//alert($(id).offsetWidth + "#" + $(id).offsetHeight);
	},

	hide: function(id) {
		$(id).style.display = "none";
	},

	getPosition: function(mevent) {
		KnowledgerootMenu.mouseX = Event.pointerX(mevent);
		KnowledgerootMenu.mouseY = Event.pointerY(mevent);

		id = KnowledgerootMenu.id;

		// now check if menu is shown and hide if mouse is not over
		if(id != "" && $(id).style.display == "block") {
			if(KnowledgerootMenu.mouseX < ($(id).offsetLeft-2) || KnowledgerootMenu.mouseX > ($(id).offsetLeft+$(id).offsetWidth+2) || KnowledgerootMenu.mouseY < ($(id).offsetTop-2) || KnowledgerootMenu.mouseY > ($(id).offsetTop+$(id).offsetHeight+2)) {
				KnowledgerootMenu.hide(id);
			}
		}
	},

	getMenu: function(id,menuname,pageid, contentid) {
		script = "ajax-xml.php";

		if(typeof(contentid) != 'number') {
			contentid = "";
		}

		var ajaxopen = new Ajax.Request(
			script,
			{
				method:'post',
				postBody: 'ajaxmenu='+menuname+'&id='+pageid+'&contentid='+contentid,
				onComplete:KnowledgerootMenu.buildMenu,
				onFailure:KnowledgerootMenu.showError
			}
		);
	},

	buildMenu: function(r) {
		try {
			var root = r.responseXML.getElementsByTagName("root");

			if(navigator.appName == "Opera" || navigator.appName == "opera") {
				var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
			} else if (document.all) {
				var data = unescape(r.responseXML.getElementsByTagName("html")[0].firstChild.nodeValue);
			} else {
				var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
			}

			$(KnowledgerootMenu.id).innerHTML = data;
			KnowledgerootMenu.showMenu(KnowledgerootMenu.id);
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number + "#" + e.file, "error");
			setTimeout("HideMessage()", 7000);
		}
	},

	showError: function(r) {

	}
}

// init menu
KnowledgerootMenu.init();
