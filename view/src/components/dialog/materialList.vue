<template>
  <el-dialog
    :visible.sync="tableShowVisible"
    :title="tableTitle"
    @close="onCancel()"
  >

    <material
      ref="material"
      :form-config-props="formConfig"
      :material-type="materialType"
      :form-props="form"
      :see-in-dialog="seeInDialog"
      :ischeckbox-visible="checkboxVisible"
    />
  </el-dialog>
</template>

<script>

import material from '@/views/material'

export default {
  components: {
    material
  },
  props: {
    tableTitle: { type: String, default: '' },
    materialType: { type: Number, default: -1 },
    tableVisible: { type: Boolean, default: false },
    checkboxVisible: {
      type: Boolean,
      default() {
        return false
      }
    }
  },
  data() {
    return {
      form: {
        sex: -1,
        material: '',
        daterange: [Date.now() - 30 * 86400000, Date.now()],
        current_page: 1,
        size: 24,
        collect: 1,
        status: 1
      },
      formConfig: {
        formItemList: [
          [
            {
              type: 'text',
              prop: 'material',
              name: 'material',
              label: '素材',
              placeholder: '素材ID/名称'
            },
            {
              type: 'select',
              prop: 'sex',
              name: 'sex',
              label: '偏好',
              placeholder: '请选择',
              optList: [
                {
                  value: -1,
                  label: '全部'
                },
                {
                  value: '0',
                  label: '男频'
                },
                {
                  value: '1',
                  label: '女频'
                }
              ]
            },
            {
              type: 'select',
              prop: 'language',
              name: 'language',
              label: '语言',
              placeholder: '请选择',
              multiple: 1,
              optList: []
            },
            {
              type: 'select',
              prop: 'tag',
              name: 'tag',
              label: '标签',
              placeholder: '请选择',
              multiple: 1,
              optList: []
            },
            {
              type: 'select',
              prop: 'excluded_tag',
              name: 'excluded_tag',
              label: '排除标签',
              placeholder: '请选择',
              multiple: 1,
              optList: []
            },
            {
              type: 'select',
              prop: 'collect',
              name: 'collect',
              label: '收藏',
              placeholder: '请选择',
              optList: [
                {
                  value: 0,
                  label: '不区分'
                },
                {
                  value: 1,
                  label: '只展示收藏'
                }
              ]
            },
            {
              type: 'selectUser',
              prop: 'creator',
              name: 'creator',
              showAll: true,
              label: '创建人',
              placeholder: '创建人',
              userRange: [
                this.def.role.designer,
                this.def.role.creatorManger,
                this.def.role.creator,
                this.def.role.creatorAssistant
              ]
            },
            {
              type: 'selectUser',
              prop: 'user_id',
              name: 'user_id',
              showAll: true,
              label: '指派人',
              placeholder: '指派人',
              userRange: [
                this.def.role.creatorManger, this.def.role.creator, this.def.role.creatorAssistant
              ]
            }
          ],
          [{
            type: 'daterange',
            name: 'daterange',
            label: '上传时间',
            prop: 'daterange',
            dateFormate: 'timestamp'
          }]
        ],

        operate: [
          // {
          //   type: 'danger',
          //   icon: 'el-icon-back',
          //   name: '取消',
          //   handleClick: this.onCancel
          // },
          {
            type: 'primary',
            icon: 'el-icon-search',
            name: '查询',
            handleClick: this.search
          },
          {
            type: 'primary',
            icon: 'el-icon-refresh-right',
            name: '重置',
            handleClick: this.reset
          },
          {
            type: 'primary',
            icon: 'el-icon-price-tag',
            name: '标签管理',
            handleClick: this.handleTag
          },
          {
            type: 'primary',
            icon: 'el-icon-caret-bottom',
            name: '下一页',
            handleClick: this.loadNextPage
          },
          {
            type: 'warning',
            icon: 'el-icon-right',
            name: '确定',
            handleClick: this.onSave
          }

        ]
      },
      seeInDialog: true
    }
  },
  computed: {
    tableShowVisible: {
      get() {
        return this.tableVisible
      },
      set(val) {
      }
    }

  },
  create() {

  },
  watch: {},
  methods: {
    showChek() {
      this.$refs.material.showCheck()
    },
    onCancel() {
      this.reset()
      this.$emit('onCancelMaterial')
      this.$refs.material.clearMaterialChecked()
      this.selectVal = []
    },
    onSave() {
      this.reset()
      const list = this.$refs.material.material_checked
      const tmp = []
      for (const i in list) {
        tmp.push(this.$refs.material.materialsKeyMap[list[i]])
      }
      this.$emit('onSaveMaterial', tmp)
      this.$refs.material.clearMaterialChecked()
    },
    search() {
      this.$refs.material.search()
    },
    reset() {
      this.$refs.material.reset()
    },
    handleTag() {
      this.$refs.material.handleTag()
    },
    loadNextPage() {
      this.$refs.material.loadNextPage()
    }

  }

}

</script>

<style scoped>
</style>
