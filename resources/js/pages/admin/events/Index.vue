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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreVertical, Plus, Eye, Pencil, Trash2, Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface EventSpace {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

interface Staff {
    id: number;
    user: User;
}

interface Event {
    id: number;
    title: string;
    client_name: string;
    start_date: string;
    end_date: string;
    status: 'pending' | 'confirmed' | 'completed' | 'cancelled';
    event_space: EventSpace;
    staff: Staff[];
}

interface Props {
    events: {
        data: Event[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status?: string;
        space?: number;
    };
    spaces: EventSpace[];
}

const props = defineProps<Props>();

// Use a special string constant for "all" filters
const ALL_STATUS = '__all_status__';
const ALL_SPACE = '__all_space__';

const statusFilter = ref(props.filters.status || ALL_STATUS);
const spaceFilter = ref(props.filters.space?.toString() || ALL_SPACE);

watch([statusFilter, spaceFilter], ([status, space]) => {
    const params: Record<string, string> = {};

    // Only add to params if it's not the "all" option
    if (status && status !== ALL_STATUS) {
        params.status = status;
    }
    if (space && space !== ALL_SPACE) {
        params.space = space;
    }

    router.get('/admin/events', params, {
        preserveState: true,
        preserveScroll: true,
    });
});

const getStatusVariant = (status: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        pending: 'outline',
        confirmed: 'default',
        completed: 'secondary',
        cancelled: 'destructive',
    };
    return variants[status] || 'outline';
};

const deleteEvent = (eventId: number) => {
    if (confirm('Are you sure you want to delete this event?')) {
        router.delete(`/admin/events/${eventId}`);
    }
};

const getStaffNames = (staff: Staff[]): string => {
    if (!staff || staff.length === 0) return 'No staff assigned';
    if (staff.length === 1) return staff[0].user.name;
    if (staff.length === 2) return `${staff[0].user.name}, ${staff[1].user.name}`;
    return `${staff[0].user.name} +${staff.length - 1} more`;
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events', href: '/admin/events' },
];
</script>

<template>
    <Head title="Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Events</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage all event bookings
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/events/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Event
                    </Link>
                </Button>
            </div>

            <div class="flex gap-3">
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="ALL_STATUS">All Statuses</SelectItem>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="confirmed">Confirmed</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="spaceFilter">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filter by space" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="ALL_SPACE">All Spaces</SelectItem>
                        <SelectItem
                            v-for="space in spaces"
                            :key="space.id"
                            :value="space.id.toString()"
                        >
                            {{ space.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Event</TableHead>
                            <TableHead>Client</TableHead>
                            <TableHead>Space</TableHead>
                            <TableHead>Dates</TableHead>
                            <TableHead>Staff</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[70px]"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="event in events.data" :key="event.id">
                            <TableCell class="font-medium">
                                {{ event.title }}
                            </TableCell>
                            <TableCell>{{ event.client_name }}</TableCell>
                            <TableCell>{{ event.event_space.name }}</TableCell>
                            <TableCell>
                                {{ new Date(event.start_date).toLocaleDateString() }}
                                -
                                {{ new Date(event.end_date).toLocaleDateString() }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1 text-sm">
                                    <Users class="h-3 w-3 text-muted-foreground" />
                                    <span class="text-muted-foreground">
                                        {{ getStaffNames(event.staff) }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(event.status)">
                                    {{ event.status }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon">
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/admin/events/${event.id}`"
                                                class="flex cursor-pointer items-center"
                                            >
                                                <Eye class="mr-2 h-4 w-4" />
                                                View
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="`/admin/events/${event.id}/edit`"
                                                class="flex cursor-pointer items-center"
                                            >
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="deleteEvent(event.id)"
                                            class="cursor-pointer text-destructive"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div
                v-if="events.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ (events.current_page - 1) * events.per_page + 1 }}
                    to
                    {{
                        Math.min(
                            events.current_page * events.per_page,
                            events.total,
                        )
                    }}
                    of {{ events.total }} events
                </p>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="events.current_page === 1"
                        @click="
                            router.get(
                                `/admin/events?page=${events.current_page - 1}`,
                            )
                        "
                    >
                        Previous
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="events.current_page === events.last_page"
                        @click="
                            router.get(
                                `/admin/events?page=${events.current_page + 1}`,
                            )
                        "
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
