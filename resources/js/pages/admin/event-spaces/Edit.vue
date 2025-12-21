<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

interface EventSpace {
    id: number;
    name: string;
    description: string | null;
    capacity: number | null;
    is_active: boolean;
}

interface Props {
    space: EventSpace;
}

const props = defineProps<Props>();

const isActive = ref(props.space.is_active);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Event Spaces', href: '/admin/event-spaces' },
    { title: 'Edit', href: `/admin/event-spaces/${props.space.id}/edit` },
];
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
                <Form
                    :action="`/admin/event-spaces/${space.id}`"
                    method="put"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="grid gap-2">
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                name="name"
                                type="text"
                                :default-value="space.name"
                                required
                                autofocus
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                name="description"
                                rows="4"
                                :default-value="space.description || ''"
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="capacity">Capacity</Label>
                            <Input
                                id="capacity"
                                name="capacity"
                                type="number"
                                min="1"
                                :default-value="space.capacity"
                            />
                            <InputError :message="errors.capacity" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                name="is_active"
                                v-model:checked="isActive"
                            />
                            <Label for="is_active" class="cursor-pointer">
                                Active (available for booking)
                            </Label>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="processing">
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
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
