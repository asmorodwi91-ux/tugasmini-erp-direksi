export function useAuthActions() {
  const { api } = useApi()
  const auth = useAuthStore()
  const login = (email: string, password: string) =>
    api('/auth/login', { method: 'POST', body: { email, password } })
  const verifyOtp = async (email: string, otp: string) => {
    const res: any = await api('/auth/verify-otp', { method: 'POST', body: { email, otp } })
    auth.setAuth(res.token, res.user); return res
  }
  const logout = async () => {
    try { await api('/auth/logout', { method: 'POST' }) } catch {}
    auth.logout()
    await navigateTo('/login', { replace: true })
  }
  return { login, verifyOtp, logout }
}
