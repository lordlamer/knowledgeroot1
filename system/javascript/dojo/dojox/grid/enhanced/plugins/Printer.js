/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.grid.enhanced.plugins.Printer"]){
dojo._hasResource["dojox.grid.enhanced.plugins.Printer"]=true;
dojo.provide("dojox.grid.enhanced.plugins.Printer");
dojo.require("dojo.DeferredList");
dojo.require("dojox.grid.enhanced._Plugin");
dojo.require("dojox.grid.enhanced.plugins.exporter.TableWriter");
dojo.declare("dojox.grid.enhanced.plugins.Printer",dojox.grid.enhanced._Plugin,{name:"printer",constructor:function(_1){
this.grid=_1;
this._mixinGrid(_1);
_1.setExportFormatter(function(_2,_3,_4,_5){
return _3.format(_4,_5);
});
},_mixinGrid:function(){
var g=this.grid;
g.printGrid=dojo.hitch(this,this.printGrid);
g.printSelected=dojo.hitch(this,this.printSelected);
g.exportToHTML=dojo.hitch(this,this.exportToHTML);
g.exportSelectedToHTML=dojo.hitch(this,this.exportSelectedToHTML);
g.normalizePrintedGrid=dojo.hitch(this,this.normalizeRowHeight);
},printGrid:function(_6){
this.exportToHTML(_6,dojo.hitch(this,this._print));
},printSelected:function(_7){
this.exportSelectedToHTML(_7,dojo.hitch(this,this._print));
},exportToHTML:function(_8,_9){
_8=this._formalizeArgs(_8);
var _a=this;
this.grid.exportGrid("table",_8,function(_b){
_a._wrapHTML(_8.title,_8.cssFiles,_8.titleInBody+_b).then(_9);
});
},exportSelectedToHTML:function(_c,_d){
_c=this._formalizeArgs(_c);
var _e=this;
this.grid.exportSelected("table",_c.writerArgs,function(_f){
_e._wrapHTML(_c.title,_c.cssFiles,_c.titleInBody+_f).then(_d);
});
},_loadCSSFiles:function(_10){
var dl=dojo.map(_10,function(_11){
_11=dojo.trim(_11);
if(_11.substring(_11.length-4).toLowerCase()===".css"){
return dojo.xhrGet({url:_11});
}else{
var d=new dojo.Deferred();
d.callback(_11);
return d;
}
});
return dojo.DeferredList.prototype.gatherResults(dl);
},_print:function(_12){
var win,_13=this,_14=function(w){
var doc=w.document;
doc.open();
doc.write(_12);
doc.close();
_13.normalizeRowHeight(doc);
};
if(!window.print){
return;
}else{
if(dojo.isChrome||dojo.isOpera){
win=window.open("javascript: ''","","status=0,menubar=0,location=0,toolbar=0,width=1,height=1,resizable=0,scrollbars=0");
_14(win);
win.print();
win.close();
}else{
var fn=this._printFrame,dn=this.grid.domNode;
if(!fn){
var _15=dn.id+"_print_frame";
if(!(fn=dojo.byId(_15))){
fn=dojo.create("iframe");
fn.id=_15;
fn.frameBorder=0;
dojo.style(fn,{width:"1px",height:"1px",position:"absolute",right:0,bottom:0,border:"none",overflow:"hidden"});
if(!dojo.isIE){
dojo.style(fn,"visibility","hidden");
}
dn.appendChild(fn);
}
this._printFrame=fn;
}
win=fn.contentWindow;
_14(win);
win.focus();
win.print();
}
}
},_wrapHTML:function(_16,_17,_18){
return this._loadCSSFiles(_17).then(function(_19){
var i,_1a=["<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">","<html ",dojo._isBodyLtr()?"":"dir=\"rtl\"","><head><title>",_16,"</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"></meta>"];
for(i=0;i<_19.length;++i){
_1a.push("<style type=\"text/css\">",_19[i],"</style>");
}
_1a.push("</head>");
if(_18.search(/^\s*<body/i)<0){
_18="<body>"+_18+"</body>";
}
_1a.push(_18,"</html>");
return _1a.join("");
});
},normalizeRowHeight:function(doc){
var _1b=dojo.query(".grid_view",doc.body);
var _1c=dojo.map(_1b,function(_1d){
return dojo.query(".grid_header",_1d)[0];
});
var _1e=dojo.map(_1b,function(_1f){
return dojo.query(".grid_row",_1f);
});
var _20=_1e[0].length;
var i,v,h,_21=0;
for(v=_1b.length-1;v>=0;--v){
h=dojo.contentBox(_1c[v]).h;
if(h>_21){
_21=h;
}
}
for(v=_1b.length-1;v>=0;--v){
dojo.style(_1c[v],"height",_21+"px");
}
for(i=0;i<_20;++i){
_21=0;
for(v=_1b.length-1;v>=0;--v){
h=dojo.contentBox(_1e[v][i]).h;
if(h>_21){
_21=h;
}
}
for(v=_1b.length-1;v>=0;--v){
dojo.style(_1e[v][i],"height",_21+"px");
}
}
var _22=0,ltr=dojo._isBodyLtr();
for(v=0;v<_1b.length;++v){
dojo.style(_1b[v],ltr?"left":"right",_22+"px");
_22+=dojo.marginBox(_1b[v]).w;
}
},_formalizeArgs:function(_23){
_23=(_23&&dojo.isObject(_23))?_23:{};
_23.title=String(_23.title)||"";
if(!dojo.isArray(_23.cssFiles)){
_23.cssFiles=[_23.cssFiles];
}
_23.titleInBody=_23.title?["<h1>",_23.title,"</h1>"].join(""):"";
return _23;
}});
dojox.grid.EnhancedGrid.registerPlugin(dojox.grid.enhanced.plugins.Printer,{"dependency":["exporter"]});
}
