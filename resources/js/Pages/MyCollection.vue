<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import { cleanCardName } from '../store';

const items = ref([]);
const summary = ref({ total_value: 0, total_cards: 0 });
const isReady = ref(false);
const token = ref(null);
const slug = ref(null);
const showKey = ref(false);

const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);

const loadData = async () => {
    try {
        const itemsRes = await axios.get('/api/collection/items');
        items.value = itemsRes.data || [];
        const sumRes = await axios.get('/api/collection/summary');
        summary.value = sumRes.data || { total_value: 0, total_cards: 0 };
    } catch (e) {
        items.value = [];
    }
};

const exportData = (type) => {
    if (!token.value) return;
    window.location.href = `/api/export/${type}?token=${token.value}`;
};

const ownedItems = computed(() => items.value.filter(i => i.is_owned === true || i.is_owned === 1));
const wishlistItems = computed(() => items.value.filter(i => (i.is_wishlist === true || i.is_wishlist === 1) && !(i.is_owned === true || i.is_owned === 1)));

onMounted(async () => {
    let storedToken = localStorage.getItem('collection_token');
    if (!storedToken) {
        window.location.href = '/';
        return;
    }
    try {
        axios.defaults.headers.common['X-Collection-Token'] = storedToken;
        const response = await axios.post('/collection/verify');
        token.value = storedToken;
        slug.value = response.data.public_slug;
        await loadData();
        isReady.value = true;
    } catch (error) {
        window.location.href = '/';
    }
});
</script>

<template>
    <AppLayout>
        <div v-if="!isReady" class="flex items-center justify-center py-20">
            <div class="text-gray-500 font-medium animate-pulse">Loading Collection...</div>
        </div>
        
        <div v-else class="space-y-8">
            <div class="bg-gradient-to-r from-gray-900 to-black rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 border border-gray-800 relative z-10">
                <div class="text-center md:text-left">
                    <h2 class="text-gray-400 font-medium text-sm mb-1 uppercase tracking-wider">Total Estimated Value</h2>
                    <div class="text-4xl font-bold tracking-tight text-green-400">{{ formatRupiah(summary.total_value) }}</div>
                </div>
                <div class="bg-white/10 px-6 py-3 rounded-xl backdrop-blur-sm border border-white/5 text-center">
                    <div class="text-gray-400 text-sm mb-1 uppercase tracking-wider">Total Cards Owned</div>
                    <div class="text-2xl font-bold">{{ summary.total_cards }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
                <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Private Key</div>
                        <div class="flex items-center bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <input :type="showKey ? 'text' : 'password'" readonly :value="token" class="w-full bg-transparent text-sm p-3 outline-none text-red-500 font-mono" />
                            <button @click="showKey = !showKey" class="p-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                <svg v-if="showKey" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Public Share URL</div>
                        <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-sm text-blue-500 font-mono">
                            {{ window.location.origin }}/c/{{ slug }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="exportData('pdf')" class="p-3 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-xl transition border border-red-100 dark:border-red-800" title="Download PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </button>
                    <button @click="exportData('excel')" class="p-3 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-600 dark:text-green-400 rounded-xl transition border border-green-100 dark:border-green-800" title="Download Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="17"></line><line x1="16" y1="13" x2="8" y2="17"></line></svg>
                    </button>
                </div>
            </div>

            <div v-if="ownedItems.length" class="space-y-4">
                <h3 class="text-xl font-bold border-b border-gray-200 dark:border-gray-800 pb-2 text-gray-900 dark:text-white">Owned Cards</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <div v-for="item in ownedItems" :key="item.id" class="relative group h-full flex flex-col hover:z-[60] transition-all duration-300">
                        <div class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#1a1a1a] shadow-sm h-full">
                            <div class="aspect-[63/88] relative z-10">
                                <img :src="item.card?.image_url" class="absolute inset-0 w-full h-full object-cover transition-all duration-300 group-hover:scale-[1.2] group-hover:-translate-y-6 group-hover:z-[70] rounded-t-xl group-hover:rounded-xl group-hover:shadow-2xl origin-bottom" />
                            </div>
                            <div class="p-4 flex flex-col flex-1 relative z-20 bg-white dark:bg-[#1a1a1a] rounded-b-xl">
                                <div class="font-medium text-sm leading-tight text-gray-900 dark:text-gray-100">{{ cleanCardName(item.card?.name) }}</div>
                                <div class="uppercase text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-1 mb-2">{{ item.card?.category || 'BASIC' }}</div>
                                <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                                    <span class="text-[10px] font-medium text-gray-500">{{ item.card?.card_number }}</span>
                                    <span class="text-xs font-bold px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded">Qty: {{ item.quantity }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="wishlistItems.length" class="space-y-4 pt-8">
                <h3 class="text-xl font-bold border-b border-gray-200 dark:border-gray-800 pb-2 text-pink-600 dark:text-pink-400">Wishlist</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 opacity-80">
                    <div v-for="item in wishlistItems" :key="item.id" class="relative group h-full flex flex-col hover:z-[60] transition-all duration-300">
                        <div class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-[#1a1a1a] shadow-sm h-full">
                            <div class="aspect-[63/88] grayscale relative z-10">
                                <img :src="item.card?.image_url" class="absolute inset-0 w-full h-full object-cover transition-all duration-300 group-hover:scale-[1.2] group-hover:-translate-y-6 group-hover:z-[70] rounded-t-xl group-hover:rounded-xl group-hover:shadow-2xl origin-bottom group-hover:grayscale-0" />
                            </div>
                            <div class="p-4 flex flex-col flex-1 relative z-20 bg-gray-50 dark:bg-[#1a1a1a] rounded-b-xl">
                                <div class="font-medium text-sm leading-tight text-gray-900 dark:text-gray-100">{{ cleanCardName(item.card?.name) }}</div>
                                <div class="uppercase text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-1 mb-2">{{ item.card?.category || 'BASIC' }}</div>
                                <div class="mt-auto text-[10px] font-medium text-pink-600 dark:text-pink-400">Looking for this</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="!ownedItems.length && !wishlistItems.length" class="text-center py-20 text-gray-500 relative z-10">
                Your collection is empty. Explore the Library to add cards.
            </div>
        </div>
    </AppLayout>
</template>