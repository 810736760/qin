import { Message } from 'element-ui'
import { definition } from '@/utils/definition'

const Tool = {}

/**
 * 将时间戳转化为浏览器所在时间
 * @param utime
 * @param fmt
 * @returns {string}
 */
Tool.unixTimeToTimeStr = function(utime, fmt = 'yyyy-MM-dd') {
  const intTime = Tool.fmtTimestamp(utime)
  if (!intTime) {
    return ''
  }
  const dd = new Date()
  dd.setTime(intTime)
  return dd.Format(fmt)
}

Tool.curTimezone = function() {
  return -(new Date().getTimezoneOffset() / 60)
}

/**
 *  把秒数转化为时分秒
 * @param mss
 * @returns {*}
 */
Tool.fmt_hms = function(mss) {
  mss = mss % 86400
  let hours = parseInt((mss / 3600))
  if (hours < 10) {
    hours = '0' + hours
  }
  mss = mss % 3600
  let minutes = parseInt((mss / 60))
  if (minutes < 10) {
    minutes = '0' + minutes
  }
  // let seconds = mss % (60)
  // if (seconds < 10) {
  //   seconds = '0' + seconds
  // }
  return hours + ':' + minutes
}
/**
 * 在对象/数组中Obj找值为Value的键值
 * @param value
 * @param obj
 * @param compare
 * @returns {string | undefined}
 */
Tool.findKey = (value, obj, compare = (a, b) => a === b) => {
  return Object.keys(obj).find(k => compare(obj[k], value))
}

/**
 * 统一展示message消息
 * @param content_message
 * @param showType
 */
Tool.showMessage = function(content_message, showType = 'success') {
  Message({
    message: content_message,
    type: showType
  })
}

// 把整数转化千分位
Tool.IntToThousands = function(num) {
  num = (num || 0).toString()
  let result = ''

  while (num.length > 3) {
    result = ',' + num.slice(-3) + result
    num = num.slice(0, num.length - 3)
  }

  if (num) {
    result = num + result
  }

  return result.replace(/^-,/g, '-')
}

// 把浮点数转化千分位
Tool.floatToThousands = function(num) {
  num = parseFloat(num)
  let result = (num.toFixed(2) + '').replace(/\d{1,3}(?=(\d{3})+(\.\d*)?$)/g, '$&,')

  result = result.replace(/^-,/g, '-')

  return result
}

// 为数字加千分位符
Tool.toTh = function(num) {
  num = (num || 0).toString()

  if (num.indexOf('.') < 0) { // 整数
    return Tool.IntToThousands(num)
  } else { // 浮点数
    return Tool.floatToThousands(num)
  }
}

// 为数字去掉千分位符,该函数是toTh的逆向运算
Tool.unTh = function(str) {
  return Number(str.replace(/,/g, ''))
}
/**
 * fillArgs 用法
 * @returns {String}
 */
// eslint-disable-next-line no-extend-native
String.prototype.fillArgs = function() {
  let formated = this
  for (let i = 0; i < arguments.length; i++) {
    const param = '\{' + i + '\}'
    formated = formated.replace(param, arguments[i])
  }
  return formated
}

/**
 *
 * @param fmt
 * @returns {*}
 * @constructor
 * 日期格式化
 */
// eslint-disable-next-line no-extend-native
Date.prototype.Format = function(fmt) { // author: meizz
  const o = {
    'M+': this.getMonth() + 1, // 月份
    'd+': this.getDate(), // 日
    'h+': this.getHours(), // 小时
    'm+': this.getMinutes(), // 分
    's+': this.getSeconds(), // 秒
    'q+': Math.floor((this.getMonth() + 3) / 3), // 季度
    'S': this.getMilliseconds() // 毫秒
  }
  if (/(y+)/.test(fmt)) {
    fmt = fmt.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length))
  }
  for (const k in o) {
    if (new RegExp('(' + k + ')').test(fmt)) {
      fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (('00' + o[k]).substr(('' + o[k]).length)))
    }
  }
  return fmt
}

