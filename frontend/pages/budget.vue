<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()

// Form ajukan usulan
const idDept = ref(3)
const periode = ref('2026-02')
const plafon = ref(850000000)
const msgAjukan = ref('')

// Keputusan
const idUsulan = ref<number | null>(null)
const detail = ref<any>(null)
const catatan = ref('')
const msgPutusan = ref('')

const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')

async function ajukan() {
  msgAjukan.value = ''
  try {
    const res: any = await api('/budget/proposal', {
      method: 'POST', body: { id_dept: idDept.value, periode: periode.value, plafon: plafon.value },
    })
    idUsulan.value = res.id
    msgAjukan.value = `Usulan #${res.id} diajukan (status: ${res.status})`
    await loadDetail()
  } catch (e: any) { msgAjukan.value = 'Gagal mengajukan' }
}
async function loadDetail() {
  if (!idUsulan.value) return
  detail.value = await api(`/budget/proposal/${idUsulan.value}`)
}
async function putuskan(keputusan: string) {
  msgPutusan.value = ''
  try {
    const res: any = await api('/budget/decision', {
      method: 'POST', body: { id: idUsulan.value, keputusan, catatan: catatan.value },
    })
    msgPutusan.value = `Keputusan tersimpan: ${res.status}`
    await loadDetail()
  } catch (e: any) { msgPutusan.value = 'Gagal' }
}
</script>
<template>
  <div class="max-w-2xl">
    <h1 class="page-title mb-6">Review &amp; Approve Anggaran</h1>

    <div class="card mb-5 space-y-4">
      <div class="section-title">1. Ajukan Usulan Anggaran</div>
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="form-label text-grey">ID Dept</label>
          <input v-model.number="idDept" class="input-field mt-1.5" />
        </div>
        <div>
          <label class="form-label text-grey">Periode</label>
          <input v-model="periode" class="input-field mt-1.5" />
        </div>
        <div>
          <label class="form-label text-grey">Plafon</label>
          <input v-model.number="plafon" class="input-field mt-1.5" />
        </div>
      </div>
      <button @click="ajukan" class="btn-primary">Ajukan</button>
      <div v-if="msgAjukan" class="text-sm text-primary">{{ msgAjukan }}</div>
    </div>

    <div v-if="detail" class="card space-y-4">
      <div class="section-title">2. Keputusan Direktur Utama</div>
      <div class="text-sm text-grey">
        Usulan #{{ detail.id }} · {{ detail.departemen }} · {{ detail.periode }} ·
        <StatusBadge :status="detail.status === 'approved' ? 'hijau' : detail.status === 'revisi' ? 'kuning' : 'kuning'" />
      </div>
      <div class="font-display text-xl font-extrabold tracking-tight">{{ fmt(detail.plafon_diajukan) }}</div>
      <textarea v-model="catatan" placeholder="Catatan (opsional)"
        class="input-field" rows="2"></textarea>
      <div class="flex gap-3">
        <button @click="putuskan('setujui')" class="btn-success">Setujui</button>
        <button @click="putuskan('revisi')" class="btn-warning">Minta Revisi</button>
      </div>
      <div v-if="msgPutusan" class="text-sm text-primary">{{ msgPutusan }}</div>
    </div>
  </div>
</template>
