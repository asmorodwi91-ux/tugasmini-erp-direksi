<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()

const list = ref<any[]>([])
const idUsulan = ref<number | null>(null)
const detail = ref<any>(null)
const catatan = ref('')
const msg = ref('')
const err = ref('')
const submitting = ref(false)

const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
const fmtJt = (n: number) => {
  const v = Number(n || 0)
  if (Math.abs(v) >= 1e9) return 'Rp ' + (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M'
  return 'Rp ' + Math.round(v / 1e6).toLocaleString('id-ID') + ' jt'
}
const fmtTgl = (t: string) => t ? new Date(t).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'

const statusMap: Record<string, { warna: string; label: string }> = {
  menunggu: { warna: 'kuning', label: 'Menunggu' },
  approved: { warna: 'hijau', label: 'Disetujui' },
  revisi: { warna: 'kuning', label: 'Revisi' },
}
const statusInfo = computed(() => statusMap[detail.value?.status] ?? { warna: 'kuning', label: detail.value?.status })
const sudahDiputus = computed(() => ['approved', 'revisi'].includes(detail.value?.status))

async function loadList() {
  const res: any = await api('/budget/proposals')
  list.value = res.data ?? []
  if (!idUsulan.value && list.value.length) {
    idUsulan.value = list.value[0].id
    await loadDetail()
  }
}
async function loadDetail() {
  if (!idUsulan.value) return
  msg.value = ''; err.value = ''
  detail.value = await api(`/budget/proposal/${idUsulan.value}`)
  catatan.value = ''
}
async function pilih(id: number) {
  idUsulan.value = id
  await loadDetail()
}
async function putuskan(keputusan: 'setujui' | 'revisi') {
  msg.value = ''; err.value = ''
  if (keputusan === 'revisi' && !catatan.value.trim()) {
    err.value = 'Catatan wajib diisi saat meminta revisi.'
    return
  }
  submitting.value = true
  try {
    const res: any = await api('/budget/decision', {
      method: 'POST', body: { id: idUsulan.value, keputusan, catatan: catatan.value },
    })
    msg.value = res.status === 'approved' ? 'Usulan anggaran disetujui.' : 'Usulan dikembalikan untuk revisi.'
    await loadDetail()
    await loadList()
  } catch (e: any) {
    err.value = e?.data?.error || 'Gagal menyimpan keputusan.'
  } finally {
    submitting.value = false
  }
}
onMounted(loadList)
</script>
<template>
  <div>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="page-title">Review &amp; Approve Anggaran</h1>
      <StatusBadge v-if="detail" :status="statusInfo.warna">Status: {{ statusInfo.label }}</StatusBadge>
    </div>

    <!-- Pemilih usulan -->
    <div v-if="list.length" class="flex flex-wrap gap-2 mb-5">
      <button v-for="u in list" :key="u.id" @click="pilih(u.id)"
        class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
        :class="idUsulan === u.id ? 'bg-primary text-white border-primary' : 'bg-white text-grey border-line hover:border-grey-light'">
        {{ u.departemen }} · {{ u.periode }}
      </button>
    </div>

    <div v-if="detail" class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
      <!-- Kolom kiri -->
      <div class="lg:col-span-2 space-y-5">
        <div class="card">
          <div class="kpi-label mb-4">Usulan Anggaran — Dept. {{ detail.departemen }} · Periode {{ detail.periode }}</div>
          <div class="grid grid-cols-2 gap-y-5 gap-x-8">
            <div>
              <div class="text-xs text-grey mb-1">Plafon Diajukan</div>
              <div class="font-display font-bold text-ink">{{ fmt(detail.plafon_diajukan) }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Diajukan oleh</div>
              <div class="font-semibold text-ink">{{ detail.pengaju || '-' }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Tanggal Pengajuan</div>
              <div class="font-semibold text-ink">{{ fmtTgl(detail.tanggal_pengajuan) }}</div>
            </div>
            <div>
              <div class="text-xs text-grey mb-1">Realisasi Periode Lalu</div>
              <div class="font-semibold text-ink">{{ fmt(detail.realisasi_lalu) }}</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="kpi-label mb-3">Pembanding: Usulan vs Realisasi</div>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-grey text-left border-b border-line">
                <th class="py-2.5 font-medium text-xs uppercase tracking-wide">Pos Anggaran</th>
                <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-right">Realisasi Lalu</th>
                <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-right">Plafon Diajukan</th>
                <th class="py-2.5 font-medium text-xs uppercase tracking-wide text-right">Selisih</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in detail.rincian" :key="i" class="border-b border-line/60 last:border-0">
                <td class="py-3 text-ink">{{ r.pos }}</td>
                <td class="py-3 text-right font-mono text-grey">{{ fmtJt(r.realisasi_lalu) }}</td>
                <td class="py-3 text-right font-mono">{{ fmtJt(r.plafon_diajukan) }}</td>
                <td class="py-3 text-right font-mono"
                  :class="(r.selisih_persen ?? 0) > 0 ? 'text-status-merah' : 'text-status-hijau'">
                  {{ r.selisih_persen === null ? '-' : (r.selisih_persen > 0 ? '+' : '') + r.selisih_persen + '%' }}
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="border-t border-line font-bold">
                <td class="py-3">Total</td>
                <td class="py-3 text-right font-mono">{{ fmtJt(detail.realisasi_lalu) }}</td>
                <td class="py-3 text-right font-mono">{{ fmtJt(detail.plafon_diajukan) }}</td>
                <td class="py-3"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Kolom kanan: keputusan -->
      <div class="card">
        <div class="kpi-label mb-4">Keputusan</div>

        <div v-if="sudahDiputus"
          class="text-sm rounded-btn px-4 py-3 mb-4"
          :class="detail.status === 'approved' ? 'bg-status-hijau-soft text-status-hijau' : 'bg-status-kuning-soft text-status-kuning'">
          Usulan ini sudah <strong>{{ statusInfo.label.toLowerCase() }}</strong>.
          <div v-if="detail.catatan" class="mt-1 text-grey font-normal">Catatan: {{ detail.catatan }}</div>
        </div>

        <template v-else>
          <label class="form-label text-grey">Catatan</label>
          <textarea v-model="catatan" placeholder="Tulis catatan / arahan revisi..."
            class="input-field mt-1.5 mb-4" rows="4"></textarea>

          <button @click="putuskan('setujui')" :disabled="submitting" class="btn-primary w-full mb-3">
            &check; Setujui Anggaran
          </button>
          <button @click="putuskan('revisi')" :disabled="submitting" class="btn-outline w-full">
            &circlearrowleft; Minta Revisi
          </button>
        </template>

        <p v-if="err" class="text-sm text-status-merah mt-3">{{ err }}</p>
        <p v-if="msg" class="text-sm text-status-hijau mt-3">{{ msg }}</p>

        <div class="mt-5 pt-4 border-t border-line/60 space-y-2 text-xs text-grey">
          <div>✅ Disetujui → simpan + jejak audit → jadi plafon acuan di Modul 3 (indikator warna anggaran).</div>
          <div>↺ Revisi → dikembalikan ke Manager Keuangan.</div>
        </div>
      </div>
    </div>

    <div v-else class="loading-text">Memuat usulan anggaran...</div>
  </div>
</template>
