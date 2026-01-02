<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface EventSpace {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Staff {
    id: number;
    user: User;
    position: string | null;
}

interface Props {
    spaces: EventSpace[];
    staff: Staff[];
    prefill?: {
        start_date?: string;
        end_date?: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    event_space_id: null as number | null,
    title: '',
    description: '',
    client_name: '',
    client_email: '',
    client_phone: '',
    start_date: props.prefill?.start_date || '',
    end_date: props.prefill?.end_date || '',
    start_time: '',
    end_time: '',
    status: 'pending',
    notes: '',
    staff_ids: [] as number[],
});

const submitForm = () => {
    form.post('/admin/events');
};

const toggleStaff = (staffId: number) => {
    const index = form.staff_ids.indexOf(staffId);
    if (index > -1) {
        form.staff_ids.splice(index, 1);
    } else {
        form.staff_ids.push(staffId);
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events', href: '/admin/events' },
    { title: 'Create', href: '/admin/events/create' },
];
</script>

<template>
    <Head title="Create Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" @click="$inertia.visit('/admin/events')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-semibold">Create Event</h1>
                    <p class="text-sm text-muted-foreground">
                        Add a new event booking
                    </p>
                </div>
            </div>

            <div class="max-w-2xl">
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
                                placeholder="Annual Conference 2026"
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="event_space_id">Event Space *</Label>
                            <Select v-model="form.event_space_id" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select event space" />
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
                                placeholder="Event description..."
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
                                placeholder="John Doe"
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
                                placeholder="john@example.com"
                            />
                            <InputError :message="form.errors.client_email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="client_phone">Client Phone</Label>
                            <Input
                                id="client_phone"
                                v-model="form.client_phone"
                                type="tel"
                                placeholder="+1 (555) 123-4567"
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
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_time">End Time</Label>
                                <Input
                                    id="end_time"
                                    v-model="form.end_time"
                                    type="time"
                                />
                                <InputError :message="form.errors.end_time" />
                            </div>
                        </div>
                    </div>

                    <!-- NEW: Staff Assignment Section -->
                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Staff Assignment</h3>
                        <p class="text-sm text-muted-foreground">
                            Select staff members to assign to this event (optional)
                        </p>

                        <div v-if="staff.length > 0" class="space-y-2">
                            <div
                                v-for="staffMember in staff"
                                :key="staffMember.id"
                                class="flex items-center space-x-2 rounded-lg border p-3 hover:bg-accent"
                            >
                                <Checkbox
                                    :id="`staff-${staffMember.id}`"
                                    :checked="form.staff_ids.includes(staffMember.id)"
                                    @update:checked="toggleStaff(staffMember.id)"
                                />
                                <label
                                    :for="`staff-${staffMember.id}`"
                                    class="flex-1 cursor-pointer text-sm"
                                >
                                    <div class="font-medium">{{ staffMember.user.name }}</div>
                                    <div v-if="staffMember.position" class="text-xs text-muted-foreground">
                                        {{ staffMember.position }}
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div v-else class="text-sm text-muted-foreground">
                            No available staff members
                        </div>
                        <InputError :message="form.errors.staff_ids" />
                    </div>

                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Additional Details</h3>

                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <Select v-model="form.status" required>
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
                                placeholder="Internal notes..."
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="form.processing">
                            Create Event
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
            </div>
        </div>
    </AppLayout>
</template>
