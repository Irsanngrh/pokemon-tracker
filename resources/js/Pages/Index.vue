<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import AppLayout from '../Layouts/AppLayout.vue';
import { cleanCardName } from '../store';

const isReady = ref(false);
const expansions = ref([]);
const cards = ref([]);
const selectedExpansion = ref('all');
const selectedCardDetails = ref(null);

const isModalZoomed = ref(false);
const zoomOriginX = ref(50);
const zoomOriginY = ref(50);

const searchQuery = ref('');
const filterType = ref('Semua');
const filterPokeType = ref('Semua');
const filterStage = ref('Semua');
const filterIllustrator = ref('Semua');
const filterRarity = ref('Semua');
const activeDropdown = ref(null);

const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
};

const closeDropdowns = () => {
    activeDropdown.value = null;
};

const selectFilter = (type, value) => {
    if (type === 'expansion') { selectedExpansion.value = value; loadCards(); }
    if (type === 'type') filterType.value = value;
    if (type === 'pokeType') filterPokeType.value = value;
    if (type === 'stage') filterStage.value = value;
    if (type === 'rarity') filterRarity.value = value;
    if (type === 'illustrator') filterIllustrator.value = value;
    activeDropdown.value = null;
};

const getTypeName = (url) => {
    if (!url) return 'Unknown';
    const match = url.match(/type_([a-zA-Z]+)\.png/);
    return match ? match[1] : 'Unknown';
};

const loadExpansions = async () => {
    const response = await axios.get('/api/expansions');
    expansions.value = response.data;
    await loadCards();
};

const loadCards = async () => {
    const response = await axios.get(`/api/cards?expansion_id=${selectedExpansion.value}`);
    cards.value = response.data;
    searchQuery.value = ''; filterType.value = 'Semua'; filterPokeType.value = 'Semua';
    filterStage.value = 'Semua'; filterIllustrator.value = 'Semua'; filterRarity.value = 'Semua';
    isReady.value = true;
};

const availableIllustrators = computed(() => ['Semua', ...new Set(cards.value.map(c => c.illustrator).filter(i => i && i !== '--'))].sort());
const availableRarities = computed(() => ['Semua', ...new Set(cards.value.map(c => c.rarity).filter(r => r && r !== '--'))].sort());
const availablePokeTypes = computed(() => ['Semua', ...new Set(cards.value.filter(c => !['Item', 'Supporter', 'Stadium', 'Pokémon Tool', 'Energi Spesial'].includes(c.category)).map(c => getTypeName(c.details?.type_url)).filter(t => t !== 'Unknown'))].sort());

const filteredCards = computed(() => {
    return cards.value.filter(card => {
        if (searchQuery.value && !card.name.toLowerCase().includes(searchQuery.value.toLowerCase())) return false;
        const isTrainer = ['Item', 'Supporter', 'Stadium', 'Pokémon Tool', 'Energi Spesial'].includes(card.category);
        if (filterType.value === 'Pokémon' && isTrainer) return false;
        if (filterType.value !== 'Semua' && filterType.value !== 'Pokémon' && card.category !== filterType.value) return false;
        if (filterType.value === 'Pokémon' || filterType.value === 'Semua') {
            if (filterPokeType.value !== 'Semua' && getTypeName(card.details?.type_url) !== filterPokeType.value) return false;
            if (filterStage.value !== 'Semua') {
                if (filterStage.value === 'ex') {
                    if (!card.name.toLowerCase().includes('ex')) return false;
                } else if (card.category !== filterStage.value) {
                    return false;
                }
            }
        }
        if (filterIllustrator.value !== 'Semua' && card.illustrator !== filterIllustrator.value) return false;
        if (filterRarity.value !== 'Semua' && card.rarity !== filterRarity.value) return false;
        return true;
    });
});

const openModal = (card) => {
    selectedCardDetails.value = card;
    isModalZoomed.value = false;
};

const updateZoomOrigin = (e) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    zoomOriginX.value = Math.max(0, Math.min(100, x));
    zoomOriginY.value = Math.max(0, Math.min(100, y));
};

const handleClickZoom = (e) => {
    if (!isModalZoomed.value) { updateZoomOrigin(e); }
    isModalZoomed.value = !isModalZoomed.value;
};

const handleZoomMove = (e) => {
    if (!isModalZoomed.value) return;
    updateZoomOrigin(e);
};

