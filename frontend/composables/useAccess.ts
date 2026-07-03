// Aturan akses per halaman.
// - `modul`  : butuh hak akses (minimal 'lihat') pada modul tsb di tabel hak_akses.
// - `roles`  : dibatasi ke daftar role tertentu (untuk halaman admin/khusus).
// - tanpa keduanya: boleh untuk semua user yang login.
export type MenuItem = {
  to: string
  label: string
  modul?: string
  roles?: string[]
}

export const MENU: MenuItem[] = [
  { to: '/', label: '📊 Dashboard', modul: 'Dashboard' },
  { to: '/approval', label: '✅ Approval PO', modul: 'Approval' },
  { to: '/keuangan', label: '💰 Laporan Keuangan', modul: 'Keuangan' },
  { to: '/operasional', label: '📈 Kinerja Operasional', modul: 'Operasional' },
  { to: '/notifikasi', label: '🔔 Notifikasi' },
  { to: '/hak-akses', label: '🔑 Hak Akses', roles: ['Manager IT', 'Direktur Utama'] },
  { to: '/ekspor', label: '⬇️ Ekspor Laporan', modul: 'Ekspor' },
  { to: '/budget', label: '🧮 Review Anggaran', roles: ['Direktur Utama', 'Wakil Direktur', 'Manager Keuangan'] },
]

export function useAccess() {
  const auth = useAuthStore()

  const bolehAkses = (item: MenuItem): boolean => {
    if (item.roles) return item.roles.includes(auth.role)
    if (item.modul) return auth.bisaLihat(item.modul)
    return true
  }

  const menuTampil = computed(() => MENU.filter(bolehAkses))

  const bolehAksesPath = (path: string): boolean => {
    const item = MENU.find((m) => m.to === path)
    if (!item) return true // halaman tak terdaftar (mis. detail dinamis) → biarkan
    return bolehAkses(item)
  }

  return { MENU, menuTampil, bolehAkses, bolehAksesPath }
}
