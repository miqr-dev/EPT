<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import axios from 'axios'

const handleLogout = () => {
  router.flushAll()
}

const page = usePage()
const initialCanViewContract = computed(() => !!page.props.auth?.user?.contract_view_enabled)
const canViewContract = ref<boolean>(initialCanViewContract.value)
const contractWindow = ref<Window | null>(null)
let polling: number | null = null

const closeContractWindow = () => {
  if (contractWindow.value && !contractWindow.value.closed) {
    contractWindow.value.close()
  }
  contractWindow.value = null
}

const refreshContractStatus = async () => {
  try {
    const response = await axios.get(route('api.my-contract-status'))
    canViewContract.value = !!response.data?.user_contract_view_enabled
  } catch (error) {
    console.error('Fehler beim Laden des Vertragsstatus', error)
  }
}

const openContract = () => {
  closeContractWindow()
  contractWindow.value = window.open(route('my.pdf'), '_blank', 'noopener')
}

watch(initialCanViewContract, (value) => {
  canViewContract.value = !!value
})

watch(canViewContract, (enabled) => {
  if (!enabled) {
    closeContractWindow()
  }
})

onMounted(() => {
  refreshContractStatus()
  polling = window.setInterval(refreshContractStatus, 3000)
})

onUnmounted(() => {
  if (polling) {
    window.clearInterval(polling)
  }
  closeContractWindow()
})
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
      <div v-if="canViewContract" class="block">
        <Button variant="outline" class="w-full" type="button" @click="openContract">Vertrag ansehen</Button>
      </div>
    </div>
  </div>
</template>
