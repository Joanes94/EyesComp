<template>
    <div class="min-h-screen bg-slate-100 flex flex-col text-slate-800">
        <!-- Navigation Supérieure -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <router-link to="/dashboard" class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-sky-600 text-white font-black flex items-center justify-center text-xl shadow-sm">
                            EC
                        </div>
                        <span class="text-xl font-bold text-slate-900 tracking-tight hidden sm:inline">EyesComp</span>
                    </router-link>

                    <nav class="flex items-center gap-2 pl-4 border-l border-slate-200 text-base font-medium">
                        <router-link to="/dashboard" class="px-3.5 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition">
                            Tableau de bord
                        </router-link>
                        <router-link to="/products" class="px-3.5 py-2 rounded-xl bg-sky-50 text-sky-700 font-bold">
                            Catalogue Produits
                        </router-link>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>{{ authStore.currentStore?.name || 'Boutique Principale' }}</span>
                    </div>

                    <button
                        @click="openProductModal()"
                        class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                    >
                        <span>+ Nouveau Produit</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Contenu -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Barre d'outils / Filtres -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="relative w-full md:w-96">
                    <input
                        v-model="searchQuery"
                        @input="handleSearch"
                        type="text"
                        placeholder="Recherche par nom, SKU ou code-barres..."
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-sky-500 focus:outline-none placeholder:text-slate-400"
                    />
                    <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <select
                        v-model="selectedCategory"
                        @change="fetchProducts"
                        class="px-4 py-3 rounded-xl border border-slate-200 text-sm font-medium bg-white focus:ring-2 focus:ring-sky-500 text-slate-700 flex-1 md:flex-initial"
                    >
                        <option value="">Toutes les catégories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }} ({{ cat.products_count || 0 }})
                        </option>
                    </select>

                    <button
                        @click="openCategoryModal"
                        class="px-4 py-3 rounded-xl border border-slate-200 text-sm font-bold bg-slate-50 hover:bg-slate-100 text-slate-700 transition cursor-pointer flex items-center gap-1.5 shrink-0"
                    >
                        <span>⚙ Gérer catégories</span>
                    </button>
                </div>
            </div>

            <!-- Liste des produits -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div v-if="isLoading" class="p-16 text-center text-slate-400 text-base">
                    <svg class="animate-spin h-8 w-8 text-sky-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Chargement du catalogue...
                </div>

                <div v-else-if="products.length === 0" class="p-16 text-center">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        📦
                    </div>
                    <p class="text-lg font-bold text-slate-800">Aucun produit dans le catalogue</p>
                    <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                        Commencez par ajouter vos articles pour pouvoir enregistrer des ventes et suivre le stock.
                    </p>
                    <button
                        @click="openProductModal()"
                        class="mt-5 px-6 py-3 bg-sky-600 text-white text-sm font-bold rounded-xl hover:bg-sky-700 transition cursor-pointer"
                    >
                        Créer un premier produit
                    </button>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-base text-slate-700">
                        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-4 px-5">Produit</th>
                                <th class="py-4 px-5">Catégorie</th>
                                <th class="py-4 px-5">Unité de base</th>
                                <th class="py-4 px-5 text-right">Prix d'achat</th>
                                <th class="py-4 px-5 text-right">Prix de vente</th>
                                <th class="py-4 px-5 text-center">Unités gros</th>
                                <th class="py-4 px-5 text-center">Statut</th>
                                <th class="py-4 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="product in products" :key="product.id" class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-900 text-base">{{ product.name }}</div>
                                    <div class="text-xs text-slate-500 flex items-center gap-3 mt-1">
                                        <span v-if="product.sku" class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">SKU: {{ product.sku }}</span>
                                        <span v-if="product.barcode" class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">EAN: {{ product.barcode }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-5 text-sm font-semibold text-slate-600">
                                    {{ product.category?.name || 'Sans catégorie' }}
                                </td>
                                <td class="py-4 px-5 text-sm text-slate-600">
                                    {{ product.base_unit }}
                                </td>
                                <td class="py-4 px-5 text-right text-sm font-medium text-slate-500">
                                    {{ formatCurrency(product.purchase_price) }}
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <span class="font-extrabold text-slate-900 text-base">{{ formatCurrency(product.sale_price) }}</span>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span v-if="product.units && product.units.length > 0" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                        {{ product.units.length }} unité(s)
                                    </span>
                                    <span v-else class="text-slate-300 text-sm">—</span>
                                </td>
                                <td class="py-4 px-5 text-center">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold',
                                            product.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'
                                        ]"
                                    >
                                        {{ product.status === 'active' ? 'Actif' : 'Archivé' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            @click="openProductModal(product)"
                                            class="p-2 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-xl transition"
                                            title="Modifier"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="toggleArchive(product)"
                                            :title="product.status === 'active' ? 'Archiver' : 'Restaurer'"
                                            class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal Ajout/Modification Produit -->
        <div v-if="showProductModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-lg">
                        {{ editingProduct ? 'Modifier le produit' : 'Nouveau produit' }}
                    </h3>
                    <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">✕</button>
                </div>

                <form @submit.prevent="saveProduct" class="p-6 space-y-5 overflow-y-auto flex-1 text-base">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom du produit *</label>
                        <input
                            v-model="productForm.name"
                            type="text"
                            required
                            placeholder="Ex: Ciment Dangote 50kg, Huile Mayor 1L..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-sky-500 focus:outline-none"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catégorie</label>
                            <select
                                v-model="productForm.category_id"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-sky-500 focus:outline-none text-base"
                            >
                                <option :value="null">Sans catégorie</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unité de base</label>
                            <input
                                v-model="productForm.base_unit"
                                type="text"
                                placeholder="pièce, kg, sac, litre..."
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">SKU / Réf interne</label>
                            <input
                                v-model="productForm.sku"
                                type="text"
                                placeholder="Ex: CIM-50"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Code-barres (EAN)</label>
                            <input
                                v-model="productForm.barcode"
                                type="text"
                                placeholder="Scannez ou saisissez..."
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-sky-500 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Prix Achat (XOF)</label>
                            <input
                                v-model.number="productForm.purchase_price"
                                type="number"
                                required
                                min="0"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none text-right font-bold text-base"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Prix Vente (XOF)</label>
                            <input
                                v-model.number="productForm.sale_price"
                                type="number"
                                required
                                min="0"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none text-right font-extrabold text-sky-700 text-base"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Alerte Stock</label>
                            <input
                                v-model.number="productForm.alert_threshold"
                                type="number"
                                min="0"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none text-right text-base font-semibold"
                            />
                        </div>
                    </div>

                    <!-- Unités de vente supplémentaires (F-PROD-03) -->
                    <div class="border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Unités de vente en gros (Cartons, Packs...)</span>
                            <button
                                type="button"
                                @click="addUnitRow"
                                class="text-sm font-bold text-sky-600 hover:text-sky-700 cursor-pointer"
                            >
                                + Ajouter unité
                            </button>
                        </div>

                        <div v-for="(unit, index) in productForm.units" :key="index" class="grid grid-cols-12 gap-2 mb-2.5 items-center">
                            <input
                                v-model="unit.unit_name"
                                type="text"
                                placeholder="Nom (ex: Carton)"
                                class="col-span-4 px-3 py-2 rounded-xl border border-slate-200 text-sm"
                            />
                            <input
                                v-model.number="unit.conversion_factor"
                                type="number"
                                min="1"
                                placeholder="Qté (ex: 24)"
                                class="col-span-3 px-3 py-2 rounded-xl border border-slate-200 text-sm text-right"
                            />
                            <input
                                v-model.number="unit.sale_price"
                                type="number"
                                min="0"
                                placeholder="Prix gros"
                                class="col-span-4 px-3 py-2 rounded-xl border border-slate-200 text-sm text-right font-bold text-sky-700"
                            />
                            <button
                                type="button"
                                @click="removeUnitRow(index)"
                                class="col-span-1 text-rose-500 hover:text-rose-700 text-lg font-bold text-center cursor-pointer"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="showProductModal = false"
                            class="px-5 py-3 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="isSaving"
                            class="px-6 py-3 rounded-xl bg-sky-600 text-white text-sm font-bold hover:bg-sky-700 transition disabled:opacity-50 cursor-pointer"
                        >
                            {{ isSaving ? 'Enregistrement...' : 'Enregistrer le produit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Catégories avec Ajout, Modification et Suppression -->
        <div v-if="showCategoryModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">Gestion des catégories</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Créez, modifiez ou supprimez vos rayons & catégories</p>
                    </div>
                    <button @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold p-1">✕</button>
                </div>

                <!-- Formulaire d'ajout rapide -->
                <form @submit.prevent="createCategory" class="flex gap-2">
                    <input
                        v-model="newCategoryName"
                        type="text"
                        required
                        placeholder="Ex: Quincaillerie, Électricité, Boissons..."
                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                    />
                    <button
                        type="submit"
                        :disabled="isSavingCategory"
                        class="px-4 py-2.5 bg-sky-600 text-white font-bold text-sm rounded-xl hover:bg-sky-700 transition disabled:opacity-50 cursor-pointer"
                    >
                        {{ isSavingCategory ? 'Ajout...' : '+ Ajouter' }}
                    </button>
                </form>

                <!-- Liste des catégories existantes avec édition et suppression -->
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 border border-slate-100 rounded-xl">
                    <div v-if="categories.length === 0" class="p-6 text-center text-sm text-slate-400">
                        Aucune catégorie créée pour le moment.
                    </div>

                    <div
                        v-for="cat in categories"
                        :key="cat.id"
                        class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition"
                    >
                        <!-- Mode édition -->
                        <div v-if="editingCategoryId === cat.id" class="flex-1 flex items-center gap-2 mr-2">
                            <input
                                v-model="editingCategoryName"
                                type="text"
                                class="flex-1 px-3 py-1.5 rounded-lg border border-sky-400 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 font-semibold"
                            />
                            <button
                                @click="saveEditCategory(cat)"
                                class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition"
                            >
                                Valider
                            </button>
                            <button
                                @click="cancelEditCategory"
                                class="px-2.5 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-300 transition"
                            >
                                Annuler
                            </button>
                        </div>

                        <!-- Mode affichage normal -->
                        <div v-else class="flex-1 flex items-center justify-between pr-3">
                            <div>
                                <span class="font-bold text-slate-900 text-sm block">{{ cat.name }}</span>
                                <span class="text-xs text-slate-400">{{ cat.products_count || 0 }} produit(s) rattaché(s)</span>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div v-if="editingCategoryId !== cat.id" class="flex items-center gap-1">
                            <button
                                @click="startEditCategory(cat)"
                                class="p-2 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition"
                                title="Modifier le nom de la catégorie"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button
                                @click="deleteCategory(cat)"
                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                title="Supprimer la catégorie"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 text-right">
                    <button
                        @click="showCategoryModal = false"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition cursor-pointer"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import api from '../api';

const authStore = useAuthStore();

const products = ref([]);
const categories = ref([]);
const isLoading = ref(false);
const isSaving = ref(false);
const isSavingCategory = ref(false);

const searchQuery = ref('');
const selectedCategory = ref('');

const showProductModal = ref(false);
const editingProduct = ref(null);

const showCategoryModal = ref(false);
const newCategoryName = ref('');
const editingCategoryId = ref(null);
const editingCategoryName = ref('');

const productForm = reactive({
    name: '',
    category_id: null,
    base_unit: 'pièce',
    sku: '',
    barcode: '',
    purchase_price: 0,
    sale_price: 0,
    alert_threshold: 5,
    units: [],
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('fr-FR').format(val || 0) + ' XOF';
};

const fetchCategories = async () => {
    try {
        const res = await api.get('/categories');
        categories.value = res.data.categories;
    } catch (e) {
        console.error(e);
    }
};

const fetchProducts = async () => {
    isLoading.value = true;
    try {
        const params = {
            category_id: selectedCategory.value || undefined,
            search: searchQuery.value || undefined,
            store_id: authStore.currentStore?.id,
        };
        const res = await api.get('/products', { params });
        products.value = res.data.products.data || res.data.products;
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value = false;
    }
};

let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchProducts();
    }, 250);
};

const openProductModal = (product = null) => {
    editingProduct.value = product;
    if (product) {
        productForm.name = product.name;
        productForm.category_id = product.category_id;
        productForm.base_unit = product.base_unit || 'pièce';
        productForm.sku = product.sku || '';
        productForm.barcode = product.barcode || '';
        productForm.purchase_price = product.purchase_price;
        productForm.sale_price = product.sale_price;
        productForm.alert_threshold = product.alert_threshold;
        productForm.units = product.units ? JSON.parse(JSON.stringify(product.units)) : [];
    } else {
        productForm.name = '';
        productForm.category_id = null;
        productForm.base_unit = 'pièce';
        productForm.sku = '';
        productForm.barcode = '';
        productForm.purchase_price = 0;
        productForm.sale_price = 0;
        productForm.alert_threshold = 5;
        productForm.units = [];
    }
    showProductModal.value = true;
};

const addUnitRow = () => {
    productForm.units.push({
        unit_name: '',
        conversion_factor: 12,
        sale_price: 0,
    });
};

const removeUnitRow = (idx) => {
    productForm.units.splice(idx, 1);
};

const saveProduct = async () => {
    isSaving.value = true;
    try {
        if (editingProduct.value) {
            await api.put(`/products/${editingProduct.value.id}`, productForm);
        } else {
            await api.post('/products', productForm);
        }
        showProductModal.value = false;
        fetchProducts();
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de la sauvegarde du produit.');
    } finally {
        isSaving.value = false;
    }
};

const toggleArchive = async (product) => {
    const action = product.status === 'active' ? 'archive' : 'restore';
    try {
        await api.post(`/products/${product.id}/${action}`);
        fetchProducts();
    } catch (e) {
        console.error(e);
    }
};

const openCategoryModal = () => {
    showCategoryModal.value = true;
    editingCategoryId.value = null;
};

const createCategory = async () => {
    if (!newCategoryName.value.trim()) return;
    isSavingCategory.value = true;
    try {
        await api.post('/categories', { name: newCategoryName.value });
        newCategoryName.value = '';
        await fetchCategories();
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de la création de la catégorie.');
    } finally {
        isSavingCategory.value = false;
    }
};

const startEditCategory = (cat) => {
    editingCategoryId.value = cat.id;
    editingCategoryName.value = cat.name;
};

const cancelEditCategory = () => {
    editingCategoryId.value = null;
    editingCategoryName.value = '';
};

const saveEditCategory = async (cat) => {
    if (!editingCategoryName.value.trim()) return;
    try {
        await api.put(`/categories/${cat.id}`, { name: editingCategoryName.value });
        editingCategoryId.value = null;
        await fetchCategories();
        await fetchProducts();
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de la modification de la catégorie.');
    }
};

const deleteCategory = async (cat) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer la catégorie "${cat.name}" ?`)) return;
    try {
        await api.delete(`/categories/${cat.id}`);
        await fetchCategories();
        await fetchProducts();
    } catch (e) {
        alert(e.response?.data?.message || 'Impossible de supprimer cette catégorie car des produits y sont encore rattachés.');
    }
};

onMounted(() => {
    fetchCategories();
    fetchProducts();
});
</script>
