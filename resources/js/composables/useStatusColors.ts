export type EventStatus = 'pending' | 'confirmed' | 'completed' | 'cancelled';

export interface StatusColors {
    background: string;
    border: string;
    text: string;
    tailwind: string;
}

export interface StatusConfig {
    colors: StatusColors;
    badge: 'default' | 'secondary' | 'outline' | 'destructive';
    label: string;
    icon?: string;
}

/**
 * Centralized status color system
 * Ensures consistency across calendar, badges, and all UI components
 */
export function useStatusColors() {
    const statusConfig: Record<EventStatus, StatusConfig> = {
        pending: {
            colors: {
                background: '#f59e0b',
                border: '#d97706',
                text: '#ffffff',
                tailwind: 'bg-amber-500 border-amber-600',
            },
            badge: 'outline',
            label: 'Pending',
            icon: 'Clock',
        },
        confirmed: {
            colors: {
                background: '#10b981',
                border: '#059669',
                text: '#ffffff',
                tailwind: 'bg-green-500 border-green-600',
            },
            badge: 'default',
            label: 'Confirmed',
            icon: 'CheckCircle',
        },
        completed: {
            colors: {
                background: '#6b7280',
                border: '#4b5563',
                text: '#ffffff',
                tailwind: 'bg-gray-500 border-gray-600',
            },
            badge: 'secondary',
            label: 'Completed',
            icon: 'CheckCheck',
        },
        cancelled: {
            colors: {
                background: '#ef4444',
                border: '#dc2626',
                text: '#ffffff',
                tailwind: 'bg-red-500 border-red-600',
            },
            badge: 'destructive',
            label: 'Cancelled',
            icon: 'XCircle',
        },
    };

    /**
     * Get configuration for a status
     */
    const getStatusConfig = (status: EventStatus): StatusConfig => {
        return statusConfig[status] || statusConfig.pending;
    };

    /**
     * Get colors for a status
     */
    const getStatusColors = (status: EventStatus): StatusColors => {
        return getStatusConfig(status).colors;
    };

    /**
     * Get badge variant for a status
     */
    const getStatusBadgeVariant = (status: EventStatus): 'default' | 'secondary' | 'outline' | 'destructive' => {
        return getStatusConfig(status).badge;
    };

    /**
     * Get label for a status
     */
    const getStatusLabel = (status: EventStatus): string => {
        return getStatusConfig(status).label;
    };

    /**
     * Get all statuses with their configurations
     */
    const getAllStatuses = (): Array<{ value: EventStatus; config: StatusConfig }> => {
        return Object.entries(statusConfig).map(([value, config]) => ({
            value: value as EventStatus,
            config,
        }));
    };

    return {
        statusConfig,
        getStatusConfig,
        getStatusColors,
        getStatusBadgeVariant,
        getStatusLabel,
        getAllStatuses,
    };
}
