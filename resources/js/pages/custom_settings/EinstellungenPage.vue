<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'; // Assuming a main layout
import Heading from '@/components/Heading.vue';   // Assuming a Heading component
import { Button } from '@/components/ui/button'; // Assuming a Button component
import { ArrowRight } from 'lucide-vue-next';   // Example icon for the button

</script>

<template>
  <Head title="Einstellungen" />
  <AppLayout>
    <div class="container mx-auto px-4 py-8">
      <Heading level="1" class="mb-6">Einstellungen</Heading>

      <div class="bg-white shadow-sm rounded-lg p-6">
        <Heading level="2" class="mb-2">Fragen Management</Heading>
        <p class="text-gray-600 mb-4">
          Zugriff auf das Modul zur Verwaltung von Testfragen.
        </p>
        <Link :href="route('einstellungen.fragen')"> <!-- Assuming route name will be 'einstellungen.fragen' -->
          <Button variant="outline">
            Fragen
            <ArrowRight class="ml-2 h-4 w-4" />
          </Button>
        </Link>
      </div>

      <!-- Future sections for other 'Einstellungen' can be added here -->

    </div>
  </AppLayout>
</template>
