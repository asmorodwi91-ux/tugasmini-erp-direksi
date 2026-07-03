import { defineStore } from 'pinia'

type AppUser = {
  id: number
  nama: string
  role: string
  akses?: Record<string, string>
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null as string | null,
    user: null as AppUser | null,
  }),
  getters: {
    isLoggedIn: (s) => !!s.token,
    akses: (s) => s.user?.akses ?? {},
    role: (s) => s.user?.role ?? '',
  },
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
    // Cek apakah user boleh melihat modul (punya hak akses apa pun)
    bisaLihat(modul: string): boolean {
      return !!this.user?.akses?.[modul]
    },
    // Cek level minimal (lihat < ubah < setujui)
    bisa(modul: string, minimal: 'lihat' | 'ubah' | 'setujui' = 'lihat'): boolean {
      const urutan = { lihat: 1, ubah: 2, setujui: 3 }
      const level = this.user?.akses?.[modul]
      if (!level) return false
      return (urutan[level as keyof typeof urutan] ?? 0) >= urutan[minimal]
    },
    logout() {
      this.token = null; this.user = null
      useCookie('erp_token').value = null
      useCookie('erp_user').value = null
    },
  },
})
