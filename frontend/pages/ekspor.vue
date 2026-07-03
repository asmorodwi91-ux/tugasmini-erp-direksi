<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const jenis = ref('keuangan')
const periode = ref('2026-01')
const format = ref('pdf')
const hasil = ref<any>(null)
const error = ref('')
const loading = ref(false)

async function ekspor() {
  error.value = ''; hasil.value = null; loading.value = true
  try {
    hasil.value = await api('/export', {
      method: 'POST',
      body: { jenis: jenis.value, periode: periode.value, format: format.value },
    })
  } catch (e: any) {
    error.value = e?.data?.error === 'akses_ditolak' ? 'Akses ditolak — Anda tidak punya izin ekspor.' : 'Gagal ekspor'
  } finally { loading.value = false }
}
</script>
<template>
  <div class="max-w-xl">
    <h1 class="page-title mb-6">Ekspor Laporan</h1>

    <div class="card space-y-5">
      <div>
        <label class="form-label">Jenis Laporan</label>
        <select v-model="jenis" class="input-field mt-1.5">
          <option value="keuangan">Laporan Keuangan</option>
          <option value="operasional">Kinerja Operasional</option>
          <option value="approval">Riwayat Approval</option>
        </select>
      </div>
      <div>
        <label class="form-label">Periode</label>
        <input v-model="periode" class="input-field mt-1.5" />
      </div>
      <div>
        <label class="form-label">Format</label>
        <div class="flex gap-4 mt-1.5">
          <label class="flex items-center gap-2 text-sm text-grey hover:text-ink transition-colors cursor-pointer"><input type="radio" value="pdf" v-model="format" /> PDF</label>
          <label class="flex items-center gap-2 text-sm text-grey hover:text-ink transition-colors cursor-pointer"><input type="radio" value="excel" v-model="format" /> Excel</label>
        </div>
      </div>
      <button @click="ekspor" :disabled="loading" class="btn-primary">
        {{ loading ? 'Memproses...' : 'Ekspor Sekarang' }}
      </button>
    </div>

    <div v-if="hasil" class="card mt-5 text-sm">
      <div class="text-status-hijau font-semibold mb-1.5">✓ Ekspor berhasil</div>
      <div class="text-grey">File: <span class="font-mono">{{ hasil.url }}</span></div>
      <div class="text-grey mt-0.5">Stempel: {{ hasil.stempel }}</div>
    </div>
    <div v-if="error" class="mt-5 text-sm text-status-merah">⚠️ {{ error }}</div>
  </div>
</template>
