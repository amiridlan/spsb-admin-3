<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    TrendingUp,
    TrendingDown,
    Calendar,
    Users,
    Building2,
    Clock,
    BarChart3,
    PieChart,
} from 'lucide-vue-next';

interface Props {
    dateRange: {
        start: string;
        end: string;
    };
    overview: any;
    bookingTrends: any[];
    spaceMetrics: any[];
    statusMetrics: any;
    staffMetrics: any[];
    timeMetrics: any;
    clientMetrics: any;
}

const props = defineProps<Props>();

const startDate = ref(props.dateRange.start);
const endDate = ref(props.dateRange.end);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Metrics & Statistics', href: '/admin/metrics' },
];

function applyDateFilter() {
    router.get('/admin/metrics', {
        start: startDate.value,
        end: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function setQuickRange(range: string) {
    const today = new Date();
    let start, end;

    switch (range) {
        case 'today':
            start = end = today.toISOString().split('T')[0];
            break;
        case 'week':
            start = new Date(today.setDate(today.getDate() - today.getDay())).toISOString().split('T')[0];
            end = new Date(today.setDate(today.getDate() - today.getDay() + 6)).toISOString().split('T')[0];
            break;
        case 'month':
            start = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            break;
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            start = new Date(today.getFullYear(), quarter * 3, 1).toISOString().split('T')[0];
            end = new Date(today.getFullYear(), quarter * 3 + 3, 0).toISOString().split('T')[0];
            break;
        case 'year':
            start = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            end = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0];
            break;
    }

    if (start && end) {
        startDate.value = start;
        endDate.value = end;
        applyDateFilter();
    }
}

const growthColor = computed(() => {
    return props.overview.booking_growth >= 0 ? 'text-green-600' : 'text-red-600';
});

const growthIcon = computed(() => {
    return props.overview.booking_growth >= 0 ? TrendingUp : TrendingDown;
});
</script>

<template>
    <Head title="Metrics & Statistics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Metrics & Statistics</h1>
                    <p class="text-sm text-muted-foreground">
                        Detailed analytics and insights for your bookings
                    </p>
                </div>
            </div>

            <!-- Date Range Filter -->
            <Card>
                <CardHeader>
                    <CardTitle>Date Range</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <Label htmlFor="start-date">Start Date</Label>
                            <Input
                                id="start-date"
                                type="date"
                                v-model="startDate"
                            />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <Label htmlFor="end-date">End Date</Label>
                            <Input
                                id="end-date"
                                type="date"
                                v-model="endDate"
                            />
                        </div>
                        <div class="flex items-end">
                            <Button @click="applyDateFilter">Apply</Button>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button variant="outline" size="sm" @click="setQuickRange('today')">Today</Button>
                        <Button variant="outline" size="sm" @click="setQuickRange('week')">This Week</Button>
                        <Button variant="outline" size="sm" @click="setQuickRange('month')">This Month</Button>
                        <Button variant="outline" size="sm" @click="setQuickRange('quarter')">This Quarter</Button>
                        <Button variant="outline" size="sm" @click="setQuickRange('year')">This Year</Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Overview Statistics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Bookings</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ overview.total_bookings }}</div>
                        <div class="mt-1 flex items-center gap-1 text-xs" :class="growthColor">
                            <component :is="growthIcon" class="h-3 w-3" />
                            <span>{{ Math.abs(overview.booking_growth) }}% vs previous period</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Confirmation Rate</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ overview.confirmation_rate }}%</div>
                        <p class="text-xs text-muted-foreground">
                            {{ overview.confirmed_bookings }} of {{ overview.total_bookings }} confirmed
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Average Duration</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ overview.avg_duration }} days</div>
                        <p class="text-xs text-muted-foreground">
                            Total: {{ overview.total_days }} days
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Cancellation Rate</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ overview.total_bookings > 0 ? Math.round((overview.cancelled_bookings / overview.total_bookings) * 100) : 0 }}%
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ overview.cancelled_bookings }} cancelled
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- Space Utilization -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Building2 class="h-5 w-5" />
                            Space Utilization
                        </CardTitle>
                        <CardDescription>Booking count and utilization rate by space</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Space</TableHead>
                                    <TableHead class="text-right">Bookings</TableHead>
                                    <TableHead class="text-right">Days</TableHead>
                                    <TableHead class="text-right">Utilization</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="space in spaceMetrics" :key="space.id">
                                    <TableCell class="font-medium">{{ space.name }}</TableCell>
                                    <TableCell class="text-right">{{ space.booking_count }}</TableCell>
                                    <TableCell class="text-right">{{ space.total_days }}</TableCell>
                                    <TableCell class="text-right">{{ space.utilization_rate }}%</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Staff Performance -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5" />
                            Staff Assignments
                        </CardTitle>
                        <CardDescription>Assignment count by staff member</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Staff</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead class="text-right">Assignments</TableHead>
                                    <TableHead class="text-right">Days</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="staff in staffMetrics.slice(0, 10)" :key="staff.id">
                                    <TableCell class="font-medium">{{ staff.name }}</TableCell>
                                    <TableCell class="text-sm text-muted-foreground">{{ staff.position || 'N/A' }}</TableCell>
                                    <TableCell class="text-right">{{ staff.assignment_count }}</TableCell>
                                    <TableCell class="text-right">{{ staff.total_days }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <!-- Time Metrics -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Clock class="h-5 w-5" />
                        Time Analysis
                    </CardTitle>
                    <CardDescription>Booking patterns and lead times</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <h4 class="mb-3 font-medium">Bookings by Day of Week</h4>
                            <div class="space-y-2">
                                <div
                                    v-for="(count, day) in timeMetrics.day_of_week"
                                    :key="day"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-sm">{{ day }}</span>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-32 bg-muted rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-primary"
                                                :style="{ width: `${(count / Math.max(...Object.values(timeMetrics.day_of_week))) * 100}%` }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium w-8 text-right">{{ count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="mb-3 font-medium">Lead Time Statistics</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Average Lead Time</span>
                                    <span class="font-medium">{{ timeMetrics.avg_lead_time }} days</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Minimum Lead Time</span>
                                    <span class="font-medium">{{ timeMetrics.min_lead_time }} days</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Maximum Lead Time</span>
                                    <span class="font-medium">{{ timeMetrics.max_lead_time }} days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Client Metrics -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        Client Insights
                    </CardTitle>
                    <CardDescription>Client behavior and retention metrics</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <h4 class="mb-3 font-medium">Client Summary</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Unique Clients</span>
                                    <span class="font-medium">{{ clientMetrics.unique_clients }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">New Clients</span>
                                    <span class="font-medium">{{ clientMetrics.new_clients }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Returning Clients</span>
                                    <span class="font-medium">{{ clientMetrics.returning_clients }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Return Rate</span>
                                    <span class="font-medium">{{ clientMetrics.return_rate }}%</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="mb-3 font-medium">Top Clients</h4>
                            <div class="space-y-2">
                                <div
                                    v-for="client in clientMetrics.top_clients.slice(0, 5)"
                                    :key="client.client_name"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-sm">{{ client.client_name }}</span>
                                    <span class="text-sm font-medium">{{ client.booking_count }} bookings</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
