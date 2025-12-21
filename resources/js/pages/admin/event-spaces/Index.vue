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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreVertical, Plus, Pencil, Trash2 } from 'lucide-vue-next';

interface EventSpace {
    id: number;
    name: string;
    description: string | null;
    capacity: number | null;
    is_active: boolean;
    events_count: number;
    created_at: string;
}

interface Props {
    spaces: {
        data: EventSpace[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

defineProps<Props>();

const deleteSpace = (spaceId: number) => {
    if (confirm('Are you sure you want to delete this event space?')) {
        router.delete(`/admin/event-spaces/${spaceId}`);
    }
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Event Spaces', href: '/admin/event-spaces' },
];
</script>

<template>
    <Head title="Event Spaces" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Event Spaces</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your bookable event spaces
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/event-spaces/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Space
                    </Link>
                </Button>
            </div>

            <div class="rounded-lg border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Capacity</TableHead>
                            <TableHead>Events</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="w-[70px]"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="space in spaces.data" :key="space.id">
                            <TableCell class="font-medium">
                                {{ space.name }}
                            </TableCell>
                            <TableCell class="max-w-md truncate">
                                {{ space.description || 'N/A' }}
                            </TableCell>
                            <TableCell>
                                {{ space.capacity || 'N/A' }}
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline">
                                    {{ space.events_count }} events
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        space.is_active ? 'default' : 'outline'
                                    "
                                >
                                    {{ space.is_active ? 'Active' : 'Inactive' }}
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
                                                :href="`/admin/event-spaces/${space.id}/edit`"
                                                class="flex cursor-pointer items-center"
                                            >
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @click="deleteSpace(space.id)"
                                            class="cursor-pointer text-destructive"
                                            :disabled="space.events_count > 0"
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
        </div>
    </AppLayout>
</template>
