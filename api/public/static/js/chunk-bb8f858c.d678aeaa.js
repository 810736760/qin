(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-bb8f858c"],{"6ad6":function(e,t,i){var a,n,r;(function(i,o){n=[],a=o,r="function"===typeof a?a.apply(t,n):a,void 0===r||(e.exports=r)})(0,(function(){"use strict";var e=["|","^"],t=[",",";","\t","|","^"],i=["\r\n","\r","\n"];function a(e){var t=typeof e;return"function"===t||"object"===t&&!!e}var n=Array.isArray||function(e){return"[object Array]"===toString.call(e)};function r(e){return"string"===typeof e}function o(e){return!isNaN(Number(e))}function s(e){return 0==e||1==e}function l(e){return null==e}function c(e){return null!=e}function h(e,t){return c(e)?e:t}function u(e,t){for(var i=0,a=e.length;i<a;i+=1)if(!1===t(e[i],i))break}function d(e){return e.replace(/"/g,'\\"')}function m(e){return"attrs["+e+"]"}function p(e,t){return o(e)?"Number("+m(t)+")":s(e)?"Boolean("+m(t)+" == true)":"String("+m(t)+")"}function f(e,t,i,a){var o=[];return 3==arguments.length?(t?n(t)?u(i,(function(i,a){r(t[a])?t[a]=t[a].toLowerCase():e[t[a]]=t[a],o.push("deserialize[cast["+a+"]]("+m(a)+")")})):u(i,(function(e,t){o.push(p(e,t))})):u(i,(function(e,t){o.push(m(t))})),o="return ["+o.join(",")+"]"):(t?n(t)?u(i,(function(i,n){r(t[n])?t[n]=t[n].toLowerCase():e[t[n]]=t[n],o.push('"'+d(a[n])+'": deserialize[cast['+n+"]]("+m(n)+")")})):u(i,(function(e,t){o.push('"'+d(a[t])+'": '+p(e,t))})):u(i,(function(e,t){o.push('"'+d(a[t])+'": '+m(t))})),o="return {"+o.join(",")+"}"),new Function("attrs","deserialize","cast",o)}function g(t,i){var a,n=0;return u(i,(function(i){var r,o=i;-1!=e.indexOf(i)&&(o="\\"+o),r=t.match(new RegExp(o,"g")),r&&r.length>n&&(n=r.length,a=i)})),a||i[0]}var b=function(){function e(e,a){if(a||(a={}),n(e))this.mode="encode";else{if(!r(e))throw new Error("Incompatible format!");this.mode="parse"}this.data=e,this.options={header:h(a.header,!1),cast:h(a.cast,!0)};var o=a.lineDelimiter||a.line,l=a.cellDelimiter||a.delimiter;this.isParser()?(this.options.lineDelimiter=o||g(this.data,i),this.options.cellDelimiter=l||g(this.data,t),this.data=s(this.data,this.options.lineDelimiter)):this.isEncoder()&&(this.options.lineDelimiter=o||"\r\n",this.options.cellDelimiter=l||",")}function o(e,t,i,a,n){e(new t(i,a,n))}function s(e,t){return e.slice(-t.length)!=t&&(e+=t),e}function c(e){return n(e)?"array":a(e)?"object":r(e)?"string":l(e)?"null":"primitive"}return e.prototype.set=function(e,t){return this.options[e]=t},e.prototype.isParser=function(){return"parse"==this.mode},e.prototype.isEncoder=function(){return"encode"==this.mode},e.prototype.parse=function(e){if("parse"==this.mode){if(0===this.data.trim().length)return[];var t,i,a,r=this.data,s=this.options,l=s.header,c={cell:"",line:[]},h=this.deserialize;e||(a=[],e=function(e){a.push(e)}),1==s.lineDelimiter.length&&(x=w);var u,d,m,p=r.length,g=s.cellDelimiter.charCodeAt(0),b=s.lineDelimiter.charCodeAt(s.lineDelimiter.length-1);for(_(),u=0,d=0;u<p;u++)m=r.charCodeAt(u),t.cell&&(t.cell=!1,34==m)?t.escaped=!0:t.escaped&&34==m?t.quote=!t.quote:(t.escaped&&t.quote||!t.escaped)&&(m==g?(w(c.cell+r.slice(d,u)),d=u+1):m==b&&(x(c.cell+r.slice(d,u)),d=u+1,(c.line.length>1||""!==c.line[0])&&D(),v()));return a||this}function _(){t={escaped:!1,quote:!1,cell:!0}}function y(){c.cell=""}function v(){c.line=[]}function w(e){c.line.push(t.escaped?e.slice(1,-1).replace(/""/g,'"'):e),y(),_()}function x(e){w(e.slice(0,1-s.lineDelimiter.length))}function D(){l?n(l)?(i=f(h,s.cast,c.line,l),D=function(){o(e,i,c.line,h,s.cast)},D()):l=c.line:(i||(i=f(h,s.cast,c.line)),D=function(){o(e,i,c.line,h,s.cast)},D())}},e.prototype.deserialize={string:function(e){return String(e)},number:function(e){return Number(e)},boolean:function(e){return Boolean(e)}},e.prototype.serialize={object:function(e){var t=this,i=Object.keys(e),a=Array(i.length);return u(i,(function(i,n){a[n]=t[c(e[i])](e[i])})),a},array:function(e){var t=this,i=Array(e.length);return u(e,(function(e,a){i[a]=t[c(e)](e)})),i},string:function(e){return'"'+String(e).replace(/"/g,'""')+'"'},null:function(e){return""},primitive:function(e){return e}},e.prototype.encode=function(e){if("encode"==this.mode){if(0==this.data.length)return"";var t,i,a=this.data,o=this.options,s=o.header,l=a[0],h=this.serialize,d=0;e||(i=Array(a.length),e=function(e,t){i[t+d]=e}),s&&(n(s)||(t=Object.keys(l),s=t),e(f(h.array(s)),0),d=1);var m,p=c(l);return"array"==p?(n(o.cast)?(m=Array(o.cast.length),u(o.cast,(function(e,t){r(e)?m[t]=e.toLowerCase():(m[t]=e,h[e]=e)}))):(m=Array(l.length),u(l,(function(e,t){m[t]=c(e)}))),u(a,(function(t,i){var a=Array(m.length);u(t,(function(e,t){a[t]=h[m[t]](e)})),e(f(a),i)}))):"object"==p&&(t=Object.keys(l),n(o.cast)?(m=Array(o.cast.length),u(o.cast,(function(e,t){r(e)?m[t]=e.toLowerCase():(m[t]=e,h[e]=e)}))):(m=Array(t.length),u(t,(function(e,t){m[t]=c(l[e])}))),u(a,(function(i,a){var n=Array(t.length);u(t,(function(e,t){n[t]=h[m[t]](i[e])})),e(f(n),a)}))),i?i.join(o.lineDelimiter):this}function f(e){return e.join(o.cellDelimiter)}},e.prototype.forEach=function(e){return this[this.mode](e)},e}();return b.parse=function(e,t){return new b(e,t).parse()},b.encode=function(e,t){return new b(e,t).encode()},b.forEach=function(e,t,i){return 2==arguments.length&&(i=t),new b(e,t).forEach(i)},b}))},a16c:function(e,t,i){"use strict";i.r(t);var a=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"app-container"},[i("el-upload",{directives:[{name:"show",rawName:"v-show",value:!1,expression:"false"}],attrs:{"before-upload":e.beforeUpload,action:e.upload_url,"on-progress":e.onProgressUpload,"on-success":e.onSuccess,"on-error":e.onFailed,"show-file-list":!1}},[i("el-button",{ref:"upload",attrs:{size:"small",type:"primary"}},[e._v("点击上传")])],1),i("el-row",{staticStyle:{"padding-right":"20px","margin-bottom":"20px","text-align":"right"}},[i("search-bar",{ref:"aBar",attrs:{"form-config":e.searchConfig,"loading-form":e.searchLoading,value:e.searchForm}})],1),e.tableData.length?i("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.waiting,expression:"waiting"}],attrs:{data:e.tableData,border:""}},[e._l(e.itemList,(function(t,a){return[e.seeInDialog&&t.hide?e._e():i("el-table-column",{key:a,attrs:{label:t.label,prop:t.prop,"show-overflow-tooltip":"",width:e.itemWidth(t.prop),fixed:Boolean(t.fixed)},scopedSlots:e._u([{key:"default",fn:function(a){return["date_index"===t.prop?i("div",[e._v(" "+e._s(e.dateIndexMap[a.row[t.prop]])+" ")]):"time"===t.prop?i("div",[e.tool.empty(a.row["start_time"])?i("span",[e._v("未配置")]):i("span",[e._v(" "+e._s(e.tool.fmt_hms(a.row["start_time"]))+"-"+e._s(e.tool.fmt_hms(a.row["end_time"]))+" ")])]):i("span",{domProps:{innerHTML:e._s(a.row[t.prop])}})]}}],null,!0)})]})),i("el-table-column",{attrs:{width:e.seeInDialog?80:148,fixed:"right",label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[i("el-button",{attrs:{type:"primary",size:"small"},on:{click:function(i){return e.handleEdit(t.row,t.$index)}}},[e._v("编辑 ")]),e.seeInDialog?e._e():i("el-button",{attrs:{type:"danger",size:"small"},on:{click:function(i){return e.handleDelete(t.row)}}},[e._v("删除 ")])]}}],null,!1,2175340501)})],2):e._e(),e.tableData.length?i("el-pagination",{attrs:{"current-page":e.curPage,"page-size":1,total:e.totalPage,"page-sizes":e.def.pageSizes,layout:"total,sizes,prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.curPage=t},"update:current-page":function(t){e.curPage=t},"size-change":e.handleSizeChange,"current-change":e.handleCurrentChange}}):e._e(),i("el-dialog",{attrs:{title:e.dialogTitle,visible:e.dialogFormVisible,"close-on-click-modal":!1,"close-on-press-escape":"","append-to-body":""},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[i("div",{staticClass:"search-bar"},[i("search-bar",{ref:"sBar",attrs:{"loading-form":e.loadingForm,"form-config":e.formConfig,value:e.form}})],1)])],1)},n=[],r=i("c7eb"),o=i("1da1"),s=i("5530"),l=(i("a9e3"),i("b0c0"),i("a15b"),i("e9c4"),i("2f62")),c=i("f8b4"),h=i.n(c),u={components:{},props:{searchConfigProps:{type:Object,default:function(){return{}}},searchFormProps:{type:Object,default:function(){return{}}},seeInDialog:{type:Number,default:0}},data:function(){return{tableData:[],autoWidth:!0,bookType:"xlsx",termData:[],teacherData:[],schoolData:[],searchLoading:!1,waiting:!1,curPage:1,curSize:20,totalPage:1,mapKey:[0,1],searchConfig:{formItemList:[[{type:"select",prop:"tid",name:"tid",label:"学期",width:4,clearable:0,optList:[]},{type:"select",prop:"school",name:"school",label:"学校",multiple:1,placeholder:"全部学校",width:4,optList:[]},{type:"select",prop:"teacher",name:"teacher",label:"老师",placeholder:"全部老师",multiple:1,width:4,optList:this.def.linkTypeList},{type:"select",prop:"index",name:"index",label:"周数",placeholder:"整周",multiple:1,width:4,optList:this.def.weekOnList}]],operate:[{type:"primary",icon:"el-icon-plus",name:"新增",handleClick:this.handleAdd},{type:"primary",icon:"el-icon-upload2",name:"导入Excel",handleClick:this.importExcel},{type:"primary",icon:"el-icon-download",name:"导出Excel",handleClick:this.exportExcel},{type:"primary",icon:"el-icon-document-copy",name:"复制链接",loading:!1,handleClick:this.copy},{type:"primary",icon:"el-icon-search",name:"查询",handleClick:this.getTable}]},searchForm:{tid:0,school:[],teacher:[],index:[],tel:"",teacher_name:""},dialogTitle:"新增链接",dialogFormVisible:!1,loadingForm:!1,formConfig:{formItemList:[[{type:"text",prop:"school_name",name:"school_name",label:"学校",width:24},{type:"text",prop:"class_name",name:"class_name",label:"课程",width:24},{type:"select",name:"date_index",prop:"date_index",label:"周几",width:24,optList:this.def.weekOnList},{type:"text",prop:"teacher_name",name:"teacher_name",unshow:this.seeInDialog,label:"教师名称",width:24},{type:"text",prop:"tel",name:"tel",unshow:this.seeInDialog,label:"手机号",width:24},{type:"text",prop:"price",name:"price",label:"单价",width:24},{type:"time-select",prop:"start_time_f",name:"start_time_f",label:"开始时间",width:24,options:{minTime:"11:59",maxTime:"18:01",step:"00:05",start:"12:00",end:"18:00"}},{type:"time-select",prop:"end_time_f",name:"end_time_f",label:"结束时间",width:24,options:{minTime:"11:59",maxTime:"18:01",step:"00:05",start:"12:30",end:"18:00"}},{type:"text",prop:"class_locate",name:"class_locate",label:"上课教室",width:24}]],operate:[{type:"primary",icon:"el-icon-phone-outline",name:"提交",handleClick:this.addEdit}]},form:{id:0,school_name:"",class_name:"",date_index:"",teacher_name:"",tel:"",price:"",start_time:"",end_time:"",class_locate:"",start_time_f:"16:00",end_time_f:"17:00"},choseIndex:0,key:"",base_url:"http://114.55.124.103:9527/api/class_teacher_import_excel",upload_url:""}},computed:Object(s["a"])(Object(s["a"])({},Object(l["b"])(["tid","device"])),{},{baseForm:function(){return{id:0,school_name:"",class_name:"",date_index:"",teacher_name:"",tel:"",price:"",start_time:"",end_time:"",class_locate:"",start_time_f:"16:00",end_time_f:"17:00"}},schoolList:function(){var e=[];for(var t in this.schoolData)e.push({label:this.schoolData[t],value:this.schoolData[t]});return e},teacherList:function(){var e=[];for(var t in this.teacherData)e.push({label:this.teacherData[t],value:this.teacherData[t]});return e},termList:function(){var e=[];for(var t in this.termData)e.push({label:this.termData[t].name,value:this.termData[t].id});return e},termMap:function(){return this.tool.arrayColumn(this.termData,"name","id")},shareMap:function(){return this.tool.arrayColumn(this.termData,"key","id")},itemList:function(){return[{label:"学校",prop:"school_name"},{label:"课程",prop:"class_name"},{label:"周几",prop:"date_index"},{label:"老师",prop:"teacher_name",hide:!0},{label:"手机号",prop:"tel",hide:!0},{label:"单价",prop:"price"},{label:"上课时间",prop:"time"},{label:"上课教室",prop:"class_locate"}]},dateIndexMap:function(){return this.tool.arrayColumn(this.def.weekOnList,"label","value")}}),watch:{},created:function(){this.key=this.$route.params.key,this.searchConfig=this.tool.empty(this.searchConfigProps)?this.searchConfig:this.searchConfigProps,this.searchForm=this.tool.empty(this.searchFormProps)?this.searchForm:this.searchFormProps,this.seeInDialog||(this.getConf(),this.getTable())},mounted:function(){},methods:{getTable:function(){var e=this,t=this;this.waiting=!0,this.checkTid();var i="class_teacher_list",a={};return this.seeInDialog?(i="tc/class_teacher_list/"+this.key,a={tel:t.searchForm.tel,teacher_name:t.searchForm.teacher_name}):a={tid:t.searchForm.tid,school:t.searchForm.school.join(),teacher:t.searchForm.teacher.join(),index:t.searchForm.index.join(),page_size:t.curSize,page:t.curPage},t.request(i,a,!0).then((function(i){var a=i.data;t.tableData=a.data,t.tableData.length||e.$message.warning("无匹配信息"),t.curPage=parseInt(a.current_page),t.totalPage=a.total||0,t.waiting=!1}))},getConf:function(){var e=this;this.searchLoading=!0,this.checkTid();var t="class_teacher_conf",i={tid:e.searchForm.tid};e.request(t,i,!0).then((function(t){var i=t.data;e.termData=i.terms,e.teacherData=i.teacher,e.schoolData=i.school,e.searchLoading=!1,e.searchConfig.formItemList[0][0].optList=e.termList,e.searchConfig.formItemList[0][1].optList=e.schoolList,e.searchConfig.formItemList[0][2].optList=e.teacherList})).catch((function(){e.searchLoading=!1}))},checkTid:function(){this.tool.empty(this.searchForm.tid)&&(this.searchForm.tid=parseInt(this.tid))},handleCurrentChange:function(e){this.curPage=e,this.getTable()},handleSizeChange:function(e){this.curSize=e,this.getTable()},handleEdit:function(e,t){this.choseIndex=t,e.start_time_f=this.tool.fmt_hms(e.start_time),e.end_time_f=this.tool.fmt_hms(e.end_time),this.dialogTitle="编辑"+e.teacher_name+"老师"+this.dateIndexMap[e.date_index]+"的"+e.class_name,this.form=JSON.parse(JSON.stringify(e)),this.seeInDialog&&(this.formConfig.formItemList[0][0].disabled=1,this.formConfig.formItemList[0][1].disabled=1,this.formConfig.formItemList[0][2].disabled=1,this.formConfig.formItemList[0][3].disabled=1,this.formConfig.formItemList[0][4].disabled=1,this.formConfig.formItemList[0][5].disabled=1),this.dialogFormVisible=!0},handleAdd:function(){this.dialogTitle="新增信息",this.dialogFormVisible=!0,this.form=JSON.parse(JSON.stringify(this.baseForm)),this.form.tid=this.searchForm.tid,this.choseIndex=-1},handleDelete:function(e){var t=this,i=this,a="class_teacher_add_del";i.request(a,{id:e.id,from:this.mapKey[this.seeInDialog]},!0).then((function(e){t.getTable()}))},copy:function(){this.clipboard(window.location.origin+"/tc/"+this.shareMap[this.searchForm.tid]),this.$message.success("复制成功")},clipboard:function(e){var t=document.createElement("input");t.setAttribute("value",e),document.body.appendChild(t),t.select(),document.execCommand("Copy"),document.body.removeChild(t)},addEdit:function(){var e=this;this.form.start_time=this.tool.getSecondsByTimeStr(this.form.start_time_f),this.form.end_time=this.tool.getSecondsByTimeStr(this.form.end_time_f);var t=this;this.loadingForm=!0;var i="class_teacher_add_edit",a=JSON.parse(JSON.stringify(this.form));a.from=this.mapKey[this.seeInDialog],this.seeInDialog&&(i="tc/class_teacher_add_edit/"+this.key,delete a["tid"]),t.request(i,a,!0).then((function(i){t.$message.success((-1===e.choseIndex?"新增":"编辑")+"成功"),t.loadingForm=!1,-1===t.choseIndex?t.tableData.unshift(t.form):t.$set(t.tableData,t.choseIndex,JSON.parse(JSON.stringify(t.form))),t.dialogFormVisible=!1})).catch((function(){t.loadingForm=!1}))},itemWidth:function(e){if("desktop"===this.device)return"";var t="";switch(e){case"school_name":t=185;break;case"class_name":t=120;break;default:break}return t},importExcel:function(){this.upload_url=this.base_url+"?tid="+this.searchForm.tid,this.$refs.upload.$el.click()},exportExcel:function(){var e=this;return Object(o["a"])(Object(r["a"])().mark((function t(){var i,a,n,o;return Object(r["a"])().wrap((function(t){while(1)switch(t.prev=t.next){case 0:if(!(e.totalPage>e.curSize)){t.next=5;break}return e.page=1,e.curSize=e.totalPage,t.next=5,e.getTable();case 5:i=["学校","课程","周几","老师","手机号","单价","上课时间","上课教室"],a=["school_name","class_name","date_index","teacher_name","tel","price","time","class_locate"],n=e.tableData,o=e.formatJson(a,n),o.unshift(i),h.a.downloadCsv(o,{},e.termMap[e.searchForm.tid]+".xlsx");case 11:case"end":return t.stop()}}),t)})))()},formatJson:function(e,t){var i=[];for(var a in t){var n=[];for(var r in e){var o="";switch(e[r]){case"date_index":o=this.dateIndexMap[t[a][e[r]]];break;case"time":o=this.tool.fmt_hms(t[a]["start_time"])+"-"+this.tool.fmt_hms(t[a]["end_time"]);break;default:o=t[a][e[r]];break}n.push(o)}i.push(n)}return i},beforeUpload:function(e){var t=e.name.substring(e.name.lastIndexOf(".")+1),i="xlsx"===t;return i||this.$message({message:"上传文件只能是.xlsx!",type:"warning"}),i},onProgressUpload:function(){this.searchLoading=!0},onSuccess:function(){this.searchLoading=!1,this.$message.success("上传成功"),this.getTable()},onFailed:function(){this.searchLoading=!1,this.$message.error("上传失败")}}},d=u,m=i("2877"),p=Object(m["a"])(d,a,n,!1,null,"2fb4eba6",null);t["default"]=p.exports},f8b4:function(e,t,i){var a,n,r;(function(o,s){n=[e,t,i("6ad6")],a=s,r="function"===typeof a?a.apply(t,n):a,void 0===r||(e.exports=r)})(0,(function(e,t,i){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=n(i);function n(e){return e&&e.__esModule?e:{default:e}}function r(e,t){var i=new a.default(e,t).encode(),n=new Blob(["\ufeff"+i],{type:"text/plain;charset=utf-8"});return window.URL.createObjectURL(n)}function o(e,t,i){var a=r(e,t),n=document.createElement("a");n.href=a,n.download=i,n.click(),window.URL.revokeObjectURL(a)}t.default={genUrl:r,downloadCsv:o},e.exports=t["default"]}))}}]);