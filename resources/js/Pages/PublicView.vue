<script setup>
import { computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { cleanCardName } from '../store';

const props = defineProps({ slug: String, items: Array, summary: Object });
const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
const ownedItems = computed(() => props.items.filter(item => item.is_owned));
</script>

<template>
    <AppLayout>
        <div class="space-y-8">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-medium mb-2">Public Collection</div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Trainer's Collection</h1>
            </div>
            <div class="bg-gradient-to-r from-gray-900 to-black rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 border border-gray-800">
                <div class="text-center md:text-left">
                    <h2 class="text-gray-400 font-medium text-sm mb-1 uppercase tracking-wider">Total Estimated Value</h2>
                    <div class="text-4xl font-bold tracking-tight text-green-400">{{ formatRupiah(summary.total_value) }}</div>
                </div>
                <div class="bg-white/10 px-6 py-3 rounded-xl backdrop-blur-sm border border-white/5 text-center">
                    <div class="text-gray-400 text-sm mb-1 uppercase tracking-wider">Total Cards Owned</div>
                    <div class="text-2xl font-bold">{{ summary.total_cards }}</div>
                </div>
            </div>
            <div v-if="ownedItems.length" class="space-y-4">
                <h3 class="text-xl font-bold border-b border-gray-200 dark:border-gray-800 pb-2 text-gray-900 dark:text-white">Owned Cards</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <div v-for="item in ownedItems" :key="item.id" class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#1a1a1a] shadow-sm overflow-hidden group">
                        <div class="aspect-[63/88] bg-gray-100 dark:bg-gray-800 relative overflow-hidden">
                            <img :src="item.card.image_url" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110" />
                        </div>
                        <div class="p-4 flex flex-col flex-1 relative z-10 bg-white dark:bg-[#1a1a1a]">
                            <div class="font-medium text-sm leading-tight text-gray-900 dark:text-gray-100">{{ cleanCardName(item.card.name, item.card.category) }}</div>
                            <div class="uppercase text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-1 mb-2">{{ item.card.category || 'BASIC' }}</div>
                            <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                                <span class="text-[10px] font-medium text-gray-500">{{ item.card.card_number }}</span>
                                <span class="text-xs font-bold px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded">Qty: {{ item.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>