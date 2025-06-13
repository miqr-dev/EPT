<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'; // Assuming a main layout
import Heading from '@/components/Heading.vue';   // Assuming a Heading component
import { Button } from '@/components/ui/button'; // Assuming a Button component
import { ArrowRight } from 'lucide-vue-next';   // Example icon

// Define any props if needed, e.g., if settings data is passed
// const props = defineProps<{
//   // settings: YourSettingsDataType
// }>();
</script>

<template>
  <Head title="Settings" />
  <AppLayout>
    <div class="container mx-auto px-4 py-8">
      <Heading level="1" class="mb-6">Settings</Heading>

      <div class="space-y-4">
        <div class="bg-white shadow-sm rounded-lg p-6">
          <Heading level="2" class="mb-2">Question Management</Heading>
          <p class="text-gray-600 mb-4">
            Create, edit, delete, and manage questions for your tests.
          </p>
          <Link :href="route('settings.questions')">
            <Button variant="outline">
              Go to Questions
              <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
          </Link>
        </div>

        <!-- Add more settings sections here as needed -->
        <!-- For example:
        <div class="bg-white shadow-sm rounded-lg p-6">
          <Heading level="2" class="mb-2">User Preferences</Heading>
          <p class="text-gray-600 mb-4">
            Manage your account and notification settings.
          </p>
          <Link :href="route('settings.profile')"> // Assuming a profile settings route
            <Button variant="outline">
              Manage Profile
              <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
          </Link>
        </div>
        -->
      </div>
    </div>
  </AppLayout>
</template>
