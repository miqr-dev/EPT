<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    isOpen: boolean;
    testResult: any;
}>();

const emit = defineEmits(['close', 'update']);

const form = useForm({
    result_json: '',
});

watch(() => props.testResult, (newVal) => {
    if (newVal) {
        form.result_json = JSON.stringify(newVal.result_json, null, 2);
    } else {
        form.reset();
    }
});

function submit() {
    if (props.testResult) {
        form.put(route('test-results.update', { testResult: props.testResult.id }), {
            onSuccess: () => {
                closeModal();
            },
        });
    }
}

function closeModal() {
    emit('close');
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="closeModal">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Edit Test Result</DialogTitle>
            </DialogHeader>
            <div v-if="testResult" class="grid gap-4 py-4">
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label for="result_json" class="text-right">
                        Result JSON
                    </Label>
                    <Textarea id="result_json" v-model="form.result_json" class="col-span-3 h-64" />
                </div>
            </div>
            <DialogFooter>
                <Button type="submit" @click="submit">Save changes</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
