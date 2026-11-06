<template>
    <div class="min-h-screen py-10 px-4 bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900 flex items-center justify-center">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl p-6 sm:p-8 border border-slate-100">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-600 text-white font-bold text-xl shadow-lg shadow-sky-500/30 mb-2">
                    EC
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Créer votre compte EyesComp</h1>
                <p class="text-xs text-emerald-600 font-semibold mt-1">
                    ✓ 14 jours d'essai gratuit • Sans carte bancaire • Prêt en 1 minute
                </p>
            </div>

            <!-- Message d'erreur -->
            <div v-if="errorMessage" class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ errorMessage }}</span>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
                <!-- Section Commerce -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                        Votre Commerce
                    </span>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nom du commerce *
                        </label>
                        <input
                            v-model="form.business_name"
                            type="text"
                            required
                            placeholder="Ex: Quincaillerie Sainte Trinité"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Type d'activité *
                            </label>
                            <select
                                v-model="form.business_type"
                                required
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white"
                            >
                                <option value="boutique">Boutique de quartier</option>
                                <option value="quincaillerie">Quincaillerie / Matériaux</option>
                                <option value="alimentation">Alimentation / Supérette</option>
                                <option value="boisson">Dépôt de boissons / Bar</option>
                                <option value="pharmacie">Pharmacie / Dépôt pharma</option>
                                <option value="autre">Autre commerce de détail</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Ville / Quartier
                            </label>
                            <input
                                v-model="form.store_address"
                                type="text"
                                placeholder="Ex: Cotonou, Cadjèhoun"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section Propriétaire -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                        Informations du Propriétaire
                    </span>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nom et prénom *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Ex: Jean Houndékon"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Téléphone (WhatsApp) *
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                required
                                placeholder="Ex: +22997001122"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                E-mail (facultatif)
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="jean@gmail.com"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Mot de passe *
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                minlength="8"
                                placeholder="Au moins 8 caractères"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Confirmation *
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                minlength="8"
                                placeholder="Répéter le mot de passe"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full mt-3 py-3.5 px-4 bg-sky-600 hover:bg-sky-700 active:scale-[0.99] text-white font-bold rounded-xl shadow-lg shadow-sky-600/30 transition disabled:opacity-50 text-sm flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>{{ isLoading ? 'Création de votre boutique...' : 'Créer mon commerce gratuitement' }}</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Déjà inscrit ?
                    <router-link to="/login" class="text-sky-600 font-bold hover:text-sky-700 hover:underline ml-1">
                        Se connecter
                    </router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const isLoading = ref(false);
const errorMessage = ref('');

const form = reactive({
    name: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
    business_name: '',
    business_type: 'quincaillerie',
    store_address: '',
    currency: 'XOF',
});

const handleRegister = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    if (form.password !== form.password_confirmation) {
        errorMessage.value = 'Les deux mots de passe ne correspondent pas.';
        isLoading.value = false;
        return;
    }

    try {
        await authStore.registerOwner(form);
        router.push('/dashboard');
    } catch (err) {
        const errorData = err.response?.data;
        if (errorData?.errors) {
            const firstKey = Object.keys(errorData.errors)[0];
            errorMessage.value = errorData.errors[firstKey][0];
        } else {
            errorMessage.value = errorData?.message || 'Erreur lors de la création du compte.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>
