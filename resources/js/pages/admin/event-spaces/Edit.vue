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

interface EventSpace {
    id: number;
    name: string;
    location: string; // Add location
    description: string | null;
    capacity: number | null;
    is_active: boolean;
}

interface Props {
    space: EventSpace;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Event Spaces', href: '/admin/event-spaces' },
    { title: 'Edit', href: `/admin/event-spaces/${props.space.id}/edit` },
];

const form = useForm({
    name: props.space.name,
    location: props.space.location, // Add location
    description: props.space.description || '',
    capacity: props.space.capacity,
    is_active: props.space.is_active,
});

const submit = () => {
    form.put(`/admin/event-spaces/${props.space.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${space.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/event-spaces')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Edit Event Space</h1>
                    <p class="text-sm text-muted-foreground">
                        Update space information
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
                            Update Space
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
