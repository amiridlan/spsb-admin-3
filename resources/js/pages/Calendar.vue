<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Calendar as CalendarIcon, Plus, Filter, Info, X, ChevronDown } from 'lucide-vue-next';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import type { CalendarOptions, EventDropArg, EventClickArg, DateSelectArg } from '@fullcalendar/core';
import { useStatusColors, type EventStatus } from '@/composables/useStatusColors';

interface EventSpace {
    id: number;
    name: string;
}

interface CalendarEvent {
    id: string;
    title: string;
    start: string;
    end: string;
    backgroundColor: string;
    borderColor: string;
    extendedProps: {
        status: string;
        space: string;
        space_id: number;
        client: string;
        description?: string;
    };
}

interface Props {
    events: CalendarEvent[];
    spaces: EventSpace[];
    filters?: {
        space?: number;
        status?: string;
        view?: string;
        show_cancelled?: boolean;
    };
}

const props = defineProps<Props>();
const page = usePage();
const { getAllStatuses } = useStatusColors();

const calendarRef = ref<InstanceType<typeof FullCalendar> | null>(null);
// FIXED: Use null instead of custom string constants, handle properly in watch
const selectedSpace = ref<string | null>(props.filters?.space?.toString() ?? null);
const selectedStatus = ref<string | null>(props.filters?.status ?? null);
const selectedView = ref<string>(props.filters?.view || 'dayGridMonth');
const showCancelled = ref<boolean>(props.filters?.show_cancelled ?? false);
const filterPopoverOpen = ref(false);

const user = computed(() => page.props.auth.user as any);
const canEdit = computed(() => ['superadmin', 'admin'].includes(user.value?.role));

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Calendar', href: '/calendar' },
];

const allStatuses = getAllStatuses();

// Watch for view changes and update calendar
watch(selectedView, (newView) => {
    const calendarApi = calendarRef.value?.getApi();
    if (calendarApi) {
        calendarApi.changeView(newView);
    }
});

const calendarOptions = computed<CalendarOptions>(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: selectedView.value,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: '', // We'll use our custom view switcher
    },
    events: props.events,
    editable: canEdit.value,
    droppable: canEdit.value,
    eventDrop: handleEventDrop,
    eventClick: handleEventClick,
    eventResize: handleEventResize,
    eventResizableFromStart: true,
    selectable: canEdit.value,
    select: handleDateSelect,
    selectMirror: true,
    height: 'auto',
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false,
    },
    slotLabelFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false,
    },
    displayEventTime: false,
    allDaySlot: true,
    nowIndicator: true,
    eventDisplay: 'block',
    dayMaxEvents: true,
}));

function handleEventDrop(info: EventDropArg) {
    if (!canEdit.value) {
        info.revert();
        return;
    }

    const event = info.event;
    const newStart = event.start;
    const newEnd = event.end;

    if (!newStart) {
        info.revert();
        return;
    }

    const startDate = formatDate(newStart);
    const endDate = newEnd ? formatDate(subtractDay(newEnd)) : startDate;

    if (new Date(endDate) < new Date(startDate)) {
        info.revert();
        console.error('End date cannot be before start date');
        return;
    }

    updateEventDates(event.id, startDate, endDate, info);
}

function handleEventResize(info: EventDropArg) {
    if (!canEdit.value) {
        info.revert();
        return;
    }

    const event = info.event;
    const newStart = event.start;
    const newEnd = event.end;

    if (!newStart || !newEnd) {
        info.revert();
        return;
    }

    const startDate = formatDate(newStart);
    const endDate = formatDate(subtractDay(newEnd));

    if (new Date(endDate) < new Date(startDate)) {
        info.revert();
        console.error('End date cannot be before start date');
        return;
    }

    updateEventDates(event.id, startDate, endDate, info);
}

function updateEventDates(eventId: string, startDate: string, endDate: string, info: EventDropArg) {
    router.patch(
        `/admin/events/${eventId}`,
        {
            start_date: startDate,
            end_date: endDate,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onError: (errors) => {
                info.revert();
                console.error('Failed to update event:', errors);
            },
            onSuccess: () => {
                const duration = calculateDuration(startDate, endDate);
                console.log(`Event updated: ${startDate} to ${endDate} (${duration} day${duration > 1 ? 's' : ''})`);
            },
        }
    );
}

function handleEventClick(info: EventClickArg) {
    const eventId = info.event.id;

    if (canEdit.value) {
        router.visit(`/admin/events/${eventId}`);
    } else {
        router.visit(`/staff/assignments/${eventId}`);
    }
}

function handleDateSelect(selectInfo: DateSelectArg) {
    if (!canEdit.value) return;

    const startDate = formatDate(selectInfo.start);
    const endDate = formatDate(subtractDay(selectInfo.end));

    router.visit(`/admin/events/create?start_date=${startDate}&end_date=${endDate}`);
}

function formatDate(date: Date): string {
    return date.toISOString().split('T')[0];
}

function subtractDay(date: Date): Date {
    const newDate = new Date(date);
    newDate.setDate(newDate.getDate() - 1);
    return newDate;
}

