<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { FileText, Download, Eye, Calendar, Building2, Users, DollarSign, X } from 'lucide-vue-next';

interface Props {
    spaces: any[];
    staff: any[];
}

const props = defineProps<Props>();

const reportType = ref('bookings');
const startDate = ref('');
const endDate = ref('');
const spaceId = ref('');
const staffId = ref('');
const status = ref('');
const includeCancelled = ref(false);

// Preview modal state
const showPreview = ref(false);
const reportData = ref<any>(null);
const isLoadingReport = ref(false);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/admin/reports' },
];

function setQuickRange(range: string) {
    const today = new Date();
    let start, end;

    switch (range) {
        case 'week':
            start = new Date(today.setDate(today.getDate() - today.getDay()));
            end = new Date(today.setDate(today.getDate() - today.getDay() + 6));
            break;
        case 'month':
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            start = new Date(today.getFullYear(), quarter * 3, 1);
            end = new Date(today.getFullYear(), quarter * 3 + 3, 0);
            break;
        case 'year':
            start = new Date(today.getFullYear(), 0, 1);
            end = new Date(today.getFullYear(), 11, 31);
            break;
    }

    if (start && end) {
        startDate.value = start.toISOString().split('T')[0];
        endDate.value = end.toISOString().split('T')[0];
    }
}

async function generateReport() {
    if (!startDate.value || !endDate.value) {
        alert('Please select start and end dates');
        return;
    }

    const params: any = {
        report_type: reportType.value,
        start_date: startDate.value,
        end_date: endDate.value,
    };

    if (spaceId.value) params.space_id = spaceId.value;
    if (staffId.value) params.staff_id = staffId.value;
    if (status.value) params.status = status.value;
    if (includeCancelled.value) params.include_cancelled = '1';

    try {
        isLoadingReport.value = true;
        const response = await axios.post('/admin/reports/generate', params);

        // Controller returns JSON with report data
        reportData.value = response.data.report;
        showPreview.value = true;
    } catch (error) {
        console.error('Error generating report:', error);
        alert('Failed to generate report. Please try again.');
    } finally {
        isLoadingReport.value = false;
    }
}

function exportCsv() {
    if (!startDate.value || !endDate.value) {
        alert('Please select start and end dates');
        return;
    }

    const params = new URLSearchParams({
        report_type: reportType.value,
        start_date: startDate.value,
        end_date: endDate.value,
    });

    if (spaceId.value) params.append('space_id', spaceId.value);
    if (staffId.value) params.append('staff_id', staffId.value);
    if (status.value) params.append('status', status.value);
    if (includeCancelled.value) params.append('include_cancelled', '1');

    window.location.href = `/admin/reports/export/csv?${params.toString()}`;
}

