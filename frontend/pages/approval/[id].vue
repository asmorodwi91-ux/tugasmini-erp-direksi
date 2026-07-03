<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const route = useRoute()
const { api } = useApi()
const auth = useAuthStore()
const bisaSetujui = computed(() => auth.bisa('Approval', 'setujui'))
const po = ref<any>(null)
const catatan = ref('')
const msg = ref('')
const err = ref('')
const submitting = ref(false)

async function load() {
  po.value = await api(`/po/${route.params.id}`)
}
async function decide(keputusan: 'setuju' | 'tolak') {
  msg.value = ''
  err.value = ''
  if (keputusan === 'tolak' && !catatan.value.trim()) {
    err.value = 'Catatan wajib diisi bila menolak PO.'
    return
  }
  submitting.value = true
  try {
    const res: any = await api(`/po/${route.params.id}/decision`, {
      method: 'POST',
      body: { keputusan, catatan: catatan.value },
    })
    msg.value = keputusan === 'setuju' ? 'PO berhasil disetujui.' : 'PO ditolak.'
    catatan.value = ''
    await load()
  } catch (e: any) {
    err.value = e?.data?.error === 'catatan_wajib_saat_menolak'
      ? 'Catatan wajib diisi bila menolak PO.'
      : (e?.data?.error || 'Gagal menyimpan keputusan.')
  } finally {
    submitting.value = false
  }
}
onMounted(load)

const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
const fmtTgl = (t: string) => {
  if (!t) return '-'
  return new Date(t).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
const statusMap: Record<string, { warna: string; label: string }> = {
  menunggu: { warna: 'kuning', label: 'Menunggu' },
  approved: { warna: 'hijau', label: 'Disetujui' },
  rejected: { warna: 'merah', label: 'Ditolak' },
  draft: { warna: 'kuning', label: 'Draft' },
}
const statusInfo = computed(() => statusMap[po.value?.status] ?? { warna: 'kuning', label: po.value?.status })
const sudahDiputus = computed(() => ['approved', 'rejected'].includes(po.value?.status))
</script>
<template>
  <div v-if="po">
    <NuxtLink to="/approval" class="text-sm text-grey hover:text-ink">&larr; Kembali ke daftar</NuxtLink>

    <div class="flex items-center justify-between mt-2 mb-6">
      <h1 class="page-title">Persetujuan PO Besar</h1>
      <StatusBadge :status="statusInfo.warna">Status: {{ statusInfo.label }}</StatusBadge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
      <!-- Kolom kiri: informasi & rincian -->
      <div class="lg:col-span-2 space-y-5">
        <div class="card">
          <div class="kpi-label mb-4">Informasi PO</div>
          <div class="grid grid-cols-2 gap-y-5 gap-x-8">
            <div>
              <div class="text-xs text-grey mb-1">No. PO</div>
              <div class="font-display font-bold text-ink">{{ po.id }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Tanggal</div>
              <div class="font-semibold text-ink">{{ fmtTgl(po.tanggal) }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Supplier</div>
              <div class="font-semibold text-ink">{{ po.supplier }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Nilai PO</div>
              <div class="font-semibold text-ink font-mono">{{ fmt(po.nilai) }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Departemen</div>
              <div class="font-semibold text-ink">{{ po.departemen }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Dibuat oleh</div>
              <div class="font-semibold text-ink">{{ po.dibuat_oleh || '-' }}</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="kpi-label mb-3">Rincian Item</div>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-grey text-left border-b border-line/60">
                <th class="py-2 font-medium text-xs uppercase tracking-wide">Item</th>
                <th class="py-2 font-medium text-xs uppercase tracking-wide text-center">Qty</th>
                <th class="py-2 font-medium text-xs uppercase tracking-wide text-right">Harga Satuan</th>
                <th class="py-2 font-medium text-xs uppercase tracking-wide text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(it, i) in po.items" :key="i" class="border-b border-line/40 last:border-0">
                <td class="py-3">{{ it.nama }}</td>
                <td class="py-3 text-center">{{ it.qty }}</td>
                <td class="py-3 text-right font-mono">{{ fmt(it.harga_satuan) }}</td>
                <td class="py-3 text-right font-mono">{{ fmt(it.subtotal) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="border-t border-line">
                <td class="py-3 font-bold" colspan="3">Total</td>
                <td class="py-3 text-right font-bold font-mono">{{ fmt(po.nilai) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Kolom kanan: keputusan direksi -->
      <div class="card">
        <div class="kpi-label mb-4">Keputusan Direksi</div>

        <div v-if="sudahDiputus"
          class="text-sm rounded-btn px-4 py-3 mb-4"
          :class="po.status === 'approved' ? 'bg-status-hijau-soft text-status-hijau' : 'bg-status-merah-soft text-status-merah'">
          PO ini sudah <strong>{{ statusInfo.label.toLowerCase() }}</strong>.
        </div>

        <div v-else-if="!bisaSetujui"
          class="text-sm rounded-btn px-4 py-3 bg-bg-soft text-grey">
          Anda hanya dapat melihat PO ini. Persetujuan memerlukan wewenang <strong>setujui</strong> pada modul Approval.
        </div>

        <template v-else>
          <label class="form-label text-grey">Catatan (wajib bila menolak)</label>
          <textarea v-model="catatan" placeholder="Tulis catatan / alasan..."
            class="input-field mt-1.5 mb-4" rows="4"></textarea>

          <button @click="decide('setuju')" :disabled="submitting" class="btn-primary w-full mb-3">
            &check; Setujui PO
          </button>
          <button @click="decide('tolak')" :disabled="submitting"
            class="btn-outline w-full !border-status-merah !text-status-merah hover:!bg-status-merah-soft">
            &times; Tolak PO
          </button>
        </template>

        <p v-if="err" class="text-sm text-status-merah mt-3">{{ err }}</p>
        <p v-if="msg" class="text-sm text-status-hijau mt-3">{{ msg }}</p>

        <div class="mt-5 pt-4 border-t border-line/60 space-y-2 text-xs text-grey">
          <div class="flex items-center gap-2">🔒 Tautan berlaku 1&times; / 24 jam</div>
          <div class="flex items-center gap-2">📍 Perangkat &amp; IP dicatat otomatis</div>
          <div class="flex items-center gap-2">🛡 Keputusan tersimpan ke jejak audit</div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="loading-text">Memuat data PO...</div>
</template>
