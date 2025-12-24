<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Calendar,
    Users,
    Building2,
    TrendingUp,
    Clock,
    AlertCircle,
    CheckCircle,
    XCircle,
    CalendarDays,
} from 'lucide-vue-next';

interface Props {
    role: string;
    stats?: any;
    upcomingEvents?: any[];
    recentBookings?: any[];
    eventsByStatus?: any;
    eventsByMonth?: any[];
    spaceUtilization?: any[];
    pendingActions?: any;
    staff?: any;
    currentAssignments?: any[];
    upcomingAssignments?: any[];
    weekSchedule?: any[];
    todayEvents?: any[];
    noProfile?: boolean;
}

const props = defineProps<Props>();
const page = usePage();

const isAdmin = computed(() => ['superadmin', 'admin'].includes(props.role));
const isStaff = computed(() => props.role === 'staff');

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        confirmed: 'default',
        completed: 'secondary',
        cancelled: 'destructive',
    };
    return variants[status] || 'outline';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <!-- Admin Dashboard -->
        <div v-if="isAdmin" class="flex flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Dashboard</h1>
                <p class="text-sm text-muted-foreground">
                    Overview of your event booking system
                </p>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Events</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_events }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.month_bookings }} this month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Event Spaces</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_spaces }}</div>
                        <p class="text-xs text-muted-foreground">Active spaces</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Staff Members</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_staff }}</div>
                        <p class="text-xs text-muted-foreground">Available for assignments</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Actions</CardTitle>
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ pendingActions.pending_approvals }}</div>
                        <p class="text-xs text-muted-foreground">Require approval</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Status Breakdown -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <Clock class="h-4 w-4 text-amber-500" />
                            Pending
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending_bookings }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-green-500" />
                            Confirmed
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.confirmed_bookings }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <CheckCircle class="h-4 w-4 text-gray-500" />
                            Completed
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completed_bookings }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <XCircle class="h-4 w-4 text-red-500" />
                            Cancelled
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.cancelled_bookings }}</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Upcoming Events -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Upcoming Events</CardTitle>
                                <CardDescription>Next 30 days</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/admin/events">View All</Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="upcomingEvents && upcomingEvents.length" class="space-y-4">
                            <div
                                v-for="event in upcomingEvents.slice(0, 5)"
                                :key="event.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">{{ event.title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ event.event_space.name }} • {{ formatDate(event.start_date) }}
                                    </p>
                                </div>
                                <Badge :variant="getStatusVariant(event.status)">
                                    {{ event.status }}
                                </Badge>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No upcoming events</p>
                    </CardContent>
                </Card>

                <!-- Recent Bookings -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Recent Bookings</CardTitle>
                                <CardDescription>Latest activity</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/admin/events">View All</Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentBookings && recentBookings.length" class="space-y-4">
                            <div
                                v-for="booking in recentBookings.slice(0, 5)"
                                :key="booking.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">{{ booking.title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ booking.client_name }} • {{ formatDate(booking.start_date) }}
                                    </p>
                                </div>
                                <Badge :variant="getStatusVariant(booking.status)">
                                    {{ booking.status }}
                                </Badge>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No recent bookings</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <Button as-child>
                            <Link href="/admin/events/create">
                                <Calendar class="mr-2 h-4 w-4" />
                                New Event
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/calendar">
                                <CalendarDays class="mr-2 h-4 w-4" />
                                View Calendar
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/admin/events?status=pending">
                                <AlertCircle class="mr-2 h-4 w-4" />
                                Pending Approvals ({{ pendingActions.pending_approvals }})
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Staff Dashboard -->
        <div v-else-if="isStaff && !noProfile" class="flex flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">My Dashboard</h1>
                <p class="text-sm text-muted-foreground">
                    Welcome back, {{ staff.user.name }}
                </p>
            </div>

            <!-- Staff Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Assignments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_assignments }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Current</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.current_assignments }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Upcoming</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.upcoming_assignments }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Completed</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.completed_assignments }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Today's Events -->
            <Card v-if="todayEvents && todayEvents.length">
                <CardHeader>
                    <CardTitle>Today's Events</CardTitle>
                    <CardDescription>Events happening today</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div
                            v-for="event in todayEvents"
                            :key="event.id"
                            class="flex items-center justify-between rounded-lg border p-4"
                        >
                            <div>
                                <p class="font-medium">{{ event.title }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ event.event_space.name }}
                                </p>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/staff/assignments/${event.id}`">View</Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Current Assignments -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Current Assignments</CardTitle>
                                <CardDescription>Events in progress</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/staff/assignments?filter=current">View All</Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="currentAssignments && currentAssignments.length" class="space-y-4">
                            <div
                                v-for="assignment in currentAssignments"
                                :key="assignment.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">{{ assignment.title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ assignment.event_space.name }}
                                    </p>
                                </div>
                                <Badge :variant="getStatusVariant(assignment.status)">
                                    {{ assignment.status }}
                                </Badge>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No current assignments</p>
                    </CardContent>
                </Card>

                <!-- Upcoming Assignments -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Upcoming Assignments</CardTitle>
                                <CardDescription>Next 30 days</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/staff/assignments?filter=upcoming">View All</Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="upcomingAssignments && upcomingAssignments.length" class="space-y-4">
                            <div
                                v-for="assignment in upcomingAssignments.slice(0, 5)"
                                :key="assignment.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex-1">
                                    <p class="font-medium">{{ assignment.title }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatDate(assignment.start_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No upcoming assignments</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2">
                        <Button as-child>
                            <Link href="/staff/assignments">
                                <Calendar class="mr-2 h-4 w-4" />
                                View All Assignments
                            </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/staff/assignments/calendar">
                                <CalendarDays class="mr-2 h-4 w-4" />
                                Calendar View
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Staff Without Profile -->
        <div v-else-if="isStaff && noProfile" class="flex flex-col gap-6 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Staff Profile Required</CardTitle>
                    <CardDescription>You need a staff profile to view assignments</CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        Please contact your administrator to create a staff profile for your account.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
