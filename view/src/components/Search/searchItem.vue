<template>
  <div>
    <el-select
      slot="prepend"
      v-model="selectVal"
      multiple
      placeholder="全部包"
    >
      <template v-for="(item,index) in systemInfo">
        <el-option :key="index" :label="item.name" :value="item.platform" />
      </template>
    </el-select>
    <el-button
      slot="append"
      :icon="icon"
      @click="onSave"
    />
  </div>
</template>

<script>

export default {
  props: {
    placeholder: { type: String, default: '全部包' },
    output: { type: String, default: 'onSaveSearchItem' },
    inputType: { type: String, default: 'string' },
    icon: { type: String, default: 'el-icon-search' }
  },
  data() {
    return {
      inputVal: '',
      selectVal: []
    }
  },
  computed: {
    systemInfo() {
      return this.$store.getters.system
    }
  },
  methods: {
    onSave() {
      this.$emit(this.output, this.selectVal)
    }
  }
}
</script>

<style scoped>

</style>
