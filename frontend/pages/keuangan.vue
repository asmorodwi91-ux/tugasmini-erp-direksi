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
const fmtJt = (n: number) => 'Rp ' + Math.round(Number(n || 0) / 1000000).toLocaleString('id-ID') + ' jt'
const buckets = ['0-30', '31-60', '61-90', '>90']
</script>
<template>
  <div>
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
      <h1 class="font-display text-2xl font-extrabold">Laporan Keuangan</h1>
      <div class="flex gap-2">
        <select v-model="periode" @change="load" class="border border-line rounded-full px-4 py-1.5 text-sm bg-white">
          <option value="2026-01">Periode: Jan 2026</option>
          <option value="2026-06">Periode: Jun 2026</option>
        </select>
        <button class="border border-line rounded-full px-4 py-1.5 text-sm bg-white">Departemen: Semua ▾</button>
        <button class="bg-primary text-white rounded-full px-4 py-1.5 text-sm font-semibold">⬇ Ekspor</button>
      </div>
    </div>

    <div v-if="loading" class="text-grey">Memuat data...</div>
    <div v-else-if="data">
      <!-- KPI CARDS -->
      <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-xs text-grey uppercase tracking-wide">Laba / Rugi (P&L)</div>
          <div class="font-display text-2xl font-extrabold my-2">{{ fmtJt(data.pl) }}</div>
          <span class="inline-block text-xs font-semibold rounded-full px-3 py-1 bg-green-100 text-status-hijau">▲ Surplus</span>
        </div>
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-xs text-grey uppercase tracking-wide">Arus Kas</div>
          <div class="font-display text-2xl font-extrabold my-2">{{ fmtJt(data.arus_kas) }}</div>
          <span class="inline-block text-xs font-semibold rounded-full px-3 py-1 bg-green-100 text-status-hijau">Positif</span>
        </div>
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-xs text-grey uppercase tracking-wide">Pemakaian Anggaran</div>
          <div class="font-display text-2xl font-extrabold my-2">{{ data.pemakaian_anggaran }}%</div>
          <span v-if="data.indikator_warna"
            class="inline-block text-xs font-semibold rounded-full px-3 py-1"
            :class="{
              'bg-green-100 text-status-hijau': data.indikator_warna==='hijau',
              'bg-yellow-100 text-status-kuning': data.indikator_warna==='kuning',
              'bg-red-100 text-status-merah': data.indikator_warna==='merah'
            }">
            {{ data.indikator_warna }} · 70-90%
          </span>
        </div>
      </div>

      <!-- AGING + CHART -->
      <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Aging table -->
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-sm font-semibold text-grey uppercase tracking-wide mb-3">Aging Hutang-Piutang</div>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-grey text-left border-b border-line">
                <th class="py-2">Umur</th><th class="py-2 text-right">Hutang</th><th class="py-2 text-right">Piutang</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="b in buckets" :key="b" class="border-b border-line last:border-0">
                <td class="py-2">{{ b }} hari</td>
                <td class="py-2 text-right font-mono">{{ fmtJt(data.aging?.hutang?.[b]) }}</td>
                <td class="py-2 text-right font-mono">{{ fmtJt(data.aging?.piutang?.[b]) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Chart -->
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-sm font-semibold text-grey uppercase tracking-wide mb-3">Tren Laba &amp; Arus Kas</div>
          <svg viewBox="0 0 320 140" preserveAspectRatio="none" class="w-full" style="height:150px">
            <polyline points="10,110 65,95 120,100 175,75 230,60 300,40" fill="none" stroke="#1f4e79" stroke-width="3"/>
            <polyline points="10,125 65,115 120,118 175,105 230,95 300,80" fill="none" stroke="#2E7D4F" stroke-width="3"/>
          </svg>
          <div class="flex gap-4 text-xs text-grey mt-2">
            <span>● <span class="text-primary">Laba (biru)</span></span>
            <span>● <span class="text-status-hijau">Arus kas (hijau)</span></span>
          </div>
        </div>
      </div>

      <!-- Info banner -->
      <div class="bg-[#FAF0D7] border-l-4 border-status-kuning rounded-lg px-4 py-3 text-sm text-[#8A5D12]">
        ⚠️ Indikator anggaran ≥ 80% akan memicu peringatan otomatis ke Direksi (Modul 5). Warna: hijau &lt;70% · kuning 70-90% · merah &gt;90%.
      </div>
    </div>
  </div>
</template>