/**
 * 检测数组是否有某个值
 * @param arr
 * @param val
 * @returns {boolean}
 * @constructor
 */
Tool.isInArray = function(arr, val) {
  const testStr = ',' + arr.join(',') + ','
  return testStr.indexOf(',' + val + ',') !== -1
}
/**
 *  判断数组是否有该键值 无则返回val
 * @param arr
 * @param val
 * @param defaultValue
 * @returns {*}
 */
Tool.isSet = function(arr, val, defaultValue = '') {
  if (!arr) {
    return defaultValue
  }
  return Object.prototype.hasOwnProperty.call(arr, val) ? arr[val] : defaultValue
}
/**
 * 键是否存在
 * @param arr
 * @param val
 * @returns {boolean|boolean}
 */
Tool.isKeyExist = function(arr, val) {
  return Tool.empty(arr) ? false : Object.prototype.hasOwnProperty.call(arr, val)
}

Tool.isMSet = function(arr, keys, defaultValue = '') {
  if (Tool.empty(arr) || Tool.empty(keys)) {
    return {}
  }
  let temp = {}
  for (const i in keys) {
    temp[keys[i]] = Tool.isSet(arr, keys[i], defaultValue)
  }
  return temp
}

Tool.getOs = function(url) {
  if (!url) {
    return ''
  }
  for (const i in definition.os) {
    if (url.indexOf(definition.os[i]['type']) !== -1) {
      return definition.os[i]['type']
    }
  }
  return ''
}

Tool.getDevice = function(device, val = 'Android') {
  let tmp = val
  if (!device) {
    return tmp
  }

  for (const i in definition.device) {
    if (device.indexOf(definition.device[i]['type']) === -1) {
      continue
    }
    tmp = definition.device[i]['type']
  }
  return tmp
}

Tool.getOsUrl = function(list, appId, os) {
  if (Tool.empty(list)) {
    return ''
  }
  if (os === 'google') {
    os = 'google_play'
  }
  for (const i in list) {
    if (list[i]['id'] === appId) {
      return Tool.isSet(list[i]['object_store_urls'], os)
    }
  }
  return ''
}
Tool.getAppListByOs = function(list, os) {
  if (Tool.empty(list) || Tool.empty(os)) {
    return list
  }
  if (os === 'google') {
    os = 'google_play'
  }
  const tmp = []
  for (const i in list) {
    if (Tool.isSet(list[i]['object_store_urls'], os)) {
      tmp.push(list[i])
    }
  }
  return tmp
}

Tool.getTTAppListByOs = function(list, os) {
  const temp = []
  for (const i in list) {
    const platform = list[i]['platform']
    if (!os || !Tool.isIncludeBy(os, platform)) {
      continue
    }
    temp.push(list[i])
  }
  return temp
}

Tool.getVer = function(device, os) {
  if (!device) {
    return ['5.0', 'above']
  }
  const arr = device.split('_')
  let tmp = []
  for (const i in arr) {
    if (Tool.isInArray([os, 'and', 'ver', 'to'], arr[i])) {
      continue
    }
    tmp.push(arr[i])
  }

  if (!tmp.length && os === 'Android') {
    tmp = ['2.0', 'above']
  }
  return tmp
}

/**
 * 数据去重
 * @param arr
 * @returns {any[]}
 */
Tool.unique = function(arr) {
  const x = new Set(arr)
  return [...x]
}

/**
 * 返回数组第一个值
 * @param arr
 * @returns {*}
 */
Tool.getFirstInArr = function(arr) {
  if (Array.isArray(arr)) {
    if (arr.length === 0) {
      return []
    }
    return arr[0]
  } else if (typeof (arr) === 'object') {
    const keyArr = Object.keys(arr)
    return Tool.getFirstInArr(keyArr)
  }
}

/**
 * 把字符串转为数组
 * @param str
 * @param sign
 * @returns {*}
 */
Tool.string2Arr = function(str, sign) {
  if (!str) {
    return []
  }
  return str.split(sign)
}
/**
 * 把对象转为数组
 * @param obj
 * @returns {Array}
 */
