<template>
  <div class="p-6 bg-gray-900 min-h-screen text-gray-100">
    <h2 class="text-2xl font-bold mb-6 text-indigo-400">
      Ajouter une étape au niveau : {{ level.name }}
    </h2>

    <form @submit.prevent="submit" class="space-y-4 bg-gray-800 p-4 rounded-lg">
      <input v-model="form.title" type="text" placeholder="Titre de l'étape"
             class="w-full px-3 py-2 rounded bg-gray-700 text-white focus:ring-2 focus:ring-indigo-400" />

      <input v-model="form.constraints" type="text" placeholder="Contraintes"
             class="w-full px-3 py-2 rounded bg-gray-700 text-white focus:ring-2 focus:ring-indigo-400" />

      <input v-model="form.order" type="number" placeholder="Ordre"
             class="w-full px-3 py-2 rounded bg-gray-700 text-white focus:ring-2 focus:ring-indigo-400" />

      <button type="submit"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded text-white font-semibold">
        Enregistrer l'étape
      </button>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

// Props envoyées par Inertia (depuis le contrôleur)
defineProps({
  level: Object
})

// Formulaire réactif
const form = reactive({
  title: '',
  constraints: '',
  order: 1
})

// Soumission du formulaire
const submit = () => {
  router.post(`/levels/${level.id}/steps`, form)
}
</script>
