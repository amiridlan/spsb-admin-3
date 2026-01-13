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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, XCircle, Clock } from 'lucide-vue-next';
import { ref } from 'vue';

interface LeaveRequest {
    id: number;
    leave_type: 'annual' | 'sick' | 'emergency';
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'pending';
    conflict_events: Array<{
        id: number;
        title: string;
        start_date: string;
        end_date: string;
        event_space: string | null;
    }> | null;
    staff: {
        id: number;
        position: string | null;
        department?: {
            id: number;
            name: string;
        };
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
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
    filters: {
        staff_id?: string;
        leave_type?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Leave Requests', href: '/admin/leave/requests' },
    { title: 'HR Review', href: '/admin/leave/hr/pending' },
];

const selectedRequest = ref<LeaveRequest | null>(null);
const approveDialogOpen = ref(false);
const rejectDialogOpen = ref(false);

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    reason: '',
});

const getLeaveTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        annual: 'Annual Leave',
        sick: 'Sick Leave',
        emergency: 'Emergency Leave',
    };
    return labels[type] || type;
};

const openApproveDialog = (request: LeaveRequest) => {
    selectedRequest.value = request;
    approveForm.reset();
    approveDialogOpen.value = true;
};

const openRejectDialog = (request: LeaveRequest) => {
    selectedRequest.value = request;
    rejectForm.reset();
    rejectDialogOpen.value = true;
};

const submitApproval = () => {
    if (!selectedRequest.value) return;

    approveForm.post(`/admin/leave/hr/${selectedRequest.value.id}/approve`, {
        preserveScroll: true,
        onSuccess: () => {
            approveDialogOpen.value = false;
            selectedRequest.value = null;
        },
    });
};

const submitRejection = () => {
    if (!selectedRequest.value) return;

    rejectForm.post(`/admin/leave/hr/${selectedRequest.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            rejectDialogOpen.value = false;
            selectedRequest.value = null;
        },
    });
};
</script>

<template>
    <Head title="HR Leave Review" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">HR Leave Review</h1>
                    <p class="text-sm text-muted-foreground">
                        Review pending leave requests requiring HR approval
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <div class="text-sm font-medium">Pending HR Review</div>
                        <Clock class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ leaveRequests.total }}</div>
                        <p class="text-xs text-muted-foreground">Awaiting your review</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Leave Requests Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Pending Leave Requests</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="leaveRequests.data.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Staff</TableHead>
                                    <TableHead>Department</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Dates</TableHead>
                                    <TableHead>Days</TableHead>
                                    <TableHead>Conflicts</TableHead>
                                    <TableHead>Actions</TableHead>
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
                                        <Badge v-if="request.staff.department" variant="outline">
                                            {{ request.staff.department.name }}
                                        </Badge>
                                        <span v-else class="text-sm text-muted-foreground">No dept</span>
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
                                        <div v-if="request.conflict_events && request.conflict_events.length > 0" class="flex items-center gap-1 text-orange-600">
                                            <AlertCircle class="h-4 w-4" />
                                            <span class="text-sm">{{ request.conflict_events.length }} event(s)</span>
                                        </div>
                                        <span v-else class="text-sm text-green-600">None</span>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <Button
                                                variant="default"
                                                size="sm"
                                                @click="openApproveDialog(request)"
                                            >
                                                <CheckCircle class="mr-1 h-4 w-4" />
                                                Approve
                                            </Button>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                @click="openRejectDialog(request)"
                                            >
                                                <XCircle class="mr-1 h-4 w-4" />
                                                Reject
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="rounded-lg border p-8 text-center">
                        <p class="text-sm text-muted-foreground">
                            No pending leave requests for HR review
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
                                @click="router.visit(`/admin/leave/hr/pending?page=${leaveRequests.current_page - 1}`)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="leaveRequests.current_page === leaveRequests.last_page"
                                @click="router.visit(`/admin/leave/hr/pending?page=${leaveRequests.current_page + 1}`)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Approve Dialog -->
    <Dialog v-model:open="approveDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Approve Leave Request (HR)</DialogTitle>
                <DialogDescription>
                    Approve this leave request as HR. If the department head has already approved, the request will be fully approved.
                    <span v-if="selectedRequest">
                        {{ selectedRequest.staff.user.name }} -
                        {{ getLeaveTypeLabel(selectedRequest.leave_type) }}
                        ({{ selectedRequest.total_days }} days)
                    </span>
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="approve-notes">Notes (optional)</Label>
                    <Textarea
                        id="approve-notes"
                        v-model="approveForm.notes"
                        placeholder="Add any notes for the department head..."
                        rows="3"
                    />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="approveDialogOpen = false">
                    Cancel
                </Button>
                <Button
                    @click="submitApproval"
                    :disabled="approveForm.processing"
                >
                    <CheckCircle class="mr-2 h-4 w-4" />
                    Approve as HR
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Reject Dialog -->
    <Dialog v-model:open="rejectDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Reject Leave Request</DialogTitle>
                <DialogDescription>
                    Provide a reason for rejecting this leave request.
                    <span v-if="selectedRequest">
                        {{ selectedRequest.staff.user.name }} -
                        {{ getLeaveTypeLabel(selectedRequest.leave_type) }}
                        ({{ selectedRequest.total_days }} days)
                    </span>
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="reject-reason">Rejection Reason *</Label>
                    <Textarea
                        id="reject-reason"
                        v-model="rejectForm.reason"
                        placeholder="Explain why this request is being rejected..."
                        rows="4"
                        :class="{ 'border-red-500': rejectForm.errors.reason }"
                    />
                    <p v-if="rejectForm.errors.reason" class="text-sm text-red-500">
                        {{ rejectForm.errors.reason }}
                    </p>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="rejectDialogOpen = false">
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    @click="submitRejection"
                    :disabled="rejectForm.processing || !rejectForm.reason"
                >
                    <XCircle class="mr-2 h-4 w-4" />
                    Reject Request
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
