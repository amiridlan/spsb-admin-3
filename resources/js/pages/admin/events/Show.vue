<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Calendar, Clock, User, Mail, Phone, Building2 } from 'lucide-vue-next';

interface EventSpace {
    id: number;
    name: string;
}

interface Creator {
    id: number;
    name: string;
    email: string;
}

interface Event {
    id: number;
    title: string;
    description: string | null;
    client_name: string;
    client_email: string;
    client_phone: string | null;
    start_date: string;
    end_date: string;
    start_time: string | null;
    end_time: string | null;
    status: 'pending' | 'confirmed' | 'completed' | 'cancelled';
    notes: string | null;
    event_space: EventSpace;
    creator: Creator;
    created_at: string;
}

interface Props {
    event: Event;
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

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events', href: '/admin/events' },
    { title: event.title, href: `/admin/events/${event.id}` },
];
</script>

<template>
    <Head :title="event.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="$inertia.visit('/admin/events')">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-2xl font-semibold">{{ event.title }}</h1>
                        <p class="text-sm text-muted-foreground">
                            Event details and information
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/admin/events/${event.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Event
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4 rounded-lg border p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium">Event Information</h3>
                        <Badge :variant="getStatusVariant(event.status)">
                            {{ event.status }}
                        </Badge>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <Building2 class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Event Space</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ event.event_space.name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Calendar class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Date Range</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(event.start_date).toLocaleDateString() }}
                                    -
                                    {{ new Date(event.end_date).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>

                        <div v-if="event.start_time || event.end_time" class="flex items-start gap-3">
                            <Clock class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Time</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ event.start_time || 'N/A' }} - {{ event.end_time || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="event.description">
                            <p class="text-sm font-medium">Description</p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ event.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">Client Information</h3>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Name</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ event.client_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Mail class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Email</p>

                                    :href="`mailto:${event.client_email}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ event.client_email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="event.client_phone" class="flex items-start gap-3">
                            <Phone class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Phone</p>

                                    :href="`tel:${event.client_phone}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ event.client_phone }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="event.notes" class="space-y-4 rounded-lg border p-6 md:col-span-2">
                    <h3 class="font-medium">Internal Notes</h3>
                    <p class="text-sm text-muted-foreground whitespace-pre-wrap">
                        {{ event.notes }}
                    </p>
                </div>

                <div class="space-y-4 rounded-lg border p-6 md:col-span-2">
                    <h3 class="font-medium">Metadata</h3>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium">Created By</p>
                            <p class="text-sm text-muted-foreground">
                                {{ event.creator.name }} ({{ event.creator.email }})
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium">Created At</p>
                            <p class="text-sm text-muted-foreground">
                                {{ new Date(event.created_at).toLocaleString() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
