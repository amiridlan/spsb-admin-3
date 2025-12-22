<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Event Spaces', href: '/admin/event-spaces' },
    { title: 'Create', href: '/admin/event-spaces/create' },
];

const form = useForm({
    name: '',
    location: '',
    description: '',
    capacity: null as number | null,
    is_active: true,
});

const submit = () => {
    form.post('/admin/event-spaces', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Create Event Space" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/event-spaces')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Create Event Space</h1>
                    <p class="text-sm text-muted-foreground">
                        Add a new bookable space
                    </p>
                </div>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="grid gap-2">
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                placeholder="Main Hall"
                                :aria-invalid="!!form.errors.name"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="location">Location *</Label>
                            <Input
                                id="location"
                                v-model="form.location"
                                type="text"
                                required
                                placeholder="Building A, Floor 2"
                                :aria-invalid="!!form.errors.location"
                            />
                            <InputError :message="form.errors.location" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Describe the space..."
                                :aria-invalid="!!form.errors.description"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="capacity">Capacity</Label>
                            <Input
                                id="capacity"
                                v-model.number="form.capacity"
                                type="number"
                                min="1"
                                placeholder="100"
                                :aria-invalid="!!form.errors.capacity"
                            />
                            <InputError :message="form.errors.capacity" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                v-model:checked="form.is_active"
                            />
                            <Label for="is_active" class="cursor-pointer">
                                Active (available for booking)
                            </Label>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Create Space
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/admin/event-spaces')"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
