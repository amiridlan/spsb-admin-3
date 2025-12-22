<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Main Menu</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>

    <!-- Event Space Group -->
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Event Space</SidebarGroupLabel>
        <SidebarMenu>
            <!-- Empty for now -->
        </SidebarMenu>
    </SidebarGroup>

    <!-- CMMS Group -->
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>CMMS</SidebarGroupLabel>
        <SidebarMenu>
            <!-- Empty for now -->
        </SidebarMenu>
    </SidebarGroup>

    <!-- HRMS Group -->
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>HRMS</SidebarGroupLabel>
        <SidebarMenu>
            <!-- Empty for now -->
        </SidebarMenu>
    </SidebarGroup>
</template>
