<template>
  <div>
    <span v-if="showText" style="margin-right: 10px">广告账户</span>
    <el-select
      v-model="selectVal"
      :filter-method="dataFilter"
      filterable
      style="min-width: 300px"
      placeholder="请选择广告账户"
      @change="onSave"
      @blur="reset"
    >
      <el-option
        v-for="(item,index) in accountInfo"
        :key="index"
        :label="item.name"
        :value="item.aid"
      />
    </el-select>
  </div>
</template>

<script>

import { cookieJs } from '@/utils/auth'
import { mapGetters } from 'vuex'

export default {
  props: {
    showText: {
      type: Boolean,
      default: true
    },
    showAid: {
      type: [Number, String],
      default: 0
    }
  },
  data() {
    return {
      selectVal: this.$store.getters.nowTiktokAid,
      aInfo: this.$store.getters.tiktokAccounts
    }
  },
  computed: {
    ...mapGetters([
      'tiktokAccounts'
    ]),
    accountInfo: {
      get() {
        return this.aInfo
      },
      set(val) {
      }
    },
    accountMap() {
      const tmp = {}
      for (const i in this.tiktokAccounts) {
        tmp[this.tiktokAccounts[i].aid] = this.tiktokAccounts[i]
      }
      return tmp
    }
  },
  watch: {
    showAid: {
      handler: function(val, oldVal) {
        this.selectVal = val.toString() || this.selectVal
      },
      deep: true
    }
  },
  mounted() {
    const query = this.$route.query
    if (this.tool.isSet(query, 'tkAid')) {
      if (!this.tool.isSet(this.accountMap, query.tkAid)) {
        this.$message.warning('无权访问广告账户ID【' + query.tkAid + '】！')
        return
      }
      this.selectVal = (query.tkAid || 0).toString()
      this.onSave()
    }
  },
  methods: {
    onSave() {
      const info = this.accountMap[this.selectVal]
      cookieJs.setCurTiktokAid(info.aid)

      this.$store.commit('business/SET_TIKTOK_AID', info.aid)

      this.$store.commit('business/SET_TT_AID_TIME_ZONE', info.timezone_offset_hours_utc)
      this.$emit('onSaveSelectTkAid', info)
    },
    reset() {
      // setTimeout(function() {
      //   this.aInfo = this.tiktokAccounts
      // }, 2000)
      // this.$nextTick(() => {
      //   this.aInfo = this.tiktokAccounts
      // })
    },
    dataFilter(val) {
      this.selectVal = val
      const map = this.tiktokAccounts
      let rs = []
      if (val) { // val存在
        rs = map.filter((item) => {
          if (!!~item.name.toLowerCase().indexOf(val.toLowerCase()) || !!~(item.aid).toString().indexOf(val)) {
            return true
          }
        })
      } else { // val为空时，还原数组
        rs = map
      }
      if (rs.length === 1) {
        this.selectVal = rs[0].aid
        this.onSave()
      }
      this.aInfo = rs
    }
  }
}
</script>

<style scoped>

</style>
