<template>
  <div :style="divCss">
    <div style="flex: 0 0 390px">
      <span v-if="showText" style="margin-right: 10px">广告账户</span>
      <el-select
        v-model="selectVal"
        filterable
        style="min-width: 300px"
        placeholder="请选择广告账户"
        :filter-method="dataFilter"
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
    <div v-if="nowAidInfo.spend_cap && !seeInDialog" :style="spendCss">
      <div><span>总预算 :{{ tool.numFormat(nowAidInfo.spend_cap / 100) }}</span></div>
      <div>已消耗 :<span :style="colorShow(nowAidInfo)">{{
        nowAidInfo.use
      }}%</span></div>
    </div>
  </div>
</template>

<script>

import { cookieJs } from '@/utils/auth'
import { mapGetters } from 'vuex'

export default {
  props: {
    seeInDialog: {
      type: Boolean,
      default: false
    },
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
      selectVal: this.$store.getters.nowAid,
      aInfo: this.$store.getters.accounts
    }
  },
  computed: {
    ...mapGetters([
      'accounts',
      'device'
    ]),
    accountInfo: {
      get() {
        return this.aInfo
      },
      set(val) {
      }
    },
    isMobile() {
      return this.device === 'mobile'
    },
    divCss() {
      let css = 'display:flex;'
      if (this.isMobile) {
        css = ''
      }
      return css
    },
    spendCss() {
      let css = 'flex: 1'
      if (this.isMobile) {
        css = 'margin-top:10px'
      }
      return css
    },
    accountMap() {
      const tmp = {}
      for (const i in this.accounts) {
        tmp[this.accounts[i].aid.toString()] = this.accounts[i]
      }
      return tmp
    },
    nowAidInfo() {
      let info = this.accountMap[this.selectVal]
      if (!this.tool.isSet(info, 'spend_cap')) {
        return ''
      }
      info['use'] = info.spend_cap ? (info.amount_spent * 100 / info.spend_cap).toFixed(2) : 0
      return info
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

  },
  created() {
    const query = this.$route.query
    if (!this.seeInDialog && this.tool.isSet(query, 'aid')) {
      if (!this.tool.isSet(this.accountMap, query.aid)) {
        this.$message.warning('无权访问广告账户ID【' + query.aid + '】！')
        return
      }
      this.selectVal = query.aid.toString() || 0
      this.onSave()
    }
  },
  methods: {
    colorShow(info) {
      let color = 'green'
      if (info.use > 80) {
        color = 'red'
      }
      return 'color:' + color
    },
    reset() {
      // setTimeout(function() {
      //   this.aInfo = this.accounts
      // }, 2000)
      // this.$nextTick(() => {
      //   this.aInfo = this.accounts
      // })

    },
    onSave() {
      const info = this.accountMap[parseInt(this.selectVal)]
      cookieJs.setCurAid(info.aid)
      cookieJs.setBid(info.bm_id)
      cookieJs.setCurPlatform(info.platform)
      this.$store.commit('business/SET_BM_ID', info.bm_id)
      this.$store.commit('business/SET_AID', info.aid)
      this.$store.commit('business/SET_PLATFORM', info.platform)
      this.$store.commit('business/SET_AID_TIME_ZONE', info.timezone_offset_hours_utc)
      this.$emit('onSaveSelectAid', info)
    },
    dataFilter(val) {
      this.selectVal = val
      const map = this.accounts
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
      this.aInfo = rs
      if (rs.length === 1) {
        this.selectVal = rs[0].aid
        this.onSave()
      }
    }
  }
}
</script>

<style scoped>

</style>
