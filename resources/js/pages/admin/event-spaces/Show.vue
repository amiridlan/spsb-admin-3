<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, MapPin, Users, Building2, Calendar, CheckCircle, XCircle } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    client_name: string;
    start_date: string;
    end_date: string;
    status: 'pending' | 'confirmed' | 'completed' | 'cancelled';
}

interface EventSpace {
    id: number;
    name: string;
    location: string;
    description: string | null;
    capacity: number | null;
    image: string | null;
    is_active: boolean;
    events_count: number;
    created_at: string;
    events?: Event[];
}

interface Props {
    space: EventSpace;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Event Spaces', href: '/admin/event-spaces' },
    { title: props.space.name, href: `/admin/event-spaces/${props.space.id}` },
];

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        confirmed: 'default',
        completed: 'secondary',
        cancelled: 'destructive',
    };
    return variants[status] || 'outline';
};
</script>

<template>
    <Head :title="space.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="$inertia.visit('/admin/event-spaces')">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-semibold">{{ space.name }}</h1>
                            <Badge :variant="space.is_active ? 'default' : 'secondary'">
                                {{ space.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Event space details and bookings
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/admin/event-spaces/${space.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Space
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content (Left) -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Image -->
                    <Card v-if="space.image" class="overflow-hidden">
                        <div class="relative h-64 w-full overflow-hidden bg-muted">
                            <img
                                :src="`/storage/${space.image}`"
                                :alt="space.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </Card>

                    <!-- Description -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Description</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p v-if="space.description" class="text-sm text-muted-foreground whitespace-pre-wrap">
                                {{ space.description }}
                            </p>
                            <p v-else class="text-sm italic text-muted-foreground">
                                No description provided
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Recent Events -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Recent Events</CardTitle>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/admin/events?space=${space.id}`">
                                        View All
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="space.events && space.events.length > 0" class="rounded-lg border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Event</TableHead>
                                            <TableHead>Client</TableHead>
                                            <TableHead>Dates</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead class="w-[70px]"></TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="event in space.events" :key="event.id">
                                            <TableCell class="font-medium">
                                                {{ event.title }}
                                            </TableCell>
                                            <TableCell>{{ event.client_name }}</TableCell>
                                            <TableCell class="text-sm">
                                                <template v-if="event.start_date === event.end_date">
                                                    {{ new Date(event.start_date).toLocaleDateString() }}
                                                </template>
                                                <template v-else>
                                                    {{ new Date(event.start_date).toLocaleDateString() }}
                                                    -
                                                    {{ new Date(event.end_date).toLocaleDateString() }}
                                                </template>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="getStatusVariant(event.status)">
                                                    {{ event.status }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Button variant="ghost" size="sm" as-child>
                                                    <Link :href="`/admin/events/${event.id}`">
                                                        View
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="rounded-lg border p-8 text-center">
                                <Calendar class="mx-auto mb-2 h-8 w-8 text-muted-foreground/50" />
                                <p class="text-sm text-muted-foreground">No events booked yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar (Right) -->
                <div class="space-y-6">
                    <!-- Details Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Location -->
                            <div class="flex items-start gap-3">
                                <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Location</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ space.location }}
                                    </p>
                                </div>
                            </div>

                            <!-- Capacity -->
                            <div v-if="space.capacity" class="flex items-start gap-3">
                                <Users class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Capacity</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ space.capacity }} people
                                    </p>
                                </div>
                            </div>

                            <!-- Total Events -->
                            <div class="flex items-start gap-3">
                                <Building2 class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Total Events</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ space.events_count }} bookings
                                    </p>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex items-start gap-3">
                                <component
                                    :is="space.is_active ? CheckCircle : XCircle"
                                    class="mt-0.5 h-4 w-4 text-muted-foreground"
                                />
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Availability</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ space.is_active ? 'Available for booking' : 'Not available' }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Metadata</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <p class="text-sm font-medium">Created</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(space.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Space ID</p>
                                <p class="text-sm text-muted-foreground font-mono">
                                    {{ space.id }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" class="w-full justify-start" as-child>
                                <Link :href="`/admin/events/create?space=${space.id}`">
                                    <Calendar class="mr-2 h-4 w-4" />
                                    Create Event Here
                                </Link>
                            </Button>
                            <Button variant="outline" class="w-full justify-start" as-child>
                                <Link :href="`/admin/events?space=${space.id}`">
                                    <Building2 class="mr-2 h-4 w-4" />
                                    View All Events
                                </Link>
                            </Button>
                            <Button variant="outline" class="w-full justify-start" as-child>
                                <Link :href="`/admin/event-spaces/${space.id}/edit`">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Edit Space
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
