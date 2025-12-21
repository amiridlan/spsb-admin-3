<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
    roles: string[];
}

defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/admin/users' },
    { title: 'Create', href: '/admin/users/create' },
];
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/users')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Create User</h1>
                    <p class="text-sm text-muted-foreground">
                        Add a new user to the system
                    </p>
                </div>
            </div>

            <div class="max-w-2xl">
                <Form
                    action="/admin/users"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-4 rounded-lg border p-6">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                name="name"
                                type="text"
                                required
                                autofocus
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                required
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="role">Role</Label>
                            <Select name="role" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="role in roles"
                                        :key="role"
                                        :value="role"
                                    >
                                        {{
                                            role.charAt(0).toUpperCase() +
                                            role.slice(1)
                                        }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.role" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                required
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation">
                                Confirm Password
                            </Label>
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                            />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="processing">
                            Create User
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/admin/users')"
                        >
                            Cancel
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
