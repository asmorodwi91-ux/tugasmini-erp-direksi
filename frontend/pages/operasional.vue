<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const { periodeOptions } = usePeriode()
const periode = ref('2026-01')
const data = ref<any>(null)
const loading = ref(true)

const eksporMsg = ref('')
const eksporErr = ref('')
const eksporLoading = ref(false)

async function load() {
  loading.value = true
  try { data.value = await api(`/ops/scorecard?periode=${periode.value}`) }
  finally { loading.value = false }
}
onMounted(load)

async function ekspor(format: 'pdf' | 'excel' = 'pdf') {
  eksporMsg.value = ''
  eksporErr.value = ''
  eksporLoading.value = true
  try {
    const blob: Blob = await api('/export', {
      method: 'POST',
      body: { jenis: 'operasional', periode: periode.value, format },
      responseType: 'blob',
    })
    const ext = format === 'excel' ? 'csv' : 'pdf'
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `operasional-${periode.value}.${ext}`
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
    eksporMsg.value = `Laporan ${ext.toUpperCase()} berhasil diunduh.`
  } catch (e: any) {
    const status = e?.status || e?.response?.status
    eksporErr.value = status === 403 ? 'Akses ditolak — Anda tidak punya izin ekspor.' : 'Gagal mengekspor laporan.'
  } finally {
    eksporLoading.value = false
    setTimeout(() => { eksporMsg.value = ''; eksporErr.value = '' }, 6000)
  }
}

const skorColor = (s: string) => (s === 'A' ? 'hijau' : s === 'B' ? 'kuning' : 'merah')
const skorBadge = (s: string) => ({
  A: 'bg-status-hijau-soft text-status-hijau',
  B: 'bg-status-kuning-soft text-status-kuning',
  C: 'bg-status-merah-soft text-status-merah',
}[s] || 'bg-bg-soft text-grey')

// Grafik perbandingan skor antar-departemen (persen real)
const bars = computed(() => {
  const rows: any[] = data.value?.departemen ?? []
  if (!rows.length) return []
  const max = Math.max(...rows.map((r) => Number(r.persen)), 1)
  return rows.map((r) => ({
    label: r.nama,
    persen: Number(r.persen),
    height: Math.round((Number(r.persen) / max) * 100),
    skor: r.skor,
  }))
})

// Grafik tren skor total 6 bulan (rata-rata persen)
const trend = computed(() => {
  const rows: any[] = data.value?.tren ?? []
  if (!rows.length) return { line: '', months: [] as string[], points: [] as any[] }
  const vals = rows.map((r) => Number(r.skor))
  const max = Math.max(...vals, 1)
  const min = Math.min(...vals, 0)
  const span = max - min || 1
  const n = rows.length
  const x = (i: number) => (n === 1 ? 160 : 10 + (i / (n - 1)) * 300)
  const y = (v: number) => 110 - ((v - min) / span) * 90
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
  return {
    line: rows.map((r, i) => `${x(i)},${y(Number(r.skor))}`).join(' '),
    months: rows.map((r) => BULAN[Number((r.periode || '').split('-')[1]) - 1] ?? r.periode),
    points: rows.map((r, i) => ({ cx: x(i), cy: y(Number(r.skor)), skor: r.skor })),
  }
})
</script>
<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Kinerja Operasional</h1>
      <div class="flex gap-2 items-center">
        <select v-model="periode" @change="load" class="select-field">
          <option v-for="p in periodeOptions" :key="p.value" :value="p.value">Periode: {{ p.label }}</option>
        </select>
        <button @click="ekspor('pdf')" :disabled="eksporLoading"
          class="bg-primary text-white rounded-full px-4 py-1.5 text-sm font-semibold disabled:opacity-60">
          {{ eksporLoading ? 'Memproses...' : '⬇ Ekspor' }}
        </button>
        <button @click="ekspor('excel')" :disabled="eksporLoading"
          class="border border-primary text-primary rounded-full px-4 py-1.5 text-sm font-semibold disabled:opacity-60">
          CSV
        </button>
      </div>
    </div>

    <div v-if="eksporMsg" class="mb-4 text-sm text-status-hijau">✓ {{ eksporMsg }}</div>
    <div v-if="eksporErr" class="mb-4 text-sm text-status-merah">⚠️ {{ eksporErr }}</div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <template v-else-if="data">
      <!-- SCORECARD TABLE -->
      <div class="card mb-5">
        <div class="kpi-label mb-3">Scorecard Kinerja Semua Departemen</div>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-grey text-left border-b border-line">
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide">Departemen</th>
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide">Metrik Utama</th>
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-right">Aktual</th>
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-right">Target</th>
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-center">Skor</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, i) in data.departemen" :key="i" class="border-b border-line/60 last:border-0">
              <td class="py-3 font-semibold text-ink">{{ d.nama }}</td>
              <td class="py-3 text-grey">{{ d.metrik }}</td>
              <td class="py-3 text-right font-mono">{{ d.persen }}%</td>
              <td class="py-3 text-right font-mono text-grey">{{ d.target }}%</td>
              <td class="py-3 text-center">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold"
                  :class="skorBadge(d.skor)">{{ d.skor }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- CHARTS -->
      <div class="grid grid-cols-2 gap-5">
        <!-- Perbandingan skor -->
        <div class="card">
          <div class="kpi-label mb-4">Perbandingan Skor Antar-Departemen</div>
          <div v-if="bars.length" class="flex items-end gap-3 h-32">
            <div v-for="(b, i) in bars" :key="b.label"
              class="flex-1 rounded-t relative"
              :title="`${b.label}: ${b.persen}% (${b.skor})`"
              :style="{ height: b.height + '%', background: i % 2 === 1 ? '#6C9BD1' : '#1F4E79' }"></div>
          </div>
          <div v-else class="h-32 flex items-center justify-center text-sm text-grey">Belum ada data</div>
          <div v-if="bars.length" class="flex gap-3 mt-2 text-[11px] text-grey">
            <span v-for="b in bars" :key="b.label" class="flex-1 text-center truncate">{{ b.label }}</span>
          </div>
        </div>

        <!-- Tren skor total -->
        <div class="card">
          <div class="kpi-label mb-4">Tren Skor Total (6 bulan)</div>
          <svg v-if="trend.months.length" viewBox="0 0 320 120" preserveAspectRatio="none" class="w-full" style="height:128px">
            <polyline :points="trend.line" fill="none" stroke="#1F4E79" stroke-width="3"
              stroke-linecap="round" stroke-linejoin="round" />
            <circle v-for="(p, i) in trend.points" :key="i" :cx="p.cx" :cy="p.cy" r="3" fill="#1F4E79" />
          </svg>
          <div v-else class="flex items-center justify-center text-sm text-grey" style="height:128px">Belum ada data</div>
          <div class="flex justify-between mt-2 text-[11px] text-grey">
            <span v-for="m in trend.months" :key="m">{{ m }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
