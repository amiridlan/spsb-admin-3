<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Edit, Trash2, Users } from 'lucide-vue-next';

interface Department {
    id: number;
    name: string;
    code: string | null;
    description: string | null;
    head_user_id: number | null;
    head?: {
        id: number;
        name: string;
        email: string;
    } | null;
    staff_count?: number;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    departments: Department[];
    pagination?: Pagination;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Departments', href: '/admin/departments' },
];
</script>

<template>
    <Head title="Departments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Departments</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage organization departments
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/departments/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Department
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
                                Code
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Department Head
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Staff Count
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="department in props.departments"
                            :key="department.id"
                            class="border-b"
                        >
                            <td class="p-4 align-middle">
                                <div>
                                    <div class="font-medium">{{ department.name }}</div>
                                    <div v-if="department.description" class="text-sm text-muted-foreground">
                                        {{ department.description }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 align-middle">
                                <Badge variant="outline" v-if="department.code">
                                    {{ department.code }}
                                </Badge>
                                <span v-else class="text-sm text-muted-foreground">-</span>
                            </td>
                            <td class="p-4 align-middle">
                                <div v-if="department.head">
                                    <div class="font-medium text-sm">{{ department.head.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ department.head.email }}</div>
                                </div>
                                <span v-else class="text-sm text-muted-foreground">Not assigned</span>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <Users class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ department.staff_count || 0 }}</span>
                                </div>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link
                                            :href="`/admin/departments/${department.id}/edit`"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="
                                            $inertia.delete(
                                                `/admin/departments/${department.id}`
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
