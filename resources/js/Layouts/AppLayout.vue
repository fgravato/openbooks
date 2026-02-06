<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Avatar from '@/Components/UI/Avatar.vue';
import Dropdown from '@/Components/UI/Dropdown.vue';

defineProps<{
  title?: string;
}>();

const page = usePage();
const user = page.props.auth.user;
const sidebarOpen = ref(true);

const navigation = [
  { name: 'Dashboard', href: route('dashboard'), icon: 'HomeIcon' },
  { name: 'Invoices', href: route('invoices.index'), icon: 'DocumentTextIcon' },
  { name: 'Clients', href: route('clients.index'), icon: 'UsersIcon' },
  { name: 'Expenses', href: route('expenses.index'), icon: 'CreditCardIcon' },
  { name: 'Settings', href: route('settings.profile'), icon: 'CogIcon' },
];
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <Head :title="title" />

    <!-- Sidebar -->
    <div :class="[sidebarOpen ? 'w-64' : 'w-20', 'fixed inset-y-0 left-0 z-50 transition-all duration-300 bg-indigo-700 dark:bg-indigo-900 overflow-y-auto']">
      <div class="flex items-center justify-center h-16 bg-indigo-800 dark:bg-indigo-950 px-4">
        <span v-if="sidebarOpen" class="text-white text-xl font-bold">OpenBooks</span>
        <span v-else class="text-white text-xl font-bold">OB</span>
      </div>
      <nav class="mt-5 px-2 space-y-1">
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          :class="[
            route().current(item.href) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600',
            'group flex items-center px-2 py-2 text-base font-medium rounded-md transition-colors'
          ]"
        >
          <span :class="[sidebarOpen ? 'mr-4' : 'mx-auto']">
            <!-- Icon placeholder -->
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </span>
          <span v-if="sidebarOpen">{{ item.name }}</span>
        </Link>
      </nav>
    </div>

    <!-- Main Content -->
    <div :class="[sidebarOpen ? 'pl-64' : 'pl-20', 'flex flex-col flex-1 transition-all duration-300']">
      <!-- Top Header -->
      <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 sticky top-0 z-40">
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="flex items-center space-x-4">
          <Dropdown align="right" width="48">
            <template #trigger>
              <button class="flex items-center focus:outline-none">
                <Avatar :name="user?.name || ''" size="sm" />
                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300 hidden md:block">{{ user?.name }}</span>
              </button>
            </template>
            <template #content>
              <div class="block px-4 py-2 text-xs text-gray-400">Manage Account</div>
              <Link :href="route('settings.profile')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</Link>
              <div class="border-t border-gray-100 dark:border-gray-700"></div>
              <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</Link>
            </template>
          </Dropdown>
        </div>
      </header>

      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
