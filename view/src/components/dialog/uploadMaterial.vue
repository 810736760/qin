<template>
  <el-dialog
    :visible.sync="tableShowVisible"
    :close-on-click-modal="false"
    :title="tableTitle"
    @close="onCancel()"
  >
    <el-form
      ref="add_material_form_ref"
      v-loading="uploadLoading"
      :model="add_material_form"
      :rules="add_rules"
      label-width="100px"
    >
      <el-form-item label="偏好" prop="sex">
        <el-radio-group v-model="add_material_form.sex">
          <el-radio label="0">男频</el-radio>
          <el-radio label="1">女频</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item v-if="power!==2" label="指派人" prop="user_ids">
        <el-select

          v-model="add_material_form.user_ids"
          multiple
          filterable
          clearable
        >
          <el-option
            v-for="(j, k) in userList"
            :key="k"
            :label="j.nickname"
            :value="j.id"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="标签">
        <el-select
          v-model="add_material_form.tags"
          multiple
          allow-create
          default-first-option
          filterable
          clearable
        >
          <el-option
            v-for="(j, k) in tagList"
            :key="k"
            :label="j.label"
            :value="j.value"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="素材上传" prop="file">
        <el-upload
          ref="material_upload_ref"
          v-model="add_material_form.file"
          action="#"
          :http-request="uploadFile"
          multiple
          :file-list="fileList"
          :auto-upload="false"
        >
          <el-button size="small" type="primary">点击上传</el-button>
          <div slot="tip" class="el-upload__tip" />
        </el-upload>
      </el-form-item>
    </el-form>
    <div slot="footer" class="dialog-footer">
      <el-button
        @click.stop.prevent="resetAddUploadForm"
      >取 消
      </el-button>
      <el-button
        type="primary"
        @click.stop.prevent="materAdd"
      >确 定
      </el-button>
    </div>

  </el-dialog>
</template>

<script>

import { materialUrl } from '@/api/url'

export default {
  props: {
    tableVisible: { type: Boolean, default: false },
    tableTitle: { type: String, default: '' },
    uploadLoading: { type: Boolean, default: false },
    oneData: {
      type: Object, default: function() {
        return {}
      }
    },
    userList: {
      type: Array, default: function() {
        return []
      }
    },
    tagList: {
      type: Array, default: function() {
        return []
      }
    }
  },

  data() {
    return {
      add_material_form: {},
      selectVal: [],
      users: []
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
    // selectVal: {
    //   get() {
    //     return this.oneData
    //   },
    //   set(val) {
    //   }
    // }

  },
  create() {

  },
  watch: {
    oneData: {
      handler: function(val, oldVal) {
        this.add_material_form = val
      },
      deep: true
    }
  },
  methods: {
    onCancel() {
      this.$emit('onCancelSelect')
      this.selectVal = []
    },
    onSave() {
      this.$emit('onSaveSelect', this.selectVal)
    },
    uploadFile: function(file) {
      this.uploadLoading = true
      var formFile = new FormData()
      const fileObj = file.file
      formFile.append('tags', this.add_material_form.tags)
      formFile.append('sex', this.add_material_form.sex)
      formFile.append('user_ids', this.add_material_form.user_ids)
      formFile.append('file', fileObj) // 加入文件对象
      const nowTime = new Date().toLocaleString()
      this.request(materialUrl, formFile, false, 'post')
        .then((response) => {
          const msg = response.msg
          if (response.code === 200) {
            this.logTableData.unshift({
              name: fileObj.name,
              msg: msg || '上传成功',
              time: nowTime,
              success: 1
            })
          } else {
            const info = response.data.info
            this.logTableData.unshift({
              name: fileObj.name,
              msg: msg,
              info: info,
              time: nowTime,
              success: 0
            })
          }
          this.uploadMaterialVisible = false
          this.fileList = []
          this.logVisible = true
          this.uploadLoading = false
        })
        .catch(() => {
          this.logTableData.unshift({
            name: fileObj.name,
            msg: '未知错误',
            time: nowTime,
            success: 0
          })
          this.uploadMaterialVisible = false
          this.fileList = []
          this.logVisible = true
          this.uploadLoading = false
        })
    }

  }

}

</script>

<style scoped>

</style>
