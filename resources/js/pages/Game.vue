<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    level: Object,
    nextLevelUrl: String
})

const page = usePage()
const currentStepIndex = ref(0)
const inputPassword = ref('')
const error = ref('')
const successMessage = ref('')
const isLoading = ref(false)
const lastSuccessfulPassword = ref('')

const currentStep = computed(() => props.level.steps[currentStepIndex.value])
const progress = computed(() => ((currentStepIndex.value + 1) / props.level.steps.length) * 100)

const validateStep = () => {
    error.value = ''
    successMessage.value = ''
    isLoading.value = true

    // Use axios for the request (AJAX). Axios will send X-Requested-With header so controller returns JSON.
    axios.post(currentStep.value.validate_url, { password: inputPassword.value })
        .then((res) => {
            isLoading.value = false

            if (res.data?.success) {
                // garder la dernière tentative qui a réussi afin de proposer de la définir
                lastSuccessfulPassword.value = inputPassword.value
                inputPassword.value = ''

                if (currentStepIndex.value < props.level.steps.length - 1) {
                    currentStepIndex.value++
                    successMessage.value = ''
                } else {
                    // Niveau terminé : afficher message et actions (pas de redirection automatique)
                    successMessage.value = `Bravo ! Vous avez terminé le niveau ${props.level.name} !`
                    // Si la dernière tentative est présente mais trop courte (<6),
                    // avancer automatiquement vers le niveau suivant si possible.
                    if (lastSuccessfulPassword.value && lastSuccessfulPassword.value.length < 6) {
                        if (props.nextLevelUrl) {
                            // petit délai pour que l'utilisateur voie le message
                            setTimeout(() => {
                                goToNextLevel()
                            }, 900)
                        }
                    }
                }
            } else if (res.data?.error) {
                error.value = res.data.error
            } else {
                error.value = 'Une erreur inconnue est survenue.'
            }
        })
        .catch((err) => {
            isLoading.value = false

            // Validation errors (422) may come back in err.response.data.errors
            if (err.response && err.response.status === 422) {
                const data = err.response.data
                if (data && data.error) {
                    error.value = data.error
                } else if (data && data.errors && data.errors.password) {
                    error.value = data.errors.password[0]
                } else {
                    error.value = 'Erreur de validation.'
                }
                return
            }

            error.value = 'Une erreur inconnue est survenue.'
            console.error('Validation request failed', err)
        })
}

const setAsPassword = () => {
    error.value = ''
    successMessage.value = ''

    if (!lastSuccessfulPassword.value) {
        error.value = 'Aucun mot de passe à définir.'
        return
    }

    isLoading.value = true

    axios.post('/user/password/set-from-game', { password: lastSuccessfulPassword.value })
        .then((res) => {
            isLoading.value = false
            if (res.data?.success) {
                successMessage.value = 'Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter avec ce mot de passe.'
                // clear stored candidate so user doesn't accidentally reuse
                lastSuccessfulPassword.value = ''
            } else if (res.data?.error) {
                error.value = res.data.error
            } else {
                error.value = 'Erreur lors de la mise à jour du mot de passe.'
            }
        })
        .catch((err) => {
            isLoading.value = false
            if (err.response && err.response.data && err.response.data.errors) {
                // validation errors
                const data = err.response.data
                if (data.error) error.value = data.error
                else if (data.errors && data.errors.password) error.value = data.errors.password[0]
                else error.value = 'Erreur de validation.'
                return
            }
            error.value = 'Une erreur inconnue est survenue.'
            console.error('Set password request failed', err)
        })
}

const goToNextLevel = () => {
    if (props.nextLevelUrl) {
        router.visit(props.nextLevelUrl)
    }
}
</script>

<template>
    <div class="p-6 bg-gray-900 min-h-screen text-gray-100 font-sans">
        <h2 class="text-3xl font-extrabold mb-6 text-green-400 border-b border-gray-700 pb-2">
            Niveau : {{ level.title }}
        </h2>
        <p class="text-gray-400 mb-4">{{ level.description }}</p>

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
                    :disabled="!inputPassword || !!successMessage || isLoading"
                    class="mt-4 w-full px-4 py-3 bg-green-600 hover:bg-green-500 rounded-lg text-white font-bold text-lg transition duration-150 transform hover:scale-[1.01] disabled:bg-gray-600 disabled:cursor-not-allowed shadow-md">
                {{ isLoading ? 'Vérification...' : 'Valider' }}
            </button>

            <p v-if="error" class="text-red-400 mt-3 p-2 bg-red-900/30 rounded-lg border border-red-500 text-sm font-medium">
                Erreur : {{ error }}
            </p>
        </div>

        <!-- Actions affichées à la fin du niveau : définir mot de passe / aller au niveau suivant -->
        <div v-if="successMessage" class="mt-6 bg-blue-800 border border-blue-600 text-white p-4 rounded-lg mb-6 shadow-xl">
            <div class="flex items-center justify-between">
                <p class="font-medium">{{ successMessage }}</p>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button v-if="lastSuccessfulPassword && lastSuccessfulPassword.length >= 6"
                        @click="setAsPassword"
                        :disabled="isLoading"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-white font-medium disabled:opacity-60">
                    {{ isLoading ? 'Traitement...' : 'Définir ce mot de passe pour mon compte' }}
                </button>

                <button v-if="nextLevelUrl"
                        @click="goToNextLevel"
                        :disabled="isLoading"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-500 rounded-lg text-white font-medium disabled:opacity-60">
                    Aller au niveau suivant
                </button>
            </div>
        </div>

        <div v-else class="text-center p-10 text-xl text-gray-500">
            Chargement du niveau...
        </div>

    </div>
</template>