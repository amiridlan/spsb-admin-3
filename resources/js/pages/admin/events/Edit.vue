<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import DatePicker from '@/components/DatePicker.vue';
import TimePicker from '@/components/TimePicker.vue';
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
import { useForm, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

interface EventSpace {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface AssignedStaff {
    id: number;
    user: User;
    position: string | null;
}

interface Staff {
    id: number;
    user: User;
    position: string | null;
}

interface Event {
    id: number;
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
    staff: AssignedStaff[];
}

interface Props {
    event: Event;
    spaces: EventSpace[];
    staff: Staff[];
}

const props = defineProps<Props>();

// Initialize form with properly formatted data
const form = useForm({
    event_space_id: String(props.event.event_space_id),
    title: props.event.title,
    description: props.event.description || '',
    client_name: props.event.client_name,
    client_email: props.event.client_email,
    client_phone: props.event.client_phone || '',
    start_date: props.event.start_date, // Already in YYYY-MM-DD format from backend
    end_date: props.event.end_date, // Already in YYYY-MM-DD format from backend
    start_time: props.event.start_time || null, // null instead of empty string
    end_time: props.event.end_time || null, // null instead of empty string
    status: props.event.status,
    notes: props.event.notes || '',
    staff_ids: props.event.staff.map(s => s.id),
});

const submitForm = () => {
    // Convert event_space_id back to number for backend
    // Ensure null times are sent as null, not undefined or empty string
    const formData = {
        ...form.data(),
        event_space_id: Number(form.event_space_id),
        start_time: form.start_time || null,
        end_time: form.end_time || null,
    };

    // DEBUG: Check staff_ids
    console.log('📋 Staff IDs being sent:', formData.staff_ids);
    console.log('📋 Full form data:', formData);

    form.transform(() => formData).put(`/admin/events/${props.event.id}`);
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
    { title: 'Edit', href: `/admin/events/${props.event.id}/edit` },
];
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
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="event_space_id">Event Space *</Label>
                            <Select v-model="form.event_space_id">
                                <SelectTrigger>
                                    <SelectValue>
                                        {{ spaces.find(s => s.id === Number(form.event_space_id))?.name || 'Select event space' }}
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="space in spaces"
                                        :key="space.id"
                                        :value="String(space.id)"
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
                                <DatePicker
                                    v-model="form.start_date"
                                    placeholder="Select start date"
                                />
                                <InputError :message="form.errors.start_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_date">End Date *</Label>
                                <DatePicker
                                    v-model="form.end_date"
                                    placeholder="Select end date"
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_time">Start Time</Label>
                                <TimePicker
                                    v-model="form.start_time"
                                    placeholder="Select start time"
                                />
                                <InputError :message="form.errors.start_time" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_time">End Time</Label>
                                <TimePicker
                                    v-model="form.end_time"
                                    placeholder="Select end time"
                                />
                                <InputError :message="form.errors.end_time" />
                            </div>
                        </div>
                    </div>

                    <!-- Staff Assignment Section -->
                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Staff Assignment</h3>
                        <p class="text-sm text-muted-foreground">
                            Click to select staff members to assign to this event
                        </p>

                        <div v-if="staff.length > 0" class="grid gap-2">
                            <button
                                v-for="staffMember in staff"
                                :key="staffMember.id"
                                type="button"
                                @click="toggleStaff(staffMember.id)"
                                :class="cn(
                                    'flex items-center gap-3 rounded-lg border p-4 text-left transition-all',
                                    form.staff_ids.includes(staffMember.id)
                                        ? 'border-primary bg-primary/5 ring-2 ring-primary'
                                        : 'border-border hover:border-primary/50 hover:bg-accent'
                                )"
                            >
                                <div
                                    :class="cn(
                                        'flex h-5 w-5 items-center justify-center rounded-full border-2 transition-colors',
                                        form.staff_ids.includes(staffMember.id)
                                            ? 'border-primary bg-primary'
                                            : 'border-muted-foreground'
                                    )"
                                >
                                    <svg
                                        v-if="form.staff_ids.includes(staffMember.id)"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="12"
                                        height="12"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="text-primary-foreground"
                                    >
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ staffMember.user.name }}</div>
                                    <div v-if="staffMember.position" class="text-xs text-muted-foreground">
                                        {{ staffMember.position }}
                                    </div>
                                </div>
                            </button>
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
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue>
                                        {{ form.status.charAt(0).toUpperCase() + form.status.slice(1) }}
                                    </SelectValue>
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
            </div>
        </div>
    </AppLayout>
</template>
