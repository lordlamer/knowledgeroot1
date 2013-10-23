/**
* Javascript to show dragbox
* Knowledgeroot
* Frank Habermann
* 20080103
 */

var Dragbox = {
	mouseX: 0,
	mouseY: 0,

	init: function() {
		Event.observe(document, "mousemove", this.getPosition, false);
	},

	show: function(message) {
		// get and build menu
		this.showBox(message);
	},

	showBox: function(message) {
		$('dragbox').style.left = Dragbox.mouseX+20 + 'px';
		$('dragbox').style.top = Dragbox.mouseY + 'px';
		$('dragbox').style.zIndex = 999;
		$('dragbox').style.display = "block";
		$('dragbox').innerHTML = message;
	},

	hide: function() {
		// use some gimmicks here instead of only display the div out
		Effect.Puff('dragbox');
		//$('dragbox').style.display = "none";
		//$('dragbox').innerHTML = '&nbsp;';
	},

	move: function() {
		if($('dragbox')) {
			$('dragbox').style.left = Dragbox.mouseX+20 + 'px';
			$('dragbox').style.top = Dragbox.mouseY + 'px';
			$('dragbox').style.zIndex = 999;
		}
	},

	getPosition: function(mevent) {
		Dragbox.mouseX = Event.pointerX(mevent);
		Dragbox.mouseY = Event.pointerY(mevent);
		Dragbox.move();
	}
}

// init Dragbox
Dragbox.init();
