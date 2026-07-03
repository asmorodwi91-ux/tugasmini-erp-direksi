<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
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

// Data tren & kinerja bersifat ilustratif (belum tersedia dari API /dashboard)
const trend = {
  pendapatan: '0,90 60,70 120,75 180,50 240,40 320,25',
  laba: '0,105 60,95 120,98 180,85 240,80 320,70',
}
const trendMonths = ['Ags', 'Sep', 'Okt', 'Nov', 'Des', 'Jan']
const deptBars = [
  { label: 'Dept A', height: 70 },
  { label: 'Dept B', height: 90 },
  { label: 'Dept C', height: 55 },
  { label: 'Dept D', height: 80 },
  { label: 'Dept E', height: 65 },
]
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
        <option value="2026-01">Periode: Jan 2026</option>
        <option value="2026-06">Periode: Jun 2026</option>
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
          <svg class="w-full h-32" viewBox="0 0 320 120" preserveAspectRatio="none">
            <polyline :points="trend.pendapatan" fill="none" stroke="#1F4E79" stroke-width="3"
              stroke-linecap="round" stroke-linejoin="round" />
            <polyline :points="trend.laba" fill="none" stroke="#6C9BD1" stroke-width="3"
              stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <div class="flex justify-between mt-2 text-[11px] text-grey">
            <span v-for="m in trendMonths" :key="m">{{ m }}</span>
          </div>
        </div>

        <div class="card">
          <div class="section-title text-grey mb-3">Kinerja per Departemen</div>
          <div class="flex items-end gap-3 h-32">
            <div v-for="(b, i) in deptBars" :key="b.label"
              class="flex-1 rounded-t"
              :style="{ height: b.height + '%', background: i % 2 === 1 ? '#6C9BD1' : '#1F4E79' }"></div>
          </div>
          <div class="flex gap-3 mt-2 text-[11px] text-grey">
            <span v-for="b in deptBars" :key="b.label" class="flex-1 text-center">{{ b.label }}</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
