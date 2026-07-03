<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
// Daftar PO bisa ditambah endpoint /po (list) — saat ini contoh tautan ke detail.
const poContoh = ['PO-001', 'PO-002']
</script>
<template>
  <div>
    <h1 class="page-title mb-6">Approval PO</h1>
    <ul class="space-y-3">
      <li v-for="po in poContoh" :key="po">
        <NuxtLink :to="`/approval/${po}`"
          class="card-interactive flex items-center justify-between text-primary font-semibold hover:text-primary-dark">
          <span>{{ po }}</span>
          <span class="text-grey text-sm">→</span>
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>
