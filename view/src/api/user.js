import request from '@/utils/request'

export function login(data) {
  return request({
    url: 'login',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return request({
    url: 'userInfo',
    method: 'get'
  })
}

export function logout() {
  return request({
    url: 'logout',
    method: 'post'
  })
}

export function refresh() {
  return request({
    url: 'refresh',
    method: 'post'
  })
}

export function getMember(data) {
  return request({
    url: 'adm/getMember',
    method: 'post',
    data
  })
}

export function getBM(data) {
  return request({
    url: 'bmList',
    method: 'post',
    data
  })
}

