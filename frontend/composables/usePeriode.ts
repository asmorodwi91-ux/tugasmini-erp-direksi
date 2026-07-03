// Daftar periode (bulan) untuk dropdown — 24 bulan (2 tahun) terakhir
// berakhir di 2026-06 (bulan data terakhir yang di-seed).
const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']

export function usePeriode(akhir = '2026-06', jumlah = 24) {
  const [ay, am] = akhir.split('-').map(Number)
  const opsi: { value: string; label: string }[] = []
  for (let k = 0; k < jumlah; k++) {
    const d = new Date(ay, am - 1 - k, 1)
    const y = d.getFullYear()
    const m = d.getMonth() + 1
    opsi.push({
      value: `${y}-${String(m).padStart(2, '0')}`,
      label: `${BULAN[d.getMonth()]} ${y}`,
    })
  }
  return { periodeOptions: opsi }
}
