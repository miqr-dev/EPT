<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';


const form = useForm({
  username: '',
  password: '',
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <AuthBase title="Log in to your account" description="Enter your username and password below to log in">

    <Head title="Log in" />

    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
      <div class="grid gap-6">
        <div class="grid gap-2">
          <Label for="username">Username</Label>
          <Input id="username" type="text" required autofocus :tabindex="1" autocomplete="username"
            v-model="form.username" placeholder="Username" />
          <InputError :message="form.errors.username" />
        </div>

        <div class="grid gap-2">
          <div class="flex items-center justify-between">
            <Label for="password">Password</Label>
          </div>
          <Input id="password" type="password" required :tabindex="2" autocomplete="current-password"
            v-model="form.password" placeholder="Password" />
          <InputError :message="form.errors.password" />
        </div>

        <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
          Log in
        </Button>
      </div>
    </form>
  </AuthBase>
</template>