Tool.obj2Arr = function(obj) {
  if (obj.length === 0) {
    return []
  }
  const tmp = []
  for (const i in obj) {
    tmp.push(obj[i])
  }
  return tmp
}

/**
 * 补位
 * @param num  --数字
 * @param n  -- 长度
 * @returns {string}
 * @constructor
 */
Tool.PrefixInteger = function(num, n) {
  return (Array(n).join(0) + num).slice(-n)
}
/**
 *  将DatePicker的格式转化为"YYYY-MM-DD HH:ii:ss"
 * @param fmt
 * @returns {string}
 */
Tool.getTimeStampFromDatePicker = function(fmt) {
  const o = {
    'Y': fmt.getYear() + 1900,
    'M': fmt.getMonth() + 1, // 月份
    'd': fmt.getDate(), // 日
    'h': fmt.getHours(), // 小时
    'm': fmt.getMinutes(), // 分
    's': fmt.getSeconds() // 秒
  }
  let fmtStr = ''
  for (const i in o) {
    if (i !== 'Y') {
      o[i] = Tool.PrefixInteger(o[i], 2)
    }

    fmtStr += o[i]
    if (i === 'd') {
      fmtStr += ' '
    } else if (Tool.isInArray(['Y', 'M'], i)) {
      fmtStr += '-'
    } else if (Tool.isInArray(['h', 'm'], i)) {
      fmtStr += ':'
    }
  }
  return fmtStr
}

/**
 * // 时间戳 转换成 中国标准时间
 *将时间戳转化为DatePicker支持的格式 默认10位长度
 * @param nS
 * @returns {Date}
 */
Tool.getDateFromTimeStamp = function(nS) {
  return new Date(Number(nS) * 1000)
}
// 中国标准时间 转换成 时间戳
Tool.getTimeStampFromDate = function(time, showTen = true) {
  return new Date(time).getTime() / 1000
}

/**
 * 根据时间戳长度 获取13位的时间戳
 * @param ns
 * @returns {number}
 */
Tool.fmtTimestamp = function(ns) {
  if (!ns) {
    return 0
  }
  const nsLen = ns.toString().length
  if (nsLen === 10) {
    return Number(ns) * 1000
  }
  return Number(ns)
}

Tool.getNowMonthFirst = () => {
  const date = new Date()
  date.setDate(1)
  return date
}

// 将22:30:34格式的时间转为秒数
Tool.getSecondsByTimeStr = function(timeStr) {
  const num_arr = timeStr.split(':')
  let seconds = 0
  if (num_arr.length === 3) {
    seconds = parseInt(num_arr[0]) * 3600 + parseInt(num_arr[1]) * 60 + parseInt(num_arr[2])
  } else if (num_arr.length === 2) {
    seconds = parseInt(num_arr[0]) * 3600 + parseInt(num_arr[1]) * 60
  }

  return seconds || 0
}

// 将秒数转为22:30:24形式的字串
Tool.getTimeStrBySeconds = function(seconds) {
  seconds = parseInt(seconds)
  if (isNaN(seconds)) {
    return ''
  }
  let hours = Math.floor(seconds / 3600)
  if (hours < 10) {
    hours = '0' + hours
  }
  let minutes = Math.floor((seconds % 3600) / 60)

  if (minutes < 10) {
    minutes = '0' + minutes
  }

  let sec = seconds % 60
  if (sec < 10) {
    sec = '0' + sec
  }

  return hours + ':' + minutes + ':' + sec
}

/**
 * 将数组['type':A,'name':B]转化 obj.A = B
 * @param obj
 * @param type
 * @param name
 */
Tool.fmtArrayToObj = function(obj,
  type = 'type', name = 'name') {
  if (Tool.empty(obj)) {
    return
  }
  const tmp = {}
  for (const i in obj) {
    tmp[obj[i][type]] = obj[i][name]
  }
  return tmp
}

