<script setup lang="ts">
const auth = useAuthStore()
const { logout } = useAuthActions()
const { menuTampil } = useAccess()
</script>
<template>
  <div class="min-h-screen flex">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="sidebar-logo">🏢</div>
        <span class="font-display font-extrabold text-primary-dark">Mini ERP</span>
      </div>
      <NuxtLink v-for="m in menuTampil" :key="m.to" :to="m.to"
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
