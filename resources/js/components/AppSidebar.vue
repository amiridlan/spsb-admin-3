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
import { BookOpen, LayoutGrid, Users, Building2, Calendar, UserCog, ChartColumnIncreasingIcon, Calendar1Icon, BriefcaseBusinessIcon, CalendarClock, Building } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();

// Main navigation items (Dashboard, Calendar, Metrics, Reports, My Assignments)
const mainNavItems = computed<NavItem[]>(() => {
    const user = page.props.auth?.user;

    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    // Add Calendar, Metrics, and Reports for admin users
    if (user && (user.role === 'superadmin' || user.role === 'admin')) {
        items.push(
            {
                title: 'Calendar',
                href: '/calendar',
                icon: Calendar1Icon,
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

     // Staff menu items (for staff)
    if (user && ['staff'].includes(user.role)) {
        items.push(
            {
                title: 'My Assignments',
                href: '/staff/assignments',
                icon: BriefcaseBusinessIcon,
            },
            {
                title: 'Leave Requests',
                href: '/staff/leave/requests',
                icon: CalendarClock,
            }
        );
    }

    // Head of Department menu items
    if (user && user.role === 'head_of_department') {
        items.push(
            {
                title: 'Leave Approvals',
                href: '/head/leave/requests',
                icon: CalendarClock,
            }
        );
    }

    return items;
});

// Event Space Group items
const eventSpaceItems = computed<NavItem[]>(() => {
    const user = page.props.auth?.user;
    const items: NavItem[] = [];

    if (user && (user.role === 'superadmin' || user.role === 'admin')) {
        items.push(
            {
                title: 'Events',
                href: '/admin/events',
                icon: Calendar,
            },
            {
                title: 'Event Spaces',
                href: '/admin/event-spaces',
                icon: Building2,
            }
        );
    }

    return items;
});

// HRMS Group items
const hrmsItems = computed<NavItem[]>(() => {
    const user = page.props.auth?.user;
    const items: NavItem[] = [];

    if (user && (user.role === 'superadmin' || user.role === 'admin')) {
        items.push(
            {
                title: 'Departments',
                href: '/admin/departments',
                icon: Building,
            },
            {
                title: 'Staff',
                href: '/admin/staff',
                icon: UserCog,
            },
            {
                title: 'HR Review',
                href: '/admin/leave/hr/pending',
                icon: CalendarClock,
            },
            {
                title: 'Leave Requests',
                href: '/admin/leave/requests',
                icon: CalendarClock,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            }
        );
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
            <NavMain
                :main-items="mainNavItems"
                :event-space-items="eventSpaceItems"
                :hrms-items="hrmsItems"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
