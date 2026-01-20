<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Plus, Trash2, Copy, Key, AlertTriangle, Check } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Token {
    id: number;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
    expires_at: string | null;
}

interface Props {
    tokens: Token[];
}

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'API Tokens', href: '/admin/api-tokens' },
];

const createDialogOpen = ref(false);
const copied = ref(false);

const newToken = computed(() => page.props.flash?.newToken as string | undefined);
const successMessage = computed(() => page.props.flash?.success as string | undefined);

const form = useForm({
    name: '',
    expires_at: '',
});

const createToken = () => {
    form.post('/admin/api-tokens', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            createDialogOpen.value = false;
        },
    });
};

const revokeToken = (tokenId: number) => {
    if (confirm('Are you sure you want to revoke this token?')) {
        form.delete(`/admin/api-tokens/${tokenId}`, {
            preserveScroll: true,
        });
    }
};

const revokeAllTokens = () => {
    form.delete('/admin/api-tokens/all', {
        preserveScroll: true,
    });
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="API Tokens" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">API Tokens</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your API access tokens for external integrations
                    </p>
                </div>
                <div class="flex gap-2">
                    <AlertDialog v-if="props.tokens.length > 0">
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Revoke All
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Revoke All Tokens?</AlertDialogTitle>
                                <AlertDialogDescription>
                                    This will revoke all your API tokens. Any applications using these tokens will lose access immediately. This action cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click="revokeAllTokens" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                                    Revoke All
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <Dialog v-model:open="createDialogOpen">
                        <DialogTrigger as-child>
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Create Token
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Create API Token</DialogTitle>
                                <DialogDescription>
                                    Create a new API token for external applications to access the API.
                                </DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="createToken" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name">Token Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g., Mobile App, Integration"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="expires_at">Expiration (Optional)</Label>
                                    <Input
                                        id="expires_at"
                                        v-model="form.expires_at"
                                        type="datetime-local"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Leave empty for a non-expiring token.
                                    </p>
                                </div>
                                <DialogFooter>
                                    <Button type="submit" :disabled="form.processing">
                                        Create Token
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- New Token Alert -->
            <div v-if="newToken" class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950">
                <div class="flex items-start gap-3">
                    <Key class="h-5 w-5 text-green-600 dark:text-green-400" />
                    <div class="flex-1">
                        <h3 class="font-medium text-green-800 dark:text-green-200">
                            Token Created Successfully
                        </h3>
                        <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                            Make sure to copy your new API token now. You won't be able to see it again!
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            <code class="flex-1 rounded bg-green-100 px-3 py-2 font-mono text-sm dark:bg-green-900">
                                {{ newToken }}
                            </code>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="copyToClipboard(newToken)"
                            >
                                <Check v-if="copied" class="h-4 w-4 text-green-600" />
                                <Copy v-else class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tokens List -->
            <div v-if="props.tokens.length > 0" class="rounded-md border">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Name
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Created
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Last Used
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Expires
                            </th>
                            <th class="h-12 px-4 text-left align-middle font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="token in props.tokens"
                            :key="token.id"
                            class="border-b"
                        >
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <Key class="h-4 w-4 text-muted-foreground" />
                                    <span class="font-medium">{{ token.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 align-middle text-sm text-muted-foreground">
                                {{ formatDate(token.created_at) }}
                            </td>
                            <td class="p-4 align-middle text-sm text-muted-foreground">
                                {{ formatDate(token.last_used_at) }}
                            </td>
                            <td class="p-4 align-middle">
                                <Badge
                                    :variant="token.expires_at ? 'outline' : 'secondary'"
                                >
                                    {{ token.expires_at ? formatDate(token.expires_at) : 'Never' }}
                                </Badge>
                            </td>
                            <td class="p-4 align-middle">
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button variant="destructive" size="sm">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Revoke Token?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Are you sure you want to revoke "{{ token.name }}"? Any applications using this token will lose access immediately.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction
                                                @click="revokeToken(token.id)"
                                                class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                            >
                                                Revoke
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12">
                <Key class="h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-medium">No API tokens</h3>
                <p class="mt-2 text-center text-sm text-muted-foreground">
                    You haven't created any API tokens yet. Create one to start using the API.
                </p>
                <Button class="mt-4" @click="createDialogOpen = true">
                    <Plus class="mr-2 h-4 w-4" />
                    Create Token
                </Button>
            </div>

            <!-- Usage Information -->
            <div class="rounded-lg border bg-muted/30 p-6">
                <h3 class="font-medium">Using API Tokens</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Include your API token in the <code class="rounded bg-muted px-1">Authorization</code> header of your API requests:
                </p>
                <pre class="mt-3 overflow-x-auto rounded-lg bg-muted p-4 text-sm">
<code>curl -X GET "{{ $page.props.appUrl || 'https://your-app.com' }}/api/v1/events" \
     -H "Authorization: Bearer YOUR_API_TOKEN" \
     -H "Accept: application/json"</code></pre>
                <p class="mt-3 text-sm text-muted-foreground">
                    View the <a href="/docs" class="text-primary underline">API documentation</a> for available endpoints.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
