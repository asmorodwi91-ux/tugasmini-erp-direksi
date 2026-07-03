<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const periode = ref('2026-01')
const data = ref<any>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try { data.value = await api(`/ops/scorecard?periode=${periode.value}`) }
  finally { loading.value = false }
}
onMounted(load)
const skorColor = (s: string) => s === 'A' ? 'hijau' : s === 'B' ? 'kuning' : 'merah'
</script>
<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Kinerja Operasional</h1>
      <select v-model="periode" @change="load" class="select-field">
        <option value="2026-01">Periode: Jan 2026</option>
        <option value="2026-06">Periode: Jun 2026</option>
      </select>
    </div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <div v-else-if="data" class="grid grid-cols-2 gap-5">
      <div v-for="(d, i) in data.departemen" :key="i" class="card-interactive">
        <div class="flex items-center justify-between mb-3">
          <div class="font-semibold text-ink">{{ d.nama }}</div>
          <StatusBadge :status="skorColor(d.skor)" />
        </div>
        <div class="text-xs text-grey capitalize mb-3">{{ d.kategori }}</div>
        <div class="flex items-end gap-5 text-sm">
          <div><span class="text-grey">Aktual:</span> <span class="font-mono font-bold">{{ d.aktual }}</span></div>
          <div><span class="text-grey">Target:</span> <span class="font-mono">{{ d.target }}</span></div>
          <div class="ml-auto font-display text-xl font-extrabold tracking-tight">{{ d.skor }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
