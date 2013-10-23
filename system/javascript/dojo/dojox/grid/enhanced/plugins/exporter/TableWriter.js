/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.grid.enhanced.plugins.exporter.TableWriter"]){
dojo._hasResource["dojox.grid.enhanced.plugins.exporter.TableWriter"]=true;
dojo.provide("dojox.grid.enhanced.plugins.exporter.TableWriter");
dojo.require("dojox.grid.enhanced.plugins.exporter._ExportWriter");
dojox.grid.enhanced.plugins.Exporter.registerWriter("table","dojox.grid.enhanced.plugins.exporter.TableWriter");
dojo.declare("dojox.grid.enhanced.plugins.exporter.TableWriter",dojox.grid.enhanced.plugins.exporter._ExportWriter,{constructor:function(_1){
this._viewTables=[];
this._tableAttrs=_1||{};
},_getTableAttrs:function(_2){
var _3=this._tableAttrs[_2]||"";
if(_3&&_3[0]!=" "){
_3=" "+_3;
}
return _3;
},_getRowClass:function(_4){
return _4.isHeader?" grid_header":[" grid_row grid_row_",_4.rowIdx+1,_4.rowIdx%2?" grid_even_row":" grid_odd_row"].join("");
},_getColumnClass:function(_5){
var _6=_5.cell.index+_5.colOffset+1;
return [" grid_column grid_column_",_6,_6%2?" grid_odd_column":" grid_even_column"].join("");
},beforeView:function(_7){
var _8=_7.viewIdx,_9=this._viewTables[_8],_a,_b=dojo.marginBox(_7.view.contentNode).w;
if(!_9){
var _c=0;
for(var i=0;i<_8;++i){
_c+=this._viewTables[i]._width;
}
_9=this._viewTables[_8]=["<div class=\"grid_view\" style=\"position: absolute; top: 0; ",dojo._isBodyLtr()?"left":"right",":",_c,"px;\">"];
}
_9._width=_b;
if(_7.isHeader){
_a=dojo.contentBox(_7.view.headerContentNode).h;
}else{
var _d=_7.grid.getRowNode(_7.rowIdx);
if(_d){
_a=dojo.contentBox(_d).h;
}else{
_a=_7.grid.scroller.averageRowHeight;
}
}
_9.push("<table class=\"",this._getRowClass(_7),"\" style=\"table-layout:fixed; height:",_a,"px; width:",_b,"px;\" ","border=\"0\" cellspacing=\"0\" cellpadding=\"0\" ",this._getTableAttrs("table"),"><tbody ",this._getTableAttrs("tbody"),">");
return true;
},afterView:function(_e){
this._viewTables[_e.viewIdx].push("</tbody></table>");
},beforeSubrow:function(_f){
this._viewTables[_f.viewIdx].push("<tr",this._getTableAttrs("tr"),">");
return true;
},afterSubrow:function(_10){
this._viewTables[_10.viewIdx].push("</tr>");
},handleCell:function(_11){
var _12=_11.cell;
if(_12.hidden||dojo.indexOf(_11.spCols,_12.index)>=0){
return;
}
var _13=_11.isHeader?"th":"td",_14=[_12.colSpan?" colspan=\""+_12.colSpan+"\"":"",_12.rowSpan?" rowspan=\""+_12.rowSpan+"\"":""," style=\"width: ",dojo.contentBox(_12.getHeaderNode()).w,"px;\"",this._getTableAttrs(_13)," class=\"",this._getColumnClass(_11),"\""].join(""),_15=this._viewTables[_11.viewIdx];
_15.push("<",_13,_14,">");
if(_11.isHeader){
_15.push(_12.name||_12.field);
}else{
_15.push(this._getExportDataForCell(_11.rowIdx,_11.row,_12,_11.grid));
}
_15.push("</",_13,">");
},afterContent:function(){
dojo.forEach(this._viewTables,function(_16){
_16.push("</div>");
});
},toString:function(){
var _17=dojo.map(this._viewTables,function(_18){
return _18.join("");
}).join("");
return ["<div style=\"position: relative;\">",_17,"</div>"].join("");
}});
}
