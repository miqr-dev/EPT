<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { LogOut } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import axios from 'axios'

const handleLogout = () => {
  router.flushAll()
}

const page = usePage()
const initialCanViewContract = computed(() => !!page.props.auth?.user?.contract_view_enabled)
const canViewContract = ref<boolean>(initialCanViewContract.value)
const isContractDialogOpen = ref(false)
const contractSrc = computed(
  () => `${route('my.pdf')}#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&view=FitH`,
)
let polling: number | null = null

const closeContract = () => {
  isContractDialogOpen.value = false
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
  if (!canViewContract.value) return
  isContractDialogOpen.value = true
}

watch(initialCanViewContract, (value) => {
  canViewContract.value = !!value
})

watch(canViewContract, (enabled) => {
  if (!enabled) {
    closeContract()
  }
})

const handleContractHotkeys = (event: KeyboardEvent) => {
  const key = event.key.toLowerCase()
  if ((event.ctrlKey || event.metaKey) && (key === 'p' || key === 's')) {
    event.preventDefault()
    event.stopPropagation()
  }
}

watch(isContractDialogOpen, (isOpen) => {
  if (isOpen) {
    window.addEventListener('keydown', handleContractHotkeys, true)
  } else {
    window.removeEventListener('keydown', handleContractHotkeys, true)
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
  closeContract()
  window.removeEventListener('keydown', handleContractHotkeys, true)
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
  <Dialog v-model:open="isContractDialogOpen">
    <DialogContent
      class="inset-0 top-0 left-0 h-screen w-screen max-w-none translate-x-0 translate-y-0 overflow-hidden rounded-none border-none bg-white p-0 text-black sm:max-w-none dark:bg-gray-900 dark:text-white"
      @contextmenu.prevent
    >
      <DialogHeader class="sr-only">
        <DialogTitle>Vertragsansicht</DialogTitle>
        <DialogDescription>Vertragsdetails im Vollbild anzeigen.</DialogDescription>
      </DialogHeader>
      <div class="absolute top-4 left-4 flex items-center gap-2">
        <Button variant="ghost" size="sm" @click="closeContract">✕</Button>
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Vertrag</span>
      </div>
      <div class="h-full w-full pt-12">
        <iframe
          :src="contractSrc"
          class="h-full w-full border-0"
          allow="fullscreen"
          allowfullscreen
        />
      </div>
    </DialogContent>
  </Dialog>
</template>
