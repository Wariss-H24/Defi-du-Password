
<template>
  <div class="p-6 bg-gray-900 min-h-screen text-gray-100">
    <h2 class="text-2xl font-bold text-green-400">{{ level.name }}</h2>
    <p class="text-gray-400 mb-4">{{ level.description }}</p>

    <h3 class="text-lg font-semibold text-indigo-400 mb-2">Étapes</h3>
    <ul class="space-y-2">
      <li v-for="step in level.steps" :key="step.id"
          class="flex items-center justify-between bg-gray-700 rounded-md px-3 py-2 hover:bg-gray-600 transition">
        <span>{{ step.title }}</span>
        <span class="text-xs text-gray-300">Ordre: {{ step.order }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup>
defineProps({
  level: Object
})
</script>
