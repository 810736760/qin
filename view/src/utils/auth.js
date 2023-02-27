import Cookies from 'js-cookie'
// Cookies
// 临时存储，为每一个数据源维持一个存储区域，在浏览器打开期间存在，包括页面重新加载。
//
// localStorage
// 长期存储，与 Cookies 一样，但是浏览器关闭后，数据依然会一直存在。

const cookieJs = {}
// Token start
const TokenKey = 'adc-access_token'

cookieJs.getToken = function() {
  return Cookies.get(TokenKey)
}

cookieJs.setToken = function(token) {
  return Cookies.set(TokenKey, token)
}

cookieJs.removeToken = function() {
  return Cookies.remove(TokenKey)
}

// Token end

// expire_time start
const ExpireTime = 'adc-expire_time'

cookieJs.getExpireTime = function() {
  return Cookies.get(ExpireTime)
}

cookieJs.setExpireTime = function(token) {
  return Cookies.set(ExpireTime, token)
}

cookieJs.removeExpireTime = function() {
  return Cookies.remove(ExpireTime)
}
// expire_time end

// expire_time start
const CurAid = 'adc-cur_aid'

cookieJs.getCurAid = function() {
  return Cookies.get(CurAid)
}

cookieJs.setCurAid = function(token) {
  return Cookies.set(CurAid, token)
}

cookieJs.removeCurAid = function() {
  return Cookies.remove(CurAid)
}
// expire_time end

// cur_create_params start
const CurCreateParams = 'adc-cur_create_params'

cookieJs.getCurCreateParams = function() {
  return Cookies.get(CurCreateParams)
}

cookieJs.setCurCreateParams = function(token) {
  return Cookies.set(CurCreateParams, token)
}

cookieJs.removeCurCreateParams = function() {
  return Cookies.remove(CurCreateParams)
}
// cur_create_params end

// expire_time start
const CurPlatform = 'adc-cur_platform'

cookieJs.getCurPlatform = function() {
  return Cookies.get(CurPlatform)
}

cookieJs.setCurPlatform = function(token) {
  return Cookies.set(CurPlatform, token)
}

cookieJs.removeCurPlatform = function() {
  return Cookies.remove(CurPlatform)
}

// expire_time start
const Bid = 'adc-Bid'

cookieJs.getBid = function() {
  return Cookies.get(Bid)
}

cookieJs.setBid = function(token) {
  return Cookies.set(Bid, token)
}

cookieJs.removeBid = function() {
  return Cookies.remove(Bid)
}
// expire_time end

const CurTiktokAid = 'adc-cur_tiktokAid'

cookieJs.getCurTiktokAid = function() {
  return Cookies.get(CurTiktokAid)
}

cookieJs.setCurTiktokAid = function(token) {
  return Cookies.set(CurTiktokAid, token)
}

cookieJs.removeCurTiktokAid = function() {
  return Cookies.remove(CurTiktokAid)
}

// CurCoId start
const CurCoId = 'adc-now-co-id'

cookieJs.getCurCoId = function() {
  return Cookies.get(CurCoId)
}

cookieJs.setCurCoId = function(token) {
  return Cookies.set(CurCoId, token)
}

cookieJs.removeCurCoId = function() {
  return Cookies.remove(CurCoId)
}

// CurCoId end

export
{
  cookieJs
}
