<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({ cards: Array, adminKey: String });
const processingId = ref(null);

const saveCard = (card) => {
    processingId.value = card.id;
    router.post(`/admin/cards/${card.id}?key=${props.adminKey}`, { image_url: card.image_url, rarity: card.rarity }, { preserveScroll: true, onSuccess: () => { processingId.value = null; }});
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Admin Panel</h1>
            <div class="bg-white dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 dark:bg-[#222] text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-4 font-bold tracking-widest uppercase text-[10px]">Nama Kartu</th>
                                <th class="px-6 py-4 font-bold tracking-widest uppercase text-[10px]">No</th>
                                <th class="px-6 py-4 font-bold tracking-widest uppercase text-[10px]">Seri</th>
                                <th class="px-6 py-4 font-bold tracking-widest uppercase text-[10px]">Rarity</th>
                                <th class="px-6 py-4 font-bold tracking-widest uppercase text-[10px] text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="card in cards" :key="card.id" class="hover:bg-gray-50 dark:hover:bg-[#252525] transition">
                                <td class="px-6 py-4 font-medium">{{ card.name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ card.card_number }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ card.expansion.name }}</td>
                                <td class="px-6 py-4"><input type="text" v-model="card.rarity" class="w-20 px-3 py-2 bg-white dark:bg-[#121212] border border-gray-200 dark:border-gray-700 rounded-lg text-center font-medium" /></td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="saveCard(card)" :disabled="processingId === card.id" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg disabled:opacity-50 transition shadow-sm">{{ processingId === card.id ? 'Menyimpan...' : 'Simpan' }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>