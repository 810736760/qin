<template>
  <div class="app-container">
    <el-form
      ref="formValidate"
      :model="dataFrom"
      label-width="100px"
      label-position="left"
      :rules="ruleValidate"
    >
      <el-form-item label="当前密码" prop="old">
        <el-input v-model="dataFrom.old" placeholder="当前密码" type="password" style="width: auto" />
      </el-form-item>
      <el-form-item label="新密码" prop="new">
        <el-input v-model="dataFrom.new" placeholder="当前密码" type="password" style="width: auto" />
      </el-form-item>

      <el-form-item label="新密码确认" prop="confirm">
        <el-input v-model="dataFrom.confirm" placeholder="新密码确认" type="password" style="width: auto" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="el-icon-setting" :loading="confirmLoading" @click="confirmBtn">确定</el-button>
      </el-form-item>

    </el-form>

  </div>
</template>

<script>

export default {
  data() {
    const pwdAgainCheck = async(rule, value, callback) => {
      if (!value) {
        return callback(new Error('password is required'))
      }
      if (value.length < 1) {
        return callback(new Error('重复密码不能为空！'))
      } else if (this.dataFrom.new !== this.dataFrom.confirm) {
        return callback(new Error('两次输入密码不一致！'))
      } else {
        callback()
      }
    }
    return {
      dataFrom: {},
      showEdit: true,
      confirmLoading: false,
      ruleValidate: {
        confirm: [
          { required: true, validator: pwdAgainCheck, trigger: 'blur' }
        ],
        old: [
          { required: true, trigger: 'blur' }
        ],
        new: [
          { required: true, trigger: 'blur' }
        ]

      }
    }
  },
  computed: {},
  created() {

  },
  methods: {
    confirmBtn() {
      this.$refs['formValidate'].validate((valid) => {
        if (valid) {
          this.confirmLoading = true
          this.request('editPassword',
            {
              'old': this.dataFrom.old,
              'new': this.dataFrom.new
            }
          ).then(
            response => {
              this.confirmLoading = false
              this.$message.success('已修改')
              this.$store.dispatch('user/resetToken')
            }
          )
        } else {
          this.$message.error('请检查输入参数!')
        }
      })
    }

  }
}
</script>

<style lang="scss" scoped>

</style>
