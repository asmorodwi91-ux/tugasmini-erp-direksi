<script setup lang="ts">
defineProps<{
  title: string
  value: string | number
  delta?: string
  deltaUp?: boolean
  badge?: string
  badgeVariant?: 'hijau' | 'kuning' | 'merah'
}>()
const badgeMap: Record<string, string> = {
  hijau: 'bg-status-hijau-soft text-status-hijau',
  kuning: 'bg-status-kuning-soft text-status-kuning',
  merah: 'bg-status-merah-soft text-status-merah',
}
</script>
<template>
  <div class="card">
    <div class="kpi-label">{{ title }}</div>
    <div class="kpi-value">{{ value }}</div>
    <span v-if="badge" class="inline-block text-xs font-semibold rounded-full px-2.5 py-0.5"
      :class="badgeMap[badgeVariant || 'hijau']">{{ badge }}</span>
    <div v-else-if="delta" class="text-xs font-medium" :class="deltaUp ? 'text-status-hijau' : 'text-status-merah'">{{ delta }}</div>
  </div>
</template>
