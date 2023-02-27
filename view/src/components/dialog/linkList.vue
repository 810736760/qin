<template>
  <el-dialog
    :visible.sync="tableShowVisible"
    :title="tableTitle"

    @close="onCancel()"
  >

    <Link
      ref="link"
      :book-id="bookId"
      :search-config-props="searchConfig"
      :search-form-props="formTable"
      :table-items="tableItems"
      :is-from-dialog="true"
      @useLink="useLink"
    />
  </el-dialog>
</template>

<script>

import Link from '@/views/setting/link'

export default {
  components: {
    Link
  },
  props: {
    tableTitle: { type: String, default: '' },
    tableVisible: { type: Boolean, default: false },
    bookId: { type: [Number, String], default: 0 },
    formTable: {
      type: Object,
      default: function() {
        return {}
      }
    }
  },
  data() {
    return {
      searchConfig: {
        formItemList: [
          [

            {
              type: 'text',
              prop: 'book_id',
              name: 'book_id',
              label: '书籍ID'

            }

          ]],

        operate:
          [
            {
              type: 'success',
              icon: 'el-icon-plus',
              name: '创建',
              handleClick: this.createBtn
            },
            {
              type: 'primary',
              icon: 'el-icon-search',
              name: '查询',
              handleClick: this.getTable
            }
          ]
      },
      tableItems: [
        { 'label': 'id', 'prop': 'link_id' },
        { 'label': '链接名称', 'prop': 'name' },
        { 'label': '(书本id)书本/章节', 'prop': 'book_info' }
      ]
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

  },
  create() {
  },
  watch: {},
  methods: {
    onCancel() {
      this.$emit('onCancelLink')
    },
    createBtn() {
      this.$refs.link.createBtn()
    },
    getTable() {
      this.$refs.link.getTable()
    },
    useLink(link) {
      this.$emit('useLink', link)
    }

  }

}

</script>

<style scoped>
</style>
