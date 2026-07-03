<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const route = useRoute()
const { api } = useApi()
const po = ref<any>(null)
const catatan = ref('')
const msg = ref('')

async function load() { po.value = await api(`/po/${route.params.id}`) }
async function decide(keputusan: string) {
  try {
    const res: any = await api(`/po/${route.params.id}/decision`, {
      method: 'POST', body: { keputusan, catatan: catatan.value },
    })
    msg.value = `Keputusan tersimpan: ${res.status}`
    await load()
  } catch (e: any) { msg.value = e?.data?.error || 'Gagal' }
}
onMounted(load)
const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
</script>
<template>
  <div v-if="po" class="max-w-2xl">
    <h1 class="page-title mb-1">{{ po.id }}</h1>
    <div class="text-muted mb-6">{{ po.supplier }} · {{ po.departemen }} ·
      <StatusBadge :status="po.status === 'approved' ? 'hijau' : po.status === 'rejected' ? 'merah' : 'kuning'" />
    </div>
    <div class="card mb-5">
      <div class="kpi-label mb-3">Item</div>
      <div v-for="(it, i) in po.items" :key="i" class="flex justify-between text-sm py-2 border-b border-line/60 last:border-0">
        <span>{{ it.nama }} × {{ it.qty }}</span><span class="font-mono">{{ fmt(it.subtotal) }}</span>
      </div>
      <div class="flex justify-between font-bold mt-4 pt-2"><span>Total</span><span class="font-mono">{{ fmt(po.nilai) }}</span></div>
    </div>
    <textarea v-model="catatan" placeholder="Catatan (wajib bila menolak)"
      class="input-field mb-4" rows="2"></textarea>
    <div class="flex gap-3">
      <button @click="decide('setuju')" class="btn-success">Setujui</button>
      <button @click="decide('tolak')" class="btn-danger">Tolak</button>
    </div>
    <div v-if="msg" class="mt-4 text-sm text-primary">{{ msg }}</div>
  </div>
</template>
