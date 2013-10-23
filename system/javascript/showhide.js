function ShowHide(id) {
	imageid = id+"img";
	if ( document.getElementById(id).style.display == "none" ) {
		document.getElementById(id).style.display = "block";
	} else {
		document.getElementById(id).style.display = "none";
	}
}

function Hide(id) {
	document.getElementById(id).style.display = "none";
}

function Show(id) {
	document.getElementById(id).style.display = "block";
	document.getElementById(id).style.position = "absolute";
}
function ShowById(id) {
	document.getElementById(id).style.display = "block";
	return false;
}

function ExpandOrCollapseContent(o, collapseId) {
	var ip = o.parentNode;
	var p = ip.parentNode;
	p = p.parentNode;
	var children = p.getElementsByTagName("div");
	var im = ip.getElementsByTagName("img")[0];

	if (im.src.indexOf("minus") != -1) {

		im.src = 'images/plus.jpg';
		document.getElementById(collapseId).style.display = "none";

	} else {

		im.src = 'images/minus.jpg';
		document.getElementById(collapseId).style.display = "block";
	}
	return false;
}

function CollapseContent(o, collapseId) {
	var ip = o.parentNode;
	var p = ip.parentNode;
	p = p.parentNode;
	var children = p.getElementsByTagName("div");
	var im = ip.getElementsByTagName("img")[0];

	im.src = 'images/plus.jpg';
	document.getElementById(collapseId).style.display = "none";

	return false;
}

function ExpandContent(o, collapseId) {
	var ip = o.parentNode;
	var p = ip.parentNode;
	p = p.parentNode;
	var children = p.getElementsByTagName("div");
	var im = ip.getElementsByTagName("img")[0];

	im.src = 'images/minus.jpg';
	document.getElementById(collapseId).style.display = "block";

	return false;
}