function calculateDuration(startDate: string, endDate: string): number {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = end.getTime() - start.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays;
}

function applyFilters() {
    const filters: Record<string, any> = {
        view: selectedView.value,
    };

    // FIXED: Only add to params if not null
    if (selectedSpace.value) {
        filters.space = selectedSpace.value;
    }

    if (selectedStatus.value) {
        filters.status = selectedStatus.value;
    }

    if (showCancelled.value) {
        filters.show_cancelled = '1';
    }

    router.get('/calendar', filters, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            filterPopoverOpen.value = false;
        },
    });
}

function clearFilters() {
    selectedSpace.value = null;
    selectedStatus.value = null;
    showCancelled.value = false;

    router.get('/calendar', { view: selectedView.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            filterPopoverOpen.value = false;
        },
    });
}

const activeFilterCount = computed(() => {
    let count = 0;
    if (selectedSpace.value) count++;
    if (selectedStatus.value) count++;
    if (showCancelled.value) count++;
    return count;
});

const eventStats = computed(() => {
    const total = props.events.length;
    const multiDay = props.events.filter(e => {
        const start = new Date(e.start);
        const end = new Date(e.end);
        return end.getTime() - start.getTime() > 86400000;
    }).length;

    const byStatus = allStatuses.reduce((acc, status) => {
        acc[status.value] = props.events.filter(e => e.extendedProps.status === status.value).length;
        return acc;
    }, {} as Record<string, number>);

    return { total, multiDay, byStatus };
});

const viewOptions = [
    { value: 'dayGridMonth', label: 'Month' },
    { value: 'timeGridWeek', label: 'Week' },
    { value: 'timeGridDay', label: 'Day' },
    { value: 'listWeek', label: 'List' },
];
</script>

