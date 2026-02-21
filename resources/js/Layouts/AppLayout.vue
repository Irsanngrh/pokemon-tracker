<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

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
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f0f11] text-gray-900 dark:text-gray-100 transition-colors duration-200 antialiased flex flex-col">
        <nav class="sticky top-0 z-[90] bg-white/70 dark:bg-[#151515]/80 backdrop-blur-xl border-b border-gray-200/80 dark:border-gray-800/80 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-full border-2 border-gray-900 dark:border-white relative overflow-hidden bg-white flex items-center justify-center transition transform group-hover:rotate-12">
                        <div class="absolute top-0 w-full h-1/2 bg-red-500"></div>
                        <div class="absolute w-full h-1 bg-gray-900 dark:bg-white z-10"></div>
                        <div class="w-2.5 h-2.5 rounded-full border-2 border-gray-900 dark:border-white bg-white z-20"></div>
                    </div>
                    <span class="text-xl font-black tracking-tight transition group-hover:opacity-80">Pokémon Tracker</span>
                </Link>
                <button @click="toggleTheme" class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white shadow-inner">
                    <svg v-if="!isDarkMode" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </button>
            </div>
        </nav>
        <main class="max-w-7xl mx-auto px-6 py-8 w-full flex-1 relative z-10">
            <slot />
        </main>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap');
body { font-family: 'Roboto', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 20px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #4b5563; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #9ca3af; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #6b7280; }
.custom-scrollbar { scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }
.dark .custom-scrollbar { scrollbar-color: #4b5563 transparent; }
</style>