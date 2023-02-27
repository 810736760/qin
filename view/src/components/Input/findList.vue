<template>
  <div>

    <el-select
      slot="prepend"
      v-model="selectBm"
      placeholder="请选择BM"
      @change="initSelectAid"
    >
      <template v-for="(item,index) in bmInfo">
        <el-option :key="index" :label="item.bm_name" :value="item.id" />
      </template>
    </el-select>
    <el-select
      v-model="selectAids"
      :placeholder="placeholder"
      collapse-tags
      filterable
      multiple
    >
      <template v-for="(item,index) in accountList">
        <el-option v-if="selectBm === item.bm_id" :key="index" :label="item.name" :value="item.aid.toString()" />
      </template>
    </el-select>

  </div>
</template>

<script>

export default {
  props: {
    placeholder: { type: String, default: '全部广告账户' }
  },
  data() {
    return {
      selectAids: [],
      selectBm: 1
    }
  },
  computed: {
    bmInfo() {
      return this.$store.getters.bm
    },
    accountList() {
      return this.$store.getters.accounts
    }
  },
  methods: {
    initSelectAid() {
      this.selectAids = []
    }
  }
}
</script>

<style scoped>

</style>
