<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  profile?: any
  professionGroups: any[]
}>()

const today = new Date()
const form = useForm({
  birthday: props.profile?.birthday ?? '',
  age: props.profile?.age ?? '',
  sex: props.profile?.sex ?? '',
  education: props.profile?.education ?? '',
  profession: props.profile?.profession ?? '',
  marital_status: props.profile?.marital_status ?? '',
  household: props.profile?.household ?? '',
  employed: props.profile?.employed ?? false,
  profession_group_id: props.profile?.profession_group_id ?? '',
})

// Age calculation
watch(() => form.birthday, val => {
  if (!val) return form.age = ''
  const bday = new Date(val)
  let age = today.getFullYear() - bday.getFullYear()
  const m = today.getMonth() - bday.getMonth()
  if (m < 0 || (m === 0 && today.getDate() < bday.getDate())) age--
  form.age = age
})

function submit() {
  form.post('/onboarding')
}

// Dropdown options
const educationOptions = [
  'vor der letzten Hauptschul- klasse abgeschlossen',
  'mit der letzten Hauptschul- klasse abgeschlossen',
  'Real oder Gesamtschule ohne Abschlussprüfung',
  'Real oder Gesamtschule mit Abschlussprüfung',
  'Gymnasium ohne Abitur',
  'Abitur ohne anschließendes Studium',
  'Abitur mit nicht abgeschlossenem Studium',
  'Abitur mit abgeschlossenem Studium',
  'Berufsschule'
]
const maritalStatusOptions = [
  { value: 'single', label: 'Ledig' },
  { value: 'married', label: 'Verheiratet' },
  { value: 'divorced/separated', label: 'Geschieden/Getrennt' },
  { value: 'widowed', label: 'Verwitwet' }
]
const householdOptions = [
  { value: 'single', label: 'Allein lebend' },
  { value: 'with_partner', label: 'Mit Partner/in' }
]
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 transition">
    <form @submit.prevent="submit"
          class="bg-white dark:bg-gray-800 dark:text-gray-100 shadow rounded-lg p-8 w-full max-w-xl space-y-6">
      <h2 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-2">Profil ausfüllen</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-1">Geburtsdatum</label>
          <input v-model="form.birthday" type="date"
                 class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition" required>
        </div>
        <div>
          <label class="block mb-1">Alter</label>
          <input v-model="form.age" type="number" readonly
                 class="border rounded px-2 py-2 w-full bg-gray-100 dark:bg-gray-700 dark:text-gray-100 transition">
        </div>
        <div>
          <label class="block mb-1">Geschlecht</label>
          <select v-model="form.sex" required
                  class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option value="m">Männlich</option>
            <option value="f">Weiblich</option>
            <option value="d">Divers</option>
          </select>
        </div>
        <div>
          <label class="block mb-1">Schulabschluss</label>
          <select v-model="form.education" required
                  class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="option in educationOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </div>
        <div>
          <label class="block mb-1">Familienstand</label>
          <select v-model="form.marital_status" required
                  class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="option in maritalStatusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
        </div>
        <div>
          <label class="block mb-1">Haushalt</label>
          <select v-model="form.household" required
                  class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="option in householdOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
          </select>
        </div>
        <div>
          <label class="block mb-1">Berufstätig?</label>
          <select v-model="form.employed" required
                  class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option :value="true">Ja</option>
            <option :value="false">Nein</option>
          </select>
        </div>
        <div>
          <label class="block mb-1">Berufsgruppe</label>
          <select v-model="form.profession_group_id"
                  class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="g in props.professionGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="block mb-1">Beruf</label>
          <input v-model="form.profession"
                 class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
        </div>
      </div>
      <div class="pt-4 flex justify-end">
        <button type="submit"
                class="bg-blue-600 text-white px-8 py-2 rounded font-semibold hover:bg-blue-700 transition">
          Speichern & Weiter
        </button>
      </div>
    </form>
  </div>
</template>
