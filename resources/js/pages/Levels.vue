

<template>
    <div class="p-6 min-h-screen text-gray-100">
        <h2 class="text-2xl font-bold mb-6 text-green-400">Niveaux & Étapes</h2>

    <ul class="space-y-6">
      <li v-for="level in levels" :key="level.id" class="bg-gray-800 rounded-lg shadow-md p-4">
        <div class="flex items-center justify-between">
          <div>
            <button @click="goToLevel(level.id)" class="text-left">
              <span class="text-lg font-semibold text-indigo-400 hover:underline hover:text-green-300 cursor-pointer">{{ level.name }}</span>
            </button>
            <p class="text-sm text-gray-400">{{ level.description }}</p>
          </div>
          <span class="px-3 py-1 text-xs rounded-full bg-green-700 text-white">
            {{ level.steps.length }} étapes
          </span>
        </div>

                <!-- Étapes du niveau -->
                <ul class="mt-3 space-y-2">
                    <li v-for="step in level.steps" :key="step.id"
                        class="flex items-center justify-between bg-gray-700 rounded-md px-3 py-2 hover:bg-gray-600 transition">
                        <span class="text-sm">{{ step.title }}</span>
                        <span class="text-xs text-gray-300">Ordre: {{ step.order }}</span>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
const props = defineProps({
  levels: Array
})

function goToLevel(id) {
  // Use Inertia router to visit the level play page
  router.visit(`/play/${id}`)
}
</script>
