<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    availableUsers: User[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
    { title: 'Create', href: '/admin/staff/create' },
];

const form = useForm({
    user_id: null as number | null,
    position: '',
    specializations: [] as string[],
    is_available: 'true', // Store as string in form
    notes: '',
});

const newSpecialization = ref('');

const addSpecialization = () => {
    if (newSpecialization.value.trim() && !form.specializations.includes(newSpecialization.value.trim())) {
        form.specializations.push(newSpecialization.value.trim());
        newSpecialization.value = '';
    }
};

const removeSpecialization = (spec: string) => {
    form.specializations = form.specializations.filter(s => s !== spec);
};

const submit = () => {
    // Convert string to boolean before submitting
    const formData = {
        ...form.data(),
        is_available: form.is_available === 'true', // Convert to boolean
    };

    form.transform(() => formData).post('/admin/staff', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Add Staff Member" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/staff')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Add Staff Member</h1>
                    <p class="text-sm text-muted-foreground">
                        Create a new staff profile for event assignments
                    </p>
                </div>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="grid gap-2">
                            <Label for="user_id">User *</Label>
                            <Select v-model="form.user_id" required>
                                <SelectTrigger id="user_id">
                                    <SelectValue placeholder="Select a user" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="user in availableUsers"
                                        :key="user.id"
                                        :value="user.id.toString()"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.user_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="position">Position</Label>
                            <Input
                                id="position"
                                v-model="form.position"
                                type="text"
                                placeholder="Event Coordinator"
                            />
                            <InputError :message="form.errors.position" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Specializations</Label>
                            <div class="flex gap-2">
                                <Input
                                    v-model="newSpecialization"
                                    type="text"
                                    placeholder="Add specialization"
                                    @keydown.enter.prevent="addSpecialization"
                                />
                                <Button type="button" @click="addSpecialization">
                                    Add
                                </Button>
                            </div>
                            <div v-if="form.specializations.length" class="flex flex-wrap gap-2 mt-2">
                                <Badge
                                    v-for="spec in form.specializations"
                                    :key="spec"
                                    variant="secondary"
                                    class="cursor-pointer"
                                    @click="removeSpecialization(spec)"
                                >
                                    {{ spec }}
                                    <X class="ml-1 h-3 w-3" />
                                </Badge>
                            </div>
                            <InputError :message="form.errors.specializations" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                placeholder="Additional notes about this staff member..."
                            />
                            <InputError :message="form.errors.notes" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="is_available">Availability Status</Label>
                            <Select v-model="form.is_available">
                                <SelectTrigger id="is_available">
                                    <SelectValue placeholder="Select availability" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="true">Available</SelectItem>
                                    <SelectItem value="false">Unavailable</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.is_available" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Create Staff Member
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/admin/staff')"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
