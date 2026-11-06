<template>
    <div class="min-h-screen bg-slate-100 flex flex-col">
        <!-- Barre de Navigation Supérieure -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-600 text-white font-black flex items-center justify-center text-lg shadow-sm">
                            EC
                        </div>
                        <div>
                            <span class="text-lg font-bold text-slate-900 tracking-tight block leading-tight">EyesComp</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest block">
                                {{ authStore.tenant?.name || 'Mon Entreprise' }}
                            </span>
                        </div>
                    </div>

                    <nav class="hidden md:flex items-center gap-2 pl-4 border-l border-slate-200 text-sm font-medium">
                        <router-link to="/dashboard" class="px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 font-semibold">
                            Tableau de bord
                        </router-link>
                        <router-link to="/products" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition">
                            Catalogue Produits
                        </router-link>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Badge Boutique Active -->
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ authStore.currentStore?.name || 'Boutique Principale' }}</span>
                    </div>

                    <!-- Badge Période d'essai -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        14j d'essai actif
                    </span>

                    <!-- Profil & Déconnexion -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="text-right hidden md:block">
                            <p class="text-xs font-bold text-slate-800 leading-tight">{{ authStore.user?.name }}</p>
                            <p class="text-[11px] text-slate-500 capitalize">
                                {{ authStore.roles[0] === 'owner' ? 'Propriétaire' : authStore.roles[0] || 'Utilisateur' }}
                            </p>
                        </div>

                        <button
                            @click="handleLogout"
                            title="Se déconnecter"
                            class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenu Principal -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Bannière d'accueil après authentification -->
            <div class="bg-gradient-to-r from-sky-600 to-indigo-700 rounded-2xl p-6 sm:p-8 text-white shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-md bg-white/20 text-xs font-semibold uppercase tracking-wider">
                            Tableau de bord EyesComp
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-bold mt-2 tracking-tight">
                            Bonjour, {{ authStore.user?.name }} 👋
                        </h2>
                        <p class="text-sky-100 text-sm mt-1 max-w-xl">
                            Votre espace pour <strong class="underline font-semibold">{{ authStore.tenant?.name }}</strong> est opérationnel.
                            Authentification et redirection validées avec succès.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            class="px-5 py-3 rounded-xl bg-white text-sky-800 font-bold text-sm shadow hover:bg-sky-50 transition active:scale-95 flex items-center gap-2 cursor-pointer"
                        >
                            <span>🛒 Écran de Caisse (POS)</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cartes d'indicateurs clés (Prévues pour Phase A) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ventes du jour</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2">0 <span class="text-xs font-normal text-slate-500">{{ authStore.tenant?.currency || 'XOF' }}</span></p>
                    <p class="text-xs text-slate-400 mt-1">0 ticket de caisse aujourd'hui</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock & Alertes</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-2">0 <span class="text-xs font-normal text-slate-500">en rupture</span></p>
                    <p class="text-xs text-slate-400 mt-1">Catalogue en attente de configuration</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dettes clients (Crédits)</p>
                    <p class="text-2xl font-bold text-amber-600 mt-2">0 <span class="text-xs font-normal text-slate-500">{{ authStore.tenant?.currency || 'XOF' }}</span></p>
                    <p class="text-xs text-slate-400 mt-1">Toutes les créances sont recouvrées</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Solde de caisse théorique</p>
                    <p class="text-2xl font-bold text-slate-900 mt-2">0 <span class="text-xs font-normal text-slate-500">{{ authStore.tenant?.currency || 'XOF' }}</span></p>
                    <p class="text-xs text-slate-400 mt-1">Session de caisse fermée</p>
                </div>
            </div>

            <!-- Carte récapitulative des données d'authentification -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    État du compte connecté
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500 uppercase">Utilisateur connecté</span>
                        <p class="font-bold text-slate-900">{{ authStore.user?.name }}</p>
                        <p class="text-xs text-slate-600">Téléphone : {{ authStore.user?.phone || 'Non renseigné' }}</p>
                        <p class="text-xs text-slate-600">Email : {{ authStore.user?.email || 'Non renseigné' }}</p>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded text-[11px] font-bold bg-sky-100 text-sky-800 uppercase">
                            Rôle : {{ authStore.roles.join(', ') }}
                        </span>
                    </div>

                    <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500 uppercase">Entreprise (Multi-Tenant)</span>
                        <p class="font-bold text-slate-900">{{ authStore.tenant?.name }}</p>
                        <p class="text-xs text-slate-600">Activité : {{ authStore.tenant?.business_type }}</p>
                        <p class="text-xs text-slate-600">Devise : {{ authStore.tenant?.currency }}</p>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                            Statut : {{ authStore.tenant?.status }}
                        </span>
                    </div>

                    <div class="space-y-1.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500 uppercase">Boutique active</span>
                        <p class="font-bold text-slate-900">{{ authStore.currentStore?.name }}</p>
                        <p class="text-xs text-slate-600">Code : {{ authStore.currentStore?.code || 'ST01' }}</p>
                        <p class="text-xs text-slate-600">Adresse : {{ authStore.currentStore?.address || 'Non définie' }}</p>
                        <span class="inline-block mt-2 px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 uppercase">
                            Vente négative : {{ authStore.currentStore?.allow_negative_stock ? 'Autorisée' : 'Bloquée' }}
                        </span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>
