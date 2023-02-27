<template>
  <div class="app-container">
    <el-form
      ref="dataFromRef"
      :model="dataFrom"
      :rules="dataFromRules"
      label-width="80px"
      label-position="left"
    >
      <el-form-item label="用户名">
        {{ dataFrom.nickname }}
      </el-form-item>
      <el-form-item label="邮箱">
        <el-row v-if="showEdit">
          {{ dataFrom.email }}
          <el-button type="warning" icon="el-icon-edit" circle @click="showEdit=false" />
        </el-row>
        <el-row v-else>
          <el-input v-model="dataFrom.email" placeholder="邮箱" style="width: auto;margin-right: 20px" />
          <el-button type="warning" icon="el-icon-phone-outline" :loading="cancelLoading" @click="testEmail">测试
          </el-button>
          <el-button type="primary" icon="el-icon-edit" :loading="updateLoading" @click="updateEmail">更新</el-button>
          <el-button type="default" icon="el-icon-back" @click="showEdit=true">取消</el-button>
        </el-row>
      </el-form-item>

      <el-form-item label="手机" prop="tel">
        <el-row v-if="showEditTel">
          {{ dataFrom.tel }}
          <el-button type="warning" icon="el-icon-edit" circle @click="showEditTel=false" />
        </el-row>
        <el-row v-else>
          <el-input v-model="dataFrom.tel" placeholder="邮箱" style="width: auto;margin-right: 20px" />
          <el-button type="primary" icon="el-icon-edit" :loading="updateLoading" @click="updateTel">更新</el-button>
          <el-button type="default" icon="el-icon-back" @click="showEditTel=true">取消</el-button>
        </el-row>
      </el-form-item>

      <el-form-item label="角色">
        {{ dataFrom.role_name }}
      </el-form-item>

    </el-form>

  </div>
</template>

<script>

export default {
  data() {
    const checkMobile = (rule, value, cb) => {
      // 验证手机号的正则表达式
      const regMobile = this.def.regexMap.tel
      if (regMobile.test(value)) {
        return cb()
      }
      cb(new Error('请输入合法的手机号'))
    }
    return {
      dataFrom: {},
      dataFromRules: {
        tel: [
          {
            validator: checkMobile,
            message: '请输入正确的手机号码',
            trigger: 'blur'
          }
        ]
      },
      showEdit: true,
      updateLoading: false,
      cancelLoading: false,
      showEditTel: true
    }
  },
  computed: {},
  created() {
    this.getUsers()
  },
  methods: {
    getUsers() {
      this.dataFrom = this.$store.getters.user_info
    },
    testEmail() {
      if (!this.dataFrom.email) {
        this.$message.error('请输入邮箱地址')
        return
      }
      this.cancelLoading = true
      this.request('adm/testEmail', { 'email': this.dataFrom.email }).then(
        response => {
          this.$message.success('已发送')
          this.cancelLoading = false
        }
      )
    },
    updateEmail() {
      if (!this.dataFrom.email) {
        this.$message.error('请输入邮箱地址')
        return
      }
      this.updateFunc({ 'email': this.dataFrom.email, 'admin_id': this.dataFrom.id })
    },
    updateTel() {
      if (!this.dataFrom.tel) {
        this.$message.error('请输入手机')
        return
      }
      this.$refs['dataFromRef'].validate((valid) => {
        if (valid) {
          this.updateFunc({ 'tel': this.dataFrom.tel, 'admin_id': this.dataFrom.id })
        }
      }
      )
    },
    updateFunc(data) {
      const _that = this
      _that.updateLoading = true
      this.request('adm/changeEmail',
        data
      ).then(
        response => {
          _that.updateLoading = false
          _that.showEdit = true
          _that.$message.success('已修改')
        }
      )
    }
  }
}
</script>

<style lang="scss" scoped>

</style>
