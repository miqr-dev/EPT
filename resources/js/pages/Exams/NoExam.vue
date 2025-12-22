<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const handleLogout = () => {
  router.flushAll()
}

const page = usePage()
const canViewContract = computed(() => !!page.props.auth?.user?.contract_view_enabled)
</script>



<template>

  <Head title="Bitte warten …" />
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="absolute top-4 right-4">
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        @click="handleLogout"
        class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
      >
        <LogOut class="h-5 w-5" />
        <span>Abmelden</span>
      </Link>
    </div>
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md text-center">
      <h1 class="text-2xl font-bold text-gray-800">Bitte warten …</h1>
      <Link :href="route('my-exam')" class="block mt-4">
        <Button class="w-full">Zur Prüfung</Button>
      </Link>
      <Link v-if="canViewContract" :href="route('my.pdf')" target="_blank" class="block">
        <Button variant="outline" class="w-full">Vertrag ansehen</Button>
      </Link>
    </div>
  </div>
</template>
