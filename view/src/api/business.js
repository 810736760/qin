import { request } from '@/utils/requestExport'
import { Tool } from '@/utils/tool'
import store from '@/store'

export async function getOsVer(aid = 0, platform = 0) {
  const list = store.getters.osVerList
  if (!Tool.isSet(list, platform)) {
    await request('common/osVerList', { aid, platform }).then(response => {
      list[platform] = response.data.list
      store.commit('business/SET_OS_LIST_INFO', list)
    })
  }
  return list[platform]
}

export async function getAppList(aid, platform = 0) {
  const info = store.getters.appList
  if (!Tool.isSet(info, aid)) {
    await request('common/getAppList', { aid, platform }).then(response => {
      info[aid] = response.data.list
      store.commit('business/SET_APP_LIST_INFO', info)
    })
  }
  return store.getters.appList[aid]
}

export async function getInstagramList(aid) {
  const info = store.getters.instagramList
  if (!Tool.isSet(info, aid)) {
    await request('common/instagramList', { aid }).then(response => {
      info[aid] = response.data.list
      store.commit('business/SET_INS_LIST_INFO', info)
    })
  }
  return store.getters.instagramList[aid]
}

export async function getPageList(aid = 0, platform = 0) {
  const info = store.getters.pageList
  if (!Tool.isSet(info, aid)) {
    await request('common/pageList', { aid, platform }).then(response => {
      info[aid] = response.data.list
      store.commit('business/SET_PAGE_LIST_INFO', info)
    })
  }
  return store.getters.pageList[aid]
}

export async function getLanguage(aid = 0) {
  const info = store.getters.languageList
  if (!Tool.isSet(info, aid)) {
    await request('common/languageList', { aid }).then(response => {
      info[aid] = response.data.list
      store.commit('business/SET_LANGUAGE_LIST_INFO', info)
    })
  }
  return info[aid]
}

export async function getInterestCategories(aid = 0) {
  const info = store.getters.interestCategories
  if (!Tool.isSet(info, aid)) {
    await request('common/interestCategories', { aid }).then(response => {
      info[aid] = response.data.list
      store.commit('business/SET_INTEREST_CATEGORIES_LIST_INFO', info)
    })
  }
  return info[aid]
}

export async function getFilter() {
  let info = store.getters.filter
  if (Tool.empty(info)) {
    await request('config/defaultFilter').then(response => {
      info = JSON.stringify(response.data)
      store.commit('business/SET_FILTER_INFO', info)
    })
  }
  return JSON.parse(info)
}

export async function getMatchApp(aid) {
  const info = store.getters.matchApp
  if (!Tool.isSet(info, aid)) {
    await request('common/getMatchApp', { aid }).then(response => {
      info[aid] = Tool.isSet(response.data.list, 'data', [])
      store.commit('business/SET_MATCH_APP_INFO', info)
    })
  }
  return store.getters.matchApp[aid]
}

export async function getPixels(aid, platform = 0) {
  const info = store.getters.pixels
  if (!Tool.isSet(info, aid)) {
    await request('common/getPixels', { aid, platform }).then(response => {
      info[aid] = Tool.isSet(response.data, 'list', [])
      store.commit('business/SET_PIXELS_INFO', info)
    })
  }
  return store.getters.pixels[aid]
}

export async function getShopProductSets(bm_id) {
  const info = store.getters.shopProductSet
  if (!Tool.isSet(info, bm_id)) {
    await request('common/get_shop_product_sets', { bm_id }).then(response => {
      info[bm_id] = Tool.isSet(response.data, 'list', [])
      store.commit('business/SET_SHOP_PRODUCT_SET', info)
    })
  }
  return info[bm_id]
}

export async function getShopMenu(bm_id) {
  const info = store.getters.shopMenu
  if (!Tool.isSet(info, bm_id)) {
    await request('common/get_shop_menu', { bm_id }).then(response => {
      info[bm_id] = Tool.isSet(response.data, 'list', [])
      store.commit('business/SET_SHOP_MENU', info)
    })
  }
  return info[bm_id]
}

export async function getReportFilter() {
  let info = store.getters.reportFilter
  if (Tool.empty(info)) {
    await request('config/defaultAdsFilter').then(response => {
      info = response.data
      store.commit('business/SET_REPORT_FILTER_INFO', info)
    })
  }
  return info
}

export async function getAdLocaleByKeys(ids) {
  return request('common/listAdLocaleByKeys', { 'key': ids.join() })
}

export async function search(type, q) {
  return request('common/search', { type, q })
}

export async function targeting(aid, type) {
  const info = store.getters.targeting
  if (!Tool.isSet(info, aid) || !Tool.isSet(info[aid], type)) {
    await request('common/targeting', { aid, type }).then(response => {
      info[aid] = Tool.isSet(info, aid, {})
      info[aid][type] = response.data.list
      store.commit('business/SET_TARGETING_INFO', info)
    })
  }
  return store.getters.targeting[aid][type]
}

export async function targetingSearch(aid, q = '') {
  let info = []
  await request('common/targeting', { aid, q }).then(response => {
    info = response.data.list
  })
  return info
}

export async function targetingTTSearch(aid, keyword = '') {
  let info = []
  await request('common/searchInterestCategories', { aid, keyword }).then(response => {
    info = response.data.list
  })
  return info
}

export async function getFbImageUrl(aid, hash) {
  let info = store.getters.fbImageList
  const rs = Tool.ObjMGet(info, hash)
  if (rs[1].length) {
    await request('common/listByImageHash', { aid, hash: rs[1].join() }).then(response => {
      info = Object.assign(info, response.data.list)
      store.commit('business/SET_FB_IMAGE_LIST', info)
    })
  }

  return info
}

export async function getTTImageUrl(aid, hash) {
  let info = store.getters.ttImageList
  const rs = Tool.ObjMGet(info, hash)
  if (rs[1].length) {
    await request('common/listByTTImageHash', { aid, hash: rs[1].join() }).then(response => {
      info = Object.assign(info, response.data.list)
      store.commit('business/SET_TT_IMAGE_LIST', info)
    })
  }

  return info
}

export async function getMediaUrl(sids) {
  let info = store.getters.fbImageList
  const rs = Tool.ObjMGet(info, sids)
  if (rs[1].length) {
    await request('material/getUrl', { ids: rs[1].join() }).then(response => {
      info = Object.assign(info, response.data.list)
      store.commit('business/SET_MID_URL_LIST', info)
    })
  }

  return info
}

export async function getCustomInfo(type) {
  let info = []
  await request('customList', { type }).then(response => {
    info = response.data.list
  })
  return info
}

export async function getColumnList(reload = 0) {
  let info = store.getters.columnList
  if (Tool.empty(info) || reload) {
    await request('config/column').then(response => {
      info = Tool.isSet(response.data, 'list', [])
      store.commit('business/SET_COLUMN_LIST_INFO', info)
    })
  }
  return info
}
