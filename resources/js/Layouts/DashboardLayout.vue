<template>
  <div class="min-h-screen overflow-x-hidden bg-[#f3f5ff] text-slate-950 dark:bg-[#070b1a] dark:text-white">
    <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(circle_at_18%_0%,rgba(124,58,237,.18),transparent_28%),radial-gradient(circle_at_85%_15%,rgba(56,189,248,.12),transparent_25%)]" />

    <aside :class="['fixed inset-y-0 left-0 z-50 w-64 p-3 transition duration-300 lg:translate-x-0', open ? 'translate-x-0' : '-translate-x-full']">
      <div class="flex h-full flex-col rounded-[24px] border border-white/80 bg-white/90 p-4 shadow-glass backdrop-blur-2xl dark:border-white/10 dark:bg-[#10182b]/95">
        <div class="mb-6 flex items-center justify-between">
          <AppLogo />
          <button class="grid size-9 place-items-center rounded-xl bg-slate-100 dark:bg-white/10" @click="open = false">
            <ChevronLeft class="size-4" />
          </button>
        </div>

        <nav class="app-scrollbar space-y-1 overflow-y-auto pr-1">
          <a v-for="item in items" :key="item.href" :href="item.href" :class="['flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition', isActive(item.href) ? 'bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white shadow-glow' : 'text-slate-700 hover:bg-violet-50 dark:text-slate-300 dark:hover:bg-white/10']">
            <component :is="item.icon" class="size-4 shrink-0" />
            <span class="truncate">{{ item.label }}</span>
            <span v-if="item.badge" class="ml-auto rounded-full bg-violet-100 px-2 py-0.5 text-xs text-violet-700 dark:bg-violet-500 dark:text-white">{{ item.badge }}</span>
          </a>
        </nav>

        <div class="mt-auto rounded-2xl bg-slate-100 p-3 dark:bg-white/10">
          <div class="flex items-center gap-3">
            <div class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-pink-500 font-black text-white">{{ userInitial }}</div>
            <div class="min-w-0">
              <p class="truncate text-sm font-black">{{ user.name }}</p>
              <p class="truncate text-xs text-slate-500 dark:text-slate-400">Business Owner</p>
            </div>
            <MoreVertical class="ml-auto size-4 shrink-0 text-slate-400" />
          </div>
        </div>
      </div>
    </aside>

    <div class="relative z-10 min-w-0 lg:pl-64">
      <header class="sticky top-0 z-40 px-3 py-3 backdrop-blur-2xl sm:px-5">
        <div class="flex min-w-0 items-center gap-3">
          <button class="grid size-11 shrink-0 place-items-center rounded-2xl border border-white/80 bg-white/85 shadow-sm dark:border-white/10 dark:bg-white/10 lg:hidden" @click="open = true">
            <Menu class="size-5" />
          </button>

          <div class="hidden h-12 w-full max-w-xs items-center gap-3 rounded-2xl border border-white/80 bg-white/75 px-4 shadow-sm dark:border-white/10 dark:bg-white/10 md:flex">
            <Search class="size-4 shrink-0 text-slate-400" />
            <input class="min-w-0 flex-1 bg-transparent text-sm font-semibold outline-none placeholder:text-slate-400" placeholder="Search anything..." />
            <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-black text-slate-500 dark:bg-white/10">Ctrl K</span>
          </div>

          <div class="ml-auto flex min-w-0 items-center gap-2">
            <ThemeToggle />
            <div class="relative">
              <button class="relative grid size-11 shrink-0 place-items-center rounded-2xl border border-white/80 bg-white/85 shadow-sm dark:border-white/10 dark:bg-white/10" type="button" @click="notificationOpen = !notificationOpen">
                <Bell class="size-5" />
                <span v-if="notificationCount" class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-red-500 px-1.5 text-[10px] font-black text-white ring-2 ring-white dark:ring-[#070b1a]">{{ compactCount(notificationCount) }}</span>
              </button>
              <div v-if="notificationOpen" class="absolute right-0 top-14 z-50 w-[min(21rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-glass dark:border-white/10 dark:bg-[#111a2f]">
                <div class="flex items-center justify-between border-b border-slate-100 p-3 dark:border-white/10">
                  <div>
                    <p class="text-sm font-black">Notifications</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ notificationCount }} unread inbox alerts</p>
                  </div>
                  <span class="rounded-full bg-violet-100 px-2 py-1 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">{{ compactCount(notificationCount) }}</span>
                </div>
                <div class="app-scrollbar max-h-80 overflow-y-auto p-2">
                  <a v-for="alert in notificationRows" :key="alert.id" href="/app/inbox" class="mb-2 flex gap-3 rounded-2xl bg-slate-50 p-3 text-left hover:bg-violet-50 dark:bg-white/[.06] dark:hover:bg-white/10" @click="notificationOpen = false">
                    <div class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-rose-500 text-xs font-black text-white">{{ alert.initial }}</div>
                    <div class="min-w-0">
                      <p class="truncate text-sm font-black">{{ alert.title }}</p>
                      <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ alert.text }}</p>
                    </div>
                    <span v-if="alert.count" class="ml-auto self-start rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-black text-white">{{ alert.count }}</span>
                  </a>
                  <p v-if="!notificationRows.length" class="rounded-2xl border border-dashed border-slate-200 p-4 text-center text-sm font-bold text-slate-400 dark:border-white/10">No new notifications.</p>
                </div>
              </div>
            </div>
            <div class="relative hidden sm:block">
              <button class="flex min-w-0 items-center gap-3 rounded-2xl border border-white/80 bg-white/85 px-3 py-2 shadow-sm dark:border-white/10 dark:bg-white/10" type="button" @click="profileOpen = !profileOpen">
              <div class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-pink-500 text-sm font-black text-white">{{ userInitial }}</div>
              <div class="min-w-0">
                <p class="truncate text-sm font-black">{{ user.name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Admin</p>
              </div>
                <ChevronDown class="size-4 shrink-0 text-slate-400" />
              </button>
              <div v-if="profileOpen" class="absolute right-0 top-14 z-50 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-glass dark:border-white/10 dark:bg-[#111a2f]">
                <div class="border-b border-slate-100 p-3 dark:border-white/10">
                  <p class="truncate text-sm font-black">{{ user.name }}</p>
                  <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                </div>
                <a v-for="link in profileLinks" :key="link.href" :href="link.href" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-violet-50 dark:text-slate-200 dark:hover:bg-white/10" @click="profileOpen = false">
                  <component :is="link.icon" class="size-4" />
                  {{ link.label }}
                </a>
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10" type="button" @click="logout">
                  <LogOut class="size-4" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="min-w-0 px-3 pb-24 sm:px-5 lg:pb-6">
        <slot />
      </main>

      <nav class="fixed inset-x-3 bottom-3 z-50 grid grid-cols-5 rounded-[22px] border border-white/20 bg-[#10182b]/95 p-2 text-white shadow-glow backdrop-blur-xl lg:hidden">
        <a v-for="item in items.slice(0, 5)" :key="item.href" :href="item.href" :class="['grid place-items-center gap-1 rounded-2xl py-2 text-[11px] font-bold', isActive(item.href) ? 'bg-violet-600 text-white' : 'text-white/65']">
          <span class="relative">
            <component :is="item.icon" class="size-5" />
            <span v-if="item.badge" class="absolute -right-3 -top-2 grid min-w-4 place-items-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white">{{ item.badge }}</span>
          </span>
          <span>{{ item.short }}</span>
        </a>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Bell, Bot, ChartSpline, ChevronDown, ChevronLeft, CreditCard, Database, Home, Inbox, KeyRound, LogOut, Menu, MoreVertical, Search, Send, Settings, UserCircle, Users, Workflow } from 'lucide-vue-next';
import AppLogo from '@/Components/Ui/AppLogo.vue';
import ThemeToggle from '@/Components/Ui/ThemeToggle.vue';

const open = ref(false);
const profileOpen = ref(false);
const notificationOpen = ref(false);
const page = usePage();
const user = computed(() => page.props.auth?.user ?? { name: 'John Doe', email: 'admin@chatflow.test' });
const userInitial = computed(() => user.value.name?.charAt(0)?.toUpperCase() ?? 'J');
const isActive = (href: string) => page.url.startsWith(href);
const dashboard = computed(() => page.props.dashboard as Record<string, any> | undefined);
const notificationCount = computed(() => Number(dashboard.value?.unreadNotifications ?? 0));
const notificationRows = computed(() => (dashboard.value?.notifications ?? []).map((item: Record<string, any>) => ({
  id: item.id,
  title: item.title ?? 'Inbox alert',
  text: item.text ?? 'New inbox activity',
  count: item.count ?? 0,
  initial: item.initial ?? 'C',
})));

const items = computed(() => [
  { label: 'Dashboard', short: 'Home', href: '/app/dashboard', icon: Home },
  { label: 'Inbox', short: 'Inbox', href: '/app/inbox', icon: Inbox, badge: notificationCount.value ? compactCount(notificationCount.value) : '' },
  { label: 'CRM', short: 'CRM', href: '/app/contacts', icon: Users },
  { label: 'Automations', short: 'Auto', href: '/app/automations', icon: Workflow },
  { label: 'Broadcasts', short: 'Send', href: '/app/broadcasts', icon: Send },
  { label: 'AI Training', short: 'Train', href: '/app/training', icon: Bot },
  { label: 'Analytics', short: 'Data', href: '/app/analytics', icon: ChartSpline },
  { label: 'Team', short: 'Team', href: '/app/team', icon: Users },
  { label: 'Notifications', short: 'Alerts', href: '/app/notifications', icon: Bell },
  { label: 'Integrations', short: 'Apps', href: '/app/integrations', icon: Database },
  { label: 'API Keys', short: 'Keys', href: '/app/api-keys', icon: Database },
  { label: 'Activity Logs', short: 'Logs', href: '/app/activity', icon: ChartSpline },
  { label: 'Profile', short: 'Me', href: '/app/profile', icon: Users },
  { label: 'Settings', short: 'Set', href: '/app/settings', icon: Settings },
  { label: 'Billing', short: 'Bill', href: '/app/billing', icon: CreditCard },
]);

const profileLinks = [
  { label: 'Profile', href: '/app/profile', icon: UserCircle },
  { label: 'Settings', href: '/app/settings', icon: Settings },
  { label: 'Billing', href: '/app/billing', icon: CreditCard },
  { label: 'API Keys', href: '/app/api-keys', icon: KeyRound },
];

function logout() {
  profileOpen.value = false;
  router.post('/auth/logout');
}

function compactCount(count: number) {
  return count > 99 ? '99+' : String(count);
}
</script>
