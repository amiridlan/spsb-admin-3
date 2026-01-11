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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, User, Mail, Calendar, Settings } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Department {
    id: number;
    name: string;
    code: string | null;
}

interface EventSpace {
    id: number;
    name: string;
}

interface Assignment {
    id: number;
    title: string;
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
    department?: Department | null;
    position: string | null;
    specializations: string[] | null;
    is_available: boolean;
    notes: string | null;
    annual_leave_total: number;
    annual_leave_used: number;
    annual_leave_remaining: number;
    sick_leave_total: number;
    sick_leave_used: number;
    sick_leave_remaining: number;
    emergency_leave_total: number;
    emergency_leave_used: number;
    emergency_leave_remaining: number;
    leave_notes: string | null;
    created_at: string;
}

interface Props {
    staff: Staff;
    upcomingAssignments: Assignment[];
    pastAssignments: {
        data: Assignment[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        confirmed: 'default',
        completed: 'secondary',
        cancelled: 'destructive',
    };
    return variants[status] || 'outline';
};

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/admin/staff' },
    { title: props.staff.user.name, href: `/admin/staff/${props.staff.id}` },
]);

const isAdjustLeaveOpen = ref(false);

const leaveForm = useForm({
    annual_leave_total: props.staff.annual_leave_total,
    annual_leave_used: props.staff.annual_leave_used,
    sick_leave_total: props.staff.sick_leave_total,
    sick_leave_used: props.staff.sick_leave_used,
    emergency_leave_total: props.staff.emergency_leave_total,
    emergency_leave_used: props.staff.emergency_leave_used,
    leave_notes: props.staff.leave_notes || '',
});

const submitLeaveAdjustment = () => {
    leaveForm.post(`/admin/staff/${props.staff.id}/adjust-leave`, {
        preserveScroll: true,
        onSuccess: () => {
            isAdjustLeaveOpen.value = false;
        },
    });
};
</script>

