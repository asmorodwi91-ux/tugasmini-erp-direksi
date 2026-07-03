<script setup lang="ts">
defineProps<{ title: string; value: string | number; delta?: string; deltaUp?: boolean }>()
</script>
<template>
  <div class="card">
    <div class="kpi-label">{{ title }}</div>
    <div class="kpi-value">{{ value }}</div>
    <div v-if="delta" class="text-xs font-medium" :class="deltaUp ? 'text-status-hijau' : 'text-status-merah'">{{ delta }}</div>
  </div>
</template>
