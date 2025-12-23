<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreVertical, Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next';

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
    events_count: number;
}

interface Props {
    staff: {
        data: Staff[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

defineProps<Props>();

const deleteStaff = (staffId: number) => {
    if (confirm('Are you sure you want to remove this staff member?')) {
        router.delete(`/admin/staff/${staffId}`);
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
];
</script>

<template>
    <Head title="Staff Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Staff Management</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage staff members and their assignments
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/staff/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Staff
                    </Link>
                </Button>
            </div>

            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Position</TableHead>
                            <TableHead>Specializations</TableHead>
                            <TableHead>Assignments</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[70px]"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="member in staff.data" :key="member.id">
                            <TableCell class="font-medium">
                                {{ member.user.name }}
                            </TableCell>
                            <TableCell>{{ member.user.email }}</TableCell>
                            <TableCell>
                                {{ member.position || 'N/A' }}
                            </TableCell>
                            <TableCell>
                                <div v-if="member.specializations && member.specializations.length" class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="spec in member.specializations.slice(0, 2)"
                                        :key="spec"
                                        variant="secondary"
                                        class="text-xs"
                                    >
                                        {{ spec }}
                                    </Badge>
                                    <Badge
                                        v-if="member.specializations.length > 2"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        +{{ member.specializations.length - 2 }}
                                    </Badge>
                                </div>
                                <span v-else class="text-sm text-muted-foreground">N/A</span>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    {{ member.events_count }} events
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="member.is_available ? 'default' : 'secondary'">
                                    {{ member.is_available ? 'Available' : 'Unavailable' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon">
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/admin/staff/${member.id}`"
                                                class="flex cursor-pointer items-center"
                                            >
                                                <Eye class="mr-2 h-4 w-4" />
                                                View
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/admin/staff/${member.id}/edit`"
                                                class="flex cursor-pointer items-center"
                                            >
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="deleteStaff(member.id)"
                                            class="cursor-pointer text-destructive"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
