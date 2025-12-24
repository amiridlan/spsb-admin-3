<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, Building2, Calendar, UserCog, ChartColumnIncreasingIcon, Calendar1Icon, BriefcaseBusinessIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

// Make mainNavItems a computed property so it's reactive
const mainNavItems = computed<NavItem[]>(() => {
    const user = page.props.auth?.user;

    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Add admin menu items if user has the right role
    if (user && (user.role === 'superadmin' || user.role === 'admin')) {
        items.push(
            {
                title: 'Events',
                href: '/admin/events',
                icon: Calendar,
            },
            {
                title: 'Calendar',
                href: '/calendar',
                icon: Calendar1Icon, // or Calendar icon
            },
            {
                title: 'Event Spaces',
                href: '/admin/event-spaces',
                icon: Building2,
            },
            {
                title: 'Staff',
                href: '/admin/staff',
                icon: UserCog,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Metrics',
                href: '/admin/metrics',
                icon: ChartColumnIncreasingIcon,
            },
            {
                title: 'Reports',
                href: '/admin/reports',
                icon: BookOpen,
            }
        );
    }

     // Staff menu items (for staff, admin, and superadmin)
    if (user && ['staff', 'admin', 'superadmin'].includes(user.role)) {
        items.push({
            title: 'My Assignments',
            href: '/staff/assignments',
            icon: BriefcaseBusinessIcon,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