onMounted(async () => {
    await loadExpansions();
});
</script>

<template>
    <AppLayout>
        <div v-if="activeDropdown" @click="closeDropdowns" class="fixed inset-0 z-40"></div>

        <div v-if="!isReady" class="flex items-center justify-center py-32">
            <div class="text-gray-500 font-medium animate-pulse text-lg tracking-widest uppercase">Memuat Pokedex...</div>
        </div>
        
        <div v-else class="space-y-8 relative">
            <Teleport to="body">
                <div v-if="selectedCardDetails" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click.self="selectedCardDetails = null">
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-3xl max-w-5xl w-full flex flex-col md:flex-row shadow-2xl relative border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <button @click="selectedCardDetails = null" class="absolute top-4 right-4 z-50 p-2.5 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                        
                        <div class="w-full md:w-5/12 p-8 flex justify-center items-center bg-gray-50 dark:bg-[#121212] relative overflow-hidden"
                             @mousemove="handleZoomMove" @click="handleClickZoom" @mouseleave="isModalZoomed = false"
                             :style="{ cursor: isModalZoomed ? 'zoom-out' : 'zoom-in' }">
                            <img :src="selectedCardDetails.image_url" 
                                 class="max-h-[500px] object-contain drop-shadow-2xl rounded-2xl transition-transform duration-100 ease-out pointer-events-none"
                                 :class="isModalZoomed ? 'scale-[2.5]' : 'scale-100'"
                                 :style="isModalZoomed ? { transformOrigin: `${zoomOriginX}% ${zoomOriginY}%` } : {}" />
                        </div>
                        
                        <div class="w-full md:w-7/12 p-10 overflow-y-auto max-h-[85vh] text-gray-900 dark:text-gray-100 relative z-10 custom-scrollbar">
                            <div class="mb-8 border-b border-gray-200 dark:border-gray-800 pb-6">
                                <h2 class="text-4xl font-black leading-tight tracking-tight">{{ cleanCardName(selectedCardDetails.name, selectedCardDetails.category) }}</h2>
                                <div class="uppercase text-sm font-bold text-gray-500 dark:text-gray-400 mt-2 mb-5 tracking-widest">{{ selectedCardDetails.category || 'BASIC' }}</div>
                                
                                <div v-if="!['Item', 'Supporter', 'Stadium', 'Pokémon Tool', 'Energi Spesial'].includes(selectedCardDetails.category)" class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                                    <span class="text-sm uppercase tracking-widest font-bold">HP</span>
                                    <span class="text-3xl font-medium text-gray-900 dark:text-white">{{ selectedCardDetails.details?.hp || '--' }}</span>
                                    <span class="mx-2 text-gray-300 dark:text-gray-700">/</span>
                                    <span class="text-sm uppercase tracking-widest font-bold">Tipe</span>
                                    <img v-if="selectedCardDetails.details?.type_url" :src="selectedCardDetails.details.type_url" class="w-8 h-8 drop-shadow-sm ml-1" />
                                    <span v-else class="text-xl font-medium text-gray-900 dark:text-white ml-1">--</span>
                                </div>
                            </div>
                            
                            <div class="space-y-8">
                                <div v-if="['Item', 'Supporter', 'Stadium', 'Pokémon Tool', 'Energi Spesial'].includes(selectedCardDetails.category)" class="bg-gray-50 dark:bg-[#222] p-6 rounded-2xl border border-gray-200 dark:border-gray-800">
                                    <h3 class="text-gray-400 text-xs uppercase tracking-widest font-bold mb-4">Keterangan Efek</h3>
                                    <p class="text-base text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed">{{ selectedCardDetails.details?.trainer_effect || 'Tidak ada efek.' }}</p>
                                </div>

                                <template v-else>
                                    <div v-if="selectedCardDetails.details?.attacks?.length" class="space-y-4">
                                        <div v-for="atk in selectedCardDetails.details.attacks" class="bg-gray-50 dark:bg-[#222] p-5 rounded-2xl border border-gray-200 dark:border-gray-800 flex flex-col justify-center min-h-[80px]">
                                            <div class="flex justify-between items-center" :class="{'mb-3': atk.effect}">
                                                <div class="flex items-center gap-4">
                                                    <div v-if="atk.cost?.length" class="flex gap-1.5">
                                                        <img v-for="icon in atk.cost" :src="icon" class="w-7 h-7 drop-shadow-sm" />
                                                    </div>
                                                    <span class="text-xl font-medium">{{ atk.name }}</span>
                                                </div>
                                                <span class="text-2xl font-medium text-gray-900 dark:text-white">{{ atk.damage }}</span>
                                            </div>
                                            <div class="text-[15px] text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed" v-if="atk.effect">{{ atk.effect }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex w-full text-center text-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden divide-x divide-gray-200 dark:divide-gray-800 shadow-sm bg-gray-50 dark:bg-[#222]">
                                        <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                            <div class="text-gray-400 uppercase text-[10px] mb-2 tracking-widest font-bold">Kelemahan</div>
                                            <div class="flex items-center gap-1.5 text-lg font-medium">
                                                <img v-if="selectedCardDetails.details?.weakness_icon" :src="selectedCardDetails.details.weakness_icon" class="w-6 h-6 drop-shadow-sm" />
                                                <span v-if="selectedCardDetails.details?.weakness_val">{{ selectedCardDetails.details.weakness_val }}</span>
                                                <span v-else>--</span>
                                            </div>
                                        </div>
                                        <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                            <div class="text-gray-400 uppercase text-[10px] mb-2 tracking-widest font-bold">Resistansi</div>
                                            <div class="flex items-center gap-1.5 text-lg font-medium">
                                                <img v-if="selectedCardDetails.details?.resistance_icon" :src="selectedCardDetails.details.resistance_icon" class="w-6 h-6 drop-shadow-sm" />
                                                <span v-if="selectedCardDetails.details?.resistance_val && selectedCardDetails.details.resistance_val !== '--'">{{ selectedCardDetails.details.resistance_val }}</span>
                                                <span v-else>--</span>
                                            </div>
                                        </div>
                                        <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                            <div class="text-gray-400 uppercase text-[10px] mb-2 tracking-widest font-bold">Mundur</div>
                                            <div class="flex items-center justify-center gap-1.5 min-h-[32px]">
                                                <template v-if="selectedCardDetails.details?.retreat_icons?.length">
                                                    <img v-for="icon in selectedCardDetails.details.retreat_icons" :src="icon" class="w-6 h-6 drop-shadow-sm" />
                                                </template>
                                                <span v-else class="text-lg font-medium">--</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div class="flex w-full text-center text-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden divide-x divide-gray-200 dark:divide-gray-800 shadow-sm bg-gray-50 dark:bg-[#222]">
                                    <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                        <span class="text-gray-400 text-[10px] uppercase tracking-widest font-bold">Ilustrator</span>
                                        <span class="mt-1.5 font-medium text-gray-900 dark:text-white break-words text-center w-full">{{ selectedCardDetails.illustrator || '--' }}</span>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                        <span class="text-gray-400 text-[10px] uppercase tracking-widest font-bold">Seri</span>
                                        <span class="mt-1.5 font-medium text-gray-900 dark:text-white">{{ selectedCardDetails.details?.series || '--' }}</span>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                        <span class="text-gray-400 text-[10px] uppercase tracking-widest font-bold">No. Kartu</span>
                                        <span class="mt-1.5 font-medium text-gray-900 dark:text-white">{{ selectedCardDetails.card_number || '--' }}</span>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col items-center justify-center">
                                        <span class="text-gray-400 text-[10px] uppercase tracking-widest font-bold">Rarity</span>
                                        <span class="mt-1.5 font-medium text-gray-900 dark:text-white">{{ selectedCardDetails.rarity || '--' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>

            <div class="bg-white dark:bg-[#1a1a1a] rounded-3xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-end relative z-30">
                <div class="w-full md:w-1/4 relative">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Pilih Expansion</label>
                    <div @click="toggleDropdown('expansion')" class="w-full bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl px-4 py-3 font-medium cursor-pointer flex justify-between items-center transition">
                        <span class="truncate">{{ selectedExpansion === 'all' ? 'Semua Seri' : (expansions.find(e => e.id === selectedExpansion)?.name || 'Pilih') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <ul v-if="activeDropdown === 'expansion'" class="absolute z-50 w-full mt-2 bg-white dark:bg-[#252525] border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-1">
                        <li @click.stop="selectFilter('expansion', 'all')" class="px-4 py-3 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer transition">Semua Seri</li>
                        <li v-for="exp in expansions" :key="exp.id" @click.stop="selectFilter('expansion', exp.id)" class="px-4 py-3 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer transition border-t border-gray-50 dark:border-gray-800">{{ exp.name }}</li>
                    </ul>
                </div>
                
                <div class="w-full md:w-3/4 grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Pencarian Nama</label>
                        <input type="text" v-model="searchQuery" placeholder="Cari..." class="w-full bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl px-4 py-3 transition focus:ring-2 focus:ring-blue-500/50 outline-none" />
                    </div>
                    
                    <div class="relative">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Jenis Kartu</label>
                        <div @click="toggleDropdown('type')" class="w-full bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl px-4 py-3 font-medium cursor-pointer flex justify-between items-center">
                            <span class="truncate">{{ filterType }}</span><svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <ul v-if="activeDropdown === 'type'" class="absolute z-50 w-full mt-2 bg-white dark:bg-[#252525] border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-1">
                            <li v-for="t in ['Semua', 'Pokémon', 'Item', 'Supporter', 'Stadium', 'Pokémon Tool', 'Energi Spesial']" :key="t" @click.stop="selectFilter('type', t)" class="px-4 py-3 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer">{{ t }}</li>
                        </ul>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1" :class="{'opacity-40': filterType !== 'Pokémon' && filterType !== 'Semua'}">Elemen</label>
                        <div @click="filterType === 'Pokémon' || filterType === 'Semua' ? toggleDropdown('pokeType') : null" class="w-full bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl px-4 py-3 font-medium flex justify-between items-center" :class="filterType !== 'Pokémon' && filterType !== 'Semua' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                            <span class="truncate">{{ filterPokeType }}</span><svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <ul v-if="activeDropdown === 'pokeType' && (filterType === 'Pokémon' || filterType === 'Semua')" class="absolute z-50 w-full mt-2 bg-white dark:bg-[#252525] border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-1">
                            <li v-for="pt in availablePokeTypes" :key="pt" @click.stop="selectFilter('pokeType', pt)" class="px-4 py-3 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer">{{ pt }}</li>
                        </ul>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1" :class="{'opacity-40': filterType !== 'Pokémon' && filterType !== 'Semua'}">Tipe (Stage/ex)</label>
                        <div @click="filterType === 'Pokémon' || filterType === 'Semua' ? toggleDropdown('stage') : null" class="w-full bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl px-4 py-3 font-medium flex justify-between items-center" :class="filterType !== 'Pokémon' && filterType !== 'Semua' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                            <span class="truncate">{{ filterStage }}</span><svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <ul v-if="activeDropdown === 'stage' && (filterType === 'Pokémon' || filterType === 'Semua')" class="absolute z-50 w-full mt-2 bg-white dark:bg-[#252525] border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-1">
                            <li v-for="st in ['Semua', 'Basic', 'Stage 1', 'Stage 2', 'VMAX', 'VSTAR', 'ex']" :key="st" @click.stop="selectFilter('stage', st)" class="px-4 py-3 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer">{{ st }}</li>
                        </ul>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Ilustrator</label>
                        <div @click="toggleDropdown('illustrator')" class="w-full bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm rounded-xl px-4 py-3 font-medium cursor-pointer flex justify-between items-center">
                            <span class="truncate pr-2">{{ filterIllustrator }}</span><svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <ul v-if="activeDropdown === 'illustrator'" class="absolute right-0 z-50 w-[250px] mt-2 bg-white dark:bg-[#252525] border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar py-1">
                            <li v-for="il in availableIllustrators" :key="il" @click.stop="selectFilter('illustrator', il)" class="px-4 py-3 text-sm font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer truncate">{{ il }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="filteredCards.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 relative z-10">
                <div v-for="card in filteredCards" :key="card.id" class="flex flex-col rounded-2xl bg-white dark:bg-[#1a1a1a] h-full relative group">
                    <div class="aspect-[63/88] relative z-20 overflow-visible cursor-pointer" @click="openModal(card)">
                        <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-[1.07] group-hover:-translate-y-4 origin-center group-hover:z-[70] drop-shadow-md group-hover:drop-shadow-2xl">
                            <img v-if="card.image_url" :src="card.image_url" :alt="card.name" class="w-full h-full object-cover rounded-2xl" loading="lazy" />
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="filteredCards.length === 0" class="text-center py-32 text-gray-500 font-medium tracking-widest uppercase">
                Tidak ada kartu yang cocok.
            </div>
        </div>
    </AppLayout>
</template>