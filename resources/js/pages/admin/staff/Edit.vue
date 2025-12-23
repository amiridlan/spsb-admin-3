<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Staff {
    id: number;
    user: User;
    position: string | null;
    specializations: string[] | null;
    is_available: boolean;
    notes: string | null;
}

interface Props {
    staff: Staff;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
    { title: 'Edit', href: `/admin/staff/${props.staff.id}/edit` },
];

const form = useForm({
    position: props.staff.position || '',
    specializations: props.staff.specializations || [],
    is_available: props.staff.is_available,
    notes: props.staff.notes || '',
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
    form.put(`/admin/staff/${props.staff.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${staff.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/staff')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Edit Staff Member</h1>
                    <p class="text-sm text-muted-foreground">
                        Update staff profile for {{ staff.user.name }}
                    </p>
                </div>
            </div>

            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="grid gap-2">
                            <Label>User</Label>
                            <div class="rounded-md border bg-muted/50 p-3">
                                <p class="font-medium">{{ staff.user.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ staff.user.email }}</p>
                            </div>
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

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_available"
                                v-model:checked="form.is_available"
                            />
                            <Label for="is_available" class="cursor-pointer">
                                Available for assignments
                            </Label>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Update Staff Member
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
