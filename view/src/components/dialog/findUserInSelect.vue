<template>
  <el-dialog
    :visible.sync="tableShowVisible"
    :close-on-click-modal="false"
    :title="tableTitle"
    append-to-body
    @close="onCancel()"
  >
    <div style="text-align: center">
      <el-select
        v-model="selectVal"
        filterable
        multiple
        placeholder=""
      >
        <el-option
          v-for="item in users"
          :key="item.id"
          :label="item.nickname"
          :value="item.id"
        />
      </el-select>
    </div>
    <div style="text-align:center;margin-top: 10px">
      <el-button type="danger" @click="onCancel">取消</el-button>
      <el-button type="primary" @click="onSave">提交</el-button>
    </div>

  </el-dialog>
</template>

<script>

export default {
  props: {
    tableTitle: { type: String, default: '' },
    range: {
      type: Array, default: function() {
        return []
      }
    },
    showRange: {
      type: Array, default: function() {
        return []
      }
    },
    showAll: { type: Boolean, default: false },
    tableVisible: { type: Boolean, default: false },
    oneData: {
      type: Array, default: function() {
        return []
      }
    }
  },

  data() {
    return {
      memberInfo: [],
      selectVal: []
    }
  },
  computed: {
    tableShowVisible: {
      get() {
        return this.tableVisible
      },
      set(val) {
      }
    },
    users() {
      const all = this.showAll ? this.$store.getters.allMember : this.$store.getters.member
      if (!this.showRange.length) {
        return all
      } else {
        const tmp = []
        for (const i in all) {
          if (this.tool.isInArray(this.showRange, all[i]['power'])) {
            tmp.push(all[i])
          }
        }
        return tmp
      }
    }
  },
  create() {

  },
  watch: {
    oneData: {
      handler: function(val, oldVal) {
        this.selectVal = val
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
    }
  }

}

</script>

<style scoped>

</style>