Tool.fmtObjToArray = function(obj,
  type = 'type', name = 'name') {
  if (Tool.empty(obj)) {
    return
  }
  const tmp = []
  for (const i in obj) {
    tmp.push({ [type]: i, [name]: obj[i] })
  }
  return tmp
}

/**
 * 将对象[A:[1,2]]转化 {1:A,2:A}
 */

Tool.fmtValueObjToKey = function(obj) {
  if (Tool.empty(obj)) {
    return {}
  }
  const tmp = {}
  for (const i in obj) {
    for (const j in obj[i]) {
      tmp[obj[i][j]] = i
    }
  }
  return tmp
}

/**
 * 获取前{0}的日期 20190101
 * @param date
 * @returns {string}
 */

Tool.getDateBefore = function(date) {
  date = date || 0

  let timestamp = new Date()
  timestamp = timestamp.getTime()
  const newDate = new Date(timestamp - date * 24 * 3600 * 1000)
  const fullYear = newDate.getFullYear().toString()
  let month = (newDate.getMonth() + 1).toString()
  if (month < 10) {
    month = '0' + month
  }
  let day = newDate.getDate().toString()
  if (day < 10) {
    day = '0' + day
  }

  return fullYear + month + day
}

/**
 *  把秒数转化为时分秒 只取最大值
 * @param mss
 * @param transfer
 * @returns {*}
 */
Tool.fmtSeconde2HMS = function formatDuring(mss, transfer) {
  const days = parseInt(mss / 86400)
  if (days >= 1) {
    return days + Tool.matchKeyInArr(transfer, 'd')
  }
  mss = mss % 86400
  const hours = parseInt((mss / 3600))
  if (hours >= 1) {
    return hours + Tool.matchKeyInArr(transfer, 'h')
  }
  mss = mss % 3600
  const minutes = parseInt((mss / 60))
  if (minutes >= 1) {
    return minutes + Tool.matchKeyInArr(transfer, 'm')
  }
  const seconds = mss % (60)
  return seconds + Tool.matchKeyInArr(transfer, 's')
}

Tool.nFormatter = function(num, digits) {
  const si = [
    { value: 1, symbol: '' },
    { value: 1E3, symbol: 'K' },
    { value: 1E6, symbol: 'M' },
    { value: 1E9, symbol: 'B' },
    { value: 1E12, symbol: 'T' },
    { value: 1E15, symbol: 'P' },
    { value: 1E18, symbol: 'E' }
  ]
  num = parseInt(num)
  const absNum = Math.abs(num)
  const rx = /\.0+$|(\.[0-9]*[1-9])0+$/
  let i
  for (i = si.length - 1; i > 0; i--) {
    if (absNum >= si[i].value) {
      break
    }
  }
  let fmt = (absNum / si[i].value).toFixed(digits).replace(rx, '$1') + si[i].symbol
  if (absNum - num) {
    fmt = '-' + fmt
  }
  return fmt
}

Tool.numFormat = function(num) {
  if (Tool.empty(num)) {
    return 0
  }
  num = num.toString()
  return num.replace(/^(\d+)((\.\d+)?)$/, function(s, s1, s2) {
    return s1.replace(/\d{1,3}(?=(\d{3})+$)/g, '$&,') + s2
  })
}

// array_diff
Tool.arrayDiff = function(array1, array2) {
  if (Tool.empty(array1)) {
    return []
  }
  if (Tool.empty(array2)) {
    return array1
  }
  return array1.filter(function(elm) {
    return array2.indexOf(elm) === -1
  })
}

// 获取对象或数组的长度
Tool.getLen = function(val) {
  if (JSON.stringify(val) === '[]') {
    return 0
  }
  return val.length || Object.getOwnPropertyNames(val).length
}

Tool.cutStr = function(str, len = 10) {
  let str_length = 0
  let str_len = 0
  let str_cut = ''
  str_len = str.length
  for (let i = 0; i < str_len; i++) {
    const a = str.charAt(i)
    str_length++
    if (escape(a).length > 4) {
      // 中文字符的长度经编码之后大于4
      str_length++
    }
    str_cut = str_cut.concat(a)
    if (str_length >= len) {
      str_cut = str_cut.concat('...')
      return str_cut
    }
  }
  // 如果给定字符串小于指定长度，则返回源字符串；
  if (str_length < len) {
    return str
  }
}

