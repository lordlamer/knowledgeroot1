/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit.form._ExpandingTextAreaMixin"]){
dojo._hasResource["dijit.form._ExpandingTextAreaMixin"]=true;
dojo.provide("dijit.form._ExpandingTextAreaMixin");
var needsHelpShrinking;
dojo.declare("dijit.form._ExpandingTextAreaMixin",null,{_setValueAttr:function(){
this.inherited(arguments);
this.resize();
},postCreate:function(){
this.inherited(arguments);
var _1=this.textbox;
if(needsHelpShrinking==undefined){
var te=dojo.create("textarea",{rows:"5",cols:"20",value:" ",style:{zoom:1,fontSize:"12px",height:"96px",overflow:"hidden",visibility:"hidden",position:"absolute",border:"5px solid white",margin:"0",padding:"0",boxSizing:"border-box",MsBoxSizing:"border-box",WebkitBoxSizing:"border-box",MozBoxSizing:"border-box"}},dojo.body(),"last");
needsHelpShrinking=te.scrollHeight>=te.clientHeight;
dojo.body().removeChild(te);
}
this.connect(_1,"onresize","_resizeLater");
this.connect(_1,"onfocus","_resizeLater");
_1.style.overflowY="hidden";
},startup:function(){
this.inherited(arguments);
this._resizeLater();
},_onInput:function(e){
this.inherited(arguments);
this.resize();
},_estimateHeight:function(){
var _2=this.textbox;
_2.rows=(_2.value.match(/\n/g)||[]).length+1;
},_resizeLater:function(){
this.defer("resize");
},resize:function(){
var _3=this.textbox;
function _4(){
var _5=false;
if(_3.value===""){
_3.value=" ";
_5=true;
}
var sh=_3.scrollHeight;
if(_5){
_3.value="";
}
return sh;
};
if(_3.style.overflowY=="hidden"){
_3.scrollTop=0;
}
if(this.busyResizing){
return;
}
this.busyResizing=true;
if(_4()||_3.offsetHeight){
var _6=_4()+Math.max(_3.offsetHeight-_3.clientHeight,0);
var _7=_6+"px";
if(_7!=_3.style.height){
_3.style.height=_7;
_3.rows=1;
}
if(needsHelpShrinking){
var _8=_4(),_9=_8,_a=_3.style.minHeight,_b=4,_c,_d=_3.scrollTop;
_3.style.minHeight=_7;
_3.style.height="auto";
while(_6>0){
_3.style.minHeight=Math.max(_6-_b,4)+"px";
_c=_4();
var _e=_9-_c;
_6-=_e;
if(_e<_b){
break;
}
_9=_c;
_b<<=1;
}
_3.style.height=_6+"px";
_3.style.minHeight=_a;
_3.scrollTop=_d;
}
_3.style.overflowY=_4()>_3.clientHeight?"auto":"hidden";
if(_3.style.overflowY=="hidden"){
_3.scrollTop=0;
}
}else{
this._estimateHeight();
}
this.busyResizing=false;
}});
}
