/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit._editor.plugins.EnterKeyHandling"]){
dojo._hasResource["dijit._editor.plugins.EnterKeyHandling"]=true;
dojo.provide("dijit._editor.plugins.EnterKeyHandling");
dojo.require("dojo.window");
dojo.require("dijit._editor._Plugin");
dojo.require("dijit._editor.range");
dojo.declare("dijit._editor.plugins.EnterKeyHandling",dijit._editor._Plugin,{blockNodeForEnter:"BR",constructor:function(_1){
if(_1){
if("blockNodeForEnter" in _1){
_1.blockNodeForEnter=_1.blockNodeForEnter.toUpperCase();
}
dojo.mixin(this,_1);
}
},setEditor:function(_2){
if(this.editor===_2){
return;
}
this.editor=_2;
if(this.blockNodeForEnter=="BR"){
this.editor.customUndo=true;
_2.onLoadDeferred.addCallback(dojo.hitch(this,function(d){
this.connect(_2.document,"onkeypress",function(e){
if(e.charOrCode==dojo.keys.ENTER){
var ne=dojo.mixin({},e);
ne.shiftKey=true;
if(!this.handleEnterKey(ne)){
dojo.stopEvent(e);
}
}
});
if(dojo.isIE>=9){
this.connect(_2.document.body,"onpaste",function(e){
setTimeout(dojo.hitch(this,function(){
var r=this.editor.document.selection.createRange();
r.move("character",-1);
r.select();
r.move("character",1);
r.select();
}),0);
});
}
return d;
}));
}else{
if(this.blockNodeForEnter){
var h=dojo.hitch(this,this.handleEnterKey);
_2.addKeyHandler(13,0,0,h);
_2.addKeyHandler(13,0,1,h);
this.connect(this.editor,"onKeyPressed","onKeyPressed");
}
}
},onKeyPressed:function(e){
if(this._checkListLater){
if(dojo.withGlobal(this.editor.window,"isCollapsed",dijit)){
var _3=dojo.withGlobal(this.editor.window,"getAncestorElement",dijit._editor.selection,["LI"]);
if(!_3){
dijit._editor.RichText.prototype.execCommand.call(this.editor,"formatblock",this.blockNodeForEnter);
var _4=dojo.withGlobal(this.editor.window,"getAncestorElement",dijit._editor.selection,[this.blockNodeForEnter]);
if(_4){
_4.innerHTML=this.bogusHtmlContent;
if(dojo.isIE<=9){
var r=this.editor.document.selection.createRange();
r.move("character",-1);
r.select();
}
}else{
console.error("onKeyPressed: Cannot find the new block node");
}
}else{
if(dojo.isMoz){
if(_3.parentNode.parentNode.nodeName=="LI"){
_3=_3.parentNode.parentNode;
}
}
var fc=_3.firstChild;
if(fc&&fc.nodeType==1&&(fc.nodeName=="UL"||fc.nodeName=="OL")){
_3.insertBefore(fc.ownerDocument.createTextNode(" "),fc);
var _5=dijit.range.create(this.editor.window);
_5.setStart(_3.firstChild,0);
var _6=dijit.range.getSelection(this.editor.window,true);
_6.removeAllRanges();
_6.addRange(_5);
}
}
}
this._checkListLater=false;
}
if(this._pressedEnterInBlock){
if(this._pressedEnterInBlock.previousSibling){
this.removeTrailingBr(this._pressedEnterInBlock.previousSibling);
}
delete this._pressedEnterInBlock;
}
},bogusHtmlContent:"&nbsp;",blockNodes:/^(?:P|H1|H2|H3|H4|H5|H6|LI)$/,handleEnterKey:function(e){
var _7,_8,_9,_a,_b,_c,_d=this.editor.document,br,rs,_e;
if(e.shiftKey){
var _f=dojo.withGlobal(this.editor.window,"getParentElement",dijit._editor.selection);
var _10=dijit.range.getAncestor(_f,this.blockNodes);
if(_10){
if(_10.tagName=="LI"){
return true;
}
_7=dijit.range.getSelection(this.editor.window);
_8=_7.getRangeAt(0);
if(!_8.collapsed){
_8.deleteContents();
_7=dijit.range.getSelection(this.editor.window);
_8=_7.getRangeAt(0);
}
if(dijit.range.atBeginningOfContainer(_10,_8.startContainer,_8.startOffset)){
br=_d.createElement("br");
_9=dijit.range.create(this.editor.window);
_10.insertBefore(br,_10.firstChild);
_9.setStartBefore(br.nextSibling);
_7.removeAllRanges();
_7.addRange(_9);
}else{
if(dijit.range.atEndOfContainer(_10,_8.startContainer,_8.startOffset)){
_9=dijit.range.create(this.editor.window);
br=_d.createElement("br");
_10.appendChild(br);
_10.appendChild(_d.createTextNode(" "));
_9.setStart(_10.lastChild,0);
_7.removeAllRanges();
_7.addRange(_9);
}else{
rs=_8.startContainer;
if(rs&&rs.nodeType==3){
_e=rs.nodeValue;
dojo.withGlobal(this.editor.window,function(){
_a=_d.createTextNode(_e.substring(0,_8.startOffset));
_b=_d.createTextNode(_e.substring(_8.startOffset));
_c=_d.createElement("br");
if(_b.nodeValue==""&&dojo.isWebKit){
_b=_d.createTextNode(" ");
}
dojo.place(_a,rs,"after");
dojo.place(_c,_a,"after");
dojo.place(_b,_c,"after");
dojo.destroy(rs);
_9=dijit.range.create(dojo.gobal);
_9.setStart(_b,0);
_7.removeAllRanges();
_7.addRange(_9);
});
return false;
}
return true;
}
}
}else{
_7=dijit.range.getSelection(this.editor.window);
if(_7.rangeCount){
_8=_7.getRangeAt(0);
if(_8&&_8.startContainer){
if(!_8.collapsed){
_8.deleteContents();
_7=dijit.range.getSelection(this.editor.window);
_8=_7.getRangeAt(0);
}
rs=_8.startContainer;
if(rs&&rs.nodeType==3){
dojo.withGlobal(this.editor.window,dojo.hitch(this,function(){
var _11=false;
var _12=_8.startOffset;
if(rs.length<_12){
ret=this._adjustNodeAndOffset(rs,_12);
rs=ret.node;
_12=ret.offset;
}
_e=rs.nodeValue;
_a=_d.createTextNode(_e.substring(0,_12));
_b=_d.createTextNode(_e.substring(_12));
_c=_d.createElement("br");
if(!_b.length){
_b=_d.createTextNode(" ");
_11=true;
}
if(_a.length){
dojo.place(_a,rs,"after");
}else{
_a=rs;
}
dojo.place(_c,_a,"after");
dojo.place(_b,_c,"after");
dojo.destroy(rs);
_9=dijit.range.create(dojo.gobal);
_9.setStart(_b,0);
_9.setEnd(_b,_b.length);
_7.removeAllRanges();
_7.addRange(_9);
if(_11&&!dojo.isWebKit){
dijit._editor.selection.remove();
}else{
dijit._editor.selection.collapse(true);
}
}));
}else{
var _13;
if(_8.startOffset>=0){
_13=rs.childNodes[_8.startOffset];
}
dojo.withGlobal(this.editor.window,dojo.hitch(this,function(){
var _14=_d.createElement("br");
var _15=_d.createTextNode(" ");
if(!_13){
rs.appendChild(_14);
rs.appendChild(_15);
}else{
dojo.place(_14,_13,"before");
dojo.place(_15,_14,"after");
}
_9=dijit.range.create(dojo.global);
_9.setStart(_15,0);
_9.setEnd(_15,_15.length);
_7.removeAllRanges();
_7.addRange(_9);
dijit._editor.selection.collapse(true);
}));
}
}
}else{
dijit._editor.RichText.prototype.execCommand.call(this.editor,"inserthtml","<br>");
}
}
return false;
}
var _16=true;
_7=dijit.range.getSelection(this.editor.window);
_8=_7.getRangeAt(0);
if(!_8.collapsed){
_8.deleteContents();
_7=dijit.range.getSelection(this.editor.window);
_8=_7.getRangeAt(0);
}
var _17=dijit.range.getBlockAncestor(_8.endContainer,null,this.editor.editNode);
var _18=_17.blockNode;
if((this._checkListLater=(_18&&(_18.nodeName=="LI"||_18.parentNode.nodeName=="LI")))){
if(dojo.isMoz){
this._pressedEnterInBlock=_18;
}
if(/^(\s|&nbsp;|\xA0|<span\b[^>]*\bclass=['"]Apple-style-span['"][^>]*>(\s|&nbsp;|\xA0)<\/span>)?(<br>)?$/.test(_18.innerHTML)){
_18.innerHTML="";
if(dojo.isWebKit){
_9=dijit.range.create(this.editor.window);
_9.setStart(_18,0);
_7.removeAllRanges();
_7.addRange(_9);
}
this._checkListLater=false;
}
return true;
}
if(!_17.blockNode||_17.blockNode===this.editor.editNode){
try{
dijit._editor.RichText.prototype.execCommand.call(this.editor,"formatblock",this.blockNodeForEnter);
}
catch(e2){
}
_17={blockNode:dojo.withGlobal(this.editor.window,"getAncestorElement",dijit._editor.selection,[this.blockNodeForEnter]),blockContainer:this.editor.editNode};
if(_17.blockNode){
if(_17.blockNode!=this.editor.editNode&&(!(_17.blockNode.textContent||_17.blockNode.innerHTML).replace(/^\s+|\s+$/g,"").length)){
this.removeTrailingBr(_17.blockNode);
return false;
}
}else{
_17.blockNode=this.editor.editNode;
}
_7=dijit.range.getSelection(this.editor.window);
_8=_7.getRangeAt(0);
}
var _19=_d.createElement(this.blockNodeForEnter);
_19.innerHTML=this.bogusHtmlContent;
this.removeTrailingBr(_17.blockNode);
var _1a=_8.endOffset;
var _1b=_8.endContainer;
if(_1b.length<_1a){
var ret=this._adjustNodeAndOffset(_1b,_1a);
_1b=ret.node;
_1a=ret.offset;
}
if(dijit.range.atEndOfContainer(_17.blockNode,_1b,_1a)){
if(_17.blockNode===_17.blockContainer){
_17.blockNode.appendChild(_19);
}else{
dojo.place(_19,_17.blockNode,"after");
}
_16=false;
_9=dijit.range.create(this.editor.window);
_9.setStart(_19,0);
_7.removeAllRanges();
_7.addRange(_9);
if(this.editor.height){
dojo.window.scrollIntoView(_19);
}
}else{
if(dijit.range.atBeginningOfContainer(_17.blockNode,_8.startContainer,_8.startOffset)){
dojo.place(_19,_17.blockNode,_17.blockNode===_17.blockContainer?"first":"before");
if(_19.nextSibling&&this.editor.height){
_9=dijit.range.create(this.editor.window);
_9.setStart(_19.nextSibling,0);
_7.removeAllRanges();
_7.addRange(_9);
dojo.window.scrollIntoView(_19.nextSibling);
}
_16=false;
}else{
if(_17.blockNode===_17.blockContainer){
_17.blockNode.appendChild(_19);
}else{
dojo.place(_19,_17.blockNode,"after");
}
_16=false;
if(_17.blockNode.style){
if(_19.style){
if(_17.blockNode.style.cssText){
_19.style.cssText=_17.blockNode.style.cssText;
}
}
}
rs=_8.startContainer;
var _1c;
if(rs&&rs.nodeType==3){
var _1d,_1e;
_1a=_8.endOffset;
if(rs.length<_1a){
ret=this._adjustNodeAndOffset(rs,_1a);
rs=ret.node;
_1a=ret.offset;
}
_e=rs.nodeValue;
_a=_d.createTextNode(_e.substring(0,_1a));
_b=_d.createTextNode(_e.substring(_1a,_e.length));
dojo.place(_a,rs,"before");
dojo.place(_b,rs,"after");
dojo.destroy(rs);
var _1f=_a.parentNode;
while(_1f!==_17.blockNode){
var tg=_1f.tagName;
var _20=_d.createElement(tg);
if(_1f.style){
if(_20.style){
if(_1f.style.cssText){
_20.style.cssText=_1f.style.cssText;
}
}
}
if(_1f.tagName==="FONT"){
if(_1f.color){
_20.color=_1f.color;
}
if(_1f.face){
_20.face=_1f.face;
}
if(_1f.size){
_20.size=_1f.size;
}
}
_1d=_b;
while(_1d){
_1e=_1d.nextSibling;
_20.appendChild(_1d);
_1d=_1e;
}
dojo.place(_20,_1f,"after");
_a=_1f;
_b=_20;
_1f=_1f.parentNode;
}
_1d=_b;
if(_1d.nodeType==1||(_1d.nodeType==3&&_1d.nodeValue)){
_19.innerHTML="";
}
_1c=_1d;
while(_1d){
_1e=_1d.nextSibling;
_19.appendChild(_1d);
_1d=_1e;
}
}
_9=dijit.range.create(this.editor.window);
var _21;
var _22=_1c;
if(this.blockNodeForEnter!=="BR"){
while(_22){
_21=_22;
_1e=_22.firstChild;
_22=_1e;
}
if(_21&&_21.parentNode){
_19=_21.parentNode;
_9.setStart(_19,0);
_7.removeAllRanges();
_7.addRange(_9);
if(this.editor.height){
dijit.scrollIntoView(_19);
}
if(dojo.isMoz){
this._pressedEnterInBlock=_17.blockNode;
}
}else{
_16=true;
}
}else{
_9.setStart(_19,0);
_7.removeAllRanges();
_7.addRange(_9);
if(this.editor.height){
dijit.scrollIntoView(_19);
}
if(dojo.isMoz){
this._pressedEnterInBlock=_17.blockNode;
}
}
}
}
return _16;
},_adjustNodeAndOffset:function(_23,_24){
while(_23.length<_24&&_23.nextSibling&&_23.nextSibling.nodeType==3){
_24=_24-_23.length;
_23=_23.nextSibling;
}
var ret={"node":_23,"offset":_24};
return ret;
},removeTrailingBr:function(_25){
var _26=/P|DIV|LI/i.test(_25.tagName)?_25:dijit._editor.selection.getParentOfType(_25,["P","DIV","LI"]);
if(!_26){
return;
}
if(_26.lastChild){
if((_26.childNodes.length>1&&_26.lastChild.nodeType==3&&/^[\s\xAD]*$/.test(_26.lastChild.nodeValue))||_26.lastChild.tagName=="BR"){
dojo.destroy(_26.lastChild);
}
}
if(!_26.childNodes.length){
_26.innerHTML=this.bogusHtmlContent;
}
}});
}
