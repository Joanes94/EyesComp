<template>
    <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-8 border border-slate-100">
            <!-- Header & Logo -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-sky-600 text-white font-black text-2xl shadow-lg shadow-sky-500/30 mb-3 tracking-tight">
                    EC
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">EyesComp</h1>
                <p class="text-xs text-sky-600 font-semibold tracking-wider uppercase mt-1">Point de vente & Gestion de stock</p>
            </div>

            <!-- Onglets de Connexion -->
            <div class="flex rounded-xl bg-slate-100 p-1 mb-6 text-sm font-medium">
                <button
                    type="button"
                    @click="activeTab = 'credentials'"
                    :class="[
                        'flex-1 py-2 rounded-lg transition-all duration-200 text-center',
                        activeTab === 'credentials'
                            ? 'bg-white text-sky-700 shadow-sm font-semibold'
                            : 'text-slate-500 hover:text-slate-800'
                    ]"
                >
                    E-mail / Téléphone
                </button>
                <button
                    type="button"
                    @click="activeTab = 'pin'"
                    :class="[
                        'flex-1 py-2 rounded-lg transition-all duration-200 text-center flex items-center justify-center gap-1.5',
                        activeTab === 'pin'
                            ? 'bg-white text-sky-700 shadow-sm font-semibold'
                            : 'text-slate-500 hover:text-slate-800'
                    ]"
                >
                    <span>⚡ Caisse Express (PIN)</span>
                </button>
            </div>

            <!-- Message d'erreur -->
            <div v-if="errorMessage" class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ errorMessage }}</span>
            </div>

            <!-- Formulaire 1 : Email / Téléphone + Mot de passe -->
            <form v-if="activeTab === 'credentials'" @submit.prevent="handleCredentialsLogin" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        E-mail ou Numéro de téléphone
                    </label>
                    <input
                        v-model="credentialsForm.login"
                        type="text"
                        required
                        placeholder="Ex: 97001122 ou contact@boutique.bj"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent text-sm placeholder:text-slate-400 text-slate-900 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Mot de passe
                    </label>
                    <input
                        v-model="credentialsForm.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent text-sm placeholder:text-slate-400 text-slate-900 transition"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full mt-2 py-3.5 px-4 bg-sky-600 hover:bg-sky-700 active:scale-[0.99] text-white font-semibold rounded-xl shadow-lg shadow-sky-600/25 transition disabled:opacity-50 text-sm flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>{{ isLoading ? 'Connexion en cours...' : 'Se connecter' }}</span>
                </button>
            </form>

            <!-- Formulaire 2 : Caisse Express par Code PIN (F-AUTH-04) -->
            <form v-else @submit.prevent="handlePinLogin" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Identifiant Boutique
                    </label>
                    <input
                        v-model="pinForm.store_id"
                        type="number"
                        required
                        placeholder="Ex: 1"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm text-slate-900"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Code PIN Caissier (4 à 6 chiffres)
                    </label>
                    <input
                        v-model="pinForm.pin_code"
                        type="password"
                        maxlength="6"
                        required
                        placeholder="••••"
                        class="w-full px-4 py-3 text-center text-2xl font-bold tracking-[0.5em] rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    />
                </div>

                <!-- Pavé tactile chiffres pour écran tactile -->
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button
                        v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                        :key="num"
                        type="button"
                        @click="appendPin(num)"
                        class="py-3 text-lg font-bold bg-slate-100 hover:bg-slate-200 active:bg-slate-300 rounded-xl text-slate-800 transition active:scale-95"
                    >
                        {{ num }}
                    </button>
                    <button
                        type="button"
                        @click="clearPin"
                        class="py-3 text-sm font-bold bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-xl transition active:scale-95"
                    >
                        Effacer
                    </button>
                    <button
                        type="button"
                        @click="appendPin(0)"
                        class="py-3 text-lg font-bold bg-slate-100 hover:bg-slate-200 active:bg-slate-300 rounded-xl text-slate-800 transition active:scale-95"
                    >
                        0
                    </button>
                    <button
                        type="button"
                        @click="deletePinLast"
                        class="py-3 text-sm font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl transition active:scale-95"
                    >
                        ⌫
                    </button>
                </div>

                <button
                    type="submit"
                    :disabled="isLoading || pinForm.pin_code.length < 4"
                    class="w-full mt-2 py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-semibold rounded-xl shadow-lg shadow-emerald-600/25 transition disabled:opacity-50 text-sm flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>{{ isLoading ? 'Ouverture...' : 'Ouvrir la caisse' }}</span>
                </button>
            </form>

            <!-- Pied de page inscription -->
            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Vous êtes nouveau commerçant ?
                    <router-link to="/register" class="text-sky-600 font-bold hover:text-sky-700 hover:underline ml-1">
                        Créer un compte (14j gratuits)
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

const activeTab = ref('credentials');
const isLoading = ref(false);
const errorMessage = ref('');

const credentialsForm = reactive({
    login: '',
    password: '',
});

const pinForm = reactive({
    store_id: 1,
    pin_code: '',
});

const appendPin = (digit) => {
    if (pinForm.pin_code.length < 6) {
        pinForm.pin_code += digit.toString();
    }
};

const clearPin = () => {
    pinForm.pin_code = '';
};

const deletePinLast = () => {
    pinForm.pin_code = pinForm.pin_code.slice(0, -1);
};

const handleCredentialsLogin = async () => {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        await authStore.login(credentialsForm);
        router.push('/dashboard');
    } catch (err) {
        errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.login?.[0] || 'Erreur lors de la connexion.';
    } finally {
        isLoading.value = false;
    }
};

const handlePinLogin = async () => {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        await authStore.pinLogin(pinForm);
        router.push('/dashboard');
    } catch (err) {
        errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.pin_code?.[0] || 'Code PIN invalide.';
    } finally {
        isLoading.value = false;
    }
};
</script>
