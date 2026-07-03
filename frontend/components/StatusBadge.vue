<script setup lang="ts">
import { useSlots } from 'vue'
const props = defineProps<{ status: string }>()
const slots = useSlots()
const map: Record<string, string> = {
  hijau: 'bg-status-hijau-soft text-status-hijau',
  kuning: 'bg-status-kuning-soft text-status-kuning',
  merah: 'bg-status-merah-soft text-status-merah',
}
</script>
<template>
  <span class="text-xs font-semibold rounded-full px-3 py-1 capitalize"
    :class="map[status] || 'bg-bg-soft text-grey'"><slot>{{ status }}</slot></span>
</template>
