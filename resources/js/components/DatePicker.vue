<script setup lang="ts">
import { computed } from 'vue';
import { CalendarDate, parseDate, getLocalTimeZone } from '@internationalized/date';
import { Calendar as CalendarIcon } from 'lucide-vue-next';
import { Calendar } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

interface Props {
    modelValue: string | null;
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Pick a date',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

// Convert string date to CalendarDate
const dateValue = computed<CalendarDate | undefined>(() => {
    if (!props.modelValue) return undefined;
    try {
        return parseDate(props.modelValue);
    } catch {
        return undefined;
    }
});

// Format date for display
const formattedDate = computed(() => {
    if (!dateValue.value) return props.placeholder;

    // Format as MM/DD/YYYY
    const month = String(dateValue.value.month).padStart(2, '0');
    const day = String(dateValue.value.day).padStart(2, '0');
    const year = dateValue.value.year;

    return `${month}/${day}/${year}`;
});

// Handle date selection
const handleDateSelect = (date: CalendarDate | undefined) => {
    if (date) {
        // Format as YYYY-MM-DD for backend
        const year = date.year;
        const month = String(date.month).padStart(2, '0');
        const day = String(date.day).padStart(2, '0');
        emit('update:modelValue', `${year}-${month}-${day}`);
    } else {
        emit('update:modelValue', null);
    }
};
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="cn(
                    'w-full justify-start text-left font-normal',
                    !dateValue && 'text-muted-foreground',
                )"
                :disabled="disabled"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ formattedDate }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <Calendar
                :model-value="dateValue"
                @update:model-value="handleDateSelect"
                initial-focus
            />
        </PopoverContent>
    </Popover>
</template>
