<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const { periodeOptions } = usePeriode()
const periode = ref('2026-01')
const idDept = ref('semua')
const data = ref<any>(null)
const loading = ref(true)

const eksporMsg = ref('')
const eksporErr = ref('')
const eksporLoading = ref(false)

async function load() {
  loading.value = true
  try {
    data.value = await api(`/finance/report?periode=${periode.value}&id_dept=${idDept.value}`)
  } finally {
    loading.value = false
  }
}
onMounted(load)

async function ekspor(format: 'pdf' | 'excel' = 'pdf') {
  eksporMsg.value = ''
  eksporErr.value = ''
  eksporLoading.value = true
  try {
    const blob: Blob = await api('/export', {
      method: 'POST',
      body: { jenis: 'keuangan', periode: periode.value, format, id_dept: idDept.value },
      responseType: 'blob',
    })
    const ext = format === 'excel' ? 'csv' : 'pdf'
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `keuangan-${periode.value}.${ext}`
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
    eksporMsg.value = `Laporan ${ext.toUpperCase()} berhasil diunduh.`
  } catch (e: any) {
    const status = e?.status || e?.response?.status
    eksporErr.value = status === 403
      ? 'Akses ditolak — Anda tidak punya izin ekspor.'
      : 'Gagal mengekspor laporan.'
  } finally {
    eksporLoading.value = false
    setTimeout(() => { eksporMsg.value = ''; eksporErr.value = '' }, 6000)
  }
}

const buckets = ['0-30', '31-60', '61-90', '>90']

const ringkas = (n: number) => {
  const v = Number(n || 0)
  if (Math.abs(v) >= 1e9) return 'Rp ' + (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M'
  if (Math.abs(v) >= 1e6) return 'Rp ' + Math.round(v / 1e6).toLocaleString('id-ID') + ' jt'
  return 'Rp ' + v.toLocaleString('id-ID')
}

const indikatorText: Record<string, string> = {
  hijau: 'Hijau · <70%',
  kuning: 'Kuning · 70-90%',
  merah: 'Merah · >90%',
}

// Grafik tren laba & arus kas dari data real API
const trend = computed(() => {
  const rows: any[] = data.value?.tren ?? []
  if (!rows.length) return { laba: '', arus: '', months: [] as string[] }
  const max = Math.max(...rows.map((r) => Math.max(Number(r.arus_kas), Number(r.laba))), 1)
  const n = rows.length
  const x = (i: number) => (n === 1 ? 160 : 10 + (i / (n - 1)) * 300)
  const y = (v: number) => 130 - (Number(v) / max) * 110
  const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
  return {
    arus: rows.map((r, i) => `${x(i)},${y(r.arus_kas)}`).join(' '),
    laba: rows.map((r, i) => `${x(i)},${y(r.laba)}`).join(' '),
    months: rows.map((r) => BULAN[Number((r.periode || '').split('-')[1]) - 1] ?? r.periode),
  }
})
</script>
<template>
  <div>
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
      <h1 class="font-display text-2xl font-extrabold">Laporan Keuangan</h1>
      <div class="flex gap-2 items-center">
        <select v-model="periode" @change="load" class="border border-line rounded-full px-4 py-1.5 text-sm bg-white">
          <option v-for="p in periodeOptions" :key="p.value" :value="p.value">Periode: {{ p.label }}</option>
        </select>
        <select v-model="idDept" @change="load" class="border border-line rounded-full px-4 py-1.5 text-sm bg-white">
          <option value="semua">Departemen: Semua</option>
          <option v-for="d in (data?.departemen_list ?? [])" :key="d.id_dept" :value="String(d.id_dept)">
            {{ d.nama_dept }}
          </option>
        </select>
        <button @click="ekspor('pdf')" :disabled="eksporLoading"
          class="bg-primary text-white rounded-full px-4 py-1.5 text-sm font-semibold disabled:opacity-60">
          {{ eksporLoading ? 'Memproses...' : '⬇ Ekspor PDF' }}
        </button>
        <button @click="ekspor('excel')" :disabled="eksporLoading"
          class="border border-primary text-primary rounded-full px-4 py-1.5 text-sm font-semibold disabled:opacity-60">
          CSV
        </button>
      </div>
    </div>

    <div v-if="eksporMsg" class="mb-4 text-sm text-status-hijau">✓ {{ eksporMsg }}</div>
    <div v-if="eksporErr" class="mb-4 text-sm text-status-merah">⚠️ {{ eksporErr }}</div>

    <div v-if="loading" class="text-grey">Memuat data...</div>
    <div v-else-if="data">
      <!-- KPI CARDS -->
      <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-xs text-grey uppercase tracking-wide">Laba / Rugi (P&L)</div>
          <div class="font-display text-2xl font-extrabold my-2">{{ ringkas(data.pl) }}</div>
          <span class="inline-block text-xs font-semibold rounded-full px-3 py-1"
            :class="data.pl >= 0 ? 'bg-green-100 text-status-hijau' : 'bg-red-100 text-status-merah'">
            {{ data.pl >= 0 ? '▲ Surplus' : '▼ Defisit' }}
          </span>
        </div>
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-xs text-grey uppercase tracking-wide">Arus Kas</div>
          <div class="font-display text-2xl font-extrabold my-2">{{ ringkas(data.arus_kas) }}</div>
          <span class="inline-block text-xs font-semibold rounded-full px-3 py-1"
            :class="data.arus_kas >= 0 ? 'bg-green-100 text-status-hijau' : 'bg-red-100 text-status-merah'">
            {{ data.arus_kas >= 0 ? 'Positif' : 'Negatif' }}
          </span>
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
            {{ indikatorText[data.indikator_warna] }}
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
                <td class="py-2 text-right font-mono">{{ ringkas(data.aging?.hutang?.[b]) }}</td>
                <td class="py-2 text-right font-mono">{{ ringkas(data.aging?.piutang?.[b]) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Chart -->
        <div class="bg-white border border-line rounded-xl p-4 shadow-sm">
          <div class="text-sm font-semibold text-grey uppercase tracking-wide mb-3">Tren Laba &amp; Arus Kas</div>
          <svg v-if="trend.months.length" viewBox="0 0 320 140" preserveAspectRatio="none" class="w-full" style="height:150px">
            <polyline :points="trend.arus" fill="none" stroke="#2E7D4F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline :points="trend.laba" fill="none" stroke="#1f4e79" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <div v-else class="flex items-center justify-center text-sm text-grey" style="height:150px">Data tren belum tersedia</div>
          <div class="flex justify-between mt-1 text-[11px] text-grey">
            <span v-for="m in trend.months" :key="m">{{ m }}</span>
          </div>
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
