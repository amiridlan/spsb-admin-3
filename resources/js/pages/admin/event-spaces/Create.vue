<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';

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
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.image = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    // Reset file input
    const input = document.getElementById('image') as HTMLInputElement;
    if (input) input.value = '';
};

const submit = () => {
    form.post('/admin/event-spaces', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
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
                    <!-- Basic Information -->
                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Basic Information</h3>

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
                                placeholder="Describe the space, amenities, and features..."
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
                            <p class="text-xs text-muted-foreground">
                                Maximum number of people this space can accommodate
                            </p>
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

                    <!-- Image Upload -->
                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Event Space Image</h3>

                        <!-- Image Preview -->
                        <div v-if="imagePreview" class="relative">
                            <div class="relative h-48 w-full overflow-hidden rounded-lg border">
                                <img
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                class="mt-2"
                                @click="removeImage"
                            >
                                <X class="mr-2 h-4 w-4" />
                                Remove Image
                            </Button>
                        </div>

                        <!-- Upload Input -->
                        <div v-else class="grid gap-2">
                            <Label for="image">Upload Image</Label>
                            <div class="flex items-center gap-2">
                                <Input
                                    id="image"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    @change="handleImageUpload"
                                    :aria-invalid="!!form.errors.image"
                                />
                                <Upload class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Recommended: 1200x600px (2:1 ratio), Max 2MB. Formats: JPEG, PNG, WebP
                            </p>
                            <InputError :message="form.errors.image" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Create Event Space
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
