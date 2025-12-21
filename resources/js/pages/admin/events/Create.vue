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
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface EventSpace {
    id: number;
    name: string;
}

interface Props {
    spaces: EventSpace[];
}

defineProps<Props>();

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
                <Form
                    action="/admin/events"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Event Details</h3>

                        <div class="grid gap-2">
                            <Label for="title">Event Title *</Label>
                            <Input
                                id="title"
                                name="title"
                                type="text"
                                required
                                autofocus
                                placeholder="Birthday Party"
                            />
                            <InputError :message="errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="event_space_id">Event Space *</Label>
                            <Select name="event_space_id" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select space" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="space in spaces"
                                        :key="space.id"
                                        :value="space.id.toString()"
                                    >
                                        {{ space.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.event_space_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="Event details..."
                            />
                            <InputError :message="errors.description" />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Client Information</h3>

                        <div class="grid gap-2">
                            <Label for="client_name">Client Name *</Label>
                            <Input
                                id="client_name"
                                name="client_name"
                                type="text"
                                required
                                placeholder="John Doe"
                            />
                            <InputError :message="errors.client_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="client_email">Client Email *</Label>
                            <Input
                                id="client_email"
                                name="client_email"
                                type="email"
                                required
                                placeholder="john@example.com"
                            />
                            <InputError :message="errors.client_email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="client_phone">Client Phone</Label>
                            <Input
                                id="client_phone"
                                name="client_phone"
                                type="tel"
                                placeholder="+1234567890"
                            />
                            <InputError :message="errors.client_phone" />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg border p-6">
                        <h3 class="font-medium">Schedule</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_date">Start Date *</Label>
                                <Input
                                    id="start_date"
                                    name="start_date"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.start_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_date">End Date *</Label>
                                <Input
                                    id="end_date"
                                    name="end_date"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.end_date" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_time">Start Time</Label>
                                <Input
                                    id="start_time"
                                    name="start_time"
                                    type="time"
                                />
                                <InputError :message="errors.start_time" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="end_time">End Time</Label>
                                <Input
                                    id="end_time"
                                    name="end_time"
                                    type="time"
                                />
                                <InputError :message="errors.end_time" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <Select name="status" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="confirmed">Confirmed</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="cancelled">Cancelled</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.status" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                placeholder="Internal notes..."
                            />
                            <InputError :message="errors.notes" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button type="submit" :disabled="processing">
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
                </Form>
            </div>
        </div>
    </AppLayout>
</template>
