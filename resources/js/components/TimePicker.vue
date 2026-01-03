<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Clock } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { cn } from '@/lib/utils';

interface Props {
    modelValue: string | null;
    placeholder?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select time',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

const isOpen = ref(false);

// Parse time value (HH:mm format)
const selectedHour = ref<number | null>(null);
const selectedMinute = ref<number | null>(null);

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        const [hour, minute] = newValue.split(':').map(Number);
        selectedHour.value = hour;
        selectedMinute.value = minute;
    } else {
        selectedHour.value = null;
        selectedMinute.value = null;
    }
}, { immediate: true });

// Generate hours (00-23) - 24 hour system for selection
const hours = Array.from({ length: 24 }, (_, i) => i);

// Generate all minutes (00-59)
const minutes = Array.from({ length: 60 }, (_, i) => i);

// Format time for display
const formattedTime = computed(() => {
    if (selectedHour.value === null || selectedMinute.value === null) {
        return props.placeholder;
    }

    const hour = selectedHour.value;
    const minute = selectedMinute.value;
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;

    return `${displayHour}:${String(minute).padStart(2, '0')} ${period}`;
});

// Select hour
const selectHour = (hour: number) => {
    selectedHour.value = hour;
    if (selectedMinute.value !== null) {
        updateTime();
    }
};

// Select minute
const selectMinute = (minute: number) => {
    selectedMinute.value = minute;
    if (selectedHour.value !== null) {
        updateTime();
    }
};

// Update time and emit
const updateTime = () => {
    if (selectedHour.value !== null && selectedMinute.value !== null) {
        const hourStr = String(selectedHour.value).padStart(2, '0');
        const minuteStr = String(selectedMinute.value).padStart(2, '0');
        emit('update:modelValue', `${hourStr}:${minuteStr}`);
        isOpen.value = false;
    }
};

// Clear time
const clearTime = () => {
    selectedHour.value = null;
    selectedMinute.value = null;
    emit('update:modelValue', null);  // Emit null, not empty string
    isOpen.value = false;
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="cn(
                    'w-full justify-start text-left font-normal',
                    !modelValue && 'text-muted-foreground',
                )"
                :disabled="disabled"
            >
                <Clock class="mr-2 h-4 w-4" />
                {{ formattedTime }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <div class="flex">
                <!-- Hours Column - 24 hour format, narrower -->
                <div class="border-r">
                    <div class="px-2 py-1.5 text-xs font-medium border-b bg-muted/50">Hour</div>
                    <ScrollArea class="h-[180px] w-16">
                        <div class="p-0.5 flex flex-col gap-0.5">
                            <button
                                v-for="hour in hours"
                                :key="hour"
                                type="button"
                                @click="selectHour(hour)"
                                :class="cn(
                                    'w-full px-2 py-1.5 text-xs rounded-md text-center hover:bg-accent',
                                    selectedHour === hour && 'bg-primary text-primary-foreground hover:bg-primary'
                                )"
                            >
                                {{ String(hour).padStart(2, '0') }}
                            </button>
                        </div>
                    </ScrollArea>
                </div>

                <!-- Minutes Column - All minutes 00-59 -->
                <div>
                    <div class="px-2 py-1.5 text-xs font-medium border-b bg-muted/50">Min</div>
                    <ScrollArea class="h-[180px] w-16">
                        <div class="p-0.5 flex flex-col gap-0.5">
                            <button
                                v-for="minute in minutes"
                                :key="minute"
                                type="button"
                                @click="selectMinute(minute)"
                                :class="cn(
                                    'w-full px-2 py-1.5 text-xs rounded-md text-center hover:bg-accent',
                                    selectedMinute === minute && 'bg-primary text-primary-foreground hover:bg-primary'
                                )"
                            >
                                {{ String(minute).padStart(2, '0') }}
                            </button>
                        </div>
                    </ScrollArea>
                </div>
            </div>

            <!-- Footer with Clear button -->
            <div class="p-1.5 border-t">
                <Button
                    variant="ghost"
                    size="sm"
                    class="w-full h-7 text-xs"
                    @click="clearTime"
                >
                    Clear
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
