import { login, logout, getInfo, refresh } from '@/api/user'
import { cookieJs } from '@/utils/auth'
import { resetRouter } from '@/router'

const state = {
  token: cookieJs.getToken(),
  path: [],
  user_info: {},
  expire_time: cookieJs.getExpireTime(),
  tid: 0

}

const mutations = {
  SET_TID: (state, val) => {
    state.tid = val
  },

  SET_TOKEN: (state, token) => {
    state.token = token
  },
  SET_USER_INFO: (state, user_info) => {
    state.user_info = user_info
  },
  SET_EXPIRE: (state, time) => {
    state.expire_time = time
  }

}

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      login({ nickname: username.trim(), password: password }).then(response => {
        const { data } = response
        const expireTime = Date.now() + data.expires_in * 1000
        commit('SET_TOKEN', data.access_token)
        commit('SET_EXPIRE', expireTime)
        cookieJs.setToken(data.access_token)
        cookieJs.setExpireTime(expireTime)
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo(state.token).then(response => {
        const { data } = response

        if (!data) {
          reject('验证失败')
        }
        commit('SET_USER_INFO', data.user)
        commit('SET_TID', data.term)

        resolve(data)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // user logout
  logout({ commit, dispatch, state }) {
    return new Promise((resolve, reject) => {
      logout().then(() => {
        commit('SET_TOKEN', '')
        commit('SET_USER_INFO', {})
        commit('SET_EXPIRE', 0)
        resetRouter()
        cookieJs.removeToken()
        window.location.href = window.location.origin + '/login'
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  removeCookie({ commit, dispatch, state }) {
    return new Promise((resolve, reject) => {
      commit('SET_TOKEN', '')
      commit('SET_USER_INFO', {})
      commit('SET_EXPIRE', 0)

      cookieJs.removeToken()
      cookieJs.removeExpireTime()
      resetRouter()
    })
  },

  resetToken() {
    return new Promise((resolve, reject) => {
      refresh().then(response => {
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
