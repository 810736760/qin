<template>
  <div class="popup-box">
    <el-select
      show-search
      :value="value"
      placeholder="搜索和筛选"
      style="width: 100%"
      :default-active-first-option="false"
      :show-arrow="false"
      :filter-option="false"
      :not-found-content="null"
      @search="handleSearch"
      @change="handleChange"
    >
      <el-option v-for="item in data" :key="item.id" :value="item.id">
        {{ item.name }}
      </el-option>
    </el-select>

    <!-- 弹窗 -->
    <el-dialog
      :title="cont"
      :visible="isShowModal"
      :closable="false"
      cancel-text="取消"
      ok-text="应用"
      @ok="handleOk"
      @cancel="handleCancel"
    >
      <div>
        <el-radio-group v-model="radioValue">
          <el-radio :value="1">包含</el-radio>
          <el-radio :value="2">不包含</el-radio>
        </el-radio-group>
        <el-input v-model="inputValue" class="modle-inp" />
      </div>
    </el-dialog>
  </div>
</template>

<script>

export default {
  props: {
    // formConfig: {
    //   type: Object,
    //   default: () => {
    //   }
    // },
    // value: {
    //   type: Object,
    //   default: () => {
    //   }
    // },
    // rules: {
    //   type: Object,
    //   default: () => {
    //   }
    // }
  },
  data() {
    return {
      // isSearchLock: true,
      // power: this.$store.getters.user_info.power,
      // pickerOptions: this.def.leftShortDate
      value: '',
      radioValue: 1,
      inputValue: '',
      cont: '',
      isShowModal: false,
      data: [
        {
          id: 1,
          name: '模拟数据1'
        },
        {
          id: 2,
          name: '模拟数据2'
        },
        {
          id: 3,
          name: '模拟数据3'
        },
        {
          id: 4,
          name: '模拟数据4'
        },
        {
          id: 5,
          name: '模拟数据5'
        }
      ]
    }
  },
  watch: {},
  created() {

  },
  mounted() {
  },
  methods: {
    // 子组件校验，传递到父组件
    // validateForm() {
    //   let flag = null
    //   if (this.isSearchLock) {
    //     this.$refs['ruleForm'].validate((valid) => {
    //       const vm = this
    //       if (valid) {
    //         flag = true
    //         vm.isSearchLock = flag
    //       } else {
    //         flag = false
    //         vm.isSearchLock = flag
    //         this.$message.error('保存信息不完整，请继续填写完整')
    //         setTimeout(function() {
    //           vm.isSearchLock = true
    //         }, 2000)
    //       }
    //     })
    //     return flag
    //   }
    // },
    // resetFields() {
    //   this.$refs['ruleForm'].resetFields()
    // },
    // getUserByG(range, showAll) {
    //   return this.tool.getMemberByGType(showAll ? this.$store.getters.allMember : this.$store.getters.member, range)
    // }
    handleSearch(value) {
      // 当输入值发生变化时，可相应数据
      console.log(value)
    },
    handleChange(value) {
      this.value = value
      this.cont = this.data.filter(item => item.id === value)[0].name
      this.isShowModal = true
    },
    handleOk() {
      const { inputValue, radioValue } = this
      if (!inputValue) return this.$toast.warning('请填写内容~')
      this.$message.success(
        `你选择了${radioValue === 1 ? ' 包含 ' : ' 不包含 '}，内容为${inputValue}`
      )
      this.handleCancel()
    },
    handleCancel() {
      this.inputValue = ''
      this.radioValue = 1
      this.isShowModal = false
    }
  }

}
</script>

<style lang="less" scoped>
.popup-box {
  width: 600px;
  margin: 20px auto;
}

.modle-inp {
  margin-top: 15px;
}
</style>
