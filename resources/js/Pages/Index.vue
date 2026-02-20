<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import { showToast, cleanCardName } from '../store';

const isReady = ref(false);
const expansions = ref([]);
const cards = ref([]);
const collectionItems = ref([]);
const selectedExpansion = ref(null);
const selectedCardDetails = ref(null);
const isModalZoomed = ref(false);

const initializeCollection = async () => {
    const response = await axios.post('/collection/init');
    localStorage.setItem('collection_token', response.data.private_token);
    axios.defaults.headers.common['X-Collection-Token'] = response.data.private_token;
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

const updateCollectionItem = async (cardId, field, value, cardRawName) => {
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

    const cardName = cleanCardName(cardRawName);
    if (field === 'is_owned' && value) showToast(`Added ${cardName} to Owned`);
    if (field === 'is_owned' && !value) showToast(`Removed ${cardName} from Owned`, 'error');
    if (field === 'is_wishlist' && value) showToast(`Added ${cardName} to Wishlist`);
    if (field === 'is_wishlist' && !value) showToast(`Removed ${cardName} from Wishlist`, 'error');
};

const getItem = (cardId) => {
    return collectionItems.value.find(i => i.card_id === cardId) || { is_owned: false, is_wishlist: false, quantity: 0 };
};

const openModal = (card) => {
    selectedCardDetails.value = card;
    isModalZoomed.value = false;
};

onMounted(async () => {
    let storedToken = localStorage.getItem('collection_token');
    if (storedToken) {
        try {
            await axios.post('/collection/verify', {}, { headers: { 'X-Collection-Token': storedToken } });
            axios.defaults.headers.common['X-Collection-Token'] = storedToken;
        } catch (error) {
            localStorage.removeItem('collection_token');
            await initializeCollection();
        }
    } else await initializeCollection();
    
    await loadExpansions();
    await loadCollectionItems();
    isReady.value = true;
});
</script>

<template>
    <AppLayout>
        <div v-if="!isReady" class="flex items-center justify-center py-20">
            <div class="text-gray-500 font-medium animate-pulse">Loading Workspace...</div>
        </div>
        
        <div v-else class="space-y-6">
            <div v-if="selectedCardDetails" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click.self="selectedCardDetails = null">
                <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl max-w-4xl w-full flex flex-col md:flex-row shadow-2xl relative border border-gray-200 dark:border-gray-800">
                    <button @click="selectedCardDetails = null" class="absolute top-4 right-4 z-10 p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <div class="w-full md:w-1/2 p-8 flex justify-center items-center bg-gray-50 dark:bg-black/50 rounded-l-2xl overflow-hidden relative">
                        <img :src="selectedCardDetails.image_url" 
                             @click="isModalZoomed = !isModalZoomed" 
                             :class="isModalZoomed ? 'scale-150 cursor-zoom-out' : 'max-h-[450px] object-contain drop-shadow-2xl rounded-xl cursor-zoom-in'" 
                             class="transition-transform duration-300 relative z-20" />
                    </div>
                    
                    <div class="w-full md:w-1/2 p-8 overflow-y-auto max-h-[85vh] text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <h2 class="text-3xl font-bold leading-tight">{{ cleanCardName(selectedCardDetails.name) }}</h2>
                            <div class="uppercase text-xs font-bold text-gray-500 dark:text-gray-400 mt-1">{{ selectedCardDetails.category || 'BASIC' }}</div>
                        </div>
                        
                        <div class="space-y-4">
                            <div v-if="selectedCardDetails.details?.attacks?.length" class="space-y-4">
                                <h3 class="font-bold text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-200 dark:border-gray-800 pb-1">Serangan</h3>
                                <div v-for="atk in selectedCardDetails.details.attacks" class="bg-gray-50 dark:bg-[#252525] p-3 rounded-lg border border-gray-100 dark:border-gray-800">
                                    <div class="flex justify-between font-bold text-sm mb-1">
                                        <span>{{ atk.name }}</span>
                                        <span class="text-red-600 dark:text-red-400">Kekuatan: {{ atk.damage || '0' }}</span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed"><span class="font-semibold text-gray-800 dark:text-gray-300">Efek:</span> {{ atk.effect || '-' }}</div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-2 text-center text-xs border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 dark:bg-[#252525] p-2">
                                    <div class="font-bold text-gray-400 uppercase text-[9px] mb-1">Kelemahan</div>
                                    <div class="font-medium">{{ selectedCardDetails.details?.weakness || '--' }}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-[#252525] p-2 border-l border-r border-gray-200 dark:border-gray-800">
                                    <div class="font-bold text-gray-400 uppercase text-[9px] mb-1">Resistansi</div>
                                    <div class="font-medium">{{ selectedCardDetails.details?.resistance || '--' }}</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-[#252525] p-2">
                                    <div class="font-bold text-gray-400 uppercase text-[9px] mb-1">Mundur</div>
                                    <div class="font-medium">{{ selectedCardDetails.details?.retreat || '--' }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 text-sm pt-2">
                                <div>
                                    <div class="font-bold text-gray-400 text-[10px] uppercase tracking-wider">Regulasi</div>
                                    <div class="font-medium mt-1">{{ selectedCardDetails.details?.regulation || '-' }}</div>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-400 text-[10px] uppercase tracking-wider">Nomor Kartu</div>
                                    <div class="font-medium mt-1">{{ selectedCardDetails.card_number }}</div>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-400 text-[10px] uppercase tracking-wider">Ilustrator</div>
                                    <div class="font-medium mt-1">{{ selectedCardDetails.illustrator || 'Unknown' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full flex items-center border-b border-gray-200 dark:border-gray-800">
                <div class="flex gap-3 overflow-x-auto pb-4 pt-2 hide-scrollbar w-full scroll-smooth">
                    <button v-for="expansion in expansions" :key="expansion.id" @click="selectedExpansion = expansion.id; loadCards()"
                        :class="['px-5 py-2.5 text-sm font-bold rounded-full whitespace-nowrap transition-all duration-300 border shadow-sm',
                            selectedExpansion === expansion.id ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900 dark:border-white shadow-md transform -translate-y-0.5' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 dark:bg-[#1a1a1a] dark:text-gray-400 dark:border-gray-800 dark:hover:border-gray-600']">
                        {{ expansion.name }}
                    </button>
                </div>
            </div>

            <div v-if="cards.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <div v-for="card in cards" :key="card.id" class="relative group h-full flex flex-col hover:z-[60] transition-all duration-300">
                    <div class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#1a1a1a] shadow-sm h-full">
                        <div @click="openModal(card)" class="aspect-[63/88] relative cursor-pointer z-10">
                            <img v-if="card.image_url" :src="card.image_url" :alt="card.name" class="absolute inset-0 w-full h-full object-cover transition-all duration-300 group-hover:scale-[1.2] group-hover:-translate-y-6 group-hover:z-[70] rounded-t-xl group-hover:rounded-xl group-hover:shadow-2xl origin-bottom" loading="lazy" />
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center pointer-events-none z-[80] opacity-0 group-hover:opacity-100">
                                <span class="text-white font-medium bg-black/60 px-3 py-1.5 rounded-full text-xs backdrop-blur-sm shadow-lg">Details</span>
                            </div>
                        </div>
                        <div class="p-4 flex flex-col flex-1 relative z-20 bg-white dark:bg-[#1a1a1a] rounded-b-xl">
                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ card.card_number }}</div>
                            <div class="font-medium text-sm leading-tight text-gray-900 dark:text-gray-100">{{ cleanCardName(card.name) }}</div>
                            <div class="uppercase text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-1 mb-4">{{ card.category || 'BASIC' }}</div>
                            
                            <div class="mt-auto space-y-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2 text-xs cursor-pointer text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" :checked="getItem(card.id).is_owned" @change="updateCollectionItem(card.id, 'is_owned', $event.target.checked, card.name)" class="rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-blue-600 focus:ring-blue-500"/>
                                        Owned
                                    </label>
                                    <label class="flex items-center gap-2 text-xs cursor-pointer text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" :checked="getItem(card.id).is_wishlist" @change="updateCollectionItem(card.id, 'is_wishlist', $event.target.checked, card.name)" class="rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-pink-600 focus:ring-pink-500"/>
                                        Wishlist
                                    </label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Qty</span>
                                    <input type="number" min="0" :value="getItem(card.id).quantity" @change="updateCollectionItem(card.id, 'quantity', parseInt($event.target.value) || 0, card.name)" class="w-full text-xs p-1.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-gray-700 rounded text-gray-900 dark:text-gray-100 focus:border-blue-500 outline-none"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>