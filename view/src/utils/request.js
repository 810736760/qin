import axios from 'axios'
import { MessageBox, Message, Notification } from 'element-ui'
import store from '@/store'
import { cookieJs } from '@/utils/auth'
import JSONBig from 'json-bigint'

// create an axios instance
const service = axios.create({
  baseURL: process.env.VUE_APP_BASE_API, // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 0, // request timeout
  transformResponse: [
    function(data) {
      // 对于长整型超过16位的整数，进行转json处理，preview看到的可能是错的,但请求的数据已经处理成字符串
      return loopFmt(JSONBig.parse(data))
    }
  ]

})

function loopFmt(data, key = '', len = 0) {
  const type = typeof data
  if (key === 'c') {
    if (len < 15) {
      return data[0] + '.' + data[1]
    } else {
      return data.join('').toString()
    }
  } else if (type === 'object') {
    for (const i in data) {
      if ((typeof data[i]) === 'object') {
        if (data[i] && Object.prototype.hasOwnProperty.call(data[i], 'c')) {
          data[i] = loopFmt(data[i]['c'], 'c', data[i]['e'])
        } else {
          data[i] = loopFmt(data[i], i)
        }
      }
    }
  }
  return data
}

// request interceptor
service.interceptors.request.use(
  config => {
    // do something before request is sent
    if (store.getters.token) {
      // 'Authorization': 'Bearer ' + accessToken
      config.headers['Authorization'] = 'Bearer ' + cookieJs.getToken()
    }
    if (cookieJs.getExpireTime() && Date.now() > cookieJs.getExpireTime()) {
      if (config['url'] !== 'login' && config['url'] !== 'refresh') {
        MessageBox.confirm('是否重新登录?', '登录过期', {
          confirmButtonText: '重新登录',
          showCancelButton: false,
          showClose: false,
          type: 'warning'
        }).then(() => {
          if (!store.getters.token) {
            location.reload()
          } else {
            store.dispatch('user/resetToken').then(() => {
              location.reload()
            })
          }
        })
        return Promise.reject(100) // 过期报错
      } else {
        store.dispatch('user/removeCookie').then(() => {
          location.reload()
        })
      }
    }

    return config
  },
  error => {
    // do something with request error
    return Promise.reject(error)
  }
)

// response interceptor
service.interceptors.response.use(
  /**
   * If you want to get http information such as headers or status
   * Please return  response => response
   */

  /**
   * Determine the request status by custom code
   * Here is just an example
   * You can also judge the status by HTTP Status Code
   */
  response => {
    const res = response.data
    if (res.code === 206) {
      Notification.error({
        title: '错误提示',
        duration: 0,
        dangerouslyUseHTMLString: true,
        message: '[' + res.code + ']' + (res.msg || '')
      })
      return Promise.reject(res.msg || 'Error')
    } else if (res.code !== 200 && res.code !== 422) {
      Message({
        message: '[' + res.code + ']' + (res.msg || ''),
        dangerouslyUseHTMLString: true,
        type: 'error',
        duration: 5 * 1000
      })
      return Promise.reject(res.msg || 'Error')
    } else {
      return res
    }
  },
  error => {
    if (error === 100) {
      Message({
        message: '长时间未操作,请重新登录',
        type: 'error',
        duration: 5 * 1000
      })
    }

    return Promise.reject(error)
  }
)

export default service
