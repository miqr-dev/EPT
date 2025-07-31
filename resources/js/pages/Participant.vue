<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  user: any
  profile?: any
  professionGroups: any[]
  employeds: any[]
}>()

const today = new Date()
const form = useForm({
  birthday: props.profile?.birthday ?? '',
  age: props.profile?.age ?? '',
  sex: props.profile?.sex ?? '',
  education: props.profile?.education ?? '',
  marital_status: props.profile?.marital_status ?? '',
  household: props.profile?.household ?? '',
  employed_id: props.profile?.employed_id ?? '',
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
      <h2 class="text-xl font-bold text-gray-700 dark:text-gray-100 mb-2">
        Willkommen, {{ props.user?.name || 'Teilnehmer:in' }}!
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-2">
        Bitte fülle dein Profil aus, um fortzufahren.
      </p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block mb-1">Geburtsdatum</label>
          <input v-model="form.birthday" type="date"
            class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition" required>
          <p v-if="form.errors.birthday" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.birthday }}</p>
        </div>
        <div>
          <label class="block mb-1">Alter</label>
          <input v-model="form.age" type="number" readonly
            class="border rounded px-2 py-2 w-full bg-gray-100 dark:bg-gray-700 dark:text-gray-100 transition">
          <p v-if="form.errors.age" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.age }}</p>
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
          <p v-if="form.errors.sex" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.sex }}</p>
        </div>
        <div>
          <label class="block mb-1">Schulabschluss</label>
          <select v-model="form.education" required
            class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="option in educationOptions" :key="option" :value="option">{{ option }}</option>
          </select>
          <p v-if="form.errors.education" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.education }}</p>
        </div>
        <div>
          <label class="block mb-1">Familienstand</label>
          <select v-model="form.marital_status" required
            class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="option in maritalStatusOptions" :key="option.value" :value="option.value">{{ option.label }}
            </option>
          </select>
          <p v-if="form.errors.marital_status" class="text-sm text-red-600 dark:text-red-400">{{
            form.errors.marital_status }}</p>
        </div>
        <div>
          <label class="block mb-1">Haushalt</label>
          <select v-model="form.household" required
            class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="option in householdOptions" :key="option.value" :value="option.value">{{ option.label }}
            </option>
          </select>
          <p v-if="form.errors.household" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.household }}</p>
        </div>
        <div>
          <label class="block mb-1">Berufstätig?</label>
          <select v-model="form.employed_id"
            class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="e in props.employeds" :key="e.id" :value="e.id">{{ e.name }}</option>
          </select>
          <p v-if="form.errors.employed_id" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.employed_id
            }}</p>
        </div>
        <div>
          <label class="block mb-1">Berufsgruppe</label>
          <select v-model="form.profession_group_id"
            class="border rounded px-2 py-2 w-full bg-white dark:bg-gray-700 dark:text-gray-100 transition">
            <option value="" disabled>Bitte wählen</option>
            <option v-for="g in props.professionGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
          </select>
          <p v-if="form.errors.profession_group_id" class="text-sm text-red-600 dark:text-red-400">{{
            form.errors.profession_group_id }}</p>
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
