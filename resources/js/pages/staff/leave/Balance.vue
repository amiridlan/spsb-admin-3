<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar as CalendarIcon, Plus, User } from 'lucide-vue-next';

interface LeaveBalances {
    annual: {
        total: number;
        used: number;
        remaining: number;
    };
    sick: {
        total: number;
        used: number;
        remaining: number;
    };
    emergency: {
        total: number;
        used: number;
        remaining: number;
    };
}

interface Staff {
    id: number;
    position: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Props {
    leaveBalances: LeaveBalances;
    staff: Staff;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Leave Balance', href: '/staff/leave/balance' },
];

const getProgressColor = (used: number, total: number) => {
    const percentage = (used / total) * 100;
    if (percentage >= 80) return 'bg-red-500';
    if (percentage >= 60) return 'bg-orange-500';
    return 'bg-blue-500';
};
</script>

<template>
    <Head title="Leave Balance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Leave Balance</h1>
                    <p class="text-sm text-muted-foreground">
                        View your current leave balances
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button as-child>
                        <Link href="/staff/leave/requests/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Request Leave
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/staff/leave/requests">
                            View Requests
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Staff Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Staff Information
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm text-muted-foreground">Name</p>
                        <p class="mt-1 font-medium">{{ staff.user.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Position</p>
                        <p class="mt-1 font-medium">{{ staff.position || 'N/A' }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Leave Balances -->
            <div class="grid gap-6 md:grid-cols-3">
                <!-- Annual Leave -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Annual Leave</CardTitle>
                            <CalendarIcon class="h-5 w-5 text-blue-500" />
                        </div>
                        <CardDescription>Your annual leave allocation</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-blue-600">
                                    {{ leaveBalances.annual.remaining }}
                                </p>
                                <p class="text-sm text-muted-foreground">days remaining</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ leaveBalances.annual.used }} used</p>
                                <p class="text-sm text-muted-foreground">of {{ leaveBalances.annual.total }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Usage</span>
                                <span class="font-medium">
                                    {{ Math.round((leaveBalances.annual.used / leaveBalances.annual.total) * 100) }}%
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full bg-blue-500 transition-all"
                                    :style="{ width: `${(leaveBalances.annual.used / leaveBalances.annual.total) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sick Leave -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Sick Leave</CardTitle>
                            <CalendarIcon class="h-5 w-5 text-red-500" />
                        </div>
                        <CardDescription>Your sick leave allocation</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-red-600">
                                    {{ leaveBalances.sick.remaining }}
                                </p>
                                <p class="text-sm text-muted-foreground">days remaining</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ leaveBalances.sick.used }} used</p>
                                <p class="text-sm text-muted-foreground">of {{ leaveBalances.sick.total }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Usage</span>
                                <span class="font-medium">
                                    {{ Math.round((leaveBalances.sick.used / leaveBalances.sick.total) * 100) }}%
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full bg-red-500 transition-all"
                                    :style="{ width: `${(leaveBalances.sick.used / leaveBalances.sick.total) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Emergency Leave -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Emergency Leave</CardTitle>
                            <CalendarIcon class="h-5 w-5 text-orange-500" />
                        </div>
                        <CardDescription>Your emergency leave allocation</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-orange-600">
                                    {{ leaveBalances.emergency.remaining }}
                                </p>
                                <p class="text-sm text-muted-foreground">days remaining</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ leaveBalances.emergency.used }} used</p>
                                <p class="text-sm text-muted-foreground">of {{ leaveBalances.emergency.total }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Usage</span>
                                <span class="font-medium">
                                    {{ Math.round((leaveBalances.emergency.used / leaveBalances.emergency.total) * 100) }}%
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full bg-orange-500 transition-all"
                                    :style="{ width: `${(leaveBalances.emergency.used / leaveBalances.emergency.total) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Total Summary -->
            <Card>
                <CardHeader>
                    <CardTitle>Total Leave Summary</CardTitle>
                    <CardDescription>Overview of all your leave balances</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Total Allocated</p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ leaveBalances.annual.total + leaveBalances.sick.total + leaveBalances.emergency.total }}
                            </p>
                            <p class="text-sm text-muted-foreground">days</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Total Used</p>
                            <p class="mt-2 text-3xl font-bold text-orange-600">
                                {{ leaveBalances.annual.used + leaveBalances.sick.used + leaveBalances.emergency.used }}
                            </p>
                            <p class="text-sm text-muted-foreground">days</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-muted-foreground">Total Remaining</p>
                            <p class="mt-2 text-3xl font-bold text-green-600">
                                {{ leaveBalances.annual.remaining + leaveBalances.sick.remaining + leaveBalances.emergency.remaining }}
                            </p>
                            <p class="text-sm text-muted-foreground">days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
