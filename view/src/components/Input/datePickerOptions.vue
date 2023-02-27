<template>
  <div>
    <el-date-picker
      v-model="datePicker"
      class="mobile_editor"
      style="margin-right: 5px"
      type="daterange"
      unlink-panels
      :picker-options="pickerOptions"
      start-placeholder="开始日期"
      end-placeholder="结束日期"
      align="left"
      value-format="timestamp"
      @focus="elDatePickerOnFocus"
    />
    <el-button type="primary" icon="el-icon-search" circle @click="onSave" />
  </div>
</template>

<script>

export default {
  props: {
    aidTimezone: {
      type: Number, default: 8
    },
    defaultDatePick: {
      type: Array, default: function() {
        return [
          Date.now() - (this.tool.curTimezone() - this.aidTimezone) * 3600000,
          Date.now() - (this.tool.curTimezone() - this.aidTimezone) * 3600000]
      }
    }
  },
  data() {
    return {
      datePicker: this.defaultDatePick,
      defaultTrue: true,
      pickerOptions: this.def.leftShortDate
    }
  },
  computed: {},
  watch: {
    defaultDatePick: {
      handler: function(val, oldVal) {
        this.datePicker = val
      },
      deep: true
    },
    aidTimezone: {
      handler: function(val, oldVal) {
        this.datePicker = [
          Date.now() - (this.tool.curTimezone() - val) * 3600000,
          Date.now() - (this.tool.curTimezone() - val) * 3600000
        ]
      },
      deep: true
    }
  },
  methods: {
    onSave() {
      this.$emit('onSaveDatePicker', this.datePicker)
    },
    elDatePickerOnFocus() {
      document.activeElement.blur()
    }
  }
}
</script>

<style scoped>

</style>
