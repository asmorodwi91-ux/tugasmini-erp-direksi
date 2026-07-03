<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const { periodeOptions } = usePeriode()
const periode = ref('2026-01')
const data = ref<any>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try { data.value = await api(`/dashboard?periode=${periode.value}`) }
  finally { loading.value = false }
}
onMounted(load)
const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')

const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
const labelBulan = (periode: string) => {
  const [y, m] = (periode || '').split('-')
  return `${BULAN[Number(m) - 1] ?? m} '${(y || '').slice(2)}`
}

// Grafik tren 6 bulan — dibangun dari data real API (/dashboard.tren)
const trend = computed(() => {
  const rows: any[] = data.value?.tren ?? []
  if (!rows.length) return { pendapatan: '', laba: '', months: [] as string[] }
  const max = Math.max(...rows.map((r) => Number(r.pendapatan)), 1)
  const n = rows.length
  const x = (i: number) => (n === 1 ? 160 : (i / (n - 1)) * 320)
  const y = (v: number) => 110 - (Number(v) / max) * 100
  return {
    pendapatan: rows.map((r, i) => `${x(i)},${y(r.pendapatan)}`).join(' '),
    laba: rows.map((r, i) => `${x(i)},${y(r.laba)}`).join(' '),
    months: rows.map((r) => labelBulan(r.periode)),
  }
})

// Grafik kinerja per departemen — nama & nilai real dari API (/dashboard.kinerja)
const deptBars = computed(() => {
  const rows: any[] = data.value?.kinerja ?? []
  if (!rows.length) return [] as { label: string; height: number; persen: number }[]
  const max = Math.max(...rows.map((r) => Number(r.aktual)), 1)
  return rows.map((r) => ({
    label: r.nama,
    persen: Number(r.persen),
    height: Math.round((Number(r.aktual) / max) * 100),
  }))
})
</script>
<template>
  <div>
    <AlertBanner v-if="data?.early_warning?.length"
      :text="`Early Warning: ${data.early_warning.length} kondisi kritis`">
      <NuxtLink to="/notifikasi" class="btn-outline text-xs px-3 py-1.5">Lihat</NuxtLink>
    </AlertBanner>

    <div class="page-header">
      <h1 class="page-title">Ringkasan Eksekutif</h1>
      <select v-model="periode" @change="load" class="select-field">
        <option v-for="p in periodeOptions" :key="p.value" :value="p.value">Periode: {{ p.label }}</option>
      </select>
    </div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <template v-else-if="data">
      <div class="grid grid-cols-4 gap-5">
        <KpiCard title="Pendapatan" :value="fmt(data.kpi.pendapatan)" delta="▲ 8% vs bln lalu" :delta-up="true" />
        <KpiCard title="Laba Bersih" :value="fmt(data.kpi.laba)" delta="▲ 4%" :delta-up="true" />
        <KpiCard title="PO Pending" :value="data.kpi.po_pending" delta="2 lewat 4 jam" :delta-up="false" />
        <div class="card">
          <div class="kpi-label">Pemakaian Anggaran</div>
          <div class="kpi-value">{{ data.kpi.pemakaian_anggaran }}%</div>
          <StatusBadge v-if="data.indikator_anggaran" :status="data.indikator_anggaran" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-5 mt-5">
        <div class="card">
          <div class="flex items-center justify-between mb-3">
            <div class="section-title text-grey">Tren Pendapatan &amp; Laba (6 bulan)</div>
            <div class="flex items-center gap-4 text-xs text-grey">
              <span class="flex items-center gap-1.5">
                <span class="w-3 h-0.5 rounded-full" style="background:#1F4E79"></span>Pendapatan
              </span>
              <span class="flex items-center gap-1.5">
                <span class="w-3 h-0.5 rounded-full" style="background:#6C9BD1"></span>Laba
              </span>
            </div>
          </div>
          <svg v-if="trend.months.length" class="w-full h-32" viewBox="0 0 320 120" preserveAspectRatio="none">
            <polyline :points="trend.pendapatan" fill="none" stroke="#1F4E79" stroke-width="3"
              stroke-linecap="round" stroke-linejoin="round" />
            <polyline :points="trend.laba" fill="none" stroke="#6C9BD1" stroke-width="3"
              stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <div v-else class="h-32 flex items-center justify-center text-sm text-grey">Data tren belum tersedia</div>
          <div class="flex justify-between mt-2 text-[11px] text-grey">
            <span v-for="m in trend.months" :key="m">{{ m }}</span>
          </div>
        </div>

        <div class="card">
          <div class="section-title text-grey mb-3">Kinerja per Departemen</div>
          <div v-if="deptBars.length" class="flex items-end gap-3 h-32">
            <div v-for="(b, i) in deptBars" :key="b.label"
              class="flex-1 rounded-t relative group"
              :title="`${b.label}: ${b.persen}% dari target`"
              :style="{ height: b.height + '%', background: i % 2 === 1 ? '#6C9BD1' : '#1F4E79' }"></div>
          </div>
          <div v-else class="h-32 flex items-center justify-center text-sm text-grey">Data kinerja belum tersedia</div>
          <div v-if="deptBars.length" class="flex gap-3 mt-2 text-[11px] text-grey">
            <span v-for="b in deptBars" :key="b.label" class="flex-1 text-center">{{ b.label }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
