const getters = {
  token: state => state.user.token,
  path: state => state.user.path,
  user_info: state => state.user.user_info,
  expire_time: state => state.user.expire_time,
  sidebar: state => state.app.sidebar,
  size: state => state.app.size,
  device: state => state.app.device,
  visitedViews: state => state.tagsView.visitedViews,
  cachedViews: state => state.tagsView.cachedViews,
  permission_routes: state => state.permission.routes,
  tid: state => state.user.tid
}
export default getters
