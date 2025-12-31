<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
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
import { watch } from 'vue';

interface EventSpace {
    id: number;
    name: string;
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
}

interface Props {
    event: Event;
    spaces: EventSpace[];
}

const props = defineProps<Props>();

console.log('Event data:', props.event);

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
            </div>
        </div>
    </AppLayout>
</template>
