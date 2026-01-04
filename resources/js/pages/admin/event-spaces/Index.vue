<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreVertical, Plus, Pencil, Trash2, MapPin, Users, Building2 } from 'lucide-vue-next';

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
                        Manage your event spaces and venues
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/event-spaces/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Event Space
                    </Link>
                </Button>
            </div>

            <!-- Cards Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="space in spaces.data" :key="space.id" class="overflow-hidden">
                    <!-- Image -->
                    <div class="relative h-48 w-full overflow-hidden bg-muted">
                        <img
                            v-if="space.image"
                            :src="`/storage/${space.image}`"
                            :alt="space.name"
                            class="h-full w-full object-cover transition-transform hover:scale-105"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/10 to-primary/5"
                        >
                            <Building2 class="h-16 w-16 text-muted-foreground/30" />
                        </div>

                        <!-- Status Badge (overlay on image) -->
                        <div class="absolute right-2 top-2">
                            <Badge :variant="space.is_active ? 'default' : 'secondary'">
                                {{ space.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="text-lg">{{ space.name }}</CardTitle>
                                <CardDescription class="mt-1 flex items-center gap-1">
                                    <MapPin class="h-3 w-3" />
                                    {{ space.location }}
                                </CardDescription>
                            </div>

                            <!-- Actions Dropdown -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/admin/event-spaces/${space.id}/edit`">
                                            <Pencil class="mr-2 h-4 w-4" />
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        class="text-destructive focus:text-destructive"
                                        @click="deleteSpace(space.id)"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <p v-if="space.description" class="line-clamp-2 text-sm text-muted-foreground">
                            {{ space.description }}
                        </p>
                        <p v-else class="text-sm italic text-muted-foreground">
                            No description provided
                        </p>
                    </CardContent>

                    <CardFooter class="flex items-center justify-between border-t pt-4">
                        <div class="flex items-center gap-4 text-sm text-muted-foreground">
                            <div v-if="space.capacity" class="flex items-center gap-1">
                                <Users class="h-4 w-4" />
                                <span>{{ space.capacity }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Building2 class="h-4 w-4" />
                                <span>{{ space.events_count }} events</span>
                            </div>
                        </div>

                        <Button variant="outline" size="sm" as-child>
                            <Link :href="`/admin/event-spaces/${space.id}`">
                                View Details
                            </Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State -->
            <div
                v-if="spaces.data.length === 0"
                class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center"
            >
                <Building2 class="mb-4 h-12 w-12 text-muted-foreground/50" />
                <h3 class="mb-2 text-lg font-semibold">No event spaces yet</h3>
                <p class="mb-4 text-sm text-muted-foreground">
                    Get started by creating your first event space
                </p>
                <Button as-child>
                    <Link href="/admin/event-spaces/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Event Space
                    </Link>
                </Button>
            </div>

            <!-- Pagination -->
            <div
                v-if="spaces.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ spaces.data.length }} of {{ spaces.total }} spaces
                </p>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="spaces.current_page === 1"
                        @click="router.visit(`/admin/event-spaces?page=${spaces.current_page - 1}`)"
                    >
                        Previous
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="spaces.current_page === spaces.last_page"
                        @click="router.visit(`/admin/event-spaces?page=${spaces.current_page + 1}`)"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
