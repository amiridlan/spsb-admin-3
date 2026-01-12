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
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Props {
    potentialHeads: User[];
}

defineProps<Props>();

const selectedHead = ref<string | null>(null);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Departments', href: '/admin/departments' },
    { title: 'Create', href: '/admin/departments/create' },
];
</script>

<template>
    <Head title="Create Department" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/departments')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Create Department</h1>
                    <p class="text-sm text-muted-foreground">
                        Add a new department to the organization
                    </p>
                </div>
            </div>

            <div class="max-w-2xl">
                <Form
                    action="/admin/departments"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="grid gap-2">
                            <Label for="name">Department Name</Label>
                            <Input
                                id="name"
                                name="name"
                                type="text"
                                required
                                autofocus
                                placeholder="e.g., Information Technology"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="code">Department Code</Label>
                            <Input
                                id="code"
                                name="code"
                                type="text"
                                placeholder="e.g., IT (optional)"
                                maxlength="10"
                            />
                            <p class="text-xs text-muted-foreground">
                                Short code for the department (max 10 characters)
                            </p>
                            <InputError :message="errors.code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                name="description"
                                placeholder="Brief description of the department (optional)"
                                rows="3"
                            />
                            <InputError :message="errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="head_user_id">Department Head</Label>
                            <Select v-model="selectedHead">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select department head (optional)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="user in potentialHeads"
                                        :key="user.id"
                                        :value="user.id.toString()"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <input type="hidden" name="head_user_id" :value="selectedHead || ''" />
                            <p class="text-xs text-muted-foreground">
                                User who will be the head of this department
                            </p>
                            <InputError :message="errors.head_user_id" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="processing">
                            Create Department
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/admin/departments')"
                        >
                            Cancel
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
