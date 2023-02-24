<template>
  <teacher_class
    ref="tc"
    :searchConfigProps="formConfig"
    :searchFormProps="form"
    :see-in-dialog="seeInDialog"
  />
</template>

<script>

import teacher_class from '@/views/stat/teacher_class'

export default {
  components: {
    teacher_class
  },
  props: {},
  data() {
    return {
      seeInDialog: 1,
      formConfig: {
        formItemList: [
          [
            {
              type: 'text',
              prop: 'teacher_name',
              name: 'teacher_name',
              label: '姓名',
              placeholder: '请输入姓名',
              width: 8,
            },
            {
              type: 'text',
              prop: 'tel',
              name: 'tel',
              label: '手机号',
              placeholder: '请输入手机号',
              width: 8,
            },
          ]
        ],
        operate: [
          {
            type: 'primary',
            icon: 'el-icon-search',
            name: '查询',
            loading: false,
            handleClick: this.getTable
          }
        ]
      },
      form: {
        teacher_name: '',
        tel: '',
      },

    }
  },
  computed: {},
  create() {

  },
  watch: {},
  methods: {
    getTable() {
      this.formConfig.operate[0].loading = true
      this.$refs.tc.getTable().then(() => {
        this.formConfig.operate[0].loading = false
      }).catch(() => {
        this.formConfig.operate[0].loading = false
      })
    }


  }

}

</script>

<style scoped>
</style>
