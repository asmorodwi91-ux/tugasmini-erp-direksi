export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuthStore()
  auth.loadFromCookie()
  if (!auth.isLoggedIn && to.path !== '/login') {
    return navigateTo('/login')
  }
})
