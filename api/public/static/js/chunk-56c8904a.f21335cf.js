(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-56c8904a","chunk-2d2077d3"],{"65f2":function(e,t,a){"use strict";a.r(t);var i=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("teacher_class",{ref:"tc",attrs:{searchConfigProps:e.formConfig,searchFormProps:e.form,"see-in-dialog":e.seeInDialog}})},o=[],r=a("a16c"),s={components:{teacher_class:r["default"]},props:{},data:function(){return{seeInDialog:1,formConfig:{formItemList:[[{type:"text",prop:"teacher_name",name:"teacher_name",label:"姓名",placeholder:"请输入姓名",width:8},{type:"text",prop:"tel",name:"tel",label:"手机号",placeholder:"请输入手机号",width:8}]],operate:[{type:"primary",icon:"el-icon-search",name:"查询",loading:!1,handleClick:this.getTable}]},form:{teacher_name:"",tel:""}}},computed:{},create:function(){},watch:{},methods:{getTable:function(){var e=this;this.formConfig.operate[0].loading=!0,this.$refs.tc.getTable().then((function(){e.formConfig.operate[0].loading=!1})).catch((function(){e.formConfig.operate[0].loading=!1}))}}},n=s,l=a("2877"),c=Object(l["a"])(n,i,o,!1,null,"de516436",null);t["default"]=c.exports},a16c:function(e,t,a){"use strict";a.r(t);var i=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"app-container"},[a("el-row",{staticStyle:{"padding-right":"20px","margin-bottom":"20px","text-align":"right"}},[a("search-bar",{ref:"aBar",attrs:{"form-config":e.searchConfig,"loading-form":e.searchLoading,value:e.searchForm}})],1),e.tableData.length?a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.waiting,expression:"waiting"}],attrs:{data:e.tableData,border:""}},[e._l(e.itemList,(function(t,i){return[a("el-table-column",{key:i,attrs:{label:t.label,prop:t.prop,"show-overflow-tooltip":"",fixed:Boolean(t.fixed)},scopedSlots:e._u([{key:"default",fn:function(i){return["date_index"===t.prop?a("div",[e._v(" "+e._s(e.dateIndexMap[i.row[t.prop]])+" ")]):"time"===t.prop?a("div",[e.tool.empty(i.row["start_time"])?a("span",[e._v("未配置")]):a("span",[e._v(" "+e._s(e.tool.fmt_hms(i.row["start_time"]))+"-"+e._s(e.tool.fmt_hms(i.row["end_time"]))+" ")])]):a("span",{domProps:{innerHTML:e._s(i.row[t.prop])}})]}}],null,!0)})]})),a("el-table-column",{attrs:{width:e.seeInDialog?80:148,fixed:"right",label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"primary",size:"small"},on:{click:function(a){return e.handleEdit(t.row,t.$index)}}},[e._v("编辑 ")]),e.seeInDialog?e._e():a("el-button",{attrs:{type:"danger",size:"small"},on:{click:function(a){return e.handleDelete(t.row)}}},[e._v("删除 ")])]}}],null,!1,2175340501)})],2):e._e(),e.tableData.length?a("el-pagination",{attrs:{"current-page":e.curPage,"page-size":1,total:e.totalPage,"page-sizes":e.def.pageSizes,layout:"total,sizes,prev, pager, next, jumper"},on:{"update:currentPage":function(t){e.curPage=t},"update:current-page":function(t){e.curPage=t},"size-change":e.handleSizeChange,"current-change":e.handleCurrentChange}}):e._e(),a("el-dialog",{attrs:{title:e.dialogTitle,visible:e.dialogFormVisible,"close-on-click-modal":!1,"close-on-press-escape":"","append-to-body":""},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[a("div",{staticClass:"search-bar"},[a("search-bar",{ref:"sBar",attrs:{"loading-form":e.loadingForm,"form-config":e.formConfig,value:e.form}})],1)])],1)},o=[],r=a("5530"),s=(a("a9e3"),a("b0c0"),a("a15b"),a("e9c4"),a("2f62")),n={components:{},props:{searchConfigProps:{type:Object,default:function(){return{}}},searchFormProps:{type:Object,default:function(){return{}}},seeInDialog:{type:Number,default:0}},data:function(){return{tableData:[],termData:[],teacherData:[],schoolData:[],searchLoading:!1,waiting:!1,curPage:1,curSize:20,totalPage:1,mapKey:[0,1],searchConfig:{formItemList:[[{type:"select",prop:"tid",name:"tid",label:"学期",width:4,clearable:0,optList:[]},{type:"select",prop:"school",name:"school",label:"学校",multiple:1,placeholder:"全部学校",width:4,optList:[]},{type:"select",prop:"teacher",name:"teacher",label:"老师",placeholder:"全部老师",multiple:1,width:4,optList:this.def.linkTypeList},{type:"select",prop:"index",name:"index",label:"周数",placeholder:"整周",multiple:1,width:4,optList:this.def.weekOnList}]],operate:[{type:"primary",icon:"el-icon-plus",name:"新增",handleClick:this.handleAdd},{type:"primary",icon:"el-icon-document-copy",name:"复制链接",handleClick:this.copy},{type:"primary",icon:"el-icon-search",name:"查询",handleClick:this.getTable}]},searchForm:{tid:0,school:[],teacher:[],index:[],tel:"",teacher_name:""},dialogTitle:"新增链接",dialogFormVisible:!1,loadingForm:!1,formConfig:{formItemList:[[{type:"text",prop:"school_name",name:"school_name",label:"学校",width:24},{type:"text",prop:"class_name",name:"class_name",label:"课程",width:24},{type:"select",name:"date_index",prop:"date_index",label:"周几",width:24,optList:this.def.weekOnList},{type:"text",prop:"teacher_name",name:"teacher_name",label:"教师名称",width:24},{type:"text",prop:"tel",name:"tel",label:"手机号",width:24},{type:"text",prop:"price",name:"price",label:"单价",width:24},{type:"time-select",prop:"start_time_f",name:"start_time_f",label:"开始时间",width:24,options:{minTime:"11:59",maxTime:"18:01",step:"00:30",start:"12:00",end:"18:00"}},{type:"time-select",prop:"end_time_f",name:"end_time_f",label:"结束时间",width:24,options:{minTime:"11:59",maxTime:"18:01",step:"00:30",start:"12:30",end:"18:00"}},{type:"text",prop:"class_locate",name:"class_locate",label:"上课教室",width:24}]],operate:[{type:"primary",icon:"el-icon-phone-outline",name:"提交",handleClick:this.addEdit}]},form:{id:0,school_name:"",class_name:"",date_index:"",teacher_name:"",tel:"",price:"",start_time:"",end_time:"",class_locate:"",start_time_f:"16:00",end_time_f:"17:00"},choseIndex:0,key:""}},computed:Object(r["a"])(Object(r["a"])({},Object(s["b"])(["tid"])),{},{baseForm:function(){return{id:0,school_name:"",class_name:"",date_index:"",teacher_name:"",tel:"",price:"",start_time:"",end_time:"",class_locate:"",start_time_f:"16:00",end_time_f:"17:00"}},schoolList:function(){var e=[];for(var t in this.schoolData)e.push({label:this.schoolData[t],value:this.schoolData[t]});return e},teacherList:function(){var e=[];for(var t in this.teacherData)e.push({label:this.teacherData[t],value:this.teacherData[t]});return e},termList:function(){var e=[];for(var t in this.termData)e.push({label:this.termData[t].name,value:this.termData[t].id});return e},shareMap:function(){return this.tool.arrayColumn(this.termData,"key","id")},itemList:function(){return[{label:"学校",prop:"school_name"},{label:"课程",prop:"class_name"},{label:"周几",prop:"date_index"},{label:"老师",prop:"teacher_name"},{label:"手机号",prop:"tel"},{label:"单价",prop:"price"},{label:"上课时间",prop:"time"},{label:"上课教室",prop:"class_locate"}]},dateIndexMap:function(){return this.tool.arrayColumn(this.def.weekOnList,"label","value")}}),watch:{},created:function(){this.key=this.$route.params.key,this.searchConfig=this.tool.empty(this.searchConfigProps)?this.searchConfig:this.searchConfigProps,this.searchForm=this.tool.empty(this.searchFormProps)?this.searchForm:this.searchFormProps,this.seeInDialog||(this.getConf(),this.getTable())},mounted:function(){},methods:{getTable:function(){var e=this,t=this;this.waiting=!0,this.checkTid();var a="class_teacher_list",i={};return this.seeInDialog?(a="tc/class_teacher_list/"+this.key,i={tel:t.searchForm.tel,teacher_name:t.searchForm.teacher_name}):i={tid:t.searchForm.tid,school:t.searchForm.school.join(),teacher:t.searchForm.teacher.join(),index:t.searchForm.index.join(),page_size:t.curSize,page:t.curPage},t.request(a,i,!0).then((function(a){var i=a.data;t.tableData=i.data,t.tableData.length||e.$message.warning("无匹配信息"),t.curPage=parseInt(i.current_page),t.totalPage=i.total||0,t.waiting=!1}))},getConf:function(){var e=this;this.searchLoading=!0,this.checkTid();var t="class_teacher_conf",a={tid:e.searchForm.tid};e.request(t,a,!0).then((function(t){var a=t.data;e.termData=a.terms,e.teacherData=a.teacher,e.schoolData=a.school,e.searchLoading=!1,e.searchConfig.formItemList[0][0].optList=e.termList,e.searchConfig.formItemList[0][1].optList=e.schoolList,e.searchConfig.formItemList[0][2].optList=e.teacherList})).catch((function(){e.searchLoading=!1}))},checkTid:function(){this.tool.empty(this.searchForm.tid)&&(this.searchForm.tid=parseInt(this.tid))},handleCurrentChange:function(e){this.curPage=e,this.getTable()},handleSizeChange:function(e){this.curSize=e,this.getTable()},handleEdit:function(e,t){this.choseIndex=t,e.start_time_f=this.tool.fmt_hms(e.start_time),e.end_time_f=this.tool.fmt_hms(e.end_time),this.dialogTitle="编辑"+e.teacher_name+"老师"+this.dateIndexMap[e.date_index]+"的"+e.class_name,this.form=JSON.parse(JSON.stringify(e)),this.seeInDialog&&(this.formConfig.formItemList[0][0].disabled=1,this.formConfig.formItemList[0][1].disabled=1,this.formConfig.formItemList[0][2].disabled=1,this.formConfig.formItemList[0][3].disabled=1,this.formConfig.formItemList[0][4].disabled=1,this.formConfig.formItemList[0][5].disabled=1),this.dialogFormVisible=!0},handleAdd:function(){this.dialogTitle="新增信息",this.dialogFormVisible=!0,this.form=JSON.parse(JSON.stringify(this.baseForm)),this.form.tid=this.tid,this.choseIndex=-1},handleDelete:function(e){var t=this,a=this,i="class_teacher_add_del";a.request(i,{id:e.id,from:this.mapKey[this.seeInDialog]},!0).then((function(e){t.getTable()}))},copy:function(){this.clipboard(window.location.origin+"/tc/"+this.shareMap[this.tid]),this.$message.success("复制成功")},clipboard:function(e){var t=document.createElement("input");t.setAttribute("value",e),document.body.appendChild(t),t.select(),document.execCommand("Copy"),document.body.removeChild(t)},addEdit:function(){var e=this;this.form.start_time=this.tool.getSecondsByTimeStr(this.form.start_time_f),this.form.end_time=this.tool.getSecondsByTimeStr(this.form.end_time_f);var t=this;this.loadingForm=!0;var a="class_teacher_add_edit",i=JSON.parse(JSON.stringify(this.form));i.from=this.mapKey[this.seeInDialog],this.seeInDialog&&(a="tc/class_teacher_add_edit/"+this.key,delete i["tid"]),t.request(a,i,!0).then((function(a){t.$message.success((-1===e.choseIndex?"新增":"编辑")+"成功"),t.loadingForm=!1,-1===t.choseIndex?t.tableData.unshift(t.form):t.$set(t.tableData,t.choseIndex,JSON.parse(JSON.stringify(t.form))),t.dialogFormVisible=!1})).catch((function(){t.loadingForm=!1}))}}},l=n,c=a("2877"),h=Object(c["a"])(l,i,o,!1,null,"1ee07be2",null);t["default"]=h.exports}}]);