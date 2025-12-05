<template>
  <div class="p-6 bg-gray-900 min-h-screen text-gray-100">
    <h2 class="text-2xl font-bold mb-6 text-green-400">Créer un Niveau</h2>
    <form @submit.prevent="submit" class="space-y-4 bg-gray-800 p-4 rounded-lg">
      <input v-model="form.name" type="text" placeholder="Nom du niveau"
             class="w-full px-3 py-2 rounded bg-gray-700 text-white focus:ring-2 focus:ring-green-400" />
      <input v-model="form.description" type="text" placeholder="Description"
             class="w-full px-3 py-2 rounded bg-gray-700 text-white focus:ring-2 focus:ring-green-400" />
      <button type="submit"
              class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded text-white font-semibold">
        Enregistrer
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({ name: '', description: '' })

const submit = () => {
  router.post('/levels', form)
}
</script>
