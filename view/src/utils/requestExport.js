import requestBase from '@/utils/request'
import store from '@/store'

export function request(requestPath, paramsArr = {}, cJson = false, method = 'post', extra = {}) {
  // 全局请求添加bm参数 若本身带了参数则略过
  paramsArr['bm_id'] = Object.prototype.hasOwnProperty.call(paramsArr, 'bm_id') ? paramsArr['bm_id'] : store.getters.nowBmId
  paramsArr['co'] = Object.prototype.hasOwnProperty.call(paramsArr, 'co') ? paramsArr['co'] : store.getters.nowCoId
  const body = {
    url: requestPath,
    method: method
  }
  if (method === 'get') {
    body.params = paramsArr
  } else {
    body.data = paramsArr
  }

  for (const i in extra) {
    body[i] = extra[i]
  }

  if (cJson) {
    body.headers = {
      'Content-Type': 'application/json'
    }
  }
  return requestBase(body)
}

