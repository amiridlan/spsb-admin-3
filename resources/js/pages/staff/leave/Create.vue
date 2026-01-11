<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Calendar as CalendarIcon, ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

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

interface Props {
    leaveBalances: LeaveBalances;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Leave Requests', href: '/staff/leave/requests' },
    { title: 'Request Leave', href: '/staff/leave/requests/create' },
];

const form = useForm({
    leave_type: '',
    start_date: '',
    end_date: '',
    reason: '',
});

const selectedLeaveBalance = computed(() => {
    if (!form.leave_type) return null;
    return props.leaveBalances[form.leave_type as keyof LeaveBalances];
});

const submit = () => {
    form.post('/staff/leave/requests', {
        onSuccess: () => {
            // Will redirect to index page
        },
    });
};
</script>

<template>
    <Head title="Request Leave" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Request Leave</h1>
                    <p class="text-sm text-muted-foreground">
                        Submit a new leave request
                    </p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/staff/leave/requests">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Requests
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Leave Request Details</CardTitle>
                            <CardDescription>
                                Fill in the details for your leave request
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Leave Type -->
                                <div class="space-y-2">
                                    <Label for="leave_type">Leave Type *</Label>
                                    <Select v-model="form.leave_type" required>
                                        <SelectTrigger id="leave_type">
                                            <SelectValue placeholder="Select leave type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="annual">Annual Leave</SelectItem>
                                            <SelectItem value="sick">Sick Leave</SelectItem>
                                            <SelectItem value="emergency">Emergency Leave</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.leave_type" class="text-sm text-destructive">
                                        {{ form.errors.leave_type }}
                                    </p>
                                    <p v-if="selectedLeaveBalance" class="text-sm text-muted-foreground">
                                        Available: {{ selectedLeaveBalance.remaining }} days
                                    </p>
                                </div>

                                <!-- Start Date -->
                                <div class="space-y-2">
                                    <Label for="start_date">Start Date *</Label>
                                    <Input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        required
                                        :min="new Date().toISOString().split('T')[0]"
                                    />
                                    <p v-if="form.errors.start_date" class="text-sm text-destructive">
                                        {{ form.errors.start_date }}
                                    </p>
                                </div>

                                <!-- End Date -->
                                <div class="space-y-2">
                                    <Label for="end_date">End Date *</Label>
                                    <Input
                                        id="end_date"
                                        v-model="form.end_date"
                                        type="date"
                                        required
                                        :min="form.start_date || new Date().toISOString().split('T')[0]"
                                    />
                                    <p v-if="form.errors.end_date" class="text-sm text-destructive">
                                        {{ form.errors.end_date }}
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div class="space-y-2">
                                    <Label for="reason">Reason *</Label>
                                    <Textarea
                                        id="reason"
                                        v-model="form.reason"
                                        placeholder="Please provide a reason for your leave request (minimum 10 characters)"
                                        required
                                        rows="4"
                                    />
                                    <p v-if="form.errors.reason" class="text-sm text-destructive">
                                        {{ form.errors.reason }}
                                    </p>
                                </div>

                                <!-- Error Message -->
                                <div v-if="form.errors.error" class="rounded-lg border border-destructive bg-destructive/10 p-4">
                                    <p class="text-sm text-destructive">{{ form.errors.error }}</p>
                                </div>

                                <!-- Submit -->
                                <div class="flex justify-end gap-3">
                                    <Button variant="outline" type="button" as-child>
                                        <Link href="/staff/leave/requests">Cancel</Link>
                                    </Button>
                                    <Button type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Leave Balances Sidebar -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CalendarIcon class="h-5 w-5" />
                                Leave Balances
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Annual Leave -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Annual Leave</span>
                                    <span class="text-lg font-bold text-blue-600">
                                        {{ leaveBalances.annual.remaining }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ leaveBalances.annual.used }} used / {{ leaveBalances.annual.total }} total
                                </p>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full bg-blue-500 transition-all"
                                        :style="{ width: `${(leaveBalances.annual.used / leaveBalances.annual.total) * 100}%` }"
                                    ></div>
                                </div>
                            </div>

                            <div class="border-t"></div>

                            <!-- Sick Leave -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Sick Leave</span>
                                    <span class="text-lg font-bold text-red-600">
                                        {{ leaveBalances.sick.remaining }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ leaveBalances.sick.used }} used / {{ leaveBalances.sick.total }} total
                                </p>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full bg-red-500 transition-all"
                                        :style="{ width: `${(leaveBalances.sick.used / leaveBalances.sick.total) * 100}%` }"
                                    ></div>
                                </div>
                            </div>

                            <div class="border-t"></div>

                            <!-- Emergency Leave -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium">Emergency Leave</span>
                                    <span class="text-lg font-bold text-orange-600">
                                        {{ leaveBalances.emergency.remaining }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ leaveBalances.emergency.used }} used / {{ leaveBalances.emergency.total }} total
                                </p>
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
            </div>
        </div>
    </AppLayout>
</template>
