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
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, User, Mail, Calendar } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface EventSpace {
    id: number;
    name: string;
}

interface Assignment {
    id: number;
    title: string;
    start_date: string;
    end_date: string;
    start_time: string | null;
    end_time: string | null;
    status: 'pending' | 'confirmed' | 'completed' | 'cancelled';
    event_space: EventSpace;
    pivot: {
        role: string | null;
        notes: string | null;
    };
}

interface Staff {
    id: number;
    user: User;
    position: string | null;
    specializations: string[] | null;
    is_available: boolean;
    notes: string | null;
    created_at: string;
}

interface Props {
    staff: Staff;
    upcomingAssignments: Assignment[];
    pastAssignments: {
        data: Assignment[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        confirmed: 'default',
        completed: 'secondary',
        cancelled: 'destructive',
    };
    return variants[status] || 'outline';
};

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
    { title: props.staff.user.name, href: `/admin/staff/${props.staff.id}` },
]);
</script>

<template>
    <Head :title="staff.user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="$inertia.visit('/admin/staff')">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-2xl font-semibold">{{ staff.user.name }}</h1>
                        <p class="text-sm text-muted-foreground">
                            Staff profile and assignments
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/admin/staff/${staff.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Profile
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4 rounded-lg border p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium">Profile Information</h3>
                        <Badge :variant="staff.is_available ? 'default' : 'secondary'">
                            {{ staff.is_available ? 'Available' : 'Unavailable' }}
                        </Badge>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Name</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ staff.user.name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Mail class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Email</p>

                                    <a :href="`mailto:${staff.user.email}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ staff.user.email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="staff.position">
                            <p class="text-sm font-medium">Position</p>
                            <p class="text-sm text-muted-foreground">
                                {{ staff.position }}
                            </p>
                        </div>

                        <div v-if="staff.specializations && staff.specializations.length">
                            <p class="text-sm font-medium mb-2">Specializations</p>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="spec in staff.specializations"
                                    :key="spec"
                                    variant="secondary"
                                >
                                    {{ spec }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="staff.notes" class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">Notes</h3>
                    <p class="text-sm text-muted-foreground whitespace-pre-wrap">
                        {{ staff.notes }}
                    </p>
                </div>
            </div>

            <!-- Upcoming Assignments -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Upcoming Assignments</h3>
                <div v-if="upcomingAssignments.length" class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Event</TableHead>
                                <TableHead>Space</TableHead>
                                <TableHead>Dates</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="assignment in upcomingAssignments" :key="assignment.id">
                                <TableCell class="font-medium">
                                    <Link
                                        :href="`/admin/events/${assignment.id}`"
                                        class="hover:underline"
                                    >
                                        {{ assignment.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ assignment.event_space.name }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1 text-sm">
                                        <Calendar class="h-3 w-3" />
                                        {{ new Date(assignment.start_date).toLocaleDateString() }}
                                        -
                                        {{ new Date(assignment.end_date).toLocaleDateString() }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="assignment.pivot.role" variant="outline">
                                        {{ assignment.pivot.role }}
                                    </Badge>
                                    <span v-else class="text-sm text-muted-foreground">N/A</span>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(assignment.status)">
                                        {{ assignment.status }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div v-else class="rounded-lg border p-8 text-center">
                    <p class="text-sm text-muted-foreground">No upcoming assignments</p>
                </div>
            </div>

            <!-- Past Assignments -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Past Assignments</h3>
                <div v-if="pastAssignments.data.length" class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Event</TableHead>
                                <TableHead>Space</TableHead>
                                <TableHead>Dates</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="assignment in pastAssignments.data" :key="assignment.id">
                                <TableCell class="font-medium">
                                    <Link
                                        :href="`/admin/events/${assignment.id}`"
                                        class="hover:underline"
                                    >
                                        {{ assignment.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ assignment.event_space.name }}</TableCell>
                                <TableCell>
                                    {{ new Date(assignment.start_date).toLocaleDateString() }}
                                    -
                                    {{ new Date(assignment.end_date).toLocaleDateString() }}
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="assignment.pivot.role" variant="outline">
                                        {{ assignment.pivot.role }}
                                    </Badge>
                                    <span v-else class="text-sm text-muted-foreground">N/A</span>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(assignment.status)">
                                        {{ assignment.status }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div v-else class="rounded-lg border p-8 text-center">
                    <p class="text-sm text-muted-foreground">No past assignments</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
