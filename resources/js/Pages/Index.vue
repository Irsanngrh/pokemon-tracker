<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';

const token = ref(null);
const slug = ref(null);
const isReady = ref(false);

const expansions = ref([]);
const cards = ref([]);
const collectionItems = ref([]);
const summary = ref({ total_value: 0, total_cards: 0 });
const selectedExpansion = ref(null);
const selectedCardDetails = ref(null);

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
};

const initializeCollection = async () => {
    const response = await axios.post('/collection/init');
    token.value = response.data.private_token;
    slug.value = response.data.public_slug;
    localStorage.setItem('collection_token', token.value);
    axios.defaults.headers.common['X-Collection-Token'] = token.value;
};

const loadSummary = async () => {
    const response = await axios.get('/api/collection/summary');
    summary.value = response.data;
};

const loadExpansions = async () => {
    const response = await axios.get('/api/expansions');
    expansions.value = response.data;
    if (expansions.value.length > 0) {
        selectedExpansion.value = expansions.value[0].id;
        await loadCards();
    }
};

const loadCards = async () => {
    if (!selectedExpansion.value) return;
    const response = await axios.get(`/api/cards?expansion_id=${selectedExpansion.value}`);
    cards.value = response.data;
};

const loadCollectionItems = async () => {
    const response = await axios.get('/api/collection/items');
    collectionItems.value = response.data;
};

const updateCollectionItem = async (cardId, field, value) => {
    let item = collectionItems.value.find(i => i.card_id === cardId);
    let payload = { card_id: cardId, is_owned: false, is_wishlist: false, quantity: 0 };

    if (item) payload = { ...item };
    payload[field] = value;

    if (field === 'quantity' && value > 0) payload.is_owned = true;
    if (field === 'is_owned' && value === false) payload.quantity = 0;
    if (field === 'is_owned' && value === true && payload.quantity === 0) payload.quantity = 1;

    const response = await axios.post('/api/collection/items', payload);
    
    if (item) Object.assign(item, response.data);
    else collectionItems.value.push(response.data);

    await loadSummary();
};

const getItem = (cardId) => {
    return collectionItems.value.find(i => i.card_id === cardId) || { is_owned: false, is_wishlist: false, quantity: 0 };
};

onMounted(async () => {
    let storedToken = localStorage.getItem('collection_token');
    if (storedToken) {
        try {
            const response = await axios.post('/collection/verify', {}, { headers: { 'X-Collection-Token': storedToken } });
            token.value = storedToken;
            slug.value = response.data.public_slug;
            axios.defaults.headers.common['X-Collection-Token'] = storedToken;
        } catch (error) {
            localStorage.removeItem('collection_token');
            await initializeCollection();
        }
    } else await initializeCollection();
    
    await loadExpansions();
    await loadCollectionItems();
    await loadSummary();
    isReady.value = true;
});
</script>

<template>
    <AppLayout>
        <div v-if="!isReady" class="flex items-center justify-center py-20">
            <div class="text-gray-500 font-medium animate-pulse">Loading Workspace...</div>
        </div>
        
        <div v-else class="space-y-8 relative">
            
            <div v-if="selectedCardDetails" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
                <div class="bg-white dark:bg-[#191919] rounded-2xl max-w-4xl w-full flex flex-col md:flex-row overflow-hidden shadow-2xl relative animate-fade-in">
                    <button @click="selectedCardDetails = null" class="absolute top-4 right-4 z-10 p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <div class="w-full md:w-1/2 bg-gray-50 dark:bg-black p-8 flex justify-center items-center">
                        <img :src="selectedCardDetails.image_url" class="max-h-[450px] object-contain drop-shadow-2xl rounded-xl" />
                    </div>
                    
                    <div class="w-full md:w-1/2 p-8 overflow-y-auto max-h-[85vh]">
                        <div class="mb-6">
                            <span class="inline-block px-3 py-1 bg-gray-800 dark:bg-gray-700 text-white text-[10px] font-bold rounded-full mb-3 tracking-wider uppercase">
                                {{ selectedCardDetails.category || 'Standard' }}
                            </span>
                            <h2 class="text-3xl font-bold leading-tight">{{ selectedCardDetails.name }}</h2>
                            <div class="text-gray-500 dark:text-gray-400 font-mono mt-1 font-semibold">{{ selectedCardDetails.card_number }}</div>
                        </div>
                        
                        <div class="space-y-5">
                            <div>
                                <h3 class="font-bold text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 pb-1 mb-2">Serangan & Efek</h3>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ selectedCardDetails.description || 'Tidak ada efek khusus.' }}</p>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 pb-1 mb-2">Ilustrator</h3>
                                <p class="text-sm font-medium">{{ selectedCardDetails.illustrator || 'Unknown' }}</p>
                            </div>
                            <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
                                <h3 class="font-bold text-green-600 text-xs uppercase tracking-wider pb-1 mb-1">Estimasi Harga Pasar</h3>
                                <div class="text-2xl font-bold">{{ selectedCardDetails.price ? formatRupiah(selectedCardDetails.price.price) : 'Rp 0' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-blue-100 font-medium text-sm mb-1">Total Estimated Value</h2>
                    <div class="text-4xl font-bold tracking-tight">{{ formatRupiah(summary.total_value) }}</div>
                </div>
                <div class="bg-white/20 px-4 py-2 rounded-lg backdrop-blur-sm border border-white/10">
                    <span class="text-blue-100 text-sm">Cards Owned: </span>
                    <span class="font-bold ml-1">{{ summary.total_cards }}</span>
                </div>
            </div>

            <div class="flex gap-4 overflow-x-auto pb-2 border-b border-gray-200 dark:border-gray-800">
                <button 
                    v-for="expansion in expansions" 
                    :key="expansion.id"
                    @click="selectedExpansion = expansion.id; loadCards()"
                    :class="[
                        'px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 whitespace-nowrap transition-colors',
                        selectedExpansion === expansion.id 
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                    ]"
                >
                    {{ expansion.name }}
                </button>
            </div>

            <div v-if="cards.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <div v-for="card in cards" :key="card.id" class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-[#191919] shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition">
                    
                    <div @click="selectedCardDetails = card" class="aspect-[63/88] bg-gray-100 dark:bg-gray-800 relative group cursor-pointer overflow-hidden">
                        <img v-if="card.image_url" :src="card.image_url" :alt="card.name" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                            <span class="opacity-0 group-hover:opacity-100 text-white font-medium bg-black/60 px-3 py-1.5 rounded-full text-xs backdrop-blur-sm">View Details</span>
                        </div>
                    </div>
                    
                    <div class="p-3 flex flex-col flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">{{ card.card_number }}</div>
                        </div>
                        <div class="font-medium text-sm mb-1 line-clamp-1" :title="card.name">{{ card.name }}</div>
                        
                        <div class="mt-auto space-y-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" :checked="getItem(card.id).is_owned" @change="updateCollectionItem(card.id, 'is_owned', $event.target.checked)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                                    Owned
                                </label>
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" :checked="getItem(card.id).is_wishlist" @change="updateCollectionItem(card.id, 'is_wishlist', $event.target.checked)" class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"/>
                                    Wishlist
                                </label>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Qty</span>
                                <input type="number" min="0" :value="getItem(card.id).quantity" @change="updateCollectionItem(card.id, 'quantity', parseInt($event.target.value) || 0)" class="w-full text-xs p-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>