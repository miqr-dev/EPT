<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps<{
  page: number;
}>();

const imageUrl = ref('');
const errorMessage = ref('');

const fetchPdfPage = async () => {
  try {
    const response = await axios.get('/pdf/get-page', { params: { page: props.page } });
    imageUrl.value = response.data.image_url;
  } catch (error: any) {
    if (error.response && error.response.data && error.response.data.error) {
      errorMessage.value = error.response.data.error;
    } else {
      errorMessage.value = 'An unexpected error occurred.';
    }
  }
};

onMounted(fetchPdfPage);
</script>

<template>
  <div class="fixed inset-0 bg-white z-50 flex items-center justify-center">
    <div v-if="errorMessage" class="text-red-500 text-2xl">
      {{ errorMessage }}
    </div>
    <img v-else-if="imageUrl" :src="imageUrl" alt="PDF Page" class="max-h-full max-w-full" />
    <div v-else class="text-2xl">Loading...</div>
  </div>
</template>
