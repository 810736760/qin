<template>
  <div class="sumbit-form">
    <el-dialog
      append-to-body
      :visible.sync="bookShowVisible"
      :close-on-click-modal="false"
      :title="bookShowTitle"
    >
      <div class="chapter-dialog">
        <el-row ref="bsmsDiv" v-loading="chapterListLoading" class="chapter-list">
          <v-selectmenu
            ref="bsms"
            v-model="chapterChose"
            :data="chapterList"
            :embed="true"
            :width="bookShowWidth"
            :multiple="bookShowMulti"
            :query="false"
            :key-field="keyField"
            :show-field="showField"
            title="免费章节列表"
            @values="chapterChoseMethods"
          />
        </el-row>
        <div class="dialog-chapter-content">
          <div style="text-align: right">
            <el-button :loading="useToContentLoading" type="primary" @click="getContent(1)">繁化内容</el-button>
            <el-button :loading="useToContentLoading" type="primary" @click="getContent(0)">使用内容</el-button>
          </div>
          <div class="text">
            <el-row v-loading="chapterContentLoading" class="text-content" v-html="chapterContent" />
            <!--            <div class="chapter-message" />-->
          </div>
        </div>
      </div>
    </el-dialog>
    <el-form
      ref="ruleForm"
      v-loading="loadingForm"
      :model="value"
      :rules="rules"
      :label-width="width"
      class="demo-ruleForm search-bar-form"
    >
      <slot name="formItem" />
      <template v-for="(item, index) in formConfig.formItemList">
        <el-row :key="index" :gutter="10">
          <template v-for="(i, kIndex) in item">
            <el-col
              v-if="(!tool.isSet(i,'power') || power === 1 || tool.isInArray(i.power,power) )&& !Boolean(i.unshow || false) "
              :key="kIndex"
              :xs="24"
              :sm="24"
              :md="24"
              :lg="i.width||12"
              :xl="i.width||8"
            >
              <template
                v-if="
                  ['text', 'textarea', 'number', 'email'].indexOf(i.type) !== -1
                "
              >
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <el-input
                    v-if="tool.isSet(i,'handleBlur')"
                    v-model="value[i.name]"
                    :type="i.type"
                    :placeholder="i.placeholder"
                    :disabled="Boolean(i.disabled || false)"
                    clearable
                    :min="i.min"
                    :max="i.max"
                    autosize
                    :class="i.class || 'whole-auto'"
                    @change="i.handleBlur"
                  />
                  <el-input
                    v-else
                    v-model="value[i.name]"
                    :type="i.type"
                    :placeholder="i.placeholder"
                    :disabled="Boolean(i.disabled || false)"
                    clearable
                    :class="i.class || 'whole-auto'"
                    autosize
                  />
                  <span v-if="i.tip" :style="i.fontColor||'color:red'">{{ i.tip }}</span>
                </el-form-item>
              </template>

              <template v-if="i.type === 'select'">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <template v-if="tool.isSet(i,'handleClick')">
                    <el-select
                      v-model="value[i.name]"
                      :multiple="Boolean(i.multiple)"
                      :placeholder="i.placeholder"
                      :disabled="Boolean(i.disabled || false)"
                      filterable
                      :allow-create="Boolean(i.allowCreate)"
                      default-first-option
                      :clearable="Boolean(tool.isSet(i,'clearable',1) )"
                      @change="i.handleClick"
                    >
                      <el-option
                        v-for="(j, k) in i.optList"
                        :key="k"
                        :label="j[i.optItem?i.optItem[1] : '']||j.label || j.name"
                        :value="j[i.optItem?i.optItem[0] : '']||j.value || (j.type || j.id ) || 0"
                      />
                    </el-select>
                  </template>
                  <template v-else>
                    <el-select
                      v-model="value[i.name]"
                      :multiple="Boolean(i.multiple)"
                      :placeholder="i.placeholder"
                      :disabled="Boolean(i.disabled || false)"
                      filterable
                      default-first-option
                      :allow-create="Boolean(i.allowCreate)"
                      :clearable="Boolean(tool.isSet(i,'clearable',1) )"
                    >
                      <el-option
                        v-for="(j, k) in i.optList"
                        :key="k"
                        :label="j.label || j.name"
                        :value="j.value || (j.type || j.id ) || 0"
                      />
                    </el-select>
                  </template>

                </el-form-item>
              </template>
              <template v-else-if="i.type === 'button'">
                <el-form-item>
                  <el-button
                    :type="i.bType || 'primary'"
                    :circle="i.circle|| false"
                    :size="i.size||'mini'"
                    @click="i.handleClick"
                  >{{ i.label }}
                  </el-button>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'online_select'">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <div style="display:flex;">
                    <div style="flex:0 0 92%">
                      <template v-if="tool.isSet(i,'handleClick')">
                        <el-select
                          v-model="value[i.name]"
                          :remote-method="i.remote_method"
                          :loading="value[i.loading]"
                          multiple
                          filterable
                          remote
                          clearable
                          :placeholder="i.placeholder || '请输入'"
                          :class="i.class || 'whole-width'"
                          @change="i.handleClick"
                        >
                          <el-option
                            v-for="(itemOnline,indexOnline) in i.optList"
                            :key="indexOnline"
                            :label="itemOnline.name"
                            :value="itemOnline.key"
                          >
                            <template v-if="tool.isIncludeBy(i.prop,/geo_locations/)">
                              <span style="float: left">{{ itemOnline.name }}</span>
                              <span style="float: right; color: #8492a6;">{{
                                geoMap[itemOnline.type] || item.type
                              }}</span>
                            </template>

                          </el-option>
                        </el-select>
                      </template>
                      <template v-else>
                        <el-select
                          v-model="value[i.name]"
                          :remote-method="i.remote_method"
                          :loading="value[i.loading]"
                          multiple
                          filterable
                          remote
                          clearable
                          :placeholder="i.placeholder || '请输入'"
                          :class="i.class || 'whole-width'"
                        >
                          <el-option
                            v-for="(itemOnline,indexOnline) in i.optList"
                            :key="indexOnline"
                            :label="itemOnline.name"
                            :value="itemOnline.key"
                          >
                            <template v-if="tool.isIncludeBy(i.prop,/geo_locations/)">
                              <span style="float: left">{{ itemOnline.name }}</span>
                              <span style="float: right; color: #8492a6;">{{
                                geoMap[itemOnline.type] || item.type
                              }}</span>
                            </template>

                          </el-option>
                        </el-select>
                      </template>
                    </div>
                    <div style="flex: 1;">
                      <template v-if="i.appendClick">
                        <el-button
                          type="primary"
                          icon="el-icon-folder-opened"
                          circle
                          @click="i.appendClick"
                        />
                      </template>
                    </div>
                  </div>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'selectUser'">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <el-select
                    v-model="value[i.name]"
                    :placeholder="i.placeholder"
                    filterable
                    clearable
                  >
                    <el-option
                      v-for="(j, k) in getUserByG(i.userRange,i.showAll)"
                      :key="k"
                      :label="j.nickname"
                      :value="j.id"
                    />
                  </el-select>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'selectGroup'">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <el-select
                    v-model="value[i.name]"
                    :placeholder="i.placeholder"
                    filterable
                    clearable
                  >
                    <el-option
                      v-for="(j, k) in group"
                      :key="k"
                      :label="j.name"
                      :value="j.id"
                    />
                  </el-select>
                </el-form-item>
              </template>

              <template v-else-if="['date', 'daterange','week','month','datetime','year'].indexOf(i.type) !== -1">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <el-date-picker
                    v-model="value[i.name]"
                    :value-format="!i.noFormat?i.dateFormate:''"
                    :format="i.format"
                    :picker-options="i.type==='daterange'?pickerOptions:{firstDayOfWeek:7}"
                    :type="i.type"
                    range-separator="-"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    :placeholder="i.placeholder"
                    align="left"
                    unlink-panels
                  />
                  <span v-if="i.tip" :style="i.fontColor||'color:red'">{{ i.tip }}</span>
                </el-form-item>
              </template>
              <template v-else-if="['time-picker'].indexOf(i.type) !== -1">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <el-time-picker
                    v-model="value[i.name]"
                    is-range
                    :arrow-control="Boolean(i.arrow||false)"
                    range-separator="-"
                    :format="i.format||'HH:mm'"
                    :start-placeholder="i.start_placeholder||'开始时间'"
                    :end-placeholder="i.end_placeholder||'结束时间'"
                    :placeholder="i.placeholder||'选择时间范围'"
                    :value-format="i.valueFormat||'HH-mm'"
                  />

                  <span v-if="i.tip" :style="i.fontColor||'color:red'">{{ i.tip }}</span>
                </el-form-item>
              </template>
              <template v-else-if="['time-select'].indexOf(i.type) !== -1">
                <template v-if="tool.isSet(i,'handleClick')">
                  <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                    <el-time-select
                      v-model="value[i.name]"
                      :placeholder="i.placeholder||i.label"
                      :picker-options="i.options"
                      @change="i.handleClick"
                    />

                    <span v-if="i.tip" :style="i.fontColor||'color:red'">{{ i.tip }}</span>
                  </el-form-item>
                </template>
                <template v-else>
                  <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                    <el-time-select
                      v-model="value[i.name]"
                      :placeholder="i.placeholder||i.label"
                      :picker-options="i.options"
                    />

                    <span v-if="i.tip" :style="i.fontColor||'color:red'">{{ i.tip }}</span>
                  </el-form-item>
                </template>
              </template>
              <template v-else-if="i.type === 'switch'">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <template v-if="tool.isSet(i,'handleClick')">
                    <el-switch
                      v-model="value[i.name]"
                      :active-value="i.active || true"
                      :inactive-value="i.inactive || false"
                      :disabled="i.disabled|| false"
                      @change="i.handleClick"
                    />
                  </template>
                  <template v-else>
                    <el-switch
                      v-model="value[i.name]"
                      :active-value="i.active || true"
                      :inactive-value="i.inactive || false"
                    />
                  </template>

                </el-form-item>
              </template>
              <template v-else-if="i.type === 'radio'">
                <el-form-item :label="i.label">
                  <el-radio-group
                    v-if="tool.isSet(i,'handleClick')"
                    v-model="value[i.name]"
                    :disabled="Boolean(i.disabled||0)"
                    @change="i.handleClick"
                  >
                    <el-radio-button
                      v-for="(j, k) in i.optList"
                      :key="k"
                      :label="j.value || j.type"
                      :disabled="Boolean(j.disabled||0)"
                    >
                      {{ j.label || j.name }}
                    </el-radio-button>
                  </el-radio-group>
                  <el-radio-group
                    v-else
                    v-model="value[i.name]"
                    :disabled="Boolean(i.disabled||0)"
                  >
                    <el-radio-button
                      v-for="(j, k) in i.optList"
                      :key="k"
                      :label="j.value || j.type"
                      :disabled="Boolean(j.disabled||0)"
                    >
                      {{ j.label || j.name }}
                    </el-radio-button>
                  </el-radio-group>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'Checkbox'">
                <el-checkbox-group v-model="value[i.name]">
                  <el-checkbox
                    v-for="ch in i.checkboxs"
                    :key="ch.value"
                    :label="ch.value"
                  >{{ ch.label }}
                  </el-checkbox>
                </el-checkbox-group>
              </template>
              <template v-else-if="i.type === 'upload_img'">
                <el-form-item :label="i.label" :prop="i.prop ? i.prop : ''">
                  <el-upload
                    ref="sBarFileList"
                    :http-request="i.uploadMethod"
                    accept=".jpeg,.jpg,.png,.webp,.gif"
                    :on-change="handleImgPreview"
                    :file-list="value[i.list]"
                    :auto-upload="defaultFalse"
                    action="#"
                    :limit="i.limit || 1"
                    :list-type="i.listType||'picture'"
                    :multiple="parseInt(i.limit) > 1"
                  >
                    <el-button size="small" type="primary">上传图片</el-button>
                    <div slot="tip" class="el-upload__tip">
                      <span v-if="i.tip" :style="i.fontColor||'color:red'">{{ i.tip }}</span>
                    </div>
                  </el-upload>

                </el-form-item>
              </template>
              <template v-else-if="i.type === 'book'">
                <el-form-item label="书籍ID" prop="book">
                  <div style="display: flex">
                    <div style="flex: 0 0 320px">
                      <el-input
                        v-model="value[i.type]"
                        type="number"
                        :placeholder="i.placeholder || '书籍ID'"
                        class="input-with-select width-auto"
                      >
                        <el-select
                          v-if="!value['book_url']"
                          slot="prepend"
                          v-model="value['book_platform']"
                          placeholder="请选择包"
                        >
                          <template v-for="(itemSys,indexSys) in system">
                            <el-option :key="indexSys" :label="itemSys.name" :value="itemSys.platform" />
                          </template>
                        </el-select>
                        <el-button
                          slot="append"
                          icon="el-icon-search"
                          @click="searchBook"
                        >
                          搜书
                        </el-button>
                      </el-input>
                    </div>
                    <div v-if="bookShowTitle">
                      <span style="color: red">
                        {{ bookShowTitle }}
                      </span>
                    </div>
                  </div>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'content'">
                <el-form-item label="内容" prop="content">
                  <tinymce ref="tinyContent" v-model="value['content']" :height="300" />
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'color'">
                <el-form-item :label="i.label" :prop="i.prop">
                  <el-color-picker
                    v-model="value[i.prop]"
                    show-alpha
                    :predefine="i.predefineColors"
                  />
                  {{ value[i.prop] }}
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'platformTree'">
                <el-form-item :label="i.label" :prop="i.prop">
                  <el-row>
                    <el-col
                      v-for="(platformItem,platformIndex) in def.platformOptions"
                      :key="platformIndex"
                      :xs="24"
                      :sm="24"
                      :md="24"
                      :lg="12"
                      :xl="6"
                    >
                      <el-tree
                        :ref="i.ref"
                        :data="fmtPfOption(platformItem)"
                        :props="defaultProps"
                        show-checkbox
                        check-on-click-node
                        default-expand-all
                        node-key="id"
                        @check="isChange=true"
                      >
                        <span
                          slot-scope="{node}"
                          class="custom-tree-node is-current"
                        >
                          <el-tooltip :content="node.label" effect="dark" placement="top">
                            <span>{{ node.label }}</span>
                          </el-tooltip>
                        </span>
                      </el-tree>
                    </el-col>

                  </el-row>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'btnList'">
                <el-form-item :label="i.label">
                  <template v-for="(btnItem,btnIndex) in i.btnList">
                    <el-button
                      v-if="tool.isSet(btnItem,'handleClick')"
                      :key="btnIndex"
                      :type="btnItem.type"
                      :icon="btnItem.icon"
                      @click.stop.prevent="btnItem.handleClick"
                    >{{ btnItem.label }}
                    </el-button>

                    <el-button
                      v-else
                      :key="btnIndex"
                      :type="btnItem.type"
                      :icon="btnItem.icon"
                    >{{ btnItem.label }}
                    </el-button>

                  </template>
                </el-form-item>
              </template>
              <template v-else-if="i.type === 'html'">
                <el-form-item :label="i.label">
                  <span v-html="value[i.prop]" />
                </el-form-item>
              </template>
            </el-col>
          </template>
        </el-row>
      </template>

      <div class="searchBtn">
        <el-button-group>
          <template v-for="(item, index) in formConfig.operate">
            <template v-if="item.class==='tableItem'">
              <el-popover :key="index" placement="bottom" width="320" trigger="click">
                <div class="check-box">
                  <el-checkbox
                    v-model="checkAll"
                    :indeterminate="isIndeterminate"
                    @change="handleCheckAllChange"
                  >全选
                  </el-checkbox>
                  <div style="margin: 15px 0;" />
                  <el-checkbox-group
                    v-model="checkboxVal"
                    class="check-flex"
                    @change="handleCheckedChange"
                  >
                    <el-checkbox
                      v-for="(itemCheck, indexC) in showItems.filter(i => i.prop)"
                      :key="indexC"
                      :disabled="itemCheck.disabled"
                      :label="itemCheck.prop"
                    >
                      {{ itemCheck.label }}
                    </el-checkbox>
                  </el-checkbox-group>
                </div>
                <el-button
                  slot="reference"
                  :icon="item.icon"
                  :type="item.type"
                >{{ item.name || '筛选列' }}
                </el-button>
              </el-popover>
            </template>
            <template v-else>
              <span :key="index">
                <el-button
                  :icon="item.icon"
                  :type="item.type"
                  :disabled="Boolean(item.disabled || false)"
                  :loading="item.loading"
                  @click.stop.prevent="item.handleClick"
                >{{ item.name }}
                </el-button>
              </span>
            </template>

          </template>

        </el-button-group>
        <slot name="operate" />
      </div>
    </el-form>
  </div>
