<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    level: Object
})

const currentStepIndex = ref(0)
const inputPassword = ref('')
const error = ref('')
const successMessage = ref('') // NOUVEAU : État pour le message de succès

const currentStep = computed(() => props.level.steps[currentStepIndex.value])
const progress = computed(() => ((currentStepIndex.value + 1) / props.level.steps.length) * 100)

const validateStep = () => {
    error.value = ''
    successMessage.value = '' // Réinitialiser avant l'appel

    router.post(
        router.route('game.validate', { level: props.level.id, step: currentStep.value.id }), 
        { password: inputPassword.value }, 
        {
            onSuccess: (page) => {
                const response = page.props.response // Supposons que le contrôleur renvoie une prop 'response'

                // Logique après validation
                if (page.props.flash?.success) { // Utilisateur réussi l'étape
                    inputPassword.value = ''
                    
                    if (currentStepIndex.value < props.level.steps.length - 1) {
                        currentStepIndex.value++
                    } else {
                        // 🛑 REMPLACEMENT DE L'ALERT PAR UN MESSAGE DANS L'UI
                        successMessage.value = `Bravo ! Vous avez terminé le niveau ${props.level.name} !`;
                        
                        // Redirection différée (vous pouvez le rendre immédiat si vous préférez)
                        setTimeout(() => {
                           // Assurez-vous que le prochain niveau existe avant de naviguer
                           router.visit(router.route('game.play', { level: props.level.id + 1 }))
                        }, 1500);
                    }
                } else if (page.props.flash?.error) {
                    error.value = page.props.flash.error
                } else {
                    // Fallback
                    error.value = 'Une erreur inconnue est survenue.'
                }
            },
            onError: (errors) => {
                error.value = errors.password ? errors.password[0] : 'Erreur de validation.'
            }
        }
    )
}
</script>

<template>
    <div class="p-6 bg-gray-900 min-h-screen text-gray-100 font-sans">
        <h2 class="text-3xl font-extrabold mb-6 text-green-400 border-b border-gray-700 pb-2">
            Niveau : {{ level.title }}
        </h2>
        <p class="text-gray-400 mb-4">{{ level.description }}</p>

        <!-- Message de Succès Global (Fin de niveau) -->
        <div v-if="successMessage" class="bg-blue-800 border border-blue-600 text-white p-4 rounded-lg mb-6 shadow-xl">
            {{ successMessage }}
        </div>

        <!-- Barre de progression -->
        <div class="mb-8">
            <div class="flex justify-between mb-1 text-sm font-medium">
                <span>Progression du niveau</span>
                <span>{{ currentStepIndex + 1 }} / {{ level.steps.length }} étapes</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-3">
                <div class="bg-green-500 h-3 rounded-full transition-all duration-500 ease-out"
                    :style="{ width: progress + '%' }">
                </div>
            </div>
        </div>

        <!-- Étape en cours -->
        <div v-if="currentStep" class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-2xl">
            <h3 class="text-xl font-bold text-indigo-400 mb-3">
                Étape {{ currentStep.order }} : Le défi
            </h3>
            
            <p class="text-md text-gray-300 mb-5 p-3 bg-gray-700 rounded-lg border-l-4 border-indigo-500 italic">
                Règle : <span class="font-semibold text-white">{{ currentStep.constraints }}</span>
            </p>

            <input v-model="inputPassword" type="text"
                    @keyup.enter="validateStep"
                    placeholder="Saisissez votre tentative de mot de passe..."
                    class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:ring-2 focus:ring-green-400 focus:border-green-400 border-none transition duration-150" />

            <button @click="validateStep"
                    :disabled="!inputPassword || !!successMessage"
                    class="mt-4 w-full px-4 py-3 bg-green-600 hover:bg-green-500 rounded-lg text-white font-bold text-lg transition duration-150 transform hover:scale-[1.01] disabled:bg-gray-600 disabled:cursor-not-allowed shadow-md">
                Valider
            </button>

            <p v-if="error" class="text-red-400 mt-3 p-2 bg-red-900/30 rounded-lg border border-red-500 text-sm font-medium">
                Erreur : {{ error }}
            </p>
        </div>
        
        <div v-else class="text-center p-10 text-xl text-gray-500">
            Chargement du niveau...
        </div>

    </div>
</template>