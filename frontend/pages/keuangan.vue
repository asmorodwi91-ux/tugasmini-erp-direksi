<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const periode = ref('2026-01')
const departemen = ref('semua')
const data = ref<any>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try { data.value = await api(`/finance/report?periode=${periode.value}`) }
  finally { loading.value = false }
}
onMounted(load)
function exportReport() {
  if (process.client) window.print()
}
const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
const buckets = ['0-30', '31-60', '61-90', '>90']
</script>
<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Laporan Keuangan</h1>
      <div class="flex items-center gap-2">
        <select v-model="periode" @change="load" class="select-field">
          <option value="2026-01">Periode: Jan 2026</option>
          <option value="2026-06">Periode: Jun 2026</option>
        </select>
        <select v-model="departemen" class="select-field">
          <option value="semua">Departemen: Semua</option>
          <option value="operasional">Departemen: Operasional</option>
          <option value="keuangan">Departemen: Keuangan</option>
          <option value="umum">Departemen: Umum</option>
        </select>
        <button type="button" @click="exportReport"
          class="rounded-full px-4 py-2 text-sm font-semibold bg-primary text-white shadow-sm transition-all duration-200 hover:bg-primary-dark active:scale-[0.98]">
          ⬇️ Ekspor
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <div v-else-if="data">
      <div class="grid grid-cols-3 gap-5 mb-6">
        <KpiCard title="Laba / Rugi (P&L)" :value="fmt(data.pl)" badge="▲ Surplus" badge-variant="hijau" />
        <KpiCard title="Arus Kas" :value="fmt(data.arus_kas)" badge="Positif" badge-variant="hijau" />
        <div class="card">
          <div class="kpi-label">Pemakaian Anggaran</div>
          <div class="kpi-value">{{ data.pemakaian_anggaran }}%</div>
          <StatusBadge v-if="data.indikator_warna" :status="data.indikator_warna" />
        </div>
      </div>

      <div class="flex flex-col md:flex-row gap-5 items-stretch">
        <div class="table-shell flex-[1.1]">
          <div class="kpi-label mb-3">Aging Hutang-Piutang</div>
          <table>
            <thead>
              <tr>
                <th>Umur</th>
                <th class="text-right">Hutang</th>
                <th class="text-right">Piutang</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="b in buckets" :key="b">
                <td>{{ b }} hari</td>
                <td class="text-right font-mono">{{ fmt(data.aging?.hutang?.[b]) }}</td>
                <td class="text-right font-mono">{{ fmt(data.aging?.piutang?.[b]) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card flex-1">
          <div class="kpi-label mb-3">Tren Laba &amp; Arus Kas</div>
          <svg class="w-full h-[150px]" viewBox="0 0 320 150" preserveAspectRatio="none">
            <polyline points="0,110 60,95 120,100 180,70 240,60 320,40" fill="none" stroke="#1F4E79" stroke-width="3" />
            <polyline points="0,130 60,120 120,115 180,100 240,95 320,80" fill="none" stroke="#2E7D4F" stroke-width="3" />
          </svg>
          <div class="text-grey text-xs mt-2.5">
            <span class="text-primary">●</span> Laba (biru) ·
            <span class="text-status-hijau">●</span> Arus kas (hijau)
          </div>
        </div>
      </div>

      <div class="mt-6">
        <AlertBanner text="Indikator anggaran ≥ 80% memicu peringatan otomatis ke Direksi. Warna: hijau <70% · kuning 70-90% · merah >90%." />
      </div>
    </div>
  </div>
</template>
