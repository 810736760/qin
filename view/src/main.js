import Vue from 'vue'

import Cookies from 'js-cookie'

import 'normalize.css/normalize.css' // a modern alternative to CSS resets

import Element from 'element-ui'
import './styles/element-variables.scss'

import '@/styles/index.scss' // global css

import App from './App'
import store from './store'
import router from './router'

import './icons' // icon
import './permission' // 路径校验

import { request } from '@/utils/requestExport' // 封装全局请求
import { Tool } from '@/utils/tool' // 使用全局公用函数
import { definition } from '@/utils/definition' // 封装全局定义
import vSelectMenu from 'v-selectmenu'
import searchBar from '@/components/Search/searchBar'

Vue.use(Element, {
  size: Cookies.get('size') || 'small' // set element-ui default size
})
Vue.use(vSelectMenu)

Vue.config.productionTip = false
Vue.prototype.request = request
Vue.prototype.tool = Tool
Vue.prototype.def = definition

Vue.component('search-bar', searchBar)

new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
