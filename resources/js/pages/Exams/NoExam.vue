<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>



<template>

  <Head title="No Exam..." />
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="absolute top-4 right-4">
      <Link :href="route('logout')" method="post" as="button" @click="handleLogout"
        class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
      <LogOut class="h-5 w-5" />
      <span>Abmelden</span>
      </Link>
    </div>
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md text-center">
      <h1 class="text-2xl font-bold text-gray-800">No Exam</h1>
    </div>
  </div>
</template>
