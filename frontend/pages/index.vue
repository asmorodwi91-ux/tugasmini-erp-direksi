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
    <div v-else-if="data" class="grid grid-cols-4 gap-5">
      <KpiCard title="Pendapatan" :value="fmt(data.kpi.pendapatan)" :delta="'▲ vs bln lalu'" :delta-up="true" />
      <KpiCard title="Laba Bersih" :value="fmt(data.kpi.laba)" />
      <KpiCard title="PO Pending" :value="data.kpi.po_pending" :delta="'menunggu approval'" :delta-up="false" />
      <div class="card">
        <div class="kpi-label">Pemakaian Anggaran</div>
        <div class="kpi-value">{{ data.kpi.pemakaian_anggaran }}%</div>
        <StatusBadge v-if="data.indikator_anggaran" :status="data.indikator_anggaran" />
      </div>
    </div>
  </div>
</template>
