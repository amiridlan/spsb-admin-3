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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar as CalendarIcon, Eye, Info } from 'lucide-vue-next';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import type { CalendarOptions, EventClickArg } from '@fullcalendar/core';
import { ref, computed } from 'vue';

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

interface CalendarEvent {
    id: string;
    title: string;
    start: string;
    end: string;
    backgroundColor: string;
    borderColor: string;
    textColor: string;
    extendedProps: {
        status: string;
        space: string;
        space_id: number;
        client: string;
        isAssigned: boolean;
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
    calendarEvents: CalendarEvent[];
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

const calendarRef = ref();

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

// Calendar Options - Read-only for staff
const calendarOptions = computed<CalendarOptions>(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,listWeek'
    },
    events: props.calendarEvents,
    editable: false, // Staff can't edit
    droppable: false, // Staff can't drag
    selectable: false, // Staff can't create
    eventClick: handleEventClick,
    height: 'auto',
    displayEventTime: false,
    allDaySlot: true,
    nowIndicator: true,
    eventDisplay: 'block',
    dayMaxEvents: true,
}));

// Handle event click
function handleEventClick(info: EventClickArg) {
    const eventId = info.event.id;
    const isAssigned = info.event.extendedProps.isAssigned;

    if (isAssigned) {
        // Go to assignment details
        router.visit(`/staff/assignments/${eventId}`);
    }
}

// Count events by type
const eventStats = computed(() => {
    const assigned = props.calendarEvents.filter(e => e.extendedProps.isAssigned).length;
    const total = props.calendarEvents.length;
    return { assigned, total, other: total - assigned };
});
</script>

<template>
    <Head title="My Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">My Assignments</h1>
                    <p class="text-sm text-muted-foreground">
                        View your event assignments and schedule
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Current Events</div>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.current }}</div>
                        <p class="text-xs text-muted-foreground">Happening now</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Upcoming Events</div>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.upcoming }}</div>
                        <p class="text-xs text-muted-foreground">Scheduled ahead</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Past Events</div>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.past }}</div>
                        <p class="text-xs text-muted-foreground">Completed</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Calendar Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Event Calendar</h2>
                            <p class="text-sm text-muted-foreground">
                                View all events. Your assignments are highlighted in green.
                            </p>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            {{ eventStats.assigned }} assigned / {{ eventStats.total }} total
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Legend -->
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
                        <div class="flex items-start gap-3">
                            <Info class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-900 dark:text-blue-100" />
                            <div class="text-sm text-blue-900 dark:text-blue-100">
                                <p class="font-medium">Calendar Guide:</p>
                                <ul class="mt-2 space-y-1">
                                    <li class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                        <span>Your Assignments - Click to view details</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                        <span>Other Events - View only (not assigned to you)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar -->
                    <div class="rounded-lg border bg-card p-4">
                        <FullCalendar ref="calendarRef" :options="calendarOptions" />
                    </div>
                </CardContent>
            </Card>

            <!-- Assignments Table -->
            <Card>
                <CardHeader>
                    <h2 class="text-lg font-semibold">My Assignments</h2>
                </CardHeader>
                <CardContent>
                    <Tabs :default-value="filter" class="w-full">
                        <TabsList>
                            <TabsTrigger value="current" @click="changeFilter('current')">
                                Current ({{ counts.current }})
                            </TabsTrigger>
                            <TabsTrigger value="upcoming" @click="changeFilter('upcoming')">
                                Upcoming ({{ counts.upcoming }})
                            </TabsTrigger>
                            <TabsTrigger value="past" @click="changeFilter('past')">
                                Past ({{ counts.past }})
                            </TabsTrigger>
                            <TabsTrigger value="all" @click="changeFilter('all')">
                                All
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent :value="filter" class="mt-6">
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
                                                    <template v-if="assignment.start_date === assignment.end_date">
                                                        {{ new Date(assignment.start_date).toLocaleDateString() }}
                                                    </template>
                                                    <template v-else>
                                                        {{ new Date(assignment.start_date).toLocaleDateString() }}
                                                        -
                                                        {{ new Date(assignment.end_date).toLocaleDateString() }}
                                                    </template>
                                                </div>
                                                <div v-if="assignment.start_time || assignment.end_time" class="text-xs text-muted-foreground">
                                                    <span v-if="assignment.start_time">{{ assignment.start_time }}</span>
                                                    <span v-if="assignment.start_time && assignment.end_time"> - </span>
                                                    <span v-if="assignment.end_time">{{ assignment.end_time }}</span>
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
                                                <Button variant="ghost" size="sm" as-child>
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

                    <!-- Pagination -->
                    <div
                        v-if="assignments.last_page > 1"
                        class="mt-4 flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ assignments.data.length }} of {{ assignments.total }} assignments
                        </p>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="assignments.current_page === 1"
                                @click="router.visit(`/staff/assignments?page=${assignments.current_page - 1}&filter=${filter}`)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="assignments.current_page === assignments.last_page"
                                @click="router.visit(`/staff/assignments?page=${assignments.current_page + 1}&filter=${filter}`)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
/* FullCalendar custom styles */
.fc {
    font-family: inherit;
}

.fc .fc-button {
    background-color: hsl(var(--primary));
    border-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
}

.fc .fc-button:hover {
    background-color: hsl(var(--primary) / 0.9);
}

.fc .fc-button-active {
    background-color: hsl(var(--primary) / 0.9);
}

.fc-theme-standard td,
.fc-theme-standard th {
    border-color: hsl(var(--border));
}

.fc-theme-standard .fc-scrollgrid {
    border-color: hsl(var(--border));
}
</style>
