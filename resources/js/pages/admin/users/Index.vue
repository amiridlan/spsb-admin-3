<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    users: User[];
    pagination?: Pagination;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Users', href: '/admin/users' },
];
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Users</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage application users
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/users/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add User
                    </Link>
                </Button>
            </div>

            <div class="rounded-md border">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Name
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Email
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Role
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in props.users"
                            :key="user.id"
                            class="border-b"
                        >
                            <td class="p-4 align-middle">{{ user.name }}</td>
                            <td class="p-4 align-middle">{{ user.email }}</td>
                            <td class="p-4 align-middle">
                                <Badge
                                    :variant="
                                        user.role === 'superadmin'
                                            ? 'destructive'
                                            : user.role === 'admin'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        user.role.charAt(0).toUpperCase() +
                                        user.role.slice(1)
                                    }}
                                </Badge>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="
                                            $inertia.delete(
                                                `/admin/users/${user.id}`
                                            )
                                        "
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
