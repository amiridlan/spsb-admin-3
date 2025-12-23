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
import { Head, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, UserPlus } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface EventSpace {
    id: number;
    name: string;
}

interface Event {
    id: number;
    title: string;
    start_date: string;
    end_date: string;
    event_space: EventSpace;
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

interface Props {
    event: Event;
    assignedStaff: AssignedStaff[];
    availableStaff: StaffAvailability[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events', href: '/admin/events' },
    { title: props.event.title, href: `/admin/events/${props.event.id}` },
    { title: 'Staff Assignment', href: `/admin/events/${props.event.id}/staff` },
];

const isAssignDialogOpen = ref(false);

const assignForm = useForm({
    staff_id: null as number | null,
    role: '',
    notes: '',
});

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

const availableStaffOptions = props.availableStaff.filter(s => s.is_available && !s.is_assigned);
</script>

<template>
    <Head :title="`Staff Assignment - ${event.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit(`/admin/events/${event.id}`)">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Staff Assignment</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage staff assignments for {{ event.title }}
                    </p>
                </div>
            </div>

            <!-- Event Info -->
            <div class="rounded-lg border p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ event.title }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ event.event_space.name }} •
                            {{ new Date(event.start_date).toLocaleDateString() }} -
                            {{ new Date(event.end_date).toLocaleDateString() }}
                        </p>
                    </div>
                    <Dialog v-model:open="isAssignDialogOpen">
                        <DialogTrigger as-child>
                            <Button>
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
                                                    :value="staff.staff_id.toString()"
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
                                        <Label for="notes">Notes</Label>
                                        <Textarea
                                            id="notes"
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
            </div>

            <!-- Assigned Staff -->
            <div class="space-y-4">
                <h3 class="font-semibold">Assigned Staff</h3>
                <div v-if="assignedStaff.length" class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
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
                                <TableCell>{{ staff.user.email }}</TableCell>
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

            <!-- Available Staff -->
            <div class="space-y-4">
                <h3 class="font-semibold">Available Staff</h3>
                <div class="rounded-lg border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Specializations</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="staff in availableStaff" :key="staff.staff_id">
                                <TableCell class="font-medium">
                                    {{ staff.staff_name }}
                                </TableCell>
                                <TableCell>
                                    {{ staff.position || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    <div v-if="staff.specializations && staff.specializations.length" class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="spec in staff.specializations.slice(0, 2)"
                                            :key="spec"
                                            variant="secondary"
                                            class="text-xs"
                                        >
                                            {{ spec }}
                                        </Badge>
                                        <Badge
                                            v-if="staff.specializations.length > 2"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            +{{ staff.specializations.length - 2 }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">N/A</span>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        v-if="staff.is_assigned"
                                        variant="default"
                                    >
                                        Assigned
                                    </Badge>
                                    <Badge
                                        v-else-if="staff.is_available"
                                        variant="outline"
                                    >
                                        Available
                                    </Badge>
                                    <Badge
                                        v-else
                                        variant="destructive"
                                    >
                                        Unavailable
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
