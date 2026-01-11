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
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Calendar as CalendarIcon, AlertCircle, CheckCircle, XCircle, User } from 'lucide-vue-next';
import { ref } from 'vue';

interface LeaveRequest {
    id: number;
    leave_type: 'annual' | 'sick' | 'emergency';
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'pending' | 'approved' | 'rejected' | 'cancelled';
    reviewed_at: string | null;
    review_notes: string | null;
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
        annual_leave_remaining: number;
        sick_leave_total: number;
        sick_leave_used: number;
        sick_leave_remaining: number;
        emergency_leave_total: number;
        emergency_leave_used: number;
        emergency_leave_remaining: number;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
    reviewer?: {
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
    { title: 'Leave Requests', href: '/admin/leave/requests' },
    { title: 'Review Request', href: `/admin/leave/requests/${props.leaveRequest.id}` },
];

const isApproveDialogOpen = ref(false);
const isRejectDialogOpen = ref(false);

const approveForm = useForm({
    notes: '',
});

const rejectForm = useForm({
    reason: '',
});

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        approved: 'default',
        rejected: 'destructive',
        cancelled: 'secondary',
    };
    return variants[status] || 'outline';
};

const getLeaveTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        annual: 'Annual Leave',
        sick: 'Sick Leave',
        emergency: 'Emergency Leave',
    };
    return labels[type] || type;
};

const submitApprove = () => {
    approveForm.post(`/admin/leave/requests/${props.leaveRequest.id}/approve`, {
        preserveScroll: true,
        onSuccess: () => {
            isApproveDialogOpen.value = false;
            approveForm.reset();
        },
    });
};

