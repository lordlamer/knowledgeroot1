/*
	Knowledgeroot
	Frank Habermann
	This file includes functions that are used to display and work with the ajaxtree
*/

	function AjaxMenuOpen(id,move,path,editor) {
		ShowMessage(msgboxloading, "loading");

		script = path + "ajax-xml.php";

        $.ajax({
            type: "POST",
            url: script,
            data: 'ajaxopen='+id+'&move='+move+'&editor='+editor,
            success: showOpen,
			error: showError,
            dataType: "xml"
        });

        $("#menuimg_"+id).attr("src",path + "images/minus.jpg");
        document.getElementById("linkid_"+id).onclick = function () { AjaxMenuClose(id,move,path,editor); };
	}

	function AjaxMenuClose(id,move,path,editor) {
		ShowMessage(msgboxloading, "loading");

		script = path + "ajax-xml.php";

        $.ajax({
            type: "POST",
            url: script,
            data: 'ajaxclose='+id+'&move='+move+'&editor='+editor,
            success: showClose,
            error: showError,
            dataType: "xml"
        });

        $("#menuimg_"+id).attr("src",path + "images/plus.jpg");
        document.getElementById("linkid_"+id).onclick = function () { AjaxMenuOpen(id,move,path,editor); };
	}

	var showOpen = function(r) {
		try {
		    var id = $(r).find('parentid').text();
            var data = $(r).find('html').text();
            $("#menu_"+id).after(data);

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number + "#" + e.file, "error");
			setTimeout("HideMessage()", 7000);
		}
	}

	var showClose = function(r) {
		try {

            $(r).find('element').each(function() {
                $("#menu_" + $(this).text()).remove();
            });

            $(r).find('content').each(function() {
                $("#contenttreeid_" + $(this).text()).remove();
            });

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number, "error");
			setTimeout("HideMessage()", 7000);
		}
	}

	var removeElement = function(id) {
        $( "#" + id ).remove();
	}

	var showError = function(r) {
	}

	var ShowTree = function() {
		$("#tree").show();
		$("#treeopener").hide();
	}

	var TreeExpand = function(move) {
		ShowMessage(msgboxloading, "loading");

        $.ajax({
            type: "POST",
            url: "ajax-xml.php",
            data: 'expandtree=yes&move='+move,
            success: showReloadTree,
            error: showError,
            dataType: "xml"
        });
	}

	var TreeCollapse = function(move) {
		ShowMessage(msgboxloading, "loading");

        $.ajax({
            type: "POST",
            url: "ajax-xml.php",
            data: 'collapsetree=yes&move='+move,
            success: showReloadTree,
            error: showError,
            dataType: "xml"
        });
	}

	var TreeReload = function(move) {
		ShowMessage(msgboxloading, "loading");

        $.ajax({
            type: "POST",
            url: "ajax-xml.php",
            data: 'reloadtree=yes&move='+move,
            success: showReloadTree,
            error: showError,
            dataType: "xml"
        });
	}

	var showReloadTree = function(r) {
		try {
			var data = $(r).find('html').text();

            $( "#treeelementtable" ).remove();
            $( "#treeelements" ).prepend( data );

			HideMessage("loading");
		} catch (e) {
			ShowMessage(e.message + "#" + e.name + "#" + e.number, "error");
			setTimeout("HideMessage()", 7000);
		}
	}

