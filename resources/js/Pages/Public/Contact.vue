<template>
  <PublicLayout>
    <Head title="Contact" />
    <section class="section-wrap grid gap-8 py-20 lg:grid-cols-[.85fr_1.15fr]">
      <div>
        <p class="text-sm font-black uppercase text-violet-600">Contact</p>
        <h1 class="mt-4 text-4xl font-black sm:text-6xl">Book a demo with the ChatFlow AI team.</h1>
        <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">Tell us about your WhatsApp volume, team size and automation goals.</p>
      </div>
      <form class="premium-card grid gap-4">
        <input v-for="field in fields" :key="field" class="rounded-2xl border border-slate-200 bg-white px-4 py-4 outline-none focus:border-violet-500 dark:border-white/10 dark:bg-white/10" :placeholder="field" />
        <textarea class="min-h-32 rounded-2xl border border-slate-200 bg-white px-4 py-4 outline-none focus:border-violet-500 dark:border-white/10 dark:bg-white/10" placeholder="What do you want to automate?" />
        <button class="rounded-2xl bg-violet-600 px-5 py-4 font-black text-white">Send Message</button>
      </form>
    </section>
  </PublicLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const fields = ['Full name', 'Work email', 'Company', 'WhatsApp messages per month'];
</script>
