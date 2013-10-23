/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.grid.enhanced.plugins.NestedSorting"]){
dojo._hasResource["dojox.grid.enhanced.plugins.NestedSorting"]=true;
dojo.provide("dojox.grid.enhanced.plugins.NestedSorting");
dojo.require("dojox.grid.enhanced._Plugin");
dojo.declare("dojox.grid.enhanced.plugins.NestedSorting",dojox.grid.enhanced._Plugin,{name:"nestedSorting",_currMainSort:"none",_currRegionIdx:-1,_a11yText:{"dojoxGridDescending":"&#9662;","dojoxGridAscending":"&#9652;","dojoxGridAscendingTip":"&#1784;","dojoxGridDescendingTip":"&#1783;","dojoxGridUnsortedTip":"x"},constructor:function(){
this._sortDef=[];
this._sortData={};
this._headerNodes={};
this._excludedColIdx=[];
this.nls=this.grid._nls;
this.grid.setSortInfo=function(){
};
this.grid.setSortIndex=dojo.hitch(this,"_setGridSortIndex");
this.grid.getSortProps=dojo.hitch(this,"getSortProps");
if(this.grid.sortFields){
this._setGridSortIndex(this.grid.sortFields,null,true);
}
this.connect(this.grid.views,"render","_initSort");
this.initCookieHandler();
if(this.grid.plugin("rearrange")){
this.subscribe("dojox/grid/rearrange/move/"+this.grid.id,dojo.hitch(this,"_onColumnDnD"));
}else{
this.connect(this.grid.layout,"moveColumn","_onMoveColumn");
}
},onStartUp:function(){
this.inherited(arguments);
this.connect(this.grid,"onHeaderCellClick","_onHeaderCellClick");
this.connect(this.grid,"onHeaderCellMouseOver","_onHeaderCellMouseOver");
this.connect(this.grid,"onHeaderCellMouseOut","_onHeaderCellMouseOut");
},_onMoveColumn:function(_1,_2,_3,_4,_5){
var cr=this._getCurrentRegion(),_6=cr&&this._getRegionHeader(cr).getAttribute("idx"),c=this._headerNodes[_6],_7=this._sortData,_8={},_9,_a;
if(cr){
this._blurRegion(cr);
this._currRegionIdx=dojo.indexOf(this._getRegions(),c.firstChild);
}
if(_4<_3){
for(_9 in _7){
_9=parseInt(_9,10);
_a=_7[_9];
if(_a){
if(_9>=_4&&_9<_3){
_8[_9+1]=_a;
}else{
if(_9==_3){
_8[_4]=_a;
}else{
_8[_9]=_a;
}
}
}
}
}else{
if(_4>_3+1){
if(!_5){
_4++;
}
for(_9 in _7){
_9=parseInt(_9,10);
_a=_7[_9];
if(_a){
if(_9>_3&&_9<_4){
_8[_9-1]=_a;
}else{
if(_9==_3){
_8[_4-1]=_a;
}else{
_8[_9]=_a;
}
}
}
}
}
}
this._sortData=_8;
this._initSort(false);
},_onColumnDnD:function(_b,_c){
if(_b!=="col"){
return;
}
var m=_c,_d={},d=this._sortData,p;
var cr=this._getCurrentRegion();
this._blurRegion(cr);
var _e=dojo.attr(this._getRegionHeader(cr),"idx");
for(p in m){
if(d[p]){
_d[m[p]]=d[p];
delete d[p];
}
if(p===_e){
_e=m[p];
}
}
for(p in _d){
d[p]=_d[p];
}
var c=this._headerNodes[_e];
this._currRegionIdx=dojo.indexOf(this._getRegions(),c.firstChild);
this._initSort(false);
},_setGridSortIndex:function(_f,_10,_11){
if(dojo.isArray(_f)){
var i,d,_12;
for(i=0;i<_f.length;i++){
d=_f[i];
_12=this.grid.getCellByField(d.attribute);
if(!_12){
console.warn("Invalid sorting option, column ",d.attribute," not found.");
return;
}
if(_12["nosort"]||!this.grid.canSort(_12.index,_12.field)){
console.warn("Invalid sorting option, column ",d.attribute," is unsortable.");
return;
}
}
this.clearSort();
dojo.forEach(_f,function(d,i){
_12=this.grid.getCellByField(d.attribute);
this.setSortData(_12.index,"index",i);
this.setSortData(_12.index,"order",d.descending?"desc":"asc");
},this);
}else{
if(!isNaN(_f)){
if(_10===undefined){
return;
}
this.setSortData(_f,"order",_10?"asc":"desc");
}else{
return;
}
}
this._updateSortDef();
if(!_11){
this.grid.sort();
}
},getSortProps:function(){
return this._sortDef.length?this._sortDef:null;
},_initSort:function(_13){
var g=this.grid,n=g.domNode,len=this._sortDef.length;
dojo.toggleClass(n,"dojoxGridSorted",!!len);
dojo.toggleClass(n,"dojoxGridSingleSorted",len===1);
dojo.toggleClass(n,"dojoxGridNestSorted",len>1);
if(len>0){
this._currMainSort=this._sortDef[0].descending?"desc":"asc";
}
var idx,_14=this._excludedCoIdx=[];
this._headerNodes=dojo.query("th",g.viewsHeaderNode).forEach(function(n){
idx=parseInt(dojo.attr(n,"idx"),10);
if(dojo.style(n,"display")==="none"||g.layout.cells[idx]["nosort"]||(g.canSort&&!g.canSort(idx,g.layout.cells[idx]["field"]))){
_14.push(idx);
}
});
this._headerNodes.forEach(this._initHeaderNode,this);
this._initFocus();
if(_13){
this._focusHeader();
}
},_initHeaderNode:function(_15){
var _16=dojo.query(".dojoxGridSortNode",_15)[0];
if(_16){
dojo.toggleClass(_16,"dojoxGridSortNoWrap",true);
}
if(dojo.indexOf(this._excludedCoIdx,dojo.attr(_15,"idx"))>=0){
dojo.addClass(_15,"dojoxGridNoSort");
return;
}
if(!dojo.query(".dojoxGridSortBtn",_15).length){
this._connects=dojo.filter(this._connects,function(_17){
if(_17._sort){
dojo.disconnect(_17);
return false;
}
return true;
});
var n=dojo.create("a",{className:"dojoxGridSortBtn dojoxGridSortBtnNested",title:this.nls.nestedSort+" - "+this.nls.ascending,innerHTML:"1"},_15.firstChild,"last");
n.onmousedown=dojo.stopEvent;
n=dojo.create("a",{className:"dojoxGridSortBtn dojoxGridSortBtnSingle",title:this.nls.singleSort+" - "+this.nls.ascending},_15.firstChild,"last");
n.onmousedown=dojo.stopEvent;
}else{
var a1=dojo.query(".dojoxGridSortBtnSingle",_15)[0];
var a2=dojo.query(".dojoxGridSortBtnNested",_15)[0];
a1.className="dojoxGridSortBtn dojoxGridSortBtnSingle";
a2.className="dojoxGridSortBtn dojoxGridSortBtnNested";
a2.innerHTML="1";
dojo.removeClass(_15,"dojoxGridCellShowIndex");
dojo.removeClass(_15.firstChild,"dojoxGridSortNodeSorted");
dojo.removeClass(_15.firstChild,"dojoxGridSortNodeAsc");
dojo.removeClass(_15.firstChild,"dojoxGridSortNodeDesc");
dojo.removeClass(_15.firstChild,"dojoxGridSortNodeMain");
dojo.removeClass(_15.firstChild,"dojoxGridSortNodeSub");
}
this._updateHeaderNodeUI(_15);
},_onHeaderCellClick:function(e){
this._focusRegion(e.target);
if(dojo.hasClass(e.target,"dojoxGridSortBtn")){
this._onSortBtnClick(e);
dojo.stopEvent(e);
this._focusRegion(this._getCurrentRegion());
}
},_onHeaderCellMouseOver:function(e){
if(!e.cell){
return;
}
if(this._sortDef.length>1){
return;
}
if(this._sortData[e.cellIndex]&&this._sortData[e.cellIndex].index===0){
return;
}
var p;
for(p in this._sortData){
if(this._sortData[p]&&this._sortData[p].index===0){
dojo.addClass(this._headerNodes[p],"dojoxGridCellShowIndex");
break;
}
}
if(!dojo.hasClass(dojo.body(),"dijit_a11y")){
return;
}
var i=e.cell.index,_18=e.cellNode;
var _19=dojo.query(".dojoxGridSortBtnSingle",_18)[0];
var _1a=dojo.query(".dojoxGridSortBtnNested",_18)[0];
var _1b="none";
if(dojo.hasClass(this.grid.domNode,"dojoxGridSingleSorted")){
_1b="single";
}else{
if(dojo.hasClass(this.grid.domNode,"dojoxGridNestSorted")){
_1b="nested";
}
}
var _1c=dojo.attr(_1a,"orderIndex");
if(_1c===null||_1c===undefined){
dojo.attr(_1a,"orderIndex",_1a.innerHTML);
_1c=_1a.innerHTML;
}
if(this.isAsc(i)){
_1a.innerHTML=_1c+this._a11yText.dojoxGridDescending;
}else{
if(this.isDesc(i)){
_1a.innerHTML=_1c+this._a11yText.dojoxGridUnsortedTip;
}else{
_1a.innerHTML=_1c+this._a11yText.dojoxGridAscending;
}
}
if(this._currMainSort==="none"){
_19.innerHTML=this._a11yText.dojoxGridAscending;
}else{
if(this._currMainSort==="asc"){
_19.innerHTML=this._a11yText.dojoxGridDescending;
}else{
if(this._currMainSort==="desc"){
_19.innerHTML=this._a11yText.dojoxGridUnsortedTip;
}
}
}
},_onHeaderCellMouseOut:function(e){
var p;
for(p in this._sortData){
if(this._sortData[p]&&this._sortData[p].index===0){
dojo.removeClass(this._headerNodes[p],"dojoxGridCellShowIndex");
break;
}
}
},_onSortBtnClick:function(e){
var _1d=e.cell.index;
if(dojo.hasClass(e.target,"dojoxGridSortBtnSingle")){
this._prepareSingleSort(_1d);
}else{
if(dojo.hasClass(e.target,"dojoxGridSortBtnNested")){
this._prepareNestedSort(_1d);
}else{
return;
}
}
dojo.stopEvent(e);
this._doSort(_1d);
},_doSort:function(_1e){
if(!this._sortData[_1e]||!this._sortData[_1e].order){
this.setSortData(_1e,"order","asc");
}else{
if(this.isAsc(_1e)){
this.setSortData(_1e,"order","desc");
}else{
if(this.isDesc(_1e)){
this.removeSortData(_1e);
}
}
}
this._updateSortDef();
this.grid.sort();
this._initSort(true);
},setSortData:function(_1f,_20,_21){
var sd=this._sortData[_1f];
if(!sd){
sd=this._sortData[_1f]={};
}
sd[_20]=_21;
},removeSortData:function(_22){
var d=this._sortData,i=d[_22].index,p;
delete d[_22];
for(p in d){
if(d[p].index>i){
d[p].index--;
}
}
},_prepareSingleSort:function(_23){
var d=this._sortData,p;
for(p in d){
delete d[p];
}
this.setSortData(_23,"index",0);
this.setSortData(_23,"order",this._currMainSort==="none"?null:this._currMainSort);
if(!this._sortData[_23]||!this._sortData[_23].order){
this._currMainSort="asc";
}else{
if(this.isAsc(_23)){
this._currMainSort="desc";
}else{
if(this.isDesc(_23)){
this._currMainSort="none";
}
}
}
},_prepareNestedSort:function(_24){
var i=this._sortData[_24]?this._sortData[_24].index:null;
if(i===0||!!i){
return;
}
this.setSortData(_24,"index",this._sortDef.length);
},_updateSortDef:function(){
this._sortDef.length=0;
var d=this._sortData,p;
for(p in d){
this._sortDef[d[p].index]={attribute:this.grid.layout.cells[p].field,descending:d[p].order==="desc"};
}
},_updateHeaderNodeUI:function(_25){
var _26=this._getCellByNode(_25);
var _27=_26.index;
var _28=this._sortData[_27];
var _29=dojo.query(".dojoxGridSortNode",_25)[0];
var _2a=dojo.query(".dojoxGridSortBtnSingle",_25)[0];
var _2b=dojo.query(".dojoxGridSortBtnNested",_25)[0];
dojo.toggleClass(_2a,"dojoxGridSortBtnAsc",this._currMainSort==="asc");
dojo.toggleClass(_2a,"dojoxGridSortBtnDesc",this._currMainSort==="desc");
if(this._currMainSort==="asc"){
_2a.title=this.nls.singleSort+" - "+this.nls.descending;
}else{
if(this._currMainSort==="desc"){
_2a.title=this.nls.singleSort+" - "+this.nls.unsorted;
}else{
_2a.title=this.nls.singleSort+" - "+this.nls.ascending;
}
}
var _2c=this;
function _2d(){
var _2e="Column "+(_26.index+1)+" "+_26.field;
var _2f="none";
var _30="ascending";
if(_28){
_2f=_28.order==="asc"?"ascending":"descending";
_30=_28.order==="asc"?"descending":"none";
}
var _31=_2e+" - is sorted by "+_2f;
var _32=_2e+" - is nested sorted by "+_2f;
var _33=_2e+" - choose to sort by "+_30;
var _34=_2e+" - choose to nested sort by "+_30;
dijit.setWaiState(_2a,"label",_31);
dijit.setWaiState(_2b,"label",_32);
var _35=[_2c.connect(_2a,"onmouseover",function(){
dijit.setWaiState(_2a,"label",_33);
}),_2c.connect(_2a,"onmouseout",function(){
dijit.setWaiState(_2a,"label",_31);
}),_2c.connect(_2b,"onmouseover",function(){
dijit.setWaiState(_2b,"label",_34);
}),_2c.connect(_2b,"onmouseout",function(){
dijit.setWaiState(_2b,"label",_32);
})];
dojo.forEach(_35,function(_36){
_36._sort=true;
});
};
_2d();
var _37=dojo.hasClass(dojo.body(),"dijit_a11y");
if(!_28){
_2b.innerHTML=this._sortDef.length+1;
return;
}
if(_28.index||(_28.index===0&&this._sortDef.length>1)){
_2b.innerHTML=_28.index+1;
}
dojo.addClass(_29,"dojoxGridSortNodeSorted");
if(this.isAsc(_27)){
dojo.addClass(_29,"dojoxGridSortNodeAsc");
_2b.title=this.nls.nestedSort+" - "+this.nls.descending;
if(_37){
_29.innerHTML=this._a11yText.dojoxGridAscendingTip;
}
}else{
if(this.isDesc(_27)){
dojo.addClass(_29,"dojoxGridSortNodeDesc");
_2b.title=this.nls.nestedSort+" - "+this.nls.unsorted;
if(_37){
_29.innerHTML=this._a11yText.dojoxGridDescendingTip;
}
}
}
dojo.addClass(_29,(_28.index===0?"dojoxGridSortNodeMain":"dojoxGridSortNodeSub"));
},isAsc:function(_38){
return this._sortData[_38].order==="asc";
},isDesc:function(_39){
return this._sortData[_39].order==="desc";
},_getCellByNode:function(_3a){
var i;
for(i=0;i<this._headerNodes.length;i++){
if(this._headerNodes[i]===_3a){
return this.grid.layout.cells[i];
}
}
return null;
},clearSort:function(){
this._sortData={};
this._sortDef.length=0;
},initCookieHandler:function(){
if(this.grid.addCookieHandler){
this.grid.addCookieHandler({name:"sortOrder",onLoad:dojo.hitch(this,"_loadNestedSortingProps"),onSave:dojo.hitch(this,"_saveNestedSortingProps")});
}
},_loadNestedSortingProps:function(_3b,_3c){
this._setGridSortIndex(_3b);
},_saveNestedSortingProps:function(_3d){
return this.getSortProps();
},_initFocus:function(){
var f=this.focus=this.grid.focus;
this._focusRegions=this._getRegions();
if(!this._headerArea){
var _3e=this._headerArea=f.getArea("header");
_3e.onFocus=f.focusHeader=dojo.hitch(this,"_focusHeader");
_3e.onBlur=f.blurHeader=f._blurHeader=dojo.hitch(this,"_blurHeader");
_3e.onMove=dojo.hitch(this,"_onMove");
_3e.onKeyDown=dojo.hitch(this,"_onKeyDown");
_3e._regions=[];
_3e.getRegions=null;
this.connect(this.grid,"onBlur","_blurHeader");
}
},_focusHeader:function(evt){
if(this._currRegionIdx===-1){
this._onMove(0,1,null);
}else{
this._focusRegion(this._getCurrentRegion());
}
try{
dojo.stopEvent(evt);
}
catch(e){
}
return true;
},_blurHeader:function(evt){
this._blurRegion(this._getCurrentRegion());
return true;
},_onMove:function(_3f,_40,evt){
var _41=this._currRegionIdx||0,_42=this._focusRegions;
var _43=_42[_41+_40];
if(!_43){
return;
}else{
if(dojo.style(_43,"display")==="none"||dojo.style(_43,"visibility")==="hidden"){
this._onMove(_3f,_40+(_40>0?1:-1),evt);
return;
}
}
this._focusRegion(_43);
var _44=this._getRegionView(_43);
_44.scrollboxNode.scrollLeft=_44.headerNode.scrollLeft;
},_onKeyDown:function(e,_45){
if(_45){
switch(e.keyCode){
case dojo.keys.ENTER:
case dojo.keys.SPACE:
if(dojo.hasClass(e.target,"dojoxGridSortBtnSingle")||dojo.hasClass(e.target,"dojoxGridSortBtnNested")){
this._onSortBtnClick(e);
}
}
}
},_getRegionView:function(_46){
var _47=_46;
while(_47&&!dojo.hasClass(_47,"dojoxGridHeader")){
_47=_47.parentNode;
}
if(_47){
return dojo.filter(this.grid.views.views,function(_48){
return _48.headerNode===_47;
})[0]||null;
}
return null;
},_getRegions:function(){
var _49=[],_4a=this.grid.layout.cells;
this._headerNodes.forEach(function(n,i){
if(dojo.style(n,"display")==="none"){
return;
}
if(_4a[i]["isRowSelector"]){
_49.push(n);
return;
}
dojo.query(".dojoxGridSortNode,.dojoxGridSortBtnNested,.dojoxGridSortBtnSingle",n).forEach(function(_4b){
dojo.attr(_4b,"tabindex",0);
_49.push(_4b);
});
},this);
return _49;
},_focusRegion:function(_4c){
if(!_4c){
return;
}
var _4d=this._getCurrentRegion();
if(_4d&&_4c!==_4d){
this._blurRegion(_4d);
}
var _4e=this._getRegionHeader(_4c);
dojo.addClass(_4e,"dojoxGridCellSortFocus");
if(dojo.hasClass(_4c,"dojoxGridSortNode")){
dojo.addClass(_4c,"dojoxGridSortNodeFocus");
}else{
if(dojo.hasClass(_4c,"dojoxGridSortBtn")){
dojo.addClass(_4c,"dojoxGridSortBtnFocus");
}
}
try{
_4c.focus();
}
catch(e){
}
this.focus.currentArea("header");
this._currRegionIdx=dojo.indexOf(this._focusRegions,_4c);
},_blurRegion:function(_4f){
if(!_4f){
return;
}
var _50=this._getRegionHeader(_4f);
dojo.removeClass(_50,"dojoxGridCellSortFocus");
if(dojo.hasClass(_4f,"dojoxGridSortNode")){
dojo.removeClass(_4f,"dojoxGridSortNodeFocus");
}else{
if(dojo.hasClass(_4f,"dojoxGridSortBtn")){
dojo.removeClass(_4f,"dojoxGridSortBtnFocus");
}
}
_4f.blur();
},_getCurrentRegion:function(){
return this._focusRegions[this._currRegionIdx];
},_getRegionHeader:function(_51){
while(_51&&!dojo.hasClass(_51,"dojoxGridCell")){
_51=_51.parentNode;
}
return _51;
},destroy:function(){
this._sortDef=this._sortData=null;
this._headerNodes=this._focusRegions=null;
this.inherited(arguments);
}});
dojox.grid.EnhancedGrid.registerPlugin(dojox.grid.enhanced.plugins.NestedSorting);
}