const reportTypes = [
    { value: 'bookings', label: 'Bookings Report', icon: Calendar, description: 'Detailed list of all bookings' },
    { value: 'spaces', label: 'Spaces Report', icon: Building2, description: 'Space utilization and performance' },
    { value: 'staff', label: 'Staff Report', icon: Users, description: 'Staff assignments and workload' },
    { value: 'financial', label: 'Financial Report', icon: DollarSign, description: 'Revenue and financial metrics (Coming soon)' },
    { value: 'custom', label: 'Custom Report', icon: FileText, description: 'Combined report with all data' },
];
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Reports</h1>
                <p class="text-sm text-muted-foreground">
                    Generate and export detailed reports
                </p>
            </div>

            <!-- Report Type Selection -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="type in reportTypes"
                    :key="type.value"
                    :class="[
                        'cursor-pointer transition-all',
                        reportType === type.value ? 'border-primary ring-2 ring-primary' : 'hover:border-primary/50'
                    ]"
                    @click="reportType = type.value"
                >
                    <CardHeader>
                        <div class="flex items-center gap-3">
                            <component :is="type.icon" class="h-5 w-5" />
                            <CardTitle class="text-base">{{ type.label }}</CardTitle>
                        </div>
                        <CardDescription>{{ type.description }}</CardDescription>
                    </CardHeader>
                </Card>
            </div>

            <!-- Report Configuration -->
            <Card>
                <CardHeader>
                    <CardTitle>Report Configuration</CardTitle>
                    <CardDescription>Configure your report parameters</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Date Range -->
                    <div>
                        <Label class="text-base font-medium">Date Range</Label>
                        <div class="mt-2 grid gap-4 md:grid-cols-2">
                            <div>
                                <Label htmlFor="start-date">Start Date</Label>
                                <Input
                                    id="start-date"
                                    type="date"
                                    v-model="startDate"
                                />
                            </div>
                            <div>
                                <Label htmlFor="end-date">End Date</Label>
                                <Input
                                    id="end-date"
                                    type="date"
                                    v-model="endDate"
                                />
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <Button variant="outline" size="sm" @click="setQuickRange('week')">This Week</Button>
                            <Button variant="outline" size="sm" @click="setQuickRange('month')">This Month</Button>
                            <Button variant="outline" size="sm" @click="setQuickRange('quarter')">This Quarter</Button>
                            <Button variant="outline" size="sm" @click="setQuickRange('year')">This Year</Button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div>
                        <Label class="text-base font-medium">Filters (Optional)</Label>
                        <div class="mt-2 grid gap-4 md:grid-cols-3">
                            <div>
                                <Label htmlFor="space">Event Space</Label>
                                <Select v-model="spaceId">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Spaces" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Spaces</SelectItem>
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

                            <div v-if="reportType === 'staff' || reportType === 'custom'">
                                <Label htmlFor="staff">Staff Member</Label>
                                <Select v-model="staffId">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Staff" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Staff</SelectItem>
                                        <SelectItem
                                            v-for="member in staff"
                                            :key="member.id"
                                            :value="member.id.toString()"
                                        >
                                            {{ member.user.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div v-if="reportType === 'bookings' || reportType === 'custom'">
                                <Label htmlFor="status">Status</Label>
                                <Select v-model="status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="confirmed">Confirmed</SelectItem>
                                        <SelectItem value="completed">Completed</SelectItem>
                                        <SelectItem value="cancelled">Cancelled</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center space-x-2">
                            <Switch id="include-cancelled" v-model:checked="includeCancelled" />
                            <Label htmlFor="include-cancelled" class="font-normal">
                                Include cancelled bookings
                            </Label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3 pt-4">
                        <Button @click="generateReport" :disabled="isLoadingReport">
                            <Eye class="mr-2 h-4 w-4" />
                            {{ isLoadingReport ? 'Generating...' : 'Preview Report' }}
                        </Button>
                        <Button variant="outline" @click="exportCsv">
                            <Download class="mr-2 h-4 w-4" />
                            Export CSV
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Reports -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Reports</CardTitle>
                    <CardDescription>Pre-configured reports for common needs</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 md:grid-cols-2">
                        <Button
                            variant="outline"
                            class="justify-start"
                            @click="() => {
                                reportType = 'bookings';
                                setQuickRange('month');
                                status = 'pending';
                                generateReport();
                            }"
                        >
                            <FileText class="mr-2 h-4 w-4" />
                            Pending Bookings This Month
                        </Button>
                        <Button
                            variant="outline"
                            class="justify-start"
                            @click="() => {
                                reportType = 'spaces';
                                setQuickRange('quarter');
                                generateReport();
                            }"
                        >
                            <Building2 class="mr-2 h-4 w-4" />
                            Space Utilization This Quarter
                        </Button>
                        <Button
                            variant="outline"
                            class="justify-start"
                            @click="() => {
                                reportType = 'staff';
                                setQuickRange('month');
                                generateReport();
                            }"
                        >
                            <Users class="mr-2 h-4 w-4" />
                            Staff Assignments This Month
                        </Button>
                        <Button
                            variant="outline"
                            class="justify-start"
                            @click="() => {
                                reportType = 'bookings';
                                setQuickRange('year');
                                generateReport();
                            }"
                        >
                            <Calendar class="mr-2 h-4 w-4" />
                            All Bookings This Year
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Report Preview Modal -->
        <Dialog v-model:open="showPreview">
            <DialogContent class=" max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle v-if="reportData">{{ reportData.title }}</DialogTitle>
                    <DialogDescription v-if="reportData">
                        {{ reportData.period }}
                    </DialogDescription>
                </DialogHeader>

                <div v-if="reportData" class="space-y-6">
                    <!-- Bookings Report -->
                    <div v-if="reportData.type === 'bookings' && reportData.data" >
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Space</TableHead>
                                    <TableHead>Client</TableHead>
                                    <TableHead>Start Date</TableHead>
                                    <TableHead>End Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Staff</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="row in reportData.data" :key="row.id">
                                    <TableCell>{{ row.id }}</TableCell>
                                    <TableCell>{{ row.title }}</TableCell>
                                    <TableCell>{{ row.space }}</TableCell>
                                    <TableCell>
                                        <div>{{ row.client_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ row.client_email }}</div>
                                    </TableCell>
                                    <TableCell>{{ row.start_date }}</TableCell>
                                    <TableCell>{{ row.end_date }}</TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                            row.status === 'confirmed' ? 'bg-green-100 text-green-700' :
                                            row.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                                            row.status === 'completed' ? 'bg-blue-100 text-blue-700' :
                                            'bg-gray-100 text-gray-700'
                                        ]">
                                            {{ row.status }}
                                        </span>
                                    </TableCell>
                                    <TableCell>{{ row.staff_count }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Spaces Report -->
                    <div v-if="reportData.type === 'spaces' && reportData.data">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Capacity</TableHead>
                                    <TableHead>Bookings</TableHead>
                                    <TableHead>Total Days</TableHead>
                                    <TableHead>Avg Duration</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="row in reportData.data" :key="row.id">
                                    <TableCell>{{ row.id }}</TableCell>
                                    <TableCell>{{ row.name }}</TableCell>
                                    <TableCell>{{ row.location }}</TableCell>
                                    <TableCell>{{ row.capacity }}</TableCell>
                                    <TableCell>{{ row.booking_count }}</TableCell>
                                    <TableCell>{{ row.total_days }}</TableCell>
                                    <TableCell>{{ row.avg_duration }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Staff Report -->
                    <div v-if="reportData.type === 'staff' && reportData.data">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Position</TableHead>
                                    <TableHead>Assignments</TableHead>
                                    <TableHead>Total Days</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="row in reportData.data" :key="row.id">
                                    <TableCell>{{ row.id }}</TableCell>
                                    <TableCell>{{ row.name }}</TableCell>
                                    <TableCell>{{ row.position }}</TableCell>
                                    <TableCell>{{ row.assignment_count }}</TableCell>
                                    <TableCell>{{ row.total_days }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Custom Report -->
                    <div v-if="reportData.type === 'custom'" class="space-y-6">
                        <div v-if="reportData.bookings">
                            <h3 class="text-lg font-semibold mb-3">Bookings</h3>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Title</TableHead>
                                        <TableHead>Space</TableHead>
                                        <TableHead>Client</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="row in reportData.bookings.data?.slice(0, 5)" :key="row.id">
                                        <TableCell>{{ row.title }}</TableCell>
                                        <TableCell>{{ row.space }}</TableCell>
                                        <TableCell>{{ row.client_name }}</TableCell>
                                        <TableCell>{{ row.start_date }}</TableCell>
                                        <TableCell>{{ row.status }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            <p v-if="reportData.bookings.data?.length > 5" class="text-sm text-muted-foreground mt-2">
                                Showing first 5 of {{ reportData.bookings.data.length }} bookings
                            </p>
                        </div>

                        <div v-if="reportData.spaces">
                            <h3 class="text-lg font-semibold mb-3">Spaces</h3>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Bookings</TableHead>
                                        <TableHead>Total Days</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="row in reportData.spaces.data?.slice(0, 5)" :key="row.id">
                                        <TableCell>{{ row.name }}</TableCell>
                                        <TableCell>{{ row.booking_count }}</TableCell>
                                        <TableCell>{{ row.total_days }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div v-if="reportData.staff">
                            <h3 class="text-lg font-semibold mb-3">Staff</h3>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Position</TableHead>
                                        <TableHead>Assignments</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="row in reportData.staff.data?.slice(0, 5)" :key="row.id">
                                        <TableCell>{{ row.name }}</TableCell>
                                        <TableCell>{{ row.position }}</TableCell>
                                        <TableCell>{{ row.assignment_count }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>

                    <!-- Financial Report -->
                    <div v-if="reportData.type === 'financial'" class="text-center py-8">
                        <p class="text-muted-foreground">{{ reportData.summary?.note || 'Financial reporting coming soon' }}</p>
                    </div>

                    <!-- Summary Section -->
                    <div v-if="reportData.summary" class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">Summary</h3>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <Card v-for="(value, key) in reportData.summary" :key="key">
                                <CardHeader class="pb-2">
                                    <CardDescription class="text-xs">
                                        {{ key.toString().replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                    </CardDescription>
                                    <CardTitle class="text-2xl">{{ value }}</CardTitle>
                                </CardHeader>
                            </Card>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showPreview = false">
                        Close
                    </Button>
                    <Button @click="exportCsv">
                        <Download class="mr-2 h-4 w-4" />
                        Export CSV
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
