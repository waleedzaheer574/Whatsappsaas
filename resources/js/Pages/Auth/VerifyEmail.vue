<template>
  <AuthLayout>
    <Head title="Verify Email" />
    <AuthCard eyebrow="Verification" title="Check your inbox" subtitle="We sent a verification link to your email. Confirm your address to activate ChatFlow AI." action="Resend Verification Email">
      <div class="rounded-3xl bg-violet-50 p-5 text-sm leading-6 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">
        Keep this tab open after clicking the verification link. Your workspace will refresh automatically.
      </div>
      <p class="text-center text-sm text-slate-500"><a href="/auth/login" class="font-black text-violet-600">Use another account</a></p>
    </AuthCard>
  </AuthLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
</script>
