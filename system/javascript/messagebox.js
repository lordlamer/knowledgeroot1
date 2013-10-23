/**
 * Javascript to show messagebox
 * Knowledgeroot
 * Frank Habermann
 * 20080103
 */

	var ShowMessage = function(msg, type) {
		$("messagebox").style.display = "block";
		$("msg").className = type;
		$("msg").innerHTML = msg;
		
		if(type == "warning") {
			Effect.Shake('messagebox');
		}
		
	}

	var HideMessage = function(type) {
		if(type != "loading") {
			Effect.BlindUp('messagebox',{duration:1.0});
		} else {
			$("messagebox").style.display = "none";
		}
	}