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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar as CalendarIcon, Eye, Plus, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface LeaveRequest {
    id: number;
    leave_type: 'annual' | 'sick' | 'emergency';
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'pending' | 'hr_approved' | 'approved' | 'rejected' | 'cancelled';
    hr_reviewed_at: string | null;
    hr_review_notes: string | null;
    head_reviewed_at: string | null;
    head_review_notes: string | null;
    conflict_events: Array<{
        id: number;
        title: string;
        start_date: string;
        end_date: string;
        event_space: string | null;
    }> | null;
    hr_reviewer?: {
        id: number;
        name: string;
    };
    head_reviewer?: {
        id: number;
        name: string;
    };
}

interface LeaveBalances {
    annual: {
        total: number;
        used: number;
        remaining: number;
    };
    sick: {
        total: number;
        used: number;
        remaining: number;
    };
    emergency: {
        total: number;
        used: number;
        remaining: number;
    };
}

interface Props {
    leaveRequests: LeaveRequest[];
    leaveBalances: LeaveBalances;
    filters: {
        status?: string;
        year?: string;
        leave_type?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Leave Requests', href: '/staff/leave/requests' },
];

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        hr_approved: 'secondary',
        approved: 'default',
        rejected: 'destructive',
        cancelled: 'secondary',
    };
    return variants[status] || 'outline';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Pending HR Review',
        hr_approved: 'Pending Head Approval',
        approved: 'Approved',
        rejected: 'Rejected',
        cancelled: 'Cancelled',
    };
    return labels[status] || status;
};

const getLeaveTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        annual: 'Annual Leave',
        sick: 'Sick Leave',
        emergency: 'Emergency Leave',
    };
    return labels[type] || type;
};

const filterByStatus = (status: string | null) => {
    router.get('/staff/leave/requests', {
        ...props.filters,
        status: status || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByLeaveType = (type: string | null) => {
    router.get('/staff/leave/requests', {
        ...props.filters,
        leave_type: type || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Leave Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">My Leave Requests</h1>
                    <p class="text-sm text-muted-foreground">
                        View and manage your leave requests
                    </p>
                </div>
                <Button as-child>
                    <Link href="/staff/leave/requests/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Request Leave
                    </Link>
                </Button>
            </div>

            <!-- Leave Balance Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Annual Leave</div>
                        <CalendarIcon class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveBalances.annual.remaining }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ leaveBalances.annual.used }} used / {{ leaveBalances.annual.total }} total days
                        </p>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full bg-blue-500 transition-all"
                                :style="{ width: `${(leaveBalances.annual.used / leaveBalances.annual.total) * 100}%` }"
                            ></div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Sick Leave</div>
                        <CalendarIcon class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveBalances.sick.remaining }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ leaveBalances.sick.used }} used / {{ leaveBalances.sick.total }} total days
                        </p>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full bg-red-500 transition-all"
                                :style="{ width: `${(leaveBalances.sick.used / leaveBalances.sick.total) * 100}%` }"
                            ></div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Emergency Leave</div>
                        <CalendarIcon class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveBalances.emergency.remaining }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ leaveBalances.emergency.used }} used / {{ leaveBalances.emergency.total }} total days
                        </p>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full bg-orange-500 transition-all"
                                :style="{ width: `${(leaveBalances.emergency.used / leaveBalances.emergency.total) * 100}%` }"
                            ></div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Leave Requests Table -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Leave Requests</CardTitle>
                        <div class="flex gap-2">
                            <Select :model-value="filters.status || 'all'" @update:model-value="filterByStatus">
                                <SelectTrigger class="w-[150px]">
                                    <SelectValue placeholder="Filter by status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="pending">Pending HR Review</SelectItem>
                                    <SelectItem value="hr_approved">Pending Head Approval</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select :model-value="filters.leave_type || 'all'" @update:model-value="filterByLeaveType">
                                <SelectTrigger class="w-[150px]">
                                    <SelectValue placeholder="Filter by type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Types</SelectItem>
                                    <SelectItem value="annual">Annual</SelectItem>
                                    <SelectItem value="sick">Sick</SelectItem>
                                    <SelectItem value="emergency">Emergency</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="leaveRequests.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>Days</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Conflicts</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="request in leaveRequests" :key="request.id">
                                    <TableCell class="font-medium">
                                        {{ getLeaveTypeLabel(request.leave_type) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            {{ new Date(request.start_date).toLocaleDateString() }}
                                            -
                                            {{ new Date(request.end_date).toLocaleDateString() }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ request.total_days }} {{ request.total_days === 1 ? 'day' : 'days' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(request.status)">
                                            {{ getStatusLabel(request.status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div v-if="request.conflict_events && request.conflict_events.length > 0" class="flex items-center gap-1 text-orange-600">
                                            <AlertCircle class="h-4 w-4" />
                                            <span class="text-sm">{{ request.conflict_events.length }}</span>
                                        </div>
                                        <span v-else class="text-sm text-muted-foreground">None</span>
                                    </TableCell>
                                    <TableCell>
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="`/staff/leave/requests/${request.id}`">
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
                            No leave requests found
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
