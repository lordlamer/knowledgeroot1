/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit._editor.plugins.TextColor"]){
dojo._hasResource["dijit._editor.plugins.TextColor"]=true;
dojo.provide("dijit._editor.plugins.TextColor");
dojo.require("dijit._editor._Plugin");
dojo.require("dijit.ColorPalette");
dojo.declare("dijit._editor.plugins.TextColor",dijit._editor._Plugin,{buttonClass:dijit.form.DropDownButton,useDefaultCommand:false,_initButton:function(){
this.inherited(arguments);
var _1=this;
this.button.loadDropDown=function(_2){
this.dropDown=new dijit.ColorPalette({value:_1.value,onChange:function(_3){
_1.editor.execCommand(_1.command,_3);
}});
_2();
};
},updateState:function(){
var _4=this.editor;
var _5=this.command;
if(!_4||!_4.isLoaded||!_5.length){
return;
}
if(this.button){
var _6=this.get("disabled");
this.button.set("disabled",_6);
if(_6){
return;
}
var _7;
try{
_7=_4.queryCommandValue(_5)||"";
}
catch(e){
_7="";
}
}
if(_7==""){
_7="#000000";
}
if(_7=="transparent"){
_7="#ffffff";
}
if(typeof _7=="string"){
if(_7.indexOf("rgb")>-1){
_7=dojo.colorFromRgb(_7).toHex();
}
}else{
_7=((_7&255)<<16)|(_7&65280)|((_7&16711680)>>>16);
_7=_7.toString(16);
_7="#000000".slice(0,7-_7.length)+_7;
}
var _8=this.button.dropDown;
if(_8&&_7!==_8.get("value")){
_8.set("value",_7,false);
}
}});
dojo.subscribe(dijit._scopeName+".Editor.getPlugin",null,function(o){
if(o.plugin){
return;
}
switch(o.args.name){
case "foreColor":
case "hiliteColor":
o.plugin=new dijit._editor.plugins.TextColor({command:o.args.name});
}
});
}
