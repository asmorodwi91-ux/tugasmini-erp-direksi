<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
const { api } = useApi()
const users = ref<any[]>([])
const loading = ref(true)
const msg = ref('')

async function load() {
  loading.value = true
  try {
    const res: any = await api('/access/users')
    users.value = res.data || []
  } finally { loading.value = false }
}
onMounted(load)
</script>
<template>
  <div>
    <h1 class="page-title mb-6">Manajemen Hak Akses</h1>

    <div v-if="loading" class="loading-text">Memuat data...</div>
    <div v-else class="table-shell">
      <table>
        <thead>
          <tr>
            <th>Nama</th><th>Role</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id">
            <td class="font-medium text-ink">{{ u.nama }}</td>
            <td>{{ u.role }}</td>
            <td><StatusBadge :status="u.status === 'Aktif' ? 'hijau' : 'merah'" /></td>
          </tr>
        </tbody>
      </table>
    </div>
    <p class="text-xs text-grey mt-4 leading-relaxed">
      Catatan: perubahan hak akses (POST /access/save) tercatat di audit log &amp; berlaku langsung.
    </p>
  </div>
</template>
