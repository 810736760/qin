<template>
  <div v-if="showDiv">
    <el-radio-group v-model="platform" @change="changPlatform">
      <el-radio-button label="1">Facebook</el-radio-button>
      <el-radio-button v-if="showTiktok" label="2" :disabled="disabledTT">Tiktok</el-radio-button>
      <el-radio-button v-if="showLine" label="3">Line</el-radio-button>
      <el-radio-button v-if="showGoogle" label="4">Google</el-radio-button>
    </el-radio-group>
  </div>
</template>

<script>

import { mapGetters } from 'vuex'

export default {
  props: {
    output: { type: String, default: 'onChangPlatform' },
    lineShow: { type: Boolean, default: false },
    disabledTT: { type: Boolean, default: false }
  },
  data() {
    return {
      platform: 1
    }
  },
  computed: {
    ...mapGetters([
      'tiktokShow',
      'googleShow'
    ]),
    showTiktok() {
      return this.tiktokShow
    },
    showGoogle() {
      return this.googleShow
    },
    showLine() {
      return this.lineShow
    },
    showDiv() {
      return this.showTiktok || this.showGoogle
    }
  },
  methods: {
    changPlatform() {
      this.$emit(this.output, parseInt(this.platform))
    }
  }
}
</script>

<style scoped>

</style>
