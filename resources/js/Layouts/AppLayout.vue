<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { toastState } from '../store';

const isDarkMode = ref(false);

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
});
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-[#121212] text-gray-900 dark:text-gray-100 transition-colors duration-200 font-sans">
        <div v-if="toastState.show" class="fixed top-20 right-6 z-[120] px-4 py-3 rounded-lg shadow-lg font-medium text-sm text-white transition-all transform duration-300" :class="toastState.type === 'success' ? 'bg-green-600 dark:bg-green-700' : 'bg-red-600 dark:bg-red-700'">
            {{ toastState.message }}
        </div>

        <nav class="sticky top-0 z-[90] flex items-center justify-between px-6 py-3 bg-white/80 dark:bg-[#121212]/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center">
                <Link href="/" class="text-lg font-bold tracking-tight hover:opacity-80 transition">Pokémon Tracker</Link>
            </div>
            <div class="flex items-center gap-4">
                <Link href="/my-collection" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">My Collection</Link>
                <div class="h-5 w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
                <button @click="toggleTheme" class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
            </div>
        </nav>
        <main class="max-w-7xl mx-auto px-6 py-8 relative z-10">
            <slot />
        </main>
    </div>
</template>

<style>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>