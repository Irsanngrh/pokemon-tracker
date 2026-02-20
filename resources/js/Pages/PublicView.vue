<script setup>
import { computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
    slug: String,
    items: Array,
    summary: Object
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
};

const ownedItems = computed(() => props.items.filter(item => item.is_owned));
const wishlistItems = computed(() => props.items.filter(item => item.is_wishlist && !item.is_owned));
</script>

<template>
    <AppLayout>
        <div class="space-y-8">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-medium mb-2">
                    Public Collection
                </div>
                <h1 class="text-3xl font-bold tracking-tight">Trainer's Collection</h1>
                <p class="text-gray-500 dark:text-gray-400">Read-only view for /c/{{ slug }}</p>
            </div>

            <div class="bg-gradient-to-r from-gray-800 to-gray-900 dark:from-black dark:to-gray-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-center md:text-left">
                    <h2 class="text-gray-400 font-medium text-sm mb-1">Total Estimated Value</h2>
                    <div class="text-4xl font-bold tracking-tight text-green-400">{{ formatRupiah(summary.total_value) }}</div>
                </div>
                <div class="bg-white/10 px-6 py-3 rounded-xl backdrop-blur-sm border border-white/5 text-center">
                    <div class="text-gray-400 text-sm mb-1">Total Cards Owned</div>
                    <div class="text-2xl font-bold">{{ summary.total_cards }}</div>
                </div>
            </div>

            <div v-if="ownedItems.length > 0" class="space-y-4">
                <h3 class="text-xl font-bold border-b border-gray-200 dark:border-gray-800 pb-2">Owned Cards</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div v-for="item in ownedItems" :key="item.id" class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-[#191919] shadow-sm">
                        <div class="aspect-[63/88] bg-gray-100 dark:bg-gray-800 relative">
                            <img v-if="item.card.image_url" :src="item.card.image_url" :alt="item.card.name" class="w-full h-full object-cover" loading="lazy" />
                        </div>
                        <div class="p-3 flex flex-col flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <div class="text-xs font-semibold text-gray-500">{{ item.card.card_number }}</div>
                                <div class="text-xs font-bold text-green-600 dark:text-green-400">
                                    {{ item.card.price ? formatRupiah(item.card.price.price) : 'Rp 0' }}
                                </div>
                            </div>
                            <div class="font-medium text-sm mb-2 line-clamp-2">{{ item.card.name }}</div>
                            <div class="mt-auto pt-2 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                                <span class="text-xs font-medium text-gray-500">{{ item.card.expansion.name }}</span>
                                <span class="text-xs font-bold px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">Qty: {{ item.quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="wishlistItems.length > 0" class="space-y-4 pt-8">
                <h3 class="text-xl font-bold border-b border-gray-200 dark:border-gray-800 pb-2 text-pink-600 dark:text-pink-400">Wishlist</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 opacity-75">
                    <div v-for="item in wishlistItems" :key="item.id" class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-gray-50 dark:bg-[#151515]">
                        <div class="aspect-[63/88] bg-gray-100 dark:bg-gray-800 relative grayscale">
                            <img v-if="item.card.image_url" :src="item.card.image_url" :alt="item.card.name" class="w-full h-full object-cover" loading="lazy" />
                        </div>
                        <div class="p-3 flex flex-col flex-1">
                            <div class="text-xs font-semibold text-gray-500 mb-1">{{ item.card.card_number }}</div>
                            <div class="font-medium text-sm mb-2 line-clamp-2">{{ item.card.name }}</div>
                            <div class="mt-auto text-xs text-pink-600 dark:text-pink-400 font-medium">Looking for this</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="ownedItems.length === 0 && wishlistItems.length === 0" class="text-center py-20 text-gray-500">
                This collection is currently empty.
            </div>
        </div>
    </AppLayout>
</template>