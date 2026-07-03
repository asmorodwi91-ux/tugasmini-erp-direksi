<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const { periodeOptions } = usePeriode()
const jenis = ref('keuangan')
const periode = ref('2026-01')
const idDept = ref('semua')
const format = ref<'pdf' | 'excel'>('pdf')
const error = ref('')
const sukses = ref('')
const loading = ref(false)

const departemenList = ref<any[]>([])
const riwayat = ref<any[]>([])

async function loadMeta() {
  try {
    const [fin, hist]: any[] = await Promise.all([
      api(`/finance/report?periode=${periode.value}`),
      api('/export/history'),
    ])
    departemenList.value = fin?.departemen_list ?? []
    riwayat.value = hist?.data ?? []
  } catch { /* abaikan */ }
}
async function loadHistory() {
  try { riwayat.value = (await api('/export/history') as any)?.data ?? [] } catch {}
}
onMounted(loadMeta)

async function ekspor() {
  error.value = ''; sukses.value = ''; loading.value = true
  try {
    const blob: Blob = await api('/export', {
      method: 'POST',
      body: { jenis: jenis.value, periode: periode.value, format: format.value, id_dept: idDept.value },
      responseType: 'blob',
    })
    const ext = format.value === 'excel' ? 'csv' : 'pdf'
    const namaFile = `${jenis.value}-${periode.value}.${ext}`
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = namaFile
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
    sukses.value = `Berhasil — ${namaFile} terunduh.`
    await loadHistory()
  } catch (e: any) {
    const status = e?.status || e?.response?.status
    error.value = status === 403 ? 'Akses ditolak — Anda tidak punya izin ekspor.' : 'Gagal mengekspor laporan.'
  } finally {
    loading.value = false
    setTimeout(() => { sukses.value = ''; error.value = '' }, 6000)
  }
}
</script>
<template>
  <div>
    <h1 class="page-title mb-6">Ekspor Laporan</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
      <!-- Kolom kiri: form -->
      <div class="card">
        <div class="kpi-label mb-4">Buat Ekspor Baru</div>

        <label class="form-label text-grey">Jenis Laporan</label>
        <select v-model="jenis" class="input-field mt-1.5 mb-4">
          <option value="keuangan">Laporan Keuangan</option>
          <option value="operasional">Kinerja Operasional</option>
          <option value="approval">Riwayat Approval</option>
        </select>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="form-label text-grey">Periode</label>
            <select v-model="periode" @change="loadMeta" class="input-field mt-1.5">
              <option v-for="p in periodeOptions" :key="p.value" :value="p.value">{{ p.label }}</option>
            </select>
          </div>
          <div>
            <label class="form-label text-grey">Departemen</label>
            <select v-model="idDept" class="input-field mt-1.5">
              <option value="semua">Semua</option>
              <option v-for="d in departemenList" :key="d.id_dept" :value="String(d.id_dept)">
                {{ d.nama_dept }}
              </option>
            </select>
          </div>
        </div>

        <label class="form-label text-grey">Format</label>
        <div class="grid grid-cols-2 gap-4 mt-1.5 mb-5">
          <button type="button" @click="format = 'pdf'"
            class="rounded-xl border-2 py-5 flex flex-col items-center gap-2 transition-all"
            :class="format === 'pdf' ? 'border-primary bg-primary-soft' : 'border-line bg-white hover:border-grey-light'">
            <span class="text-2xl">📄</span>
            <span class="text-sm font-semibold" :class="format === 'pdf' ? 'text-primary-dark' : 'text-grey'">PDF</span>
          </button>
          <button type="button" @click="format = 'excel'"
            class="rounded-xl border-2 py-5 flex flex-col items-center gap-2 transition-all"
            :class="format === 'excel' ? 'border-primary bg-primary-soft' : 'border-line bg-white hover:border-grey-light'">
            <span class="text-2xl">📊</span>
            <span class="text-sm font-semibold" :class="format === 'excel' ? 'text-primary-dark' : 'text-grey'">Excel</span>
          </button>
        </div>

        <button @click="ekspor" :disabled="loading" class="btn-primary w-full">
          {{ loading ? 'Memproses...' : 'Buat & Unduh' }}
        </button>

        <p v-if="sukses" class="text-sm text-status-hijau mt-3">✓ {{ sukses }}</p>
        <p v-if="error" class="text-sm text-status-merah mt-3">⚠️ {{ error }}</p>

        <p class="text-xs text-grey mt-4 flex items-center gap-2">
          🔒 Sistem memeriksa izin unduh dulu. Bila tidak punya izin → akses ditolak.
        </p>
      </div>

      <!-- Kolom kanan: riwayat unduh -->
      <div class="card">
        <div class="kpi-label mb-4">Riwayat Unduh</div>

        <table v-if="riwayat.length" class="w-full text-sm">
          <thead>
            <tr class="text-grey text-left border-b border-line">
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide">Laporan</th>
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-center">Format</th>
              <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-right">Waktu Unduh</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in riwayat" :key="r.id" class="border-b border-line/60 last:border-0">
              <td class="py-3 text-ink">{{ r.laporan }}</td>
              <td class="py-3 text-center">
                <span class="inline-block text-[11px] font-bold rounded px-2 py-0.5 bg-bg-soft text-grey">{{ r.format }}</span>
              </td>
              <td class="py-3 text-right text-grey font-mono">{{ r.waktu }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-text">Belum ada riwayat unduh.</div>

        <p class="text-xs text-grey mt-4 flex items-center gap-2">
          🚩 Tiap file distempel nama pengguna + waktu unduh &amp; tercatat di audit.
        </p>
      </div>
    </div>
  </div>
</template>
