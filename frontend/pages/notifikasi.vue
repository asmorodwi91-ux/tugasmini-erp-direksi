<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const level = ref('semua')
const items = ref<any[]>([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const res: any = await api(`/notifications?level=${level.value}`)
    items.value = res.data || []
  } finally { loading.value = false }
}
async function markRead(id: number) {
  await api(`/notifications/${id}/read`, { method: 'POST' })
  await load()
}
onMounted(load)
const lvlColor = (l: string) => l === 'kritis' ? 'merah' : l === 'warning' ? 'kuning' : 'hijau'
</script>
<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Notifikasi &amp; Early Warning</h1>
      <select v-model="level" @change="load" class="select-field">
        <option value="semua">Semua</option>
        <option value="kritis">Kritis</option>
        <option value="warning">Warning</option>
        <option value="info">Info</option>
      </select>
    </div>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <div v-else-if="items.length" class="space-y-3">
      <div v-for="n in items" :key="n.id_notif"
        class="list-item-card"
        :class="{ 'opacity-60': n.dibaca }">
        <div class="flex items-center gap-4">
          <StatusBadge :status="lvlColor(n.level_kritis)" />
          <div>
            <div class="text-sm font-medium text-ink">{{ n.pesan }}</div>
            <div class="text-xs text-grey mt-0.5">{{ n.jenis }} · {{ n.sumber_modul }}</div>
          </div>
        </div>
        <button v-if="!n.dibaca" @click="markRead(n.id_notif)"
          class="btn-outline text-xs px-3 py-1.5 whitespace-nowrap">
          Tandai dibaca
        </button>
        <span v-else class="text-xs text-grey">✓ dibaca</span>
      </div>
    </div>
    <div v-else class="empty-text">Tidak ada notifikasi.</div>
  </div>
</template>
