export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuthStore()
  auth.loadFromCookie()

  if (!auth.isLoggedIn && to.path !== '/login') {
    return navigateTo('/login')
  }

  // Batasi akses halaman sesuai hak akses / role
  if (auth.isLoggedIn && to.path !== '/login') {
    const { bolehAksesPath, menuTampil } = useAccess()
    if (!bolehAksesPath(to.path)) {
      // Arahkan ke menu pertama yang boleh diakses (hindari loop bila '/' juga diblokir)
      const tujuan = menuTampil.value[0]?.to || '/login'
      if (to.path !== tujuan) return navigateTo(tujuan)
    }
  }
})