</template>
<script>

import { mapGetters } from 'vuex'
import Tinymce from '@/components/Tinymce'
import clipboard from '@/directive/clipboard'

export default {
  directives: {
    clipboard
  },
  components: { Tinymce },
  props: {
    formConfig: {
      type: Object,
      default: () => {
      }
    },
    value: {
      type: Object,
      default: () => {
      }
    },
    width: {
      type: String,
      default: '100px'
    },
    firstDayStart: {
      type: Number,
      default: 1
    },
    loadingForm: {
      type: Boolean,
      default: false
    },
    useHtml: {
      type: Boolean,
      default: false
    },
    columns: {
      type: Array,
      default: () => []
    },
    rules: {
      type: Object,
      default: () => {
      }
    }
  },
  data() {
    return {
      defaultFormThead: [],
      defaultProps: {
        children: 'children',
        label: 'label'
      },
      isSearchLock: true,
      defaultFalse: false,
      power: this.$store.getters.user_info.power,
      pickerOptions: this.def.leftShortDate,
      chapterList: [],
      chapterListLoading: false,
      getChapterList: [],
      chapterContent: '',
      chapterContentLoading: false,
      bookShowVisible: false,
      bookShowWidth: 320,
      bookShowTitle: '',
      chapterChose: '',
      keyField: 'sort_id',
      showField: 'chaptername',
      isshift: false, // 快捷键 shift 是否被按下
      isctrl: false, // 快捷键 ctrl 是否被按下
      doubleCheck: false,
      useToContentLoading: false,
      curBookId: 0,
      curUrl: 0,
      curPlatform: 0,
      checkAll: true,
      checkboxVal: [],
      // 全选按钮的展示状态
      isIndeterminate: false,
      copyColumn: [],
      // 需要展示的列
      showItems: []

    }
  },
  computed: {
    ...mapGetters([
      'system',
      'group'
    ]),
    geoMap() {
      return this.tool.fmtArrayToObj(this.def.geoMap)
    },
    chapterMap() {
      return this.tool.arrayColumn(this.chapterList, 'id', 'sort_id')
    },
    chapterNameMap() {
      return this.tool.arrayColumn(this.chapterList, 'chaptername', 'id')
    },
    sortIds() {
      return this.tool.arrayColumn(this.chapterList, 'sort_id')
    },
    bookShowMulti() {
      return this.isctrl || this.isshift
    }
  },
  watch: {
    'value.content': {
      handler: function(val, oldVal) {
        if (this.tool.isSet(this.$refs, 'tinyContent')) {
          this.$refs.tinyContent[0].setContent(val)
        }
      },
      deep: true
    },
    columns: {
      handler(val, oldVal) {
        // 新数据是老数据的子集 就不用更新
        const newOne = this.tool.arrayColumn(val, 'prop')
        const oldOne = this.tool.arrayColumn(this.copyColumn, 'prop')
        const diff = this.tool.arrayDiff(newOne, oldOne)

        // 表格宽度计算公式：每个字宽度默认为14.5，(元)宽度默认30 + padding左右各20 = 50
        if (diff.length || !this.showItems.length) {
          this.showItems = this.handleItems(val)
          this.handleDefaultHead(this.showItems)
        }
        if (diff.length || !this.copyColumn.length) {
          this.copyColumn = JSON.parse(JSON.stringify(val))
        }
      },
      deep: true,
      immediate: true
    }
  },
  created() {

  },
  mounted() {
    this.$nextTick(() => {
      this.keyDown()
    })
  },
  methods: {
    // 操作自定义列
    handleDefaultHead(data) {
      this.defaultFormThead = data.filter(i => i.prop).map(i => i.prop)
      this.checkboxVal = data.filter(i => i.prop).map(i => i.prop)
    },
    handleItems(val) {
      let arr = []
      val.forEach(item => {
        if (item.child) {
          arr.push(...this.handleItems(item.child))
        } else {
          arr.push(item)
        }
      })

      return arr
    },
    // 判断是否是全选
    handleCheckedChange(value) {
      this.handleHead()
      let checkedCount = value.length
      this.checkAll = checkedCount === this.defaultFormThead.length
      this.isIndeterminate = checkedCount > 0 && checkedCount < this.defaultFormThead.length
    },
    handleCheckAllChange(val) {
      this.checkboxVal = val ? this.defaultFormThead : []
      this.isIndeterminate = false
      this.handleHead()
    },
    handleHead() {
      this.$emit('handle-column', this.handleShowColumn(this.copyColumn))
    },
    // 操作选择隐藏/显示列
    handleShowColumn(columns) {
      let arr = []
      columns = JSON.parse(JSON.stringify(columns))
      columns.forEach(i => {
        if (i.child) {
          let child = this.handleShowColumn(i.child)
          if (child.length) {
            i.child = child
            arr.push(i)
          }
        } else {
          if (!i.prop || this.checkboxVal.indexOf(i.prop) !== -1) {
            arr.push(i)
          }
        }
      })
      return arr
    },
    keyDown() {
      // 键盘按下事件
      document.onkeydown = (e) => {
        // 取消默认事件
        // e.preventDefault()
        // 事件对象兼容
        const e1 = e || event
        // 键盘按键判断:左箭头-37;上箭头-38；右箭头-39;下箭头-40  回车：13   ctrl：17   shift：16
        switch (e1.key) {
          case 'Shift':
            this.isshift = true // 如果shift按下就让他按下的标识符变为true
            break
          case 'Control':
            this.isctrl = true // 如果ctrl按下就让他按下的标识符变为true
            break
        }
      }
      // 键盘抬起事件
      document.onkeyup = (e) => {
        // 取消默认事件
        // e.preventDefault()
        // 事件对象兼容
        const e1 = e || event
        switch (e1.key) {
          case 'Shift':
            this.isshift = false // 如果shift抬起下就让他按下的标识符变为false
            break
          case 'Control':
            this.isctrl = false // 如果ctrl抬起下就让他按下的标识符变为false
            break
        }
      }
    },
    handleImgPreview(file) {
      const accept = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
      if (!this.tool.isInArray(accept, file.raw.type)) {
        this.$message.warning('不支持该类型的素材,请删除并选择正确的图片格式')
      }
    },
    // 子组件校验，传递到父组件
    validateForm() {
      let flag = null
      if (this.isSearchLock) {
        this.$refs['ruleForm'].validate((valid) => {
          const vm = this
          if (valid) {
            flag = true
            vm.isSearchLock = flag
          } else {
            flag = false
            vm.isSearchLock = flag
            this.$message.error('保存信息不完整，请继续填写完整')
            setTimeout(function() {
              vm.isSearchLock = true
            }, 2000)
          }
        })
        return flag
      }
    },
    resetFields() {
      this.$refs['ruleForm'].resetFields()
    },
    getUserByG(range, showAll) {
      return this.tool.getMemberByGType(showAll ? this.$store.getters.allMember : this.$store.getters.member, range)
    },
    async useToContent() {
      this.useToContentLoading = true
      const chapterArr = this.chapterChose.split(',')
      this.content = ''
      await this.getContentByChapterIds(chapterArr)
      this.content = this.getChapterList.join('\n')
      this.useToContentLoading = false
    },
    async getContent(traditional = false) {
      await this.useToContent()
      this.bookShowVisible = false
      const id = this.tool.ObjMGet(this.chapterMap, this.chapterChose.split(','), false)

      let content = this.useHtml ? this.content : this.tool.filterHtml(this.content)
      if (traditional) {
        content = this.tool.traditionalized(content)
        this.bookShowTitle = this.tool.traditionalized(this.bookShowTitle)
      }
      this.$emit('getContentByBook', {
        content: content,
        chapter: id[0].length ? id[0][id[0].length - 1] : 0,
        bookName: this.bookShowTitle,
        chapterName: this.chapterNameMap[id[0]] || ''
      })
    },
    chapterChoseMethods(val) {
      this.doubleCheck = !this.doubleCheck
      if (this.doubleCheck) {
        return
      }

      const chapterArr = this.tool.arrayColumn(val, 'sort_id')
      chapterArr.sort(function(a, b) {
        return a - b
      })
      // 如果按了shift建 则选中所有
      if (this.isshift) {
        let min = 1
        if (chapterArr.length > 1) {
          min = parseInt(chapterArr[0])
        }

        const max = parseInt(chapterArr[chapterArr.length - 1])
        const tmp = []
        for (let index = min; index <= max; index++) {
          if (!this.tool.isInArray(this.sortIds, index)) {
            continue
          }
          tmp.push(index)
        }
        this.chapterChose = tmp.join()
      }
      if (chapterArr.length === 1) {
        this.getContentByChapterIds(chapterArr)
      }
    },
    async getContentByChapterIds(ids) {
      const id = this.tool.ObjMGet(this.chapterMap, ids, false)
      if (!id[0].length) {
        this.$message.warning('未找到章节ID')
        return
      }
      this.chapterContentLoading = true

      await this.request('book/content', {
        chapter_ids: id[0].join(),
        book_id: this.curBookId,
        platform: this.curPlatform,
        url: this.curUrl
      }).then(
        response => {
          this.getChapterList = []
          this.chapterContentLoading = false
          if (id[0].length === 1) {
            this.chapterContent = this.fmtOneParagraph(response.data[0])
          }
          for (const i in response.data) {
            this.getChapterList.push(this.fmtOneParagraph(response.data[i]))
          }
        }
      )
    },
    fmtOneParagraph(row) {
      return '<h3>' + row['chapter_name'] + '</h3>' + row['content']
    },
    fmtPfOption(data) {
      const tmp = [{ 'id': data['positions'], 'label': data['positions'], children: [] }]
      for (const i in data['list']) {
        tmp[0]['children'].push(data['list'][i])
      }
      return tmp
    },
    searchBook() {
      this.bookShowVisible = true
      this.chapterListLoading = true
      const params = {
        book_id: this.value['book'],
        url: this.value['book_url'],
        platform: this.value['book_platform']
      }
      this.request('book/getBookInfo', params).then((res) => {
        const rs = res.data
        this.curBookId = params.book_id
        this.curUrl = params.url
        this.curPlatform = params.platform
        this.chapterListLoading = false
        this.bookShowTitle = rs.book.book_name
        this.chapterList = rs.detail
        this.chapterChose = this.tool.getFirstInArr(this.chapterMap).toString()
        // this.getContentByChapterIds([this.chapterChose])
      })
    }

  }
}
</script>
<style lang="scss" scoped>
.el-form-item__content {
  .el-date-editor--datetimerange {
    width: 100%;
  }
}

.searchBtn {
  text-align: right;
}

.check-box {
  max-height: 350px;
  overflow-y: auto;
}

.check-box .check-flex {
  display: flex;
  flex-wrap: wrap;
}

.el-form-item--small.el-form-item {
  margin-bottom: 5px !important;
}

/deep/ .el-upload {
  display: inline;
  text-align: center;
  cursor: pointer;
  outline: 0;
}

/deep/ .upload-demo {
  display: inline;
}

</style>

