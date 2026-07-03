<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const status = ref('menunggu')
const list = ref<any[]>([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const res: any = await api(`/po?status=${status.value}`)
    list.value = res.data ?? []
  } finally {
    loading.value = false
  }
}
onMounted(load)

const fmt = (n: number) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
const fmtTgl = (t: string) => {
  if (!t) return '-'
  return new Date(t).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
const badge = (s: string) => (s === 'approved' ? 'hijau' : s === 'rejected' ? 'merah' : 'kuning')
</script>
<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Approval PO</h1>
      <select v-model="status" @change="load" class="select-field">
        <option value="menunggu">Menunggu</option>
        <option value="approved">Disetujui</option>
        <option value="rejected">Ditolak</option>
        <option value="draft">Draft</option>
        <option value="semua">Semua</option>
      </select>
    </div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <div v-else-if="!list.length" class="empty-text">Tidak ada PO pada status ini.</div>
    <ul v-else class="space-y-3">
      <li v-for="po in list" :key="po.id">
        <NuxtLink :to="`/approval/${po.id}`" class="list-item-card">
          <div class="min-w-0">
            <div class="font-display font-bold text-ink">{{ po.id }}</div>
            <div class="text-sm text-grey truncate">
              {{ po.supplier }} · {{ po.departemen }} · {{ fmtTgl(po.tanggal) }}
            </div>
          </div>
          <div class="flex items-center gap-4 flex-none">
            <span class="font-mono text-sm text-ink">{{ fmt(po.nilai) }}</span>
            <StatusBadge :status="badge(po.status)">{{ po.status }}</StatusBadge>
          </div>
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>
