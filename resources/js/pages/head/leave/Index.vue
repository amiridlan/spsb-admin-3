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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Clock, UserCheck } from 'lucide-vue-next';

interface Department {
    id: number;
    name: string;
    code: string | null;
}

interface LeaveRequest {
    id: number;
    leave_type: 'annual' | 'sick' | 'emergency';
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'pending';
    hr_reviewed_at: string | null;
    hr_review_notes: string | null;
    staff: {
        id: number;
        position: string | null;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
    hr_reviewer?: {
        id: number;
        name: string;
    } | null;
}

interface Pagination {
    data: LeaveRequest[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    leaveRequests: Pagination;
    pendingCount: number;
    department: Department;
    filters: {
        staff_id?: string;
        leave_type?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Leave Approvals', href: '/head/leave/requests' },
];

const getLeaveTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        annual: 'Annual Leave',
        sick: 'Sick Leave',
        emergency: 'Emergency Leave',
    };
    return labels[type] || type;
};
</script>

<template>
    <Head title="Leave Approvals" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Department Leave Approvals</h1>
                    <p class="text-sm text-muted-foreground">
                        Review leave requests for {{ department.name }}
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Pending Your Approval</div>
                        <Clock class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ pendingCount }}</div>
                        <p class="text-xs text-muted-foreground">Awaiting your review</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Your Department</div>
                        <UserCheck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ department.name }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ department.code || 'No code' }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Leave Requests Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Leave Requests Pending Your Approval</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="leaveRequests.data.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Staff</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>Days</TableHead>
                                    <TableHead>HR Status</TableHead>
                                    <TableHead>Notes</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="request in leaveRequests.data" :key="request.id">
                                    <TableCell>
                                        <div>
                                            <p class="font-medium">{{ request.staff.user.name }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ request.staff.position || 'No position' }}
                                            </p>
                                        </div>
                                    </TableCell>
                                    <TableCell>
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
                                        <div v-if="request.hr_reviewed_at" class="flex items-center gap-2">
                                            <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200">
                                                ✓ Approved
                                            </Badge>
                                            <div class="text-xs text-muted-foreground">
                                                by {{ request.hr_reviewer?.name || 'HR' }}
                                            </div>
                                        </div>
                                        <Badge v-else variant="outline" class="bg-orange-50 text-orange-700 border-orange-200">
                                            Pending HR
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm text-muted-foreground max-w-xs truncate">
                                            {{ request.hr_review_notes || 'No notes' }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="`/head/leave/requests/${request.id}`">
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
                            No leave requests pending your approval
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="leaveRequests.last_page > 1"
                        class="mt-4 flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ leaveRequests.data.length }} of {{ leaveRequests.total }} requests
                        </p>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="leaveRequests.current_page === 1"
                                @click="router.visit(`/head/leave/requests?page=${leaveRequests.current_page - 1}`)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="leaveRequests.current_page === leaveRequests.last_page"
                                @click="router.visit(`/head/leave/requests?page=${leaveRequests.current_page + 1}`)"
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
