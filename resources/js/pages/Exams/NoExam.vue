<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue'
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  next_exam: {
    id: number
    start_time: string
  } | null
}>()

onMounted(() => {
  if (props.next_exam) {
    const examStartTime = new Date(props.next_exam.start_time).getTime()
    const now = new Date().getTime()
    const delay = examStartTime - now

    if (delay > 0) {
      setTimeout(() => {
        router.visit(route('my-exam'))
      }, delay)
    } else {
      router.visit(route('my-exam'))
    }
  }
})
</script>

<template>
  <AuthSplitLayout>
    <div class="grid gap-2 text-center">
      <AppLogo class="mb-4" />
      <h1 class="text-3xl font-bold">
        Willkommen
      </h1>
      <p class="text-balance text-muted-foreground">
        Es ist derzeit keine Prüfung für Sie freigeschaltet.
      </p>

      <Card
        v-if="props.next_exam"
        class="mt-8"
      >
        <CardHeader>
          <CardTitle> Nächste Prüfung </CardTitle>
        </CardHeader>
        <CardContent>
          <p>Die nächste Prüfung beginnt am:</p>
          <p class="text-2xl font-bold">
            {{ new Date(props.next_exam.start_time).toLocaleString('de-DE') }}
          </p>
        </CardContent>
      </Card>
    </div>
  </AuthSplitLayout>
</template>
