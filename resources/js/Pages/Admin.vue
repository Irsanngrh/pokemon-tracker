<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({ cards: Array, adminKey: String });
const processingId = ref(null);

const saveCard = (card) => {
    processingId.value = card.id;
    let currentPrice = card.price ? card.price.price : 0;
    router.post(`/admin/cards/${card.id}?key=${props.adminKey}`, { price: currentPrice, image_url: card.image_url }, { preserveScroll: true, onSuccess: () => { processingId.value = null; }});
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-red-600 dark:text-red-500">Admin Override Panel</h1>
            </div>
            <div class="bg-white dark:bg-[#191919] rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 dark:bg-[#151515] text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-4 font-medium">Card Name</th>
                                <th class="px-6 py-4 font-medium">Number</th>
                                <th class="px-6 py-4 font-medium">Set</th>
                                <th class="px-6 py-4 font-medium">Price (IDR)</th>
                                <th class="px-6 py-4 font-medium">Image URL</th>
                                <th class="px-6 py-4 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr v-for="card in cards" :key="card.id">
                                <td class="px-6 py-4 font-medium">{{ card.name }}</td>
                                <td class="px-6 py-4">{{ card.card_number }}</td>
                                <td class="px-6 py-4">{{ card.expansion.name }}</td>
                                <td class="px-6 py-4">
                                    <input type="number" v-model="card.price.price" v-if="card.price" class="w-32 px-3 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md" />
                                    <input type="number" v-else @input="(e) => card.price = { price: e.target.value }" class="w-32 px-3 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md" placeholder="0" />
                                </td>
                                <td class="px-6 py-4"><input type="text" v-model="card.image_url" class="w-64 px-3 py-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md" /></td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="saveCard(card)" :disabled="processingId === card.id" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md disabled:opacity-50 transition">{{ processingId === card.id ? 'Saving...' : 'Save' }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>