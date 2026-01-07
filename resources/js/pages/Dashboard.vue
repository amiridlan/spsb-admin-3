<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
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
    ChevronUp,
    ChevronDown,
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
    bookingsTrend?: {
        labels: string[];
        values: number[];
        change: number;
        changeType: 'increase' | 'decrease' | 'stable';
    };
    calendarEvents?: any[];
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

// Helper function to check if a date has events for a specific month/year
const hasEventsOnDate = (day: number, month: number, year: number) => {
    if (!props.calendarEvents || !Array.isArray(props.calendarEvents)) return false;

    // Format check date as YYYY-MM-DD for string comparison
    const checkDateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    return props.calendarEvents.some((event: any) => {
        // Use string comparison to avoid timezone issues
        // FullCalendar end date is exclusive (event.end is the day AFTER the last day)
        // So we need to compare: start <= checkDate < end
        return event.start <= checkDateStr && checkDateStr < event.end;
    });
};

const getEventCountForDay = (day: number, month: number, year: number) => {
    if (!props.calendarEvents || !Array.isArray(props.calendarEvents)) return 0;

    // Format check date as YYYY-MM-DD for string comparison
    const checkDateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    return props.calendarEvents.filter((event: any) => {
        // Use string comparison to avoid timezone issues
        // FullCalendar end date is exclusive (event.end is the day AFTER the last day)
        // So we need to compare: start <= checkDate < end
        return event.start <= checkDateStr && checkDateStr < event.end;
    }).length;
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
            <div class="grid gap-4 md:grid-cols-1 lg:grid-cols-1">
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

            <!-- Calendar and Bookings Trend -->
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
                <!-- Calendar -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Calendar</CardTitle>
                                <CardDescription>
                                    {{ calendarEvents?.length || 0 }} event(s) this month
                                </CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link href="/calendar">
                                    <CalendarDays class="mr-2 h-4 w-4" />
                                    View Full Calendar
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <CalendarComponent class="w-full">
                            <template #calendar-day="{ day, displayedMonth, displayedYear }">
                                <span>{{ day }}</span>
                                <div
                                    v-if="hasEventsOnDate(day, displayedMonth, displayedYear)"
                                    class="absolute bottom-1 left-1/2 -translate-x-1/2 flex gap-0.5"
                                >
                                    <div class="h-1 w-1 rounded-full bg-primary"></div>
                                    <div
                                        v-if="getEventCountForDay(day, displayedMonth, displayedYear) > 1"
                                        class="h-1 w-1 rounded-full bg-primary"
                                    ></div>
                                    <div
                                        v-if="getEventCountForDay(day, displayedMonth, displayedYear) > 2"
                                        class="h-1 w-1 rounded-full bg-primary"
                                    ></div>
                                </div>
                            </template>
                        </CalendarComponent>
                    </CardContent>
                </Card>


            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Bookings Trend Graph -->
                <Card>
                    <CardHeader>
                        <CardTitle>Bookings Trend</CardTitle>
                        <CardDescription>Last 6 months</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="bookingsTrend" class="space-y-4">
                            <!-- Trend Indicator -->
                            <div class="flex items-center gap-2">
                                <div :class="[
                                    'flex items-center gap-1 text-sm font-medium',
                                    bookingsTrend.changeType === 'increase' ? 'text-green-600' :
                                    bookingsTrend.changeType === 'decrease' ? 'text-red-600' :
                                    'text-gray-600'
                                ]">
                                    <ChevronUp v-if="bookingsTrend.changeType === 'increase'" class="h-4 w-4" />
                                    <ChevronDown v-if="bookingsTrend.changeType === 'decrease'" class="h-4 w-4" />
                                    <span>{{ Math.abs(bookingsTrend.change) }}%</span>
                                    <span class="text-muted-foreground font-normal">
                                        {{ bookingsTrend.changeType === 'increase' ? 'increase' : bookingsTrend.changeType === 'decrease' ? 'decrease' : 'no change' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Simple Line Chart -->
                            <div class="relative h-48">
                                <svg class="w-full h-full" viewBox="0 0 600 200" preserveAspectRatio="none">
                                    <!-- Grid lines -->
                                    <line v-for="i in 5" :key="`grid-${i}`"
                                          :x1="0" :y1="i * 40" :x2="600" :y2="i * 40"
                                          stroke="#e5e7eb" stroke-width="1" />

                                    <!-- Line chart -->
                                    <polyline
                                        :points="bookingsTrend.values.map((val, idx) => {
                                            const x = (idx / (bookingsTrend.values.length - 1)) * 600;
                                            const maxVal = Math.max(...bookingsTrend.values, 1);
                                            const y = 200 - ((val / maxVal) * 180);
                                            return `${x},${y}`;
                                        }).join(' ')"
                                        fill="none"
                                        stroke="hsl(var(--primary))"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                    <!-- Data points -->
                                    <circle
                                        v-for="(val, idx) in bookingsTrend.values"
                                        :key="`point-${idx}`"
                                        :cx="(idx / (bookingsTrend.values.length - 1)) * 600"
                                        :cy="200 - ((val / Math.max(...bookingsTrend.values, 1)) * 180)"
                                        r="4"
                                        fill="hsl(var(--primary))"
                                    />
                                </svg>

                                <!-- X-axis labels -->
                                <div class="flex justify-between mt-2 text-xs text-muted-foreground">
                                    <span v-for="(label, idx) in bookingsTrend.labels" :key="`label-${idx}`">
                                        {{ label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground text-center py-12">
                            No trend data available
                        </p>
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
