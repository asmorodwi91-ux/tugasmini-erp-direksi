<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const periode = ref('2026-01')
const data = ref<any>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try { data.value = await api(`/finance/report?periode=${periode.value}`) }
  finally { loading.value = false }
}
onMounted(load)
const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
const buckets = ['0-30', '31-60', '61-90', '>90']
</script>
<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Laporan Keuangan</h1>
      <select v-model="periode" @change="load" class="select-field">
        <option value="2026-01">Periode: Jan 2026</option>
        <option value="2026-06">Periode: Jun 2026</option>
      </select>
    </div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <div v-else-if="data">
      <div class="grid grid-cols-3 gap-5 mb-6">
        <KpiCard title="Laba / Rugi (P&L)" :value="fmt(data.pl)" :delta="'Surplus'" :delta-up="true" />
        <KpiCard title="Arus Kas" :value="fmt(data.arus_kas)" />
        <div class="card">
          <div class="kpi-label">Pemakaian Anggaran</div>
          <div class="kpi-value">{{ data.pemakaian_anggaran }}%</div>
          <StatusBadge v-if="data.indikator_warna" :status="data.indikator_warna" />
        </div>
      </div>

      <div class="table-shell">
        <div class="section-title px-2 pt-1 pb-4">Aging Hutang-Piutang</div>
        <table>
          <thead>
            <tr>
              <th>Umur</th><th>Hutang</th><th>Piutang</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in buckets" :key="b">
              <td>{{ b }} hari</td>
              <td class="font-mono">{{ fmt(data.aging?.hutang?.[b]) }}</td>
              <td class="font-mono">{{ fmt(data.aging?.piutang?.[b]) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
