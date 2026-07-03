<script setup lang="ts">
const auth = useAuthStore()
const { logout } = useAuthActions()
const menu = [
  { to: '/', label: '📊 Dashboard' },
  { to: '/approval', label: '✅ Approval PO' },
  { to: '/keuangan', label: '💰 Laporan Keuangan' },
  { to: '/operasional', label: '📈 Kinerja Operasional' },
  { to: '/notifikasi', label: '🔔 Notifikasi' },
  { to: '/hak-akses', label: '🔑 Hak Akses' },
  { to: '/ekspor', label: '⬇️ Ekspor Laporan' },
  { to: '/budget', label: '🧮 Review Anggaran' },
]
</script>
<template>
  <div class="min-h-screen flex">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="sidebar-logo">🏢</div>
        <span class="font-display font-extrabold text-primary-dark">Mini ERP</span>
      </div>
      <NuxtLink v-for="m in menu" :key="m.to" :to="m.to"
        class="nav-link"
        active-class="nav-link-active">
        {{ m.label }}
      </NuxtLink>
    </aside>
    <div class="flex-1 flex flex-col">
      <header class="topbar">
        <div class="topbar-title">Modul Direksi</div>
        <div class="flex items-center gap-4 text-sm">
          <span class="text-grey">{{ auth.user?.nama }} · {{ auth.user?.role }}</span>
          <button @click="logout" class="btn-ghost">Keluar</button>
        </div>
      </header>
      <main class="main-content"><slot /></main>
    </div>
  </div>
</template>