Tool.isArrayFn = function(value) {
  if (typeof Array.isArray === 'function') {
    return Array.isArray(value)
  } else {
    return Object.prototype.toString.call(value) === '[object Array]'
  }
}
/**
 * 判断是否是空对象/数组
 * @param val
 * @returns {boolean} true 为空
 */
Tool.empty = function(val) {
  if (Tool.isArrayFn(val)) {
    return val.length === 0
  } else {
    return !val || JSON.stringify(val) === '{}'
  }
}

/**
 * 批量删除对象的属性
 * @param obj
 * @param list
 * @returns {{}|*}
 */
Tool.delete = function(obj, ...list) {
  if (Tool.empty(obj) || Tool.isArrayFn(obj)) {
    return {}
  }
  list.forEach(v => {
    if (Tool.isArrayFn(v)) {
      v.forEach(v1 => {
        Reflect.deleteProperty(obj, v1)
      })
    } else {
      Reflect.deleteProperty(obj, v)
    }
  })
  return obj
}

Tool.deleteInArray = function(arr1, arr2) {
  const arr = []
  for (const i in arr1) {
    if (Tool.isInArray(arr2, arr1[i])) {
      continue
    }
    arr.push(arr1[i])
  }
  return arr
}

Tool.sum = function(arr) {
  let s = 0
  arr.forEach(function(val, idx, arr) {
    s += val
  }, 0)

  return s
}

// array_column
Tool.arrayColumn = function(array, columnName, columnIndex = '', needZero = false) {
  if (!array) {
    return []
  }

  let tmp = []
  if (columnIndex) {
    tmp = {}
  }
  for (const i in array) {
    if (Tool.empty(columnIndex)) {
      const temp = Tool.isSet(array[i], columnName)
      if (!needZero && !temp) {
        continue
      }
      tmp.push(array[i][columnName])
    } else {
      if (columnName === null) {
        tmp[Tool.isSet(array[i], columnIndex)] = array[i]
      } else {
        tmp[Tool.isSet(array[i], columnIndex)] = Tool.isSet(array[i], columnName)
      }
    }
  }
  return tmp
}
Tool.arrayKeys = function(obj) {
  if (Tool.empty(obj)) {
    return []
  }
  const temp = []
  for (const i in obj) {
    temp.push(i)
  }
  return temp
}

Tool.arrayValues = function(obj) {
  const isArray = Tool.isArrayFn(obj)
  if (isArray) {
    return obj.values()
  } else {
    return Object.values(obj)
  }
}

Tool.ObjMGet = function(obj, keyArr, getKey = true) {
  const hit = []
  const miss = []
  if (Tool.empty(obj) || Tool.empty(keyArr)) {
    return [hit, keyArr]
  }
  for (const i in keyArr) {
    const get = getKey ? keyArr[i] : Tool.isSet(obj, keyArr[i])
    if (!Tool.isSet(obj, keyArr[i])) {
      miss.push(get)
    } else {
      hit.push(get)
    }
  }
  return [hit, miss]
}

Tool.toFixed = function(num, len = 2) {
  if (Tool.empty(num)) {
    return 0
  }
  return parseFloat(num).toFixed(len)
}

// array_flip 0 - 数字
Tool.arrayFlip = function(val, type = 1) {
  if (Tool.empty(val)) {
    return []
  }

  const tmp = {}
  for (let i in val) {
    if (type === 0) {
      i = parseInt(i)
    }
    tmp[val[i]] = i
  }
  return tmp
}

