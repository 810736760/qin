import { asyncRoutes, constantRoutes } from '@/router'

function fmtUrl(path) {
  if (!path) {
    return path
  }
  if (path[path.length - 1] === '/') {
    path = path.substring(0, -2)
  }
  if (path[0] === '/') {
    path = path.substring(1)
  }
  return path
}

export function filterAsyncRoutes(routes, enablePath, path) {
  const res = []
  routes.forEach(route => {
    const tmp = { ...route }
    let selfPath = ''
    if (path) {
      selfPath = path + '/' + fmtUrl(tmp.path)
    } else {
      selfPath = fmtUrl(tmp.path)
    }

    if (Object.prototype.hasOwnProperty.call(tmp, 'children')) {
      tmp.children = filterAsyncRoutes(tmp.children, enablePath, selfPath)
    }
    if (enablePath.indexOf(selfPath) > -1 ||
      (Object.prototype.hasOwnProperty.call(tmp, 'children') && tmp['children'].length) ||
      selfPath === '*') {
      res.push(tmp)
    }
  })
  return res
}

const state = {
  routes: [],
  addRoutes: []
}

const mutations = {
  SET_ROUTES: (state, routes) => {
    state.addRoutes = routes
    state.routes = routes.concat(constantRoutes)
  }
}

const actions = {
  generateRoutes({ commit }, data) {
    const userInfo = data.user
    const path = data.view

    return new Promise(resolve => {
      let accessedRoutes = []
      if (userInfo.power === 1) {
        accessedRoutes = asyncRoutes || []
      } else if (path.length) {
        accessedRoutes = filterAsyncRoutes(asyncRoutes, path, '')
      }
      commit('SET_ROUTES', accessedRoutes)
      // cookieJs.setRoutePath(accessedRoutes)
      resolve(accessedRoutes)
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
