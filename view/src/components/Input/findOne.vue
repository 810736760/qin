<template>
  <div>
    <el-input
      v-model="inputVal"
      :placeholder="placeholder"
      :type="inputType"
      class="input-with-select width-auto"
    >
      <el-select
        v-if="showSelect"
        slot="prepend"
        v-model="selectVal"
        placeholder="请选择BM"
      >
        <template v-for="(item,index) in bmInfo">
          <el-option :key="index" :label="item.bm_name" :value="item.id" />
        </template>
      </el-select>
      <el-button
        slot="append"
        :icon="icon"
        @click="onSave"
      />
    </el-input>
  </div>
</template>

<script>

export default {
  props: {
    placeholder: { type: String, default: '' },
    output: { type: String, default: 'onSaveInputFindOne' },
    inputType: { type: String, default: 'string' },
    icon: { type: String, default: 'el-icon-search' },
    showSelect: { type: Boolean, default: false }
  },
  data() {
    return {
      inputVal: '',
      selectVal: 1
    }
  },
  computed: {
    bmInfo() {
      return this.$store.getters.bm
    }
  },
  methods: {
    onSave() {
      this.$emit(this.output, { 'input': this.inputVal, 'select': this.selectVal })
    }
  }
}
</script>

<style scoped>

</style>