Tool.arraySearch = function(arr, val) {
  if (Tool.empty(arr) || !val) {
    return -1
  }
  for (const i in arr) {
    if (arr[i] === val) return i
  }
  return -1
}
Tool.arrayRemove = function(arr, val) {
  if (Tool.empty(arr) || !val) {
    return []
  }
  const index = Tool.arraySearch(arr, val)
  if (index !== -1) {
    arr.splice(index, 1)
  }
  return arr
}

Tool.getMemberByGType = function(all, range) {
  if (Tool.empty(range)) {
    return all
  } else {
    const tmp = []
    for (const i in all) {
      if (Tool.isInArray(range, all[i]['power'])) {
        tmp.push(all[i])
      }
    }
    return tmp
  }
}
/**
 * 文件拓展名
 * @param fileName
 * @returns {*}
 */
Tool.fnGetExtension = function(fileName) {
  return (fileName.replace(/\s/g, '')).match(/^.+\/(\w+\.\w+)/g)[0].replace(/.+\./, '')
}

Tool.isIncludeBy = function(url, rule) {
  return RegExp(rule).test(url)
}

Tool.isDraft = function(fid) {
  if (!fid) {
    return false
  }
  fid = fid.toString()
  return fid.includes('c_') || fid.includes('x_')
}
Tool.filterHtml = function(html) {
  return html
    // eslint-disable-next-line no-irregular-whitespace
    .replace(/<p> <\/p>/g, '\n')
    .replace(/<(\/p)*?>/gm, '\n\n')
    .replace(/<(?:.|\n)*?>/gm, '')
    .replace(/(&rdquo;)/g, '"')
    .replace(/&ldquo;/g, '"')
    .replace(/&mdash;/g, '-')
    .replace(/&nbsp;/g, '')
    .replace(/&gt;/g, '>')
    .replace(/&lt;/g, '<')
    .replace(/<[\w\s"':=\/]*/, '')
}

/**
 * 繁体化
 * @param cc
 * @returns {string}
 */
Tool.traditionalized = function(cc) {
  let str = ''
  const charPYStr = definition.charPYStr
  const ftPYStr = definition.ftPYStr
  for (let i = 0; i < cc.length; i++) {
    if (charPYStr.indexOf(cc.charAt(i)) !== -1) {
      str += ftPYStr.charAt(charPYStr.indexOf(cc.charAt(i)))
    } else {
      str += cc.charAt(i)
    }
  }
  return str
}
/**
 * 简体化
 * @param cc
 * @returns {string}
 */
Tool.simplized = function(cc) {
  let str = ''
  const charPYStr = definition.charPYStr
  const ftPYStr = definition.ftPYStr
  for (let i = 0; i < cc.length; i++) {
    if (ftPYStr.indexOf(cc.charAt(i)) !== -1) {
      str += charPYStr.charAt(ftPYStr.indexOf(cc.charAt(i)))
    } else {
      str += cc.charAt(i)
    }
  }
  return str
}

Tool.fmtAdName = function(name) {
  const rs = name.match(/{.*}/)
  if (Tool.empty(rs)) {
    return ''
  }
  const arr = rs[0].split('/')
  const bookId = arr[0].match(/\d+/)
  const linkId = arr[1].match(/\d+/)
  const code = Tool.isSet(arr, 2)
  const isTest = Tool.isSet(arr, 3) === 'test'
  return [Tool.isSet(bookId, 0), Tool.isSet(linkId, 0), code, isTest]
}
// 合并两数组
Tool.arrayCombine = function(keyArr, valueArr) {
  let obj = {}
  keyArr.map((v, i) => {
    obj[keyArr[i]] = valueArr[i]
  })
  return obj
}

Tool.arrayMax = function(arr) {
  return Math.max.apply(Math, arr)
}

Tool.pathname = function(name) {
  return name.substring(0, name.lastIndexOf('.'))
}

/**
 * 获取一级域名
 * @param name
 * @returns {*}
 */
Tool.getTopDomain = function(name) {
  if (Tool.empty(name)) {
    return ''
  }
  const url = name.match(/http[s]?:\/\/(.*?)([:\/]|$)/)
  return url[1].split('.').slice(-2).join('.')
}

export
{
  Tool
}

