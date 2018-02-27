/**
 * Javascript to show messagebox
 * Knowledgeroot
 * Frank Habermann
 * 20080103
 */

var ShowMessage = function(msg, type) {
	$("#messagebox").show();
	$("#msg").attr("class", type);
	$("#msg").html(msg);
}

var HideMessage = function(type) {
	$("#messagebox").hide();
}