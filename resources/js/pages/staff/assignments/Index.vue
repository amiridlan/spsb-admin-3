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
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar as CalendarIcon, Eye } from 'lucide-vue-next';

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
    client_name: string;
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
    is_available: boolean;
}

interface Props {
    staff: Staff;
    assignments: {
        data: Assignment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filter: 'upcoming' | 'current' | 'past' | 'all';
    counts: {
        current: number;
        upcoming: number;
        past: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Assignments', href: '/staff/assignments' },
];

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        confirmed: 'default',
        completed: 'secondary',
        cancelled: 'destructive',
    };
    return variants[status] || 'outline';
};

const changeFilter = (filter: string) => {
    router.get('/staff/assignments', { filter }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="My Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">My Assignments</h1>
                    <p class="text-sm text-muted-foreground">
                        View and manage your event assignments
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link href="/staff/assignments/calendar">
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        Calendar View
                    </Link>
                </Button>
            </div>

            <!-- Staff Info Card -->
            <div class="rounded-lg border p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ staff.user.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ staff.position || 'Staff Member' }}
                        </p>
                    </div>
                    <Badge :variant="staff.is_available ? 'default' : 'secondary'">
                        {{ staff.is_available ? 'Available' : 'Unavailable' }}
                    </Badge>
                </div>
            </div>

            <!-- Tabs for filtering -->
            <Tabs :default-value="filter" @update:model-value="changeFilter">
                <TabsList class="grid w-full grid-cols-4">
                    <TabsTrigger value="current">
                        Current ({{ counts.current }})
                    </TabsTrigger>
                    <TabsTrigger value="upcoming">
                        Upcoming ({{ counts.upcoming }})
                    </TabsTrigger>
                    <TabsTrigger value="past">
                        Past ({{ counts.past }})
                    </TabsTrigger>
                    <TabsTrigger value="all">
                        All
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="current" class="mt-6">
                    <div v-if="assignments.data.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Event</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Space</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>My Role</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="assignment in assignments.data" :key="assignment.id">
                                    <TableCell class="font-medium">
                                        {{ assignment.title }}
                                    </TableCell>
                                    <TableCell>{{ assignment.client_name }}</TableCell>
                                    <TableCell>{{ assignment.event_space.name }}</TableCell>
                                    <TableCell>
                                        <div class="text-sm">
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
                                    <TableCell>
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="`/staff/assignments/${assignment.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="rounded-lg border p-8 text-center">
                        <p class="text-sm text-muted-foreground">
                            No current assignments found
                        </p>
                    </div>
                </TabsContent>

                <TabsContent value="upcoming" class="mt-6">
                    <div v-if="assignments.data.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Event</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Space</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>My Role</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="assignment in assignments.data" :key="assignment.id">
                                    <TableCell class="font-medium">
                                        {{ assignment.title }}
                                    </TableCell>
                                    <TableCell>{{ assignment.client_name }}</TableCell>
                                    <TableCell>{{ assignment.event_space.name }}</TableCell>
                                    <TableCell>
                                        <div class="text-sm">
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
                                    <TableCell>
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="`/staff/assignments/${assignment.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="rounded-lg border p-8 text-center">
                        <p class="text-sm text-muted-foreground">
                            No upcoming assignments found
                        </p>
                    </div>
                </TabsContent>

                <TabsContent value="past" class="mt-6">
                    <div v-if="assignments.data.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Event</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Space</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>My Role</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="assignment in assignments.data" :key="assignment.id">
                                    <TableCell class="font-medium">
                                        {{ assignment.title }}
                                    </TableCell>
                                    <TableCell>{{ assignment.client_name }}</TableCell>
                                    <TableCell>{{ assignment.event_space.name }}</TableCell>
                                    <TableCell>
                                        <div class="text-sm">
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
                                    <TableCell>
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="`/staff/assignments/${assignment.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="rounded-lg border p-8 text-center">
                        <p class="text-sm text-muted-foreground">
                            No past assignments found
                        </p>
                    </div>
                </TabsContent>

                <TabsContent value="all" class="mt-6">
                    <div v-if="assignments.data.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Event</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Space</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>My Role</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="assignment in assignments.data" :key="assignment.id">
                                    <TableCell class="font-medium">
                                        {{ assignment.title }}
                                    </TableCell>
                                    <TableCell>{{ assignment.client_name }}</TableCell>
                                    <TableCell>{{ assignment.event_space.name }}</TableCell>
                                    <TableCell>
                                        <div class="text-sm">
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
                                    <TableCell>
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="`/staff/assignments/${assignment.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="rounded-lg border p-8 text-center">
                        <p class="text-sm text-muted-foreground">
                            No assignments found
                        </p>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
