<template>
  <div class="app-container">

    <el-upload
      v-show="false"
      :before-upload="beforeUpload"
      :action="upload_url"
      :on-progress="onProgressUpload"
      :on-success="onSuccess"
      :on-error="onFailed"
      :show-file-list="false"
    >
      <el-button ref="upload" size="small" type="primary">点击上传</el-button>
    </el-upload>
    <el-row style="padding-right: 20px;margin-bottom: 20px;text-align: right">
      <search-bar
        ref="aBar"
        :form-config="searchConfig"
        :loading-form="searchLoading"
        :value="searchForm"
      />
    </el-row>
    <el-table
      v-if="tableData.length"
      v-loading="waiting"
      :data="tableData"
      border
    >

      <template v-for="(item,index) in itemList">
        <el-table-column
          v-if="!(seeInDialog&&item.hide)"
          :key="index"
          :label="item.label"
          :prop="item.prop"
          show-overflow-tooltip
          :width="itemWidth(item.prop)"
          :fixed="Boolean(item.fixed)"
        >
          <template slot-scope="scope">
            <div v-if="item.prop==='date_index'">
              {{ dateIndexMap[scope.row[item.prop]] }}
            </div>
            <div v-else-if="item.prop==='time'">
              <span v-if="tool.empty(scope.row['start_time'])">未配置</span>
              <span v-else>
                {{ tool.fmt_hms(scope.row['start_time']) }}-{{ tool.fmt_hms(scope.row['end_time']) }}
              </span>
            </div>
            <span
              v-else
              v-html="scope.row[item.prop]"
            />
          </template>

        </el-table-column>
      </template>
      <el-table-column :width="!seeInDialog?148:80" fixed="right" label="操作">
        <template slot-scope="scope">
          <el-button
            type="primary"
            size="small"
            @click="handleEdit(scope.row,scope.$index)"
          >编辑
          </el-button>

          <el-button
            v-if="!seeInDialog"
            type="danger"
            size="small"
            @click="handleDelete(scope.row)"
          >删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-pagination
      v-if="tableData.length"
      :current-page.sync="curPage"
      :page-size="1"
      :total="totalPage"
      :page-sizes="def.pageSizes"
      layout="total,sizes,prev, pager, next, jumper"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />

    <el-dialog
      :title="dialogTitle"
      :visible.sync="dialogFormVisible"
      :close-on-click-modal="false"
      close-on-press-escape
      append-to-body
    >
      <div class="search-bar">
        <search-bar
          ref="sBar"
          :loading-form="loadingForm"
          :form-config="formConfig"
          :value="form"
        />
      </div>
    </el-dialog>

  </div>
</template>

<script>

import { mapGetters } from 'vuex'
import CsvExport from 'csv-exportor'