const submitReject = () => {
    rejectForm.post(`/admin/leave/requests/${props.leaveRequest.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            isRejectDialogOpen.value = false;
            rejectForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Review Leave Request" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Review Leave Request</h1>
                    <p class="text-sm text-muted-foreground">
                        Review and take action on staff leave request
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="leaveRequest.status === 'pending'"
                        variant="default"
                        @click="isApproveDialogOpen = true"
                    >
                        <CheckCircle class="mr-2 h-4 w-4" />
                        Approve
                    </Button>
                    <Button
                        v-if="leaveRequest.status === 'pending'"
                        variant="destructive"
                        @click="isRejectDialogOpen = true"
                    >
                        <XCircle class="mr-2 h-4 w-4" />
                        Reject
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/admin/leave/requests">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Requests
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Request Information -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Request Information</CardTitle>
                                <Badge :variant="getStatusVariant(leaveRequest.status)" class="text-sm">
                                    {{ leaveRequest.status }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-muted-foreground">Leave Type</Label>
                                    <p class="mt-1 font-medium">{{ getLeaveTypeLabel(leaveRequest.leave_type) }}</p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Total Days</Label>
                                    <p class="mt-1 font-medium">
                                        {{ leaveRequest.total_days }} {{ leaveRequest.total_days === 1 ? 'day' : 'days' }}
                                    </p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Start Date</Label>
                                    <p class="mt-1 font-medium">
                                        {{ new Date(leaveRequest.start_date).toLocaleDateString('en-US', {
                                            weekday: 'short',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        }) }}
                                    </p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">End Date</Label>
                                    <p class="mt-1 font-medium">
                                        {{ new Date(leaveRequest.end_date).toLocaleDateString('en-US', {
                                            weekday: 'short',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        }) }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <Label class="text-muted-foreground">Reason</Label>
                                <p class="mt-2 whitespace-pre-wrap rounded-lg bg-muted p-4">
                                    {{ leaveRequest.reason }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Staff Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Staff Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-muted-foreground">Name</Label>
                                    <p class="mt-1 font-medium">{{ leaveRequest.staff.user.name }}</p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Email</Label>
                                    <p class="mt-1 font-medium">{{ leaveRequest.staff.user.email }}</p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Position</Label>
                                    <p class="mt-1 font-medium">{{ leaveRequest.staff.position || 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <Label class="text-muted-foreground mb-3 block">Leave Balances</Label>
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <p class="text-sm font-medium">Annual Leave</p>
                                        <p class="mt-1 text-lg font-bold text-blue-600">
                                            {{ leaveRequest.staff.annual_leave_remaining }} remaining
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ leaveRequest.staff.annual_leave_used }} / {{ leaveRequest.staff.annual_leave_total }} used
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">Sick Leave</p>
                                        <p class="mt-1 text-lg font-bold text-red-600">
                                            {{ leaveRequest.staff.sick_leave_remaining }} remaining
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ leaveRequest.staff.sick_leave_used }} / {{ leaveRequest.staff.sick_leave_total }} used
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">Emergency Leave</p>
                                        <p class="mt-1 text-lg font-bold text-orange-600">
                                            {{ leaveRequest.staff.emergency_leave_remaining }} remaining
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ leaveRequest.staff.emergency_leave_used }} / {{ leaveRequest.staff.emergency_leave_total }} used
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Review Information -->
                    <Card v-if="leaveRequest.reviewed_at">
                        <CardHeader>
                            <CardTitle>Review Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label class="text-muted-foreground">Reviewed By</Label>
                                    <p class="mt-1 font-medium">
                                        {{ leaveRequest.reviewer?.name || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Reviewed At</Label>
                                    <p class="mt-1 font-medium">
                                        {{ new Date(leaveRequest.reviewed_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="leaveRequest.review_notes" class="border-t pt-4">
                                <Label class="text-muted-foreground">Review Notes</Label>
                                <p class="mt-2 whitespace-pre-wrap rounded-lg bg-muted p-4">
                                    {{ leaveRequest.review_notes }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Conflict Events -->
                    <Card v-if="leaveRequest.conflict_events && leaveRequest.conflict_events.length > 0">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <AlertCircle class="h-5 w-5 text-orange-600" />
                                <CardTitle>Event Conflicts</CardTitle>
                            </div>
                            <CardDescription>
                                Staff has {{ leaveRequest.conflict_events.length }} event assignment(s) during this leave period
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="event in leaveRequest.conflict_events"
                                    :key="event.id"
                                    class="rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-900 dark:bg-orange-950"
                                >
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-medium">{{ event.title }}</p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                {{ new Date(event.start_date).toLocaleDateString() }}
                                                -
                                                {{ new Date(event.end_date).toLocaleDateString() }}
                                            </p>
                                            <p v-if="event.event_space" class="mt-1 text-sm text-muted-foreground">
                                                Space: {{ event.event_space }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5" />
                                Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground">Staff Member</p>
                                <p class="mt-1 text-lg font-semibold">
                                    {{ leaveRequest.staff.user.name }}
                                </p>
                            </div>
                            <div class="border-t"></div>
                            <div>
                                <p class="text-sm text-muted-foreground">Leave Type</p>
                                <p class="mt-1 text-lg font-semibold">
                                    {{ getLeaveTypeLabel(leaveRequest.leave_type) }}
                                </p>
                            </div>
                            <div class="border-t"></div>
                            <div>
                                <p class="text-sm text-muted-foreground">Duration</p>
                                <p class="mt-1 text-lg font-semibold">
                                    {{ leaveRequest.total_days }} {{ leaveRequest.total_days === 1 ? 'Day' : 'Days' }}
                                </p>
                            </div>
                            <div class="border-t"></div>
                            <div>
                                <p class="text-sm text-muted-foreground">Status</p>
                                <Badge :variant="getStatusVariant(leaveRequest.status)" class="mt-1">
                                    {{ leaveRequest.status }}
                                </Badge>
                            </div>
                            <div v-if="leaveRequest.conflict_events && leaveRequest.conflict_events.length > 0" class="border-t"></div>
                            <div v-if="leaveRequest.conflict_events && leaveRequest.conflict_events.length > 0">
                                <p class="text-sm text-muted-foreground">Event Conflicts</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <AlertCircle class="h-4 w-4 text-orange-600" />
                                    <p class="font-semibold text-orange-600">
                                        {{ leaveRequest.conflict_events.length }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Approve Dialog -->
        <Dialog v-model:open="isApproveDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Approve Leave Request</DialogTitle>
                    <DialogDescription>
                        Approve this leave request for {{ leaveRequest.staff.user.name }}. This will update their leave balance.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitApprove">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="notes">Approval Notes (Optional)</Label>
                            <Textarea
                                id="notes"
                                v-model="approveForm.notes"
                                placeholder="Add any notes or comments for this approval"
                                rows="3"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="isApproveDialogOpen = false">
                            Cancel
                        </Button>
                        <Button variant="default" type="submit" :disabled="approveForm.processing">
                            {{ approveForm.processing ? 'Approving...' : 'Approve Request' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Reject Dialog -->
        <Dialog v-model:open="isRejectDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Reject Leave Request</DialogTitle>
                    <DialogDescription>
                        Reject this leave request for {{ leaveRequest.staff.user.name }}. Please provide a reason.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitReject">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="reason">Rejection Reason *</Label>
                            <Textarea
                                id="reason"
                                v-model="rejectForm.reason"
                                placeholder="Provide a reason for rejecting this request (minimum 10 characters)"
                                rows="4"
                                required
                            />
                            <p v-if="rejectForm.errors.reason" class="text-sm text-destructive">
                                {{ rejectForm.errors.reason }}
                            </p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="isRejectDialogOpen = false">
                            Cancel
                        </Button>
                        <Button variant="destructive" type="submit" :disabled="rejectForm.processing">
                            {{ rejectForm.processing ? 'Rejecting...' : 'Reject Request' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
