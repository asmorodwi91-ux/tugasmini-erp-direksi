<script setup lang="ts">
defineProps<{ text: string }>()
</script>
<template>
  <div class="alert-banner">
    <span>⚠️ {{ text }}</span>
    <slot />
  </div>
</template>
