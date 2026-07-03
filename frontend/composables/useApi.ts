export function useApi() {
  const config = useRuntimeConfig()
  const auth = useAuthStore()
  const api = <T = any>(path: string, opts: any = {}) => {
    return $fetch<T>(path, {
      baseURL: config.public.apiBase,
      ...opts,
      headers: {
        Accept: 'application/json',
        ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
        ...(opts.headers || {}),
      },
    })
  }
  return { api }
}
