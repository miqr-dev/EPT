<script setup lang="ts">
import { ref, watch } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  show: boolean
}>()

const emit = defineEmits(['close', 'add-time'])

const minutes = ref(0)

const close = () => {
  emit('close')
}

const addTime = () => {
  if (minutes.value > 0) {
    emit('add-time', minutes.value)
    close()
  }
}

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      minutes.value = 0
    }
  },
)
</script>

<template>
  <Dialog :open="show" @update:open="close">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Zusätzliche Zeit hinzufügen</DialogTitle>
        <DialogDescription>
          Geben Sie die Anzahl der Minuten ein, die Sie dem Test dieses Teilnehmers hinzufügen
          möchten.
        </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <Input
          id="minutes"
          v-model="minutes"
          type="number"
          class="w-full"
          min="1"
          placeholder="Minuten"
        />
      </div>
      <DialogFooter>
        <Button variant="secondary" @click="close">Abbrechen</Button>
        <Button @click="addTime" :disabled="minutes <= 0">Zeit hinzufügen</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
