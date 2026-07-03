import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as { id: number; nama: string; role: string } | null,
  }),
  getters: { isLoggedIn: (s) => !!s.token },
  actions: {
    setAuth(token: string, user: any) {
      this.token = token; this.user = user
      useCookie('erp_token').value = token
      useCookie('erp_user').value = JSON.stringify(user)
    },
    loadFromCookie() {
      const c = useCookie('erp_token'); const cu = useCookie<any>('erp_user')
      if (c.value) this.token = c.value
      if (cu.value) this.user = typeof cu.value === 'string' ? JSON.parse(cu.value) : cu.value
    },
    logout() {
      this.token = null; this.user = null
      useCookie('erp_token').value = null
      useCookie('erp_user').value = null
    },
  },
})
