/*
	Copyright (c) 2004-2012, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.grid.LazyTreeGrid"]){
dojo._hasResource["dojox.grid.LazyTreeGrid"]=true;
dojo.provide("dojox.grid.LazyTreeGrid");
dojo.require("dojox.grid._View");
dojo.require("dojox.grid.TreeGrid");
dojo.require("dojox.grid.cells.tree");
dojo.require("dojox.grid.LazyTreeGridStoreModel");
dojo.declare("dojox.grid._LazyExpando",[dijit._Widget,dijit._Templated],{itemId:"",cellIdx:-1,view:null,rowIdx:-1,expandoCell:null,level:0,open:false,templateString:"<div class=\"dojoxGridExpando\"\n\t><div class=\"dojoxGridExpandoNode\" dojoAttachEvent=\"onclick:onToggle\"\n\t\t><div class=\"dojoxGridExpandoNodeInner\" dojoAttachPoint=\"expandoInner\"></div\n\t></div\n></div>\n",onToggle:function(_1){
this.setOpen(!this.view.grid.cache.getExpandoStatusByRowIndex(this.rowIdx));
try{
dojo.stopEvent(_1);
}
catch(e){
}
},setOpen:function(_2){
var g=this.view.grid,_3=g.cache.getItemByRowIndex(this.rowIdx);
if(!g.treeModel.mayHaveChildren(_3)){
g.stateChangeNode=null;
return;
}
if(_3&&!g._loading){
g.stateChangeNode=this.domNode;
g.cache.updateCache(this.rowIdx,{"expandoStatus":_2});
g.expandoFetch(this.rowIdx,_2);
this.open=_2;
}
this._updateOpenState(_3);
},_updateOpenState:function(_4){
var _5=this.view.grid;
if(_4&&_5.treeModel.mayHaveChildren(_4)){
var _6=_5.cache.getExpandoStatusByRowIndex(this.rowIdx);
this.expandoInner.innerHTML=_6?"-":"+";
dojo.toggleClass(this.domNode,"dojoxGridExpandoOpened",_6);
dijit.setWaiState(this.domNode.parentNode,"expanded",_6);
}else{
dojo.removeClass(this.domNode,"dojoxGridExpandoOpened");
}
},setRowNode:function(_7,_8,_9){
if(this.cellIdx<0||!this.itemId){
return false;
}
this._initialized=false;
this.view=_9;
this.rowIdx=_7;
this.expandoCell=_9.structure.cells[0][this.cellIdx];
var d=this.domNode;
if(d&&d.parentNode&&d.parentNode.parentNode){
this._tableRow=d.parentNode.parentNode;
}
dojo.style(this.domNode,"marginLeft",(this.level*1.125)+"em");
this._updateOpenState(_9.grid.cache.getItemByRowIndex(this.rowIdx));
return true;
}});
dojo.declare("dojox.grid._TreeGridContentBuilder",dojox.grid._ContentBuilder,{generateHtml:function(_a,_b){
var _c=this.getTableArray(),_d=this.grid,v=this.view,_e=v.structure.cells,_f=_d.getItem(_b),_10=0,_11=_d.cache.getTreePathByRowIndex(_b),_12=[],_13=[];
dojox.grid.util.fire(this.view,"onBeforeRow",[_b,_e]);
if(_f!==null&&_11!==null){
_12=_11.split("/");
_10=_12.length-1;
_13[0]="dojoxGridRowToggle-"+_12.join("-");
if(!_d.treeModel.mayHaveChildren(_f)){
_13.push("dojoxGridNoChildren");
}
}
for(var j=0,row;(row=_e[j]);j++){
if(row.hidden||row.header){
continue;
}
var tr="<tr style=\"\" class=\""+_13.join(" ")+"\" dojoxTreeGridPath=\""+_12.join("/")+"\" dojoxTreeGridBaseClasses=\""+_13.join(" ")+"\">";
_c.push(tr);
var k=0,_14=this._getColSpans(_10);
var _15=0,_16=[];
if(_14){
dojo.forEach(_14,function(c){
for(var i=0,_17;(_17=row[i]);i++){
if(i>=c.start&&i<=c.end){
_15+=this._getCellWidth(row,i);
}
}
_16.push(_15);
_15=0;
},this);
}
for(var i=0,_18,m,cc,cs;(_18=row[i]);i++){
m=_18.markup;
cc=_18.customClasses=[];
cs=_18.customStyles=[];
if(_14&&_14[k]&&(i>=_14[k].start&&i<=_14[k].end)){
var _19=_14[k].primary?_14[k].primary:_14[k].start;
if(i===_19){
m[5]=_18.formatAtLevel(_12,_f,_10,false,_13[0],cc,_b);
m[1]=cc.join(" ");
var pbm=dojo.marginBox(_18.getHeaderNode()).w-dojo.contentBox(_18.getHeaderNode()).w;
cs=_18.customStyles=["width:"+(_16[k]-pbm)+"px"];
m[3]=cs.join(";");
_c.push.apply(_c,m);
}else{
if(i===_14[k].end){
k++;
continue;
}else{
continue;
}
}
}else{
m[5]=_18.formatAtLevel(_12,_f,_10,false,_13[0],cc,_b);
m[1]=cc.join(" ");
m[3]=cs.join(";");
_c.push.apply(_c,m);
}
}
_c.push("</tr>");
}
_c.push("</table>");
return _c.join("");
},_getColSpans:function(_1a){
var _1b=this.grid.colSpans;
if(_1b&&(_1b[_1a])){
return _1b[_1a];
}else{
return null;
}
},_getCellWidth:function(_1c,_1d){
var _1e=_1c[_1d],_1f=_1e.getHeaderNode();
if(_1e.hidden){
return 0;
}
if(_1d==_1c.length-1||dojo.every(_1c.slice(_1d+1),function(_20){
return _20.hidden;
})){
var _21=dojo.position(_1c[_1d].view.headerContentNode.firstChild);
return _21.x+_21.w-dojo.position(_1f).x;
}else{
var _22;
do{
_22=_1c[++_1d];
}while(_22.hidden);
return dojo.position(_22.getHeaderNode()).x-dojo.position(_1f).x;
}
}});
dojo.declare("dojox.grid._TreeGridView",[dojox.grid._View],{_contentBuilderClass:dojox.grid._TreeGridContentBuilder,postCreate:function(){
this.inherited(arguments);
this._expandos={};
this.connect(this.grid,"_cleanupExpandoCache","_cleanupExpandoCache");
},_cleanupExpandoCache:function(_23,_24,_25){
if(_23===-1){
return;
}
dojo.forEach(this.grid.layout.cells,function(_26){
if(_26.openStates&&_26.openStates[_24]){
delete _26.openStates[_24];
}
});
for(var i in this._expandos){
if(this._expandos[i]){
this._expandos[i].destroy();
}
}
this._expandos={};
},onAfterRow:function(_27,_28,_29){
dojo.query("span.dojoxGridExpando",_29).forEach(function(n){
if(n&&n.parentNode){
var _2a,_2b,_2c=this.grid._by_idx;
if(_2c&&_2c[_27]&&_2c[_27].idty){
_2a=_2c[_27].idty;
_2b=this._expandos[_2a];
}
if(_2b){
dojo.place(_2b.domNode,n,"replace");
_2b.itemId=n.getAttribute("itemId");
_2b.cellIdx=parseInt(n.getAttribute("cellIdx"),10);
if(isNaN(_2b.cellIdx)){
_2b.cellIdx=-1;
}
}else{
_2b=dojo.parser.parse(n.parentNode)[0];
if(_2a){
this._expandos[_2a]=_2b;
}
}
if(!_2b.setRowNode(_27,_29,this)){
_2b.domNode.parentNode.removeChild(_2b.domNode);
}
dojo.destroy(n);
}
},this);
this.inherited(arguments);
}});
dojox.grid.cells.LazyTreeCell=dojo.mixin(dojo.clone(dojox.grid.cells.TreeCell),{formatAtLevel:function(_2d,_2e,_2f,_30,_31,_32,_33){
if(!_2e){
return this.formatIndexes(_33,_2d,_2e,_2f);
}
if(!dojo.isArray(_2d)){
_2d=[_2d];
}
var _34="";
var ret="";
if(this.isCollapsable){
var _35=this.grid.store,id="";
if(_2e&&_35.isItem(_2e)){
id=_35.getIdentity(_2e);
}
_32.push("dojoxGridExpandoCell");
ret="<span "+dojo._scopeName+"Type=\"dojox.grid._LazyExpando\" level=\""+_2f+"\" class=\"dojoxGridExpando\""+"\" toggleClass=\""+_31+"\" itemId=\""+id+"\" cellIdx=\""+this.index+"\"></span>";
}
var _36=this.formatIndexes(_33,_2d,_2e,_2f);
_34=ret!==""?"<div>"+ret+_36+"</div>":_36;
if(this.grid.focus.cell&&this.index===this.grid.focus.cell.index&&_2d.join("/")===this.grid.focus.rowIndex){
_32.push(this.grid.focus.focusClass);
}
return _34;
},formatIndexes:function(_37,_38,_39,_3a){
var _3b=this.grid.edit.info,d=this.get?this.get(_38[0],_39,_38):(this.value||this.defaultValue);
if(this.editable&&(this.alwaysEditing||(_3b.rowIndex===_38[0]&&_3b.cell===this))){
return this.formatEditing(d,_37,_38);
}else{
return this._defaultFormat(d,[d,_37,_3a,this]);
}
}});
dojo.declare("dojox.grid._LazyTreeLayout",dojox.grid._Layout,{setStructure:function(_3c){
var s=_3c;
var g=this.grid;
if(g&&!dojo.every(s,function(i){
return ("cells" in i);
})){
s=arguments[0]=[{cells:[s]}];
}
if(s.length===1&&s[0].cells.length===1){
s[0].type="dojox.grid._TreeGridView";
this._isCollapsable=true;
s[0].cells[0][this.grid.expandoCell].isCollapsable=true;
}
this.inherited(arguments);
},addCellDef:function(_3d,_3e,_3f){
var obj=this.inherited(arguments);
return dojo.mixin(obj,dojox.grid.cells.LazyTreeCell);
}});
dojo.declare("dojox.grid.TreeGridItemCache",null,{unInit:true,items:null,constructor:function(_40){
this.rowsPerPage=_40.rowsPerPage;
this._buildCache(_40.rowsPerPage);
},_buildCache:function(_41){
this.items=[];
for(var i=0;i<_41;i++){
this.cacheItem(i,{item:null,treePath:i+"",expandoStatus:false});
}
},cacheItem:function(_42,_43){
this.items[_42]=dojo.mixin({item:null,treePath:"",expandoStatus:false},_43);
},insertItem:function(_44,_45){
this.items.splice(_44,0,dojo.mixin({item:null,treePath:"",expandoStatus:false},_45));
},initCache:function(_46){
if(!this.unInit){
return;
}
this._buildCache(_46);
this.unInit=false;
},getItemByRowIndex:function(_47){
return this.items[_47]?this.items[_47].item:null;
},getItemByTreePath:function(_48){
for(var i=0,len=this.items.length;i<len;i++){
if(this.items[i].treePath===_48){
return this.items[i].item;
}
}
return null;
},getTreePathByRowIndex:function(_49){
return this.items[_49]?this.items[_49].treePath:null;
},getExpandoStatusByRowIndex:function(_4a){
return this.items[_4a]?this.items[_4a].expandoStatus:null;
},getInfoByItem:function(_4b){
for(var i=0,len=this.items.length;i<len;i++){
if(this.items[i].item===_4b){
return dojo.mixin({rowIdx:i},this.items[i]);
}
}
return null;
},updateCache:function(_4c,_4d){
if(this.items[_4c]){
dojo.mixin(this.items[_4c],_4d);
}
},deleteItem:function(_4e){
if(this.items[_4e]){
var _4f=this.items[_4e].treePath,i=_4e,_50,_51=_4f.indexOf("/")>0?_4f.substring(0,_4f.lastIndexOf("/")+1):"";
for(;i<this.items.length;i++){
if(this.items[i].treePath.indexOf(_51+"/")==0){
_50=this.items[i].treePath.substring(_51.length).split("/");
_50[0]=parseInt(_50[0],10)-1;
this.updateCache(i,{treePath:_51+_50.join("/")});
}else{
break;
}
}
this.items.splice(_4e,1);
}
},cleanChildren:function(_52){
var _53=this.getTreePathByRowIndex(_52);
var _54=0,i=this.items.length-1;
for(;i>=0;i--){
if(this.items[i].treePath.indexOf(_53+"/")===0&&this.items[i].treePath!==_53){
this.items.splice(i,1);
_54++;
}
}
return _54;
},emptyCache:function(){
this.unInit=true;
this._buildCache(this.rowsPerPage);
},cleanupCache:function(){
this.items=null;
}});
dojo.declare("dojox.grid.LazyTreeGrid",dojox.grid.TreeGrid,{treeModel:null,_layoutClass:dojox.grid._LazyTreeLayout,colSpans:null,postCreate:function(){
this.inherited(arguments);
this.cache=new dojox.grid.TreeGridItemCache(this);
if(!this.treeModel||!(this.treeModel instanceof dijit.tree.ForestStoreModel)){
throw new Error("dojox.grid.LazyTreeGrid: must use a treeModel and treeModel must be an instance of dijit.tree.ForestStoreModel");
}
dojo.addClass(this.domNode,"dojoxGridTreeModel");
dojo.setSelectable(this.domNode,this.selectable);
},createManagers:function(){
this.rows=new dojox.grid._RowManager(this);
this.focus=new dojox.grid._FocusManager(this);
this.edit=new dojox.grid._EditManager(this);
},createSelection:function(){
this.selection=new dojox.grid.DataSelection(this);
},setModel:function(_55){
if(!_55){
return;
}
this._setModel(_55);
this._cleanup();
this._refresh(true);
},setStore:function(_56,_57,_58){
if(!_56){
return;
}
this._setQuery(_57,_58);
this.treeModel.query=_57;
this.treeModel.store=_56;
this.treeModel.root.children=[];
this.setModel(this.treeModel);
},_setQuery:function(_59,_5a){
this.inherited(arguments);
this.treeModel.query=_59;
},destroy:function(){
this._cleanup();
this.inherited(arguments);
},_cleanup:function(){
this.cache.emptyCache();
this._cleanupExpandoCache();
},setSortIndex:function(_5b,_5c){
if(this.canSort(_5b+1)){
this._cleanup();
}
this.inherited(arguments);
},_refresh:function(_5d){
this._clearData();
this.updateRowCount(this.cache.items.length);
this._fetch(0,true);
},_updateChangedRows:function(_5e){
dojo.forEach(this.scroller.stack,function(p){
if(p*this.rowsPerPage>=_5e){
this.updateRows(p*this.rowsPerPage,this.rowsPerPage);
}else{
if((p+1)*this.rowsPerPage>=_5e){
this.updateRows(_5e,(p+1)*this.rowsPerPage-_5e+1);
}
}
},this);
},render:function(){
this.inherited(arguments);
this.setScrollTop(this.scrollTop);
},_onNew:function(_5f,_60){
var _61=false,_62,_63=this.cache.items;
if(_60&&this.store.isItem(_60.item)&&dojo.some(this.treeModel.childrenAttrs,function(c){
return c===_60.attribute;
})){
_61=true;
_62=this.cache.getInfoByItem(_60.item);
}
if(!_61){
this.inherited(arguments);
var _64=_63.length>0?String(parseInt(_63[_63.length-1].treePath.split("/")[0],10)+1):"0";
this.cache.insertItem(this.get("rowCount"),{item:_5f,treePath:_64,expandoStatus:false});
}else{
if(_62&&_62.expandoStatus&&_62.rowIdx>=0){
var _65=_62.childrenNum;
var _66=_62.treePath+"/"+_65;
var _67={item:_5f,treePath:_66,expandoStatus:false};
var _68=_62.rowIdx+1;
for(;_68<this.cache.items.length;_68++){
if(!this.cache.items[_68]||this.cache.items[_68].treePath.indexOf(_62.treePath+"/")!=0){
break;
}
}
this.cache.insertItem(_68,_67);
this.cache.updateCache(_62.rowIdx,{childrenNum:_65+1});
var _69=this.store.getIdentity(_5f);
this._by_idty[_69]={idty:_69,item:_5f};
this._by_idx.splice(_68,0,this._by_idty[_69]);
this.updateRowCount(_63.length);
this._updateChangedRows(_68);
}else{
if(_62&&_62.rowIdx>=0){
this.updateRow(_62.rowIdx);
}
}
}
},_onDelete:function(_6a){
var _6b=this.cache.getInfoByItem(_6a),i;
if(_6b&&_6b.rowIdx>=0){
if(_6b.expandoStatus){
var num=this.cache.cleanChildren(_6b.rowIdx);
this._by_idx.splice(_6b.rowIdx+1,num);
}
if(_6b.treePath.indexOf("/")>0){
var _6c=_6b.treePath.substring(0,_6b.treePath.lastIndexOf("/"));
for(i=_6b.rowIdx;i>=0;i--){
if(this.cache.items[i].treePath===_6c){
this.cache.items[i].childrenNum--;
break;
}
}
}
this.cache.deleteItem(_6b.rowIdx);
this._by_idx.splice(_6b.rowIdx,1);
this.updateRowCount(this.cache.items.length);
this._updateChangedRows(_6b.rowIdx);
}
},_cleanupExpandoCache:function(_6d,_6e,_6f){
},_fetch:function(_70,_71){
if(!this._loading){
this._loading=true;
}
_70=_70||0;
this.reqQueue=[];
var i=0,_72=[];
var _73=Math.min(this.rowsPerPage,this.cache.items.length-_70);
for(i=_70;i<_70+_73;i++){
if(this.cache.getItemByRowIndex(i)){
_72.push(this.cache.getItemByRowIndex(i));
}else{
break;
}
}
if(_72.length===_73){
this._reqQueueLen=1;
this._onFetchBegin(this.cache.items.length,{startRowIdx:_70,count:_73});
this._onFetchComplete(_72,{startRowIdx:_70,count:_73});
}else{
this.reqQueueIndex=0;
var _74="",_75="",_76=_70,_77=this.cache.getTreePathByRowIndex(_70);
_73=0;
for(i=_70+1;i<_70+this.rowsPerPage;i++){
if(!this.cache.getTreePathByRowIndex(i)){
break;
}
_74=this.cache.getTreePathByRowIndex(i-1).split("/").length-1;
_75=this.cache.getTreePathByRowIndex(i).split("/").length-1;
if(_74!==_75){
this.reqQueue.push({startTreePath:_77,startRowIdx:_76,count:_73+1});
_73=0;
_76=i;
_77=this.cache.getTreePathByRowIndex(i);
}else{
_73++;
}
}
this.reqQueue.push({startTreePath:_77,startRowIdx:_76,count:_73+1});
this._reqQueueLen=this.reqQueue.length;
for(i=0;i<this.reqQueue.length;i++){
this._fetchItems(i,dojo.hitch(this,"_onFetchBegin"),dojo.hitch(this,"_onFetchComplete"),dojo.hitch(this,"_onFetchError"));
}
}
},_fetchItems:function(idx,_78,_79,_7a){
if(this._pending_requests[this.reqQueue[idx].startRowIdx]){
return;
}
this.showMessage(this.loadingMessage);
var _7b=this.reqQueue[idx].startTreePath.split("/").length-1;
this._pending_requests[this.reqQueue[idx].startRowIdx]=true;
if(_7b===0){
this.store.fetch({start:parseInt(this.reqQueue[idx].startTreePath,10),startRowIdx:this.reqQueue[idx].startRowIdx,count:this.reqQueue[idx].count,query:this.query,sort:this.getSortProps(),queryOptions:this.queryOptions,onBegin:_78,onComplete:_79,onError:_7a});
}else{
var _7c=this.reqQueue[idx].startTreePath;
var _7d=_7c.substring(0,_7c.lastIndexOf("/"));
var _7e=_7c.substring(_7c.lastIndexOf("/")+1);
var _7f=this.cache.getItemByTreePath(_7d);
if(!_7f){
throw new Error("Lazy loading TreeGrid on fetch error:");
}
var _80=this.store.getIdentity(_7f);
var _81={start:parseInt(_7e,10),startRowIdx:this.reqQueue[idx].startRowIdx,count:this.reqQueue[idx].count,parentId:_80,sort:this.getSortProps()};
var _82=this;
var _83=function(){
if(arguments.length==1){
_79.apply(_82,[arguments[0],_81]);
}else{
_79.apply(_82,arguments);
}
};
this.treeModel.getChildren(_7f,_83,_7a,_81);
}
},_onFetchBegin:function(_84,_85){
this.cache.initCache(_84);
_84=this.cache.items.length;
this.inherited(arguments);
},filter:function(_86,_87){
this.cache.emptyCache();
this.inherited(arguments);
},_onFetchComplete:function(_88,_89,_8a){
var _8b="",_8c=_89.startRowIdx,_8d=_89.count,_8e=_88.length<=_8d?0:_89.start;
if(_88&&_88.length>0){
for(var i=0;i<_8d;i++){
_8b=this.cache.getTreePathByRowIndex(_8c+i);
if(_8b){
if(!this.cache.getItemByRowIndex(_8c+i)){
this.cache.cacheItem(_8c+i,{item:_88[_8e+i],treePath:_8b,expandoStatus:this.cache.getExpandoStatusByRowIndex(_8c+i)});
}
}
}
if(!this.scroller){
return;
}
var len=Math.min(_8d,_88.length);
for(i=0;i<len;i++){
this._addItem(_88[_8e+i],_8c+i,true);
}
this.updateRows(_8c,len);
}
if(!this.cache.items.length){
this.showMessage(this.noDataMessage);
}else{
this.showMessage();
}
this._pending_requests[_8c]=false;
this._reqQueueLen--;
if(this._loading&&this._reqQueueLen===0){
this._loading=false;
if(this._lastScrollTop){
this.setScrollTop(this._lastScrollTop);
}
}
},expandoFetch:function(_8f,_90){
if(this._loading){
return;
}
this._loading=true;
this.toggleLoadingClass(true);
var _91=this.cache.getItemByRowIndex(_8f);
this.expandoRowIndex=_8f;
this._pages=[];
if(_90){
var _92=this.store.getIdentity(_91);
var _93={start:0,count:this.rowsPerPage,parentId:_92,sort:this.getSortProps()};
this.treeModel.getChildren(_91,dojo.hitch(this,"_onExpandoComplete"),dojo.hitch(this,"_onFetchError"),_93);
}else{
var num=this.cache.cleanChildren(_8f);
this._by_idx.splice(_8f+1,num);
this._bop=this._eop=-1;
this.updateRowCount(this.cache.items.length);
this._updateChangedRows(_8f+1);
this.toggleLoadingClass(false);
if(this._loading){
this._loading=false;
}
this.focus._delayedCellFocus();
}
},_onExpandoComplete:function(_94,_95,_96){
var _97=this.cache.getTreePathByRowIndex(this.expandoRowIndex);
if(_96&&!isNaN(parseInt(_96,10))){
_96=parseInt(_96,10);
}else{
_96=_94.length;
}
var i,j=0,len=this._by_idx.length;
for(i=this.expandoRowIndex+1;j<_96;i++,j++){
this.cache.insertItem(i,{item:null,treePath:_97+"/"+j,expandoStatus:false});
}
this.updateRowCount(this.cache.items.length);
this.cache.updateCache(this.expandoRowIndex,{childrenNum:_96});
for(i=0;i<_96;i++){
this.cache.updateCache(this.expandoRowIndex+1+i,{item:_94[i]});
}
for(i=0;i<_96;i++){
this._by_idx.splice(this.expandoRowIndex+1+i,0,null);
}
for(i=0;i<Math.min(_96,this.rowsPerPage);i++){
var _98=this.store.getIdentity(_94[i]);
this._by_idty[_98]={idty:_98,item:_94[i]};
this._by_idx.splice(this.expandoRowIndex+1+i,1,this._by_idty[_98]);
}
this._updateChangedRows(this.expandoRowIndex+1);
this.toggleLoadingClass(false);
this.stateChangeNode=null;
if(this._loading){
this._loading=false;
}
this.focus._delayedCellFocus();
},toggleLoadingClass:function(_99){
if(this.stateChangeNode){
dojo.toggleClass(this.stateChangeNode,"dojoxGridExpandoLoading",_99);
}
},styleRowNode:function(_9a,_9b){
if(_9b){
this.rows.styleRowNode(_9a,_9b);
}
},onStyleRow:function(row){
if(!this.layout._isCollapsable){
this.inherited(arguments);
return;
}
var _9c=dojo.attr(row.node,"dojoxTreeGridBaseClasses");
if(_9c){
row.customClasses=_9c;
}
var i=row;
i.customClasses+=(i.odd?" dojoxGridRowOdd":"")+(i.selected?" dojoxGridRowSelected":"")+(i.over?" dojoxGridRowOver":"");
this.focus.styleRow(i);
this.edit.styleRow(i);
},dokeydown:function(e){
if(e.altKey||e.metaKey){
return;
}
var dk=dojo.keys,_9d=dijit.findWidgets(e.target)[0];
if(e.keyCode===dk.ENTER&&_9d instanceof dojox.grid._LazyExpando){
_9d.onToggle();
}
this.onKeyDown(e);
}});
dojox.grid.LazyTreeGrid.markupFactory=function(_9e,_9f,_a0,_a1){
return dojox.grid.TreeGrid.markupFactory(_9e,_9f,_a0,_a1);
};
}