<template>
    <Head title="Calendar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Event Calendar</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ canEdit ? 'View, create, and manage events' : 'View your assigned events' }}
                    </p>
                    <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                        <span>{{ eventStats.total }} event{{ eventStats.total !== 1 ? 's' : '' }}</span>
                        <span v-if="eventStats.multiDay > 0">•</span>
                        <span v-if="eventStats.multiDay > 0">{{ eventStats.multiDay }} multi-day</span>
                        <span v-if="activeFilterCount > 0">•</span>
                        <Badge v-if="activeFilterCount > 0" variant="secondary" class="text-xs">
                            {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? 's' : '' }} active
                        </Badge>
                    </div>
                </div>
                <div class="flex gap-2">
                    <!-- View Selector -->
                    <Select v-model="selectedView" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="view in viewOptions"
                                :key="view.value"
                                :value="view.value"
                            >
                                {{ view.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Button v-if="canEdit" @click="router.visit('/admin/events/create')">
                        <Plus class="mr-2 h-4 w-4" />
                        New Event
                    </Button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="flex flex-wrap items-center gap-3 rounded-lg border p-4">
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium">Filters:</span>
                </div>

                <!-- Quick Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Space Filter -->
                    <Select v-model="selectedSpace">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue placeholder="All Spaces" />
                        </SelectTrigger>
                        <SelectContent>
                            <!-- FIXED: Use empty string for "all" option -->
                            <SelectItem value="">All Spaces</SelectItem>
                            <SelectItem
                                v-for="space in spaces"
                                :key="space.id"
                                :value="space.id.toString()"
                            >
                                {{ space.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Status Filter -->
                    <Select v-model="selectedStatus">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <!-- FIXED: Use empty string for "all" option -->
                            <SelectItem value="">All Statuses</SelectItem>
                            <SelectItem
                                v-for="status in allStatuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.config.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <!-- Advanced Filters Popover -->
                    <Popover v-model:open="filterPopoverOpen">
                        <PopoverTrigger as-child>
                            <Button variant="outline" size="sm">
                                <ChevronDown class="mr-2 h-4 w-4" />
                                More
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-80">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium leading-none mb-3">Advanced Filters</h4>
                                </div>

                                <div class="flex items-center justify-between">
                                    <Label htmlFor="show-cancelled" class="text-sm">
                                        Show cancelled events
                                    </Label>
                                    <Switch
                                        id="show-cancelled"
                                        v-model:checked="showCancelled"
                                    />
                                </div>

                                <div class="border-t pt-3">
                                    <p class="text-xs text-muted-foreground mb-2">Event Distribution:</p>
                                    <div class="space-y-1">
                                        <div
                                            v-for="status in allStatuses"
                                            :key="status.value"
                                            class="flex items-center justify-between text-xs"
                                        >
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="h-2 w-2 rounded-full"
                                                    :style="{ backgroundColor: status.config.colors.background }"
                                                ></div>
                                                <span>{{ status.config.label }}</span>
                                            </div>
                                            <span class="font-medium">{{ eventStats.byStatus[status.value] || 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>
                </div>

                <div class="ml-auto flex gap-2">
                    <Button variant="default" size="sm" @click="applyFilters">
                        Apply
                    </Button>
                    <Button
                        v-if="activeFilterCount > 0"
                        variant="ghost"
                        size="sm"
                        @click="clearFilters"
                    >
                        <X class="mr-2 h-4 w-4" />
                        Clear
                    </Button>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="activeFilterCount > 0" class="flex flex-wrap gap-2">
                <Badge
                    v-if="selectedSpace"
                    variant="secondary"
                    class="cursor-pointer"
                    @click="selectedSpace = null; applyFilters()"
                >
                    Space: {{ spaces.find(s => s.id.toString() === selectedSpace)?.name }}
                    <X class="ml-1 h-3 w-3" />
                </Badge>
                <Badge
                    v-if="selectedStatus"
                    variant="secondary"
                    class="cursor-pointer"
                    @click="selectedStatus = null; applyFilters()"
                >
                    Status: {{ allStatuses.find(s => s.value === selectedStatus)?.config.label }}
                    <X class="ml-1 h-3 w-3" />
                </Badge>
                <Badge
                    v-if="showCancelled"
                    variant="secondary"
                    class="cursor-pointer"
                    @click="showCancelled = false; applyFilters()"
                >
                    Including cancelled
                    <X class="ml-1 h-3 w-3" />
                </Badge>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap gap-4 rounded-lg border p-4">
                <div class="flex items-center gap-2">
                    <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm font-medium">Legend:</span>
                </div>
                <div
                    v-for="status in allStatuses"
                    :key="status.value"
                    class="flex items-center gap-2"
                >
                    <div
                        class="h-3 w-3 rounded-full"
                        :style="{ backgroundColor: status.config.colors.background }"
                    ></div>
                    <span class="text-sm">
                        {{ status.config.label }}
                        <span class="text-muted-foreground">({{ eventStats.byStatus[status.value] || 0 }})</span>
                    </span>
                </div>
            </div>

            <!-- Instructions -->
            <div v-if="canEdit" class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950">
                <div class="flex items-start gap-3">
                    <Info class="mt-0.5 h-4 w-4 flex-shrink-0 text-blue-900 dark:text-blue-100" />
                    <div class="text-sm text-blue-900 dark:text-blue-100">
                        <p class="font-medium">Multi-day Event Management:</p>
                        <ul class="mt-2 space-y-1 list-disc list-inside">
                            <li>Drag events to reschedule (maintains duration)</li>
                            <li>Drag event edges to extend or shorten duration</li>
                            <li>Click and drag on calendar to select date range for new event</li>
                            <li>Click existing event to view/edit details</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="rounded-lg border bg-card p-4">
                <FullCalendar ref="calendarRef" :options="calendarOptions" />
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* FullCalendar custom styles */
.fc {
    font-family: inherit;
}

.fc .fc-button {
    background-color: hsl(var(--primary));
    border-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    text-transform: capitalize;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.fc .fc-button:hover {
    background-color: hsl(var(--primary) / 0.9);
    border-color: hsl(var(--primary) / 0.9);
}

.fc .fc-button:disabled {
    background-color: hsl(var(--muted));
    border-color: hsl(var(--muted));
    color: hsl(var(--muted-foreground));
    opacity: 0.5;
}

.fc .fc-button-active {
    background-color: hsl(var(--primary) / 0.9);
    border-color: hsl(var(--primary) / 0.9);
}

.fc-theme-standard td,
.fc-theme-standard th {
    border-color: hsl(var(--border));
}

.fc-theme-standard .fc-scrollgrid {
    border-color: hsl(var(--border));
}

.fc .fc-daygrid-day-number {
    color: hsl(var(--foreground));
}

.fc .fc-col-header-cell-cushion {
    color: hsl(var(--muted-foreground));
}

.fc .fc-event {
    cursor: pointer;
    border-radius: 0.25rem;
    padding: 2px 4px;
    font-size: 0.875rem;
    border-width: 2px;
}

.fc .fc-event:hover {
    opacity: 0.85;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.fc .fc-daygrid-event {
    margin-top: 2px;
    margin-bottom: 2px;
}

.fc .fc-daygrid-event-harness {
    margin-top: 1px;
    margin-bottom: 1px;
}

.fc .fc-event-main {
    padding: 2px 4px;
}

.fc-daygrid-day.fc-day-today {
    background-color: hsl(var(--accent) / 0.1) !important;
}

.fc .fc-highlight {
    background-color: hsl(var(--primary) / 0.1);
}

.fc .fc-event-resizer {
    width: 8px;
    background-color: rgba(0, 0, 0, 0.2);
}

.fc .fc-event-resizer:hover {
    background-color: rgba(0, 0, 0, 0.4);
}

.fc .fc-more-link {
    color: hsl(var(--primary));
    font-weight: 500;
}

.fc .fc-more-link:hover {
    text-decoration: underline;
}

.fc .fc-timegrid-now-indicator-line {
    border-color: hsl(var(--destructive));
    border-width: 2px;
}

.fc .fc-timegrid-now-indicator-arrow {
    border-color: hsl(var(--destructive));
}
</style>
