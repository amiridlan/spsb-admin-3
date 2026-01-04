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
    mainItems: NavItem[];
    eventSpaceItems: NavItem[];
    hrmsItems: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <!-- Main Menu -->
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Main Menu</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in mainItems" :key="item.title">
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
    <SidebarGroup v-if="eventSpaceItems.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>Event Space</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in eventSpaceItems" :key="item.title">
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

    <!-- CMMS Group -->
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>CMMS</SidebarGroupLabel>
        <SidebarMenu>
            <!-- Empty for now -->
        </SidebarMenu>
    </SidebarGroup>

    <!-- HRMS Group -->
    <SidebarGroup v-if="hrmsItems.length > 0" class="px-2 py-0">
        <SidebarGroupLabel>HRMS</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in hrmsItems" :key="item.title">
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
</template>