export default {
  components: {},
  props: {
    searchConfigProps: {
      type: Object,
      default: function() {
        return {}
      }
    },
    searchFormProps: {
      type: Object,
      default: function() {
        return {}
      }
    },
    seeInDialog: { type: Number, default: 0 }
  },
  data() {
    return {
      tableData: [],
      autoWidth: true,
      bookType: 'xlsx',
      termData: [],
      teacherData: [],
      schoolData: [],
      searchLoading: false,
      waiting: false,
      curPage: 1,
      curSize: 20,
      totalPage: 1,
      mapKey: [
        0, 1
      ],
      searchConfig: {
        formItemList: [
          [
            {
              type: 'select',
              prop: 'tid',
              name: 'tid',
              label: '学期',
              width: 4,
              clearable: 0,
              optList: []
            },
            {
              type: 'select',
              prop: 'school',
              name: 'school',
              label: '学校',
              multiple: 1,
              placeholder: '全部学校',
              width: 4,
              optList: []
            },
            {
              type: 'select',
              prop: 'teacher',
              name: 'teacher',
              label: '老师',
              placeholder: '全部老师',
              multiple: 1,
              width: 4,
              optList: this.def.linkTypeList
            },
            {
              type: 'select',
              prop: 'index',
              name: 'index',
              label: '周数',
              placeholder: '整周',
              multiple: 1,
              width: 4,
              optList: this.def.weekOnList
            }
          ]
        ],
        operate: [
          {
            type: 'primary',
            icon: 'el-icon-plus',
            name: '新增',
            handleClick: this.handleAdd
          },
          {
            type: 'primary',
            icon: 'el-icon-upload2',
            name: '导入Excel',
            handleClick: this.importExcel
          },
          {
            type: 'primary',
            icon: 'el-icon-download',
            name: '导出Excel',
            handleClick: this.exportExcel
          },
          {
            type: 'primary',
            icon: 'el-icon-document-copy',
            name: '复制链接',
            loading: false,
            handleClick: this.copy
          },
          {
            type: 'primary',
            icon: 'el-icon-search',
            name: '查询',
            handleClick: this.getTable
          }
        ]
      },
      searchForm: {
        tid: 0,
        school: [],
        teacher: [],
        index: [],
        tel: '',
        teacher_name: ''
      },
      dialogTitle: '新增链接',
      dialogFormVisible: false,
      loadingForm: false,

      formConfig: {
        formItemList: [
          [
            {
              type: 'text',
              prop: 'school_name',
              name: 'school_name',
              label: '学校',
              width: 24
            },
            {
              type: 'text',
              prop: 'class_name',
              name: 'class_name',
              label: '课程',
              width: 24
            },
            {
              type: 'select',
              name: 'date_index',
              prop: 'date_index',
              label: '周几',
              width: 24,
              optList: this.def.weekOnList
              // handleClick: this.changePlacement
            },
            {
              type: 'text',
              prop: 'teacher_name',
              name: 'teacher_name',
              unshow: this.seeInDialog,
              label: '教师名称',
              width: 24
            },
            {
              type: 'text',
              prop: 'tel',
              name: 'tel',
              unshow: this.seeInDialog,
              label: '手机号',
              width: 24
            },
            {
              type: 'text',
              prop: 'price',
              name: 'price',
              // unshow: this.seeInDialog,
              label: '单价',
              width: 24
            },
            {
              type: 'time-select',
              prop: 'start_time_f',
              name: 'start_time_f',
              label: '开始时间',
              width: 24,
              options: {
                minTime: '11:59',
                maxTime: '18:01',
                step: '00:05',
                start: '12:00',
                end: '18:00'
              }
            },
            {
              type: 'time-select',
              prop: 'end_time_f',
              name: 'end_time_f',
              label: '结束时间',
              width: 24,
              options: {
                minTime: '11:59',
                maxTime: '18:01',
                step: '00:05',
                start: '12:30',
                end: '18:00'
              }
            },
            {
              type: 'text',
              prop: 'class_locate',
              name: 'class_locate',
              label: '上课教室',
              width: 24

            }
          ]
        ],

        operate: [
          {
            type: 'primary',
            icon: 'el-icon-phone-outline',
            name: '提交',
            handleClick: this.addEdit
          }
        ]
      },

      form: {
        id: 0,
        school_name: '',
        class_name: '',
        date_index: '',
        teacher_name: '',
        tel: '',
        price: '',
        start_time: '',
        end_time: '',
        class_locate: '',
        start_time_f: '16:00',
        end_time_f: '17:00'
      },
      choseIndex: 0,
      key: '',
      base_url: process.env.VUE_APP_BASE_API + '/class_teacher_import_excel',
      upload_url: ''
    }
  },
  computed: {
    ...mapGetters([
      'tid',
      'device'
    ]),
    baseForm() {
      return {
        id: 0,
        school_name: '',
        class_name: '',
        date_index: '',
        teacher_name: '',
        tel: '',
        price: '',
        start_time: '',
        end_time: '',
        class_locate: '',
        start_time_f: '16:00',
        end_time_f: '17:00'

      }
    },
    schoolList() {
      const temp = []
      for (const i in this.schoolData) {
        temp.push({
          'label': this.schoolData[i],
          'value': this.schoolData[i]
        })
      }
      return temp
    },
    teacherList() {
      const temp = []
      for (const i in this.teacherData) {
        temp.push({
          'label': this.teacherData[i],
          'value': this.teacherData[i]
        })
      }
      return temp
    },
    termList() {
      const temp = []
      for (const i in this.termData) {
        temp.push({
          'label': this.termData[i].name,
          'value': this.termData[i].id
        })
      }
      return temp
    },
    termMap() {
      return this.tool.arrayColumn(this.termData, 'name', 'id')
    },
    shareMap() {
      return this.tool.arrayColumn(this.termData, 'key', 'id')
    },
    itemList() {
      return [
        { 'label': '学校', 'prop': 'school_name' },
        { 'label': '课程', 'prop': 'class_name' },
        { 'label': '周几', 'prop': 'date_index' },
        { 'label': '老师', 'prop': 'teacher_name', hide: true },
        { 'label': '手机号', 'prop': 'tel', hide: true },
        { 'label': '单价', 'prop': 'price' },
        { 'label': '上课时间', 'prop': 'time' },
        { 'label': '上课教室', 'prop': 'class_locate' }
      ]
    },
    dateIndexMap() {
      return this.tool.arrayColumn(this.def.weekOnList, 'label', 'value')
    }

  },
  watch: {},
  created() {
    this.key = this.$route.params.key
    this.searchConfig = !this.tool.empty(this.searchConfigProps)
      ? this.searchConfigProps
      : this.searchConfig
    this.searchForm = !this.tool.empty(this.searchFormProps) ? this.searchFormProps : this.searchForm
    if (!this.seeInDialog) {
      this.getConf()
      this.getTable()
    }
  },
  mounted() {
  },
  methods: {

    getTable() {
      const _that = this
      this.waiting = true
      this.checkTid()
      let url = 'class_teacher_list'

      let params = {}
      if (this.seeInDialog) {
        url = 'tc/class_teacher_list/' + this.key
        params = {
          tel: _that.searchForm.tel,
          teacher_name: _that.searchForm.teacher_name
        }
      } else {
        params = {
          tid: _that.searchForm.tid,
          school: _that.searchForm.school.join(),
          teacher: _that.searchForm.teacher.join(),
          index: _that.searchForm.index.join(),
          page_size: _that.curSize,
          page: _that.curPage
        }
      }

      return _that.request(url, params, true).then((res) => {
        const data = res.data

        _that.tableData = data.data
        if (!_that.tableData.length) {
          this.$message.warning('无匹配信息')
        }
        _that.curPage = parseInt(data.current_page)
        _that.totalPage = data.total || 0
        _that.waiting = false
      })
    },
    getConf() {
      const _that = this
      this.searchLoading = true
      this.checkTid()
      const url = 'class_teacher_conf'
      const params = {
        tid: _that.searchForm.tid
      }

      _that.request(url, params, true).then((res) => {
        const data = res.data
        _that.termData = data.terms
        _that.teacherData = data.teacher
        _that.schoolData = data.school
        _that.searchLoading = false
        _that.searchConfig.formItemList[0][0].optList = _that.termList
        _that.searchConfig.formItemList[0][1].optList = _that.schoolList
        _that.searchConfig.formItemList[0][2].optList = _that.teacherList

        // _that.curPlatform = params.platform
      }).catch(() => {
        _that.searchLoading = false
      })
    },

    checkTid() {
      if (this.tool.empty(this.searchForm.tid)) {
        this.searchForm.tid = parseInt(this.tid)
      }
    },

    handleCurrentChange(val) {
      this.curPage = val
      this.getTable()
    },
    handleSizeChange(val) {
      this.curSize = val
      this.getTable()
    },
    handleEdit(row, index) {
      this.choseIndex = index
      row.start_time_f = this.tool.fmt_hms(row.start_time)
      row.end_time_f = this.tool.fmt_hms(row.end_time)
      this.dialogTitle = '编辑' + row.teacher_name + '老师' + this.dateIndexMap[row.date_index] + '的' + row.class_name
      this.form = JSON.parse(JSON.stringify(row))
      if (this.seeInDialog) {
        this.formConfig.formItemList[0][0].disabled = 1
        this.formConfig.formItemList[0][1].disabled = 1
        this.formConfig.formItemList[0][2].disabled = 1
        this.formConfig.formItemList[0][3].disabled = 1
        this.formConfig.formItemList[0][4].disabled = 1
        this.formConfig.formItemList[0][5].disabled = 1
      }
      this.dialogFormVisible = true
    },
    handleAdd() {
      this.dialogTitle = '新增信息'
      this.dialogFormVisible = true
      this.form = JSON.parse(JSON.stringify(this.baseForm))
      this.form.tid = this.searchForm.tid
      this.choseIndex = -1
    },
    handleDelete(row) {
      const _that = this
      const url = 'class_teacher_add_del'
      _that.request(url, { id: row.id, from: this.mapKey[this.seeInDialog] }, true).then((res) => {
        this.getTable()
      })
    },
    copy() {
      this.clipboard(window.location.origin + '/tc/' + this.shareMap[this.searchForm.tid])
      this.$message.success('复制成功')
    },
    clipboard(randomString) {
      let input = document.createElement('input')
      input.setAttribute('value', randomString)
      document.body.appendChild(input)
      input.select()
      document.execCommand('Copy')
      document.body.removeChild(input)
    },
    addEdit() {
      this.form.start_time = this.tool.getSecondsByTimeStr(this.form.start_time_f)
      this.form.end_time = this.tool.getSecondsByTimeStr(this.form.end_time_f)
      const _that = this
      this.loadingForm = true
      let url = 'class_teacher_add_edit'
      const params = JSON.parse(JSON.stringify(this.form))
      params.from = this.mapKey[this.seeInDialog]

      if (this.seeInDialog) {
        url = 'tc/class_teacher_add_edit/' + this.key
        delete params['tid']
      }

      _that.request(url, params, true).then((res) => {
        _that.$message.success((this.choseIndex === -1 ? '新增' : '编辑') + '成功')
        _that.loadingForm = false
        if (_that.choseIndex === -1) {
          _that.tableData.unshift(_that.form)
        } else {
          _that.$set(_that.tableData, _that.choseIndex, JSON.parse(JSON.stringify(_that.form)))
        }
        _that.dialogFormVisible = false
      }).catch(() => {
        _that.loadingForm = false
      })
    },
    itemWidth(prop) {
      if (this.device === 'desktop') {
        return ''
      }
      let width = ''
      // { 'label': '学校', 'prop': 'school_name' },
      // { 'label': '课程', 'prop': 'class_name' },
      // { 'label': '周几', 'prop': 'date_index' },
      // { 'label': '老师', 'prop': 'teacher_name' },
      // { 'label': '手机号', 'prop': 'tel' },
      // { 'label': '单价', 'prop': 'price' },
      // { 'label': '上课时间', 'prop': 'time' },
      // { 'label': '上课教室', 'prop': 'class_locate' }
      switch (prop) {
        case 'school_name':
          width = 185
          break
        case 'class_name':
          width = 120
          break

        default:
          break
      }
      return width
    },
    // $_isMobile() {
    //   const rect = body.getBoundingClientRect()
    //   return rect.width - 1 < WIDTH
    // }
    importExcel() {
      this.upload_url = this.base_url + '?tid=' + this.searchForm.tid
      this.$refs.upload.$el.click()
    },
    async exportExcel() {
      if (this.totalPage > this.curSize) {
        this.page = 1
        this.curSize = this.totalPage
        await this.getTable()
      }
      const tHeader = ['学校', '课程', '周几', '老师', '手机号', '单价', '上课时间', '上课教室']
      const filterVal = ['school_name', 'class_name', 'date_index', 'teacher_name', 'tel', 'price', 'time', 'class_locate']
      const list = this.tableData
      const data = this.formatJson(filterVal, list)
      data.unshift(tHeader)
      CsvExport.downloadCsv(data, {}, this.termMap[this.searchForm.tid] + '.xlsx')
    },
    formatJson(items, data) {
      const temp = []
      for (const i in data) {
        const tmp = []
        for (const j in items) {
          let val = ''
          switch (items[j]) {
            case 'date_index':
              val = this.dateIndexMap[data[i][items[j]]]
              break
            case 'time':
              val = this.tool.fmt_hms(data[i]['start_time']) + '-' + this.tool.fmt_hms(data[i]['end_time'])
              break
            default:
              val = data[i][items[j]]
              break
          }
          tmp.push(val)
        }
        temp.push(tmp)
      }
      return temp
    },
    beforeUpload(file) {
      const testmsg = file.name.substring(file.name.lastIndexOf('.') + 1)
      const extension = testmsg === 'xlsx'
      if (!extension) {
        this.$message({
          message: '上传文件只能是.xlsx!',
          type: 'warning'
        })
      }
      return extension
    },
    onProgressUpload() {
      this.searchLoading = true
    },
    onSuccess() {
      this.searchLoading = false
      this.$message.success('上传成功')
      this.getTable()
    },
    onFailed() {
      this.searchLoading = false
      this.$message.error('上传失败')
    }

  }
}
</script>

<style lang="scss" scoped>

</style>