<template>
    <Head :title="staff.user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="$inertia.visit('/admin/staff')">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-2xl font-semibold">{{ staff.user.name }}</h1>
                        <p class="text-sm text-muted-foreground">
                            Staff profile and assignments
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/admin/staff/${staff.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Profile
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4 rounded-lg border p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium">Profile Information</h3>
                        <Badge :variant="staff.is_available ? 'default' : 'secondary'">
                            {{ staff.is_available ? 'Available' : 'Unavailable' }}
                        </Badge>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Name</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ staff.user.name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Mail class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Email</p>

                                    <a :href="`mailto:${staff.user.email}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ staff.user.email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="staff.department">
                            <p class="text-sm font-medium">Department</p>
                            <div class="mt-1">
                                <Badge variant="outline">
                                    {{ staff.department.name }}{{ staff.department.code ? ` (${staff.department.code})` : '' }}
                                </Badge>
                            </div>
                        </div>

                        <div v-if="staff.position">
                            <p class="text-sm font-medium">Position</p>
                            <p class="text-sm text-muted-foreground">
                                {{ staff.position }}
                            </p>
                        </div>

                        <div v-if="staff.specializations && staff.specializations.length">
                            <p class="text-sm font-medium mb-2">Specializations</p>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="spec in staff.specializations"
                                    :key="spec"
                                    variant="secondary"
                                >
                                    {{ spec }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="staff.notes" class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">Notes</h3>
                    <p class="text-sm text-muted-foreground whitespace-pre-wrap">
                        {{ staff.notes }}
                    </p>
                </div>
            </div>

            <!-- Leave Balance -->
            <div class="space-y-4 rounded-lg border p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Leave Balance</h3>
                    <Button variant="outline" size="sm" @click="isAdjustLeaveOpen = true">
                        <Settings class="mr-2 h-4 w-4" />
                        Adjust Leave
                    </Button>
                </div>

                <div class="space-y-4">
                    <!-- Annual Leave -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Annual Leave</span>
                            <span class="text-sm text-muted-foreground">
                                {{ staff.annual_leave_used }} / {{ staff.annual_leave_total }} used
                                ({{ staff.annual_leave_remaining }} remaining)
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-secondary">
                            <div
                                class="h-full bg-primary transition-all"
                                :style="{ width: `${(staff.annual_leave_used / staff.annual_leave_total) * 100}%` }"
                            />
                        </div>
                    </div>

                    <!-- Sick Leave -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Sick Leave</span>
                            <span class="text-sm text-muted-foreground">
                                {{ staff.sick_leave_used }} / {{ staff.sick_leave_total }} used
                                ({{ staff.sick_leave_remaining }} remaining)
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-secondary">
                            <div
                                class="h-full bg-red-500 transition-all"
                                :style="{ width: `${(staff.sick_leave_used / staff.sick_leave_total) * 100}%` }"
                            />
                        </div>
                    </div>

                    <!-- Emergency Leave -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Emergency Leave</span>
                            <span class="text-sm text-muted-foreground">
                                {{ staff.emergency_leave_used }} / {{ staff.emergency_leave_total }} used
                                ({{ staff.emergency_leave_remaining }} remaining)
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-secondary">
                            <div
                                class="h-full bg-orange-500 transition-all"
                                :style="{ width: `${(staff.emergency_leave_used / staff.emergency_leave_total) * 100}%` }"
                            />
                        </div>
                    </div>

                    <!-- Leave Notes (if exists) -->
                    <div v-if="staff.leave_notes" class="mt-4 rounded-lg bg-muted p-3">
                        <p class="text-xs font-medium text-muted-foreground mb-1">Admin Notes:</p>
                        <p class="text-sm whitespace-pre-wrap">{{ staff.leave_notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Upcoming Assignments -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Upcoming Assignments</h3>
                <div v-if="upcomingAssignments.length" class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Event</TableHead>
                                <TableHead>Space</TableHead>
                                <TableHead>Dates</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="assignment in upcomingAssignments" :key="assignment.id">
                                <TableCell class="font-medium">
                                    <Link
                                        :href="`/admin/events/${assignment.id}`"
                                        class="hover:underline"
                                    >
                                        {{ assignment.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ assignment.event_space.name }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-1 text-sm">
                                        <Calendar class="h-3 w-3" />
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
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div v-else class="rounded-lg border p-8 text-center">
                    <p class="text-sm text-muted-foreground">No upcoming assignments</p>
                </div>
            </div>

            <!-- Past Assignments -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold">Past Assignments</h3>
                <div v-if="pastAssignments.data.length" class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Event</TableHead>
                                <TableHead>Space</TableHead>
                                <TableHead>Dates</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="assignment in pastAssignments.data" :key="assignment.id">
                                <TableCell class="font-medium">
                                    <Link
                                        :href="`/admin/events/${assignment.id}`"
                                        class="hover:underline"
                                    >
                                        {{ assignment.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ assignment.event_space.name }}</TableCell>
                                <TableCell>
                                    {{ new Date(assignment.start_date).toLocaleDateString() }}
                                    -
                                    {{ new Date(assignment.end_date).toLocaleDateString() }}
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
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div v-else class="rounded-lg border p-8 text-center">
                    <p class="text-sm text-muted-foreground">No past assignments</p>
                </div>
            </div>
        </div>

        <!-- Leave Adjustment Modal -->
        <Dialog v-model:open="isAdjustLeaveOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Adjust Leave for {{ staff.user.name }}</DialogTitle>
                    <DialogDescription>
                        Update leave balances and notes for this staff member.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitLeaveAdjustment" class="space-y-6">
                    <!-- Annual Leave -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h4 class="font-medium">Annual Leave</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="annual_total">Total Days</Label>
                                <Input
                                    id="annual_total"
                                    v-model.number="leaveForm.annual_leave_total"
                                    type="number"
                                    min="0"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="annual_used">Used Days</Label>
                                <Input
                                    id="annual_used"
                                    v-model.number="leaveForm.annual_leave_used"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <p v-if="leaveForm.errors.annual_leave_used" class="text-sm text-destructive">
                                    {{ leaveForm.errors.annual_leave_used }}
                                </p>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Remaining: {{ Math.max(0, leaveForm.annual_leave_total - leaveForm.annual_leave_used) }} days
                        </p>
                    </div>

                    <!-- Sick Leave -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h4 class="font-medium">Sick Leave</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="sick_total">Total Days</Label>
                                <Input
                                    id="sick_total"
                                    v-model.number="leaveForm.sick_leave_total"
                                    type="number"
                                    min="0"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="sick_used">Used Days</Label>
                                <Input
                                    id="sick_used"
                                    v-model.number="leaveForm.sick_leave_used"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <p v-if="leaveForm.errors.sick_leave_used" class="text-sm text-destructive">
                                    {{ leaveForm.errors.sick_leave_used }}
                                </p>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Remaining: {{ Math.max(0, leaveForm.sick_leave_total - leaveForm.sick_leave_used) }} days
                        </p>
                    </div>

                    <!-- Emergency Leave -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h4 class="font-medium">Emergency Leave</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="emergency_total">Total Days</Label>
                                <Input
                                    id="emergency_total"
                                    v-model.number="leaveForm.emergency_leave_total"
                                    type="number"
                                    min="0"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="emergency_used">Used Days</Label>
                                <Input
                                    id="emergency_used"
                                    v-model.number="leaveForm.emergency_leave_used"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <p v-if="leaveForm.errors.emergency_leave_used" class="text-sm text-destructive">
                                    {{ leaveForm.errors.emergency_leave_used }}
                                </p>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Remaining: {{ Math.max(0, leaveForm.emergency_leave_total - leaveForm.emergency_leave_used) }} days
                        </p>
                    </div>

                    <!-- Leave Notes -->
                    <div class="space-y-2">
                        <Label for="leave_notes">Admin Notes</Label>
                        <Textarea
                            id="leave_notes"
                            v-model="leaveForm.leave_notes"
                            rows="3"
                            placeholder="Add notes about leave adjustments, special circumstances, etc."
                        />
                        <p v-if="leaveForm.errors.leave_notes" class="text-sm text-destructive">
                            {{ leaveForm.errors.leave_notes }}
                        </p>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isAdjustLeaveOpen = false">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="leaveForm.processing">
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
