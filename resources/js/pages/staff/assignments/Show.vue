<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, User, Mail, Phone, Building2, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface EventSpace {
    id: number;
    name: string;
}

interface StaffMember {
    id: number;
    user: User;
    pivot: {
        role: string | null;
    };
}

interface Assignment {
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
    staff: StaffMember[];
}

interface AssignmentDetails {
    role: string | null;
    notes: string | null;
}

interface Staff {
    id: number;
    user: User;
}

interface Props {
    assignment: Assignment;
    assignmentDetails: AssignmentDetails;
    staff: Staff;
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

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'My Assignments', href: '/staff/assignments' },
    { title: props.assignment.title, href: `/staff/assignments/${props.assignment.id}` },
]);
</script>

<template>
    <Head :title="assignment.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" @click="$inertia.visit('/staff/assignments')">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-2xl font-semibold">{{ assignment.title }}</h1>
                        <p class="text-sm text-muted-foreground">
                            Assignment details
                        </p>
                    </div>
                </div>
                <Badge :variant="getStatusVariant(assignment.status)">
                    {{ assignment.status }}
                </Badge>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Event Information -->
                <div class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">Event Information</h3>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <Building2 class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Event Space</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ assignment.event_space.name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Calendar class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Date Range</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(assignment.start_date).toLocaleDateString() }}
                                    -
                                    {{ new Date(assignment.end_date).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>

                        <div v-if="assignment.start_time || assignment.end_time" class="flex items-start gap-3">
                            <Clock class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Time</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ assignment.start_time || 'N/A' }} - {{ assignment.end_time || 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="assignment.description">
                            <p class="text-sm font-medium">Description</p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ assignment.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">Client Information</h3>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <User class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Name</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ assignment.client_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Mail class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Email</p>

                                    <a :href="`mailto:${assignment.client_email}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ assignment.client_email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="assignment.client_phone" class="flex items-start gap-3">
                            <Phone class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium">Phone</p>

                                    <a :href="`tel:${assignment.client_phone}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ assignment.client_phone }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Assignment Details -->
                <div class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">My Assignment</h3>

                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium">My Role</p>
                            <Badge v-if="assignmentDetails.role" variant="outline" class="mt-1">
                                {{ assignmentDetails.role }}
                            </Badge>
                            <p v-else class="text-sm text-muted-foreground">No specific role assigned</p>
                        </div>

                        <div v-if="assignmentDetails.notes">
                            <p class="text-sm font-medium">Assignment Notes</p>
                            <p class="mt-1 text-sm text-muted-foreground whitespace-pre-wrap">
                                {{ assignmentDetails.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Other Staff Members -->
                <div class="space-y-4 rounded-lg border p-6">
                    <h3 class="font-medium">
                        <Users class="mr-2 inline h-4 w-4" />
                        Other Staff Members
                    </h3>

                    <div v-if="assignment.staff.length > 1" class="space-y-2">
                        <div
                            v-for="member in assignment.staff.filter(s => s.id !== staff.id)"
                            :key="member.id"
                            class="flex items-center justify-between rounded-md border p-3"
                        >
                            <div>
                                <p class="text-sm font-medium">{{ member.user.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ member.user.email }}</p>
                            </div>
                            <Badge v-if="member.pivot.role" variant="outline" class="text-xs">
                                {{ member.pivot.role }}
                            </Badge>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        You are the only staff member assigned to this event
                    </p>
                </div>

                <!-- Event Notes (if any) -->
                <div v-if="assignment.notes" class="space-y-4 rounded-lg border p-6 md:col-span-2">
                    <h3 class="font-medium">Event Notes</h3>
                    <p class="text-sm text-muted-foreground whitespace-pre-wrap">
                        {{ assignment.notes }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
