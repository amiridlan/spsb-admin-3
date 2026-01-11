<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Head, useForm } from '@inertiajs/vue3';
import { Calendar, AlertCircle, UserCheck, CheckCircle, XCircle, FileText } from 'lucide-vue-next';
import { ref } from 'vue';

interface LeaveRequest {
    id: number;
    leave_type: 'annual' | 'sick' | 'emergency';
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'hr_approved';
    hr_reviewed_at: string;
    hr_review_notes: string | null;
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
        annual_leave_total: number;
        annual_leave_used: number;
        sick_leave_total: number;
        sick_leave_used: number;
        emergency_leave_total: number;
        emergency_leave_used: number;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
    hr_reviewer?: {
        id: number;
        name: string;
    };
}

interface Props {
    leaveRequest: LeaveRequest;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Leave Approvals', href: '/head/leave/requests' },
    { title: 'Review Request', href: '#' },
];

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

const getLeaveBalance = () => {
    const staff = props.leaveRequest.staff;
    const type = props.leaveRequest.leave_type;

    const balances: Record<string, { total: number; used: number }> = {
        annual: {
            total: staff.annual_leave_total,
            used: staff.annual_leave_used,
        },
        sick: {
            total: staff.sick_leave_total,
            used: staff.sick_leave_used,
        },
        emergency: {
            total: staff.emergency_leave_total,
            used: staff.emergency_leave_used,
        },
    };

    return balances[type] || { total: 0, used: 0 };
};

const submitApproval = () => {
    approveForm.post(`/head/leave/requests/${props.leaveRequest.id}/approve`, {
        onSuccess: () => {
            approveDialogOpen.value = false;
        },
    });
};

const submitRejection = () => {
    rejectForm.post(`/head/leave/requests/${props.leaveRequest.id}/reject`, {
        onSuccess: () => {
            rejectDialogOpen.value = false;
        },
    });
};

const balance = getLeaveBalance();
const remaining = balance.total - balance.used;
</script>

<template>
    <Head :title="`Review Leave Request - ${leaveRequest.staff.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header with Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Review Leave Request</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ leaveRequest.staff.user.name }} - {{ getLeaveTypeLabel(leaveRequest.leave_type) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="default"
                        @click="approveDialogOpen = true"
                    >
                        <CheckCircle class="mr-2 h-4 w-4" />
                        Approve Request
                    </Button>
                    <Button
                        variant="destructive"
                        @click="rejectDialogOpen = true"
                    >
                        <XCircle class="mr-2 h-4 w-4" />
                        Reject Request
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Staff Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <UserCheck class="h-5 w-5" />
                            Staff Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Name</p>
                            <p class="font-medium">{{ leaveRequest.staff.user.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Email</p>
                            <p class="font-medium">{{ leaveRequest.staff.user.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Position</p>
                            <p class="font-medium">{{ leaveRequest.staff.position || 'No position' }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Leave Details -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Leave Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Leave Type</p>
                            <Badge variant="outline">{{ getLeaveTypeLabel(leaveRequest.leave_type) }}</Badge>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Dates</p>
                            <p class="font-medium">
                                {{ new Date(leaveRequest.start_date).toLocaleDateString() }}
                                -
                                {{ new Date(leaveRequest.end_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Duration</p>
                            <p class="font-medium">
                                {{ leaveRequest.total_days }} {{ leaveRequest.total_days === 1 ? 'day' : 'days' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Status</p>
                            <Badge variant="outline" class="bg-blue-50 text-blue-700">
                                HR Approved - Pending Your Approval
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- HR Approval Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-green-600" />
                            HR Approval (Step 1)
                        </CardTitle>
                        <CardDescription>
                            This request has been approved by HR
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Approved By</p>
                            <p class="font-medium">{{ leaveRequest.hr_reviewer?.name || 'Unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Approved On</p>
                            <p class="font-medium">
                                {{ new Date(leaveRequest.hr_reviewed_at).toLocaleString() }}
                            </p>
                        </div>
                        <div v-if="leaveRequest.hr_review_notes">
                            <p class="text-sm text-muted-foreground">HR Notes</p>
                            <p class="text-sm">{{ leaveRequest.hr_review_notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Leave Balance -->
                <Card>
                    <CardHeader>
                        <CardTitle>Leave Balance</CardTitle>
                        <CardDescription>Current {{ getLeaveTypeLabel(leaveRequest.leave_type) }} balance</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Allocation</p>
                            <p class="text-2xl font-bold">{{ balance.total }} days</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Used</p>
                            <p class="text-xl font-semibold">{{ balance.used }} days</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Remaining ({{ remaining >= leaveRequest.total_days ? 'Sufficient' : 'Insufficient' }})
                            </p>
                            <p
                                class="text-xl font-semibold"
                                :class="remaining >= leaveRequest.total_days ? 'text-green-600' : 'text-red-600'"
                            >
                                {{ remaining }} days
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Reason -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Reason for Leave
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm">{{ leaveRequest.reason }}</p>
                    </CardContent>
                </Card>

                <!-- Event Conflicts -->
                <Card v-if="leaveRequest.conflict_events && leaveRequest.conflict_events.length > 0" class="md:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-orange-600">
                            <AlertCircle class="h-5 w-5" />
                            Event Conflicts Detected
                        </CardTitle>
                        <CardDescription>
                            This staff member is assigned to {{ leaveRequest.conflict_events.length }} event(s) during this period
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="event in leaveRequest.conflict_events"
                                :key="event.id"
                                class="rounded-lg border border-orange-200 bg-orange-50 p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium">{{ event.title }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ new Date(event.start_date).toLocaleDateString() }}
                                            -
                                            {{ new Date(event.end_date).toLocaleDateString() }}
                                        </p>
                                        <p v-if="event.event_space" class="text-sm text-muted-foreground">
                                            Location: {{ event.event_space }}
                                        </p>
                                    </div>
                                    <Badge variant="destructive">Conflict</Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>

    <!-- Approve Dialog -->
    <Dialog v-model:open="approveDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Approve Leave Request (Final Approval)</DialogTitle>
                <DialogDescription>
                    This will give final approval to the leave request and update the staff member's leave balance.
                    {{ leaveRequest.staff.user.name }} -
                    {{ getLeaveTypeLabel(leaveRequest.leave_type) }}
                    ({{ leaveRequest.total_days }} days)
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="approve-notes">Notes (optional)</Label>
                    <Textarea
                        id="approve-notes"
                        v-model="approveForm.notes"
                        placeholder="Add any notes about this approval..."
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
                    Approve Leave Request
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
                    {{ leaveRequest.staff.user.name }} -
                    {{ getLeaveTypeLabel(leaveRequest.leave_type) }}
                    ({{ leaveRequest.total_days }} days)
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="reject-reason">Rejection Reason *</Label>
                    <Textarea
                        id="reject-reason"
                        v-model="rejectForm.reason"
                        placeholder="Explain why this request is being rejected (e.g., insufficient coverage, peak workload period)..."
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
