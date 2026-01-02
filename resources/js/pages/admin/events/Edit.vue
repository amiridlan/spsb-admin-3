<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
<<<<<<< HEAD
<<<<<<< HEAD
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
    DialogTrigger,
} from '@/components/ui/dialog';
import { useForm, Head, router } from '@inertiajs/vue3';
import { ArrowLeft, UserPlus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
=======
import { useForm, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { watch } from 'vue';
>>>>>>> parent of bcd2403 (push for reference cc)
=======
import { useForm, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { watch } from 'vue';
>>>>>>> parent of bcd2403 (push for reference cc)

interface EventSpace {
    id: number;
    name: string;
}

<<<<<<< HEAD
<<<<<<< HEAD
interface User {
    id: number;
    name: string;
    email: string;
}

interface AssignedStaff {
    id: number;
    user: User;
    position: string | null;
    pivot: {
        role: string | null;
        notes: string | null;
    };
}

interface StaffAvailability {
    staff_id: number;
    staff_name: string;
    position: string | null;
    specializations: string[] | null;
    is_available: boolean;
    is_assigned: boolean;
}

interface Event {
    id: number;
=======
interface Event {
    id: number;
>>>>>>> parent of bcd2403 (push for reference cc)
=======
interface Event {
    id: number;
>>>>>>> parent of bcd2403 (push for reference cc)
    event_space_id: number;
    title: string;
    description: string | null;
    client_name: string;
    client_email: string;
    client_phone: string | null;
    start_date: string;
    end_date: string;
    start_time: string | null;
    end_time: string | null;
    status: string;
    notes: string | null;
    event_space: EventSpace;
}

interface Props {
    event: Event;
    spaces: EventSpace[];
<<<<<<< HEAD
<<<<<<< HEAD
    assignedStaff?: AssignedStaff[];
    availableStaff?: StaffAvailability[];
=======
>>>>>>> parent of bcd2403 (push for reference cc)
=======
>>>>>>> parent of bcd2403 (push for reference cc)
}

const props = defineProps<Props>();

<<<<<<< HEAD
<<<<<<< HEAD
=======
console.log('Event data:', props.event);

>>>>>>> parent of bcd2403 (push for reference cc)
=======
console.log('Event data:', props.event);

>>>>>>> parent of bcd2403 (push for reference cc)
// Initialize form with properly formatted data
const form = useForm({
    event_space_id: props.event.event_space_id,
    title: props.event.title,
    description: props.event.description || '',
    client_name: props.event.client_name,
    client_email: props.event.client_email,
    client_phone: props.event.client_phone || '',
    start_date: props.event.start_date,
    end_date: props.event.end_date,
    start_time: props.event.start_time || '',
    end_time: props.event.end_time || '',
    status: props.event.status,
    notes: props.event.notes || '',
});

<<<<<<< HEAD
<<<<<<< HEAD
const isAssignDialogOpen = ref(false);

const assignForm = useForm({
    staff_id: null as number | null,
    role: '',
    notes: '',
});

const submitForm = () => {
    form.put(`/admin/events/${props.event.id}`);
};

const submitAssignment = () => {
    assignForm.post(`/admin/events/${props.event.id}/staff`, {
        preserveScroll: true,
        onSuccess: () => {
            assignForm.reset();
            isAssignDialogOpen.value = false;
        },
    });
};

const removeStaff = (staffId: number) => {
    if (confirm('Are you sure you want to remove this staff member from the event?')) {
        router.delete(`/admin/events/${props.event.id}/staff/${staffId}`);
    }
};

const availableStaffOptions = computed(() => {
    if (!props.availableStaff) return [];
    return props.availableStaff.filter(s => s.is_available && !s.is_assigned);
});

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events', href: '/admin/events' },
    { title: 'Edit', href: `/admin/events/${props.event.id}/edit` },
]);
=======
=======
>>>>>>> parent of bcd2403 (push for reference cc)
// Debug: Watch form values
watch(() => form.start_date, (val) => {
    console.log('start_date changed:', val);
});

watch(() => form.end_date, (val) => {
    console.log('end_date changed:', val);
});

const submitForm = () => {
    console.log('Submitting form with data:', form.data());
    form.put(`/admin/events/${props.event.id}`);
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events', href: '/admin/events' },
    { title: 'Edit', href: `/admin/events/${props.event.id}/edit` },
];
<<<<<<< HEAD
>>>>>>> parent of bcd2403 (push for reference cc)
=======
>>>>>>> parent of bcd2403 (push for reference cc)
</script>

<template>
    <Head :title="`Edit ${event.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/events')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Edit Event</h1>
                    <p class="text-sm text-muted-foreground">
                        Update event information
                    </p>
                </div>
            </div>

<<<<<<< HEAD
            <div class="max-w-4xl space-y-6">
                <!-- Event Details Form -->
=======
            <div class="max-w-2xl">
<<<<<<< HEAD
>>>>>>> parent of bcd2403 (push for reference cc)
=======
>>>>>>> parent of bcd2403 (push for reference cc)
                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Event Details</h3>

                        <div class="grid gap-2">
                            <Label for="title">Event Title *</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="event_space_id">Event Space *</Label>
                            <Select v-model="form.event_space_id">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="space in spaces"
                                        :key="space.id"
                                        :value="space.id"
                                    >
                                        {{ space.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.event_space_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                            />
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Client Information</h3>

                        <div class="grid gap-2">
                            <Label for="client_name">Client Name *</Label>
                            <Input
                                id="client_name"
                                v-model="form.client_name"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.client_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="client_email">Client Email *</Label>
                            <Input
                                id="client_email"
                                v-model="form.client_email"
                                type="email"
                                required
                            />
                            <InputError :message="form.errors.client_email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="client_phone">Client Phone</Label>
                            <Input
                                id="client_phone"
                                v-model="form.client_phone"
                                type="tel"
                            />
                            <InputError :message="form.errors.client_phone" />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Schedule</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_date">Start Date *</Label>
                                <Input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    required
                                />
                                <InputError :message="form.errors.start_date" />
                                <p class="text-xs text-muted-foreground">Current: {{ form.start_date }}</p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_date">End Date *</Label>
                                <Input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="date"
                                    required
                                />
                                <InputError :message="form.errors.end_date" />
                                <p class="text-xs text-muted-foreground">Current: {{ form.end_date }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_time">Start Time</Label>
                                <Input
                                    id="start_time"
                                    v-model="form.start_time"
                                    type="time"
                                />
                                <InputError :message="form.errors.start_time" />
                                <p class="text-xs text-muted-foreground">Current: {{ form.start_time || 'None' }}</p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_time">End Time</Label>
                                <Input
                                    id="end_time"
                                    v-model="form.end_time"
                                    type="time"
                                />
                                <InputError :message="form.errors.end_time" />
                                <p class="text-xs text-muted-foreground">Current: {{ form.end_time || 'None' }}</p>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="confirmed">Confirmed</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Update Event
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/admin/events')"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>

                <!-- Staff Assignment Section -->
                <div v-if="assignedStaff || availableStaff" class="space-y-4 rounded-lg border p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium">Staff Assignment</h3>
                        <Dialog v-model:open="isAssignDialogOpen">
                            <DialogTrigger as-child>
                                <Button size="sm">
                                    <UserPlus class="mr-2 h-4 w-4" />
                                    Assign Staff
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <form @submit.prevent="submitAssignment">
                                    <DialogHeader>
                                        <DialogTitle>Assign Staff Member</DialogTitle>
                                        <DialogDescription>
                                            Add a staff member to this event
                                        </DialogDescription>
                                    </DialogHeader>

                                    <div class="grid gap-4 py-4">
                                        <div class="grid gap-2">
                                            <Label for="staff_id">Staff Member *</Label>
                                            <Select v-model="assignForm.staff_id" required>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select staff" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="staff in availableStaffOptions"
                                                        :key="staff.staff_id"
                                                        :value="staff.staff_id"
                                                    >
                                                        {{ staff.staff_name }}
                                                        <span v-if="staff.position" class="text-muted-foreground">
                                                            ({{ staff.position }})
                                                        </span>
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError :message="assignForm.errors.staff_id" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="role">Role</Label>
                                            <Input
                                                id="role"
                                                v-model="assignForm.role"
                                                placeholder="Event Coordinator"
                                            />
                                            <InputError :message="assignForm.errors.role" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="assignment_notes">Notes</Label>
                                            <Textarea
                                                id="assignment_notes"
                                                v-model="assignForm.notes"
                                                rows="3"
                                                placeholder="Additional notes..."
                                            />
                                            <InputError :message="assignForm.errors.notes" />
                                        </div>
                                    </div>

                                    <DialogFooter>
                                        <Button type="submit" :disabled="assignForm.processing">
                                            Assign Staff
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <!-- Assigned Staff Table -->
                    <div v-if="assignedStaff && assignedStaff.length" class="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Notes</TableHead>
                                    <TableHead class="w-[70px]"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="staff in assignedStaff" :key="staff.id">
                                    <TableCell class="font-medium">
                                        {{ staff.user.name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ staff.position || 'N/A' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="staff.pivot.role" variant="outline">
                                            {{ staff.pivot.role }}
                                        </Badge>
                                        <span v-else class="text-sm text-muted-foreground">N/A</span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="staff.pivot.notes" class="text-sm">
                                            {{ staff.pivot.notes }}
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">N/A</span>
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="removeStaff(staff.id)"
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div v-else class="rounded-lg border p-8 text-center">
                        <p class="text-sm text-muted-foreground">No staff assigned yet</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
