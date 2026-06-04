<template>
  <DashboardLayout>
    <Head :title="screen" />

    <section v-if="!isDashboard" class="grid min-w-0 gap-4">
      <div class="dash-card overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-indigo-700 text-white">
        <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
          <div>
            <p class="text-sm font-black uppercase text-white/70">ChatFlow AI</p>
            <h1 class="mt-2 text-3xl font-black sm:text-5xl">{{ pageTitle }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">{{ pageSubtitle }}</p>
          </div>
          <button class="w-full rounded-2xl bg-white px-5 py-3 text-sm font-black text-violet-700 shadow-xl sm:w-auto" @click="submitModule">{{ primaryAction }}</button>
        </div>
      </div>

      <section v-if="screen === 'Profile'" class="grid gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
        <div class="dash-card">
          <div class="grid place-items-center text-center">
            <div class="grid size-24 place-items-center rounded-[28px] bg-gradient-to-br from-amber-300 to-pink-500 text-4xl font-black text-white">{{ initial(userName) }}</div>
            <h2 class="mt-4 text-2xl font-black">{{ userName }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ page.props.auth?.user?.email ?? 'admin@chatflow.test' }}</p>
            <span class="mt-4 rounded-full bg-emerald-100 px-4 py-2 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Workspace Owner</span>
          </div>
        </div>
        <div class="grid gap-4">
          <div class="dash-card">
            <h2>Personal Information</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
              <label v-for="field in profileFields" :key="field.label" class="grid gap-2 text-sm font-bold">
                <span>{{ field.label }}</span>
                <input v-model="profileForm[field.key]" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none dark:border-white/10 dark:bg-white/10" />
              </label>
            </div>
          </div>
          <div class="dash-card">
            <h2>Security</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
              <div v-for="item in securityItems" :key="item" class="rounded-2xl bg-slate-50 p-4 text-sm font-bold dark:bg-white/8">{{ item }}</div>
            </div>
          </div>
        </div>
      </section>

      <section v-if="screen === 'API Keys'" class="dash-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h2>Localhost Chat API</h2>
            <p class="mt-2 text-sm font-bold text-slate-500 dark:text-slate-400">Use this endpoint to add incoming or outgoing chat messages from Postman, another app, or your own integration.</p>
            <div class="mt-4 rounded-2xl bg-slate-100 p-4 font-mono text-sm font-black text-slate-700 dark:bg-white/10 dark:text-slate-100">{{ chatApiUrl }}</div>
          </div>
          <div class="w-full rounded-2xl bg-slate-50 p-4 text-sm dark:bg-white/8 lg:max-w-xl">
            <p class="font-black">Headers</p>
            <pre class="mt-2 overflow-x-auto rounded-xl bg-[#0b1020] p-3 text-xs text-white">Authorization: Bearer YOUR_API_KEY
Content-Type: application/json</pre>
            <p class="mt-4 font-black">Body</p>
            <pre class="mt-2 overflow-x-auto rounded-xl bg-[#0b1020] p-3 text-xs text-white">{
  "name": "Ali Khan",
  "phone_number": "+923001234567",
  "message": "Hello from API",
  "direction": "inbound"
}</pre>
          </div>
        </div>
      </section>

      <section v-if="screen === 'Subscription Billing'" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="grid min-w-0 gap-4">
          <div v-if="page.props.flash?.error || !currentSubscription" class="rounded-[24px] border border-amber-200 bg-amber-50 p-5 text-amber-900 shadow-glass dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-100">
            <h2>Subscription Required</h2>
            <p class="mt-2 text-sm font-bold leading-6">Choose a plan to unlock dashboard, CRM, inbox, automations, team tools and integrations. Payment mode: {{ paymentGatewayLabel }}.</p>
          </div>

          <div class="grid gap-4 lg:grid-cols-3">
            <article v-for="plan in billingPlans" :key="plan.key" class="dash-card flex min-h-[320px] flex-col">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h2>{{ plan.name }}</h2>
                  <p class="mt-2 text-4xl font-black">${{ plan.price }}<span class="text-sm font-bold text-slate-500">/mo</span></p>
                </div>
                <span v-if="currentSubscription?.plan === plan.key" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Active</span>
              </div>
              <div class="mt-6 grid gap-3 text-sm font-bold text-slate-600 dark:text-slate-300">
                <p v-for="feature in plan.features" :key="feature" class="flex items-center gap-2">
                  <CheckCircle2 class="size-4 shrink-0 text-emerald-500" />
                  {{ feature }}
                </p>
              </div>
              <button class="mt-auto rounded-2xl bg-violet-600 px-5 py-3 text-sm font-black text-white shadow-glow disabled:opacity-60" :disabled="checkoutForm.processing" @click="startCheckout(plan.key)">
                {{ currentSubscription?.plan === plan.key ? 'Current Plan' : 'Pay & Unlock' }}
              </button>
            </article>
          </div>
        </div>

        <aside class="grid min-w-0 gap-4">
          <section class="dash-card">
            <h2>Current Subscription</h2>
            <div v-if="currentSubscription" class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm dark:bg-white/8">
              <p class="font-black">{{ cleanStatus(currentSubscription.plan) }} Plan</p>
              <p class="mt-1 text-slate-500 dark:text-slate-400">Status: {{ cleanStatus(currentSubscription.status) }}</p>
              <p class="mt-1 text-slate-500 dark:text-slate-400">Bought: {{ currentSubscription.created_at ? dateTime(currentSubscription.created_at) : 'Not set' }}</p>
              <p class="mt-1 text-slate-500 dark:text-slate-400">Renews: {{ currentSubscription.renews_at ? new Date(currentSubscription.renews_at).toLocaleDateString() : 'Not set' }}</p>
            </div>
            <p v-else class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">No active subscription yet.</p>
          </section>

          <section class="dash-card">
            <h2>Invoices</h2>
            <div class="mt-4 grid gap-3">
              <article v-for="invoice in invoiceRows" :key="invoice.id" class="rounded-2xl bg-slate-50 p-3 text-sm dark:bg-white/8">
                <div class="flex items-center justify-between gap-3">
                  <p class="font-black">{{ invoice.number ?? invoice.stripe_invoice_id }}</p>
                  <span class="rounded-full bg-violet-100 px-2 py-1 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">{{ cleanStatus(invoice.status) }}</span>
                </div>
                <p class="mt-1 text-slate-500 dark:text-slate-400">${{ invoice.amount_due }} due / ${{ invoice.amount_paid }} paid</p>
              </article>
              <p v-if="!invoiceRows.length" class="text-sm font-bold text-slate-400">Invoices will appear after checkout.</p>
            </div>
          </section>

          <section class="dash-card">
            <h2>Stripe Test Cards</h2>
            <div class="mt-4 grid gap-3 text-sm">
              <div v-for="card in stripeTestCards" :key="card.number" class="rounded-2xl bg-slate-50 p-3 dark:bg-white/8">
                <p class="font-black">{{ card.label }}</p>
                <p class="mt-1 font-mono text-xs text-slate-600 dark:text-slate-300">{{ card.number }}</p>
                <p class="mt-1 text-xs text-slate-500">Expiry: any future date, CVC: any 3 digits</p>
              </div>
            </div>
          </section>
        </aside>
      </section>

      <section v-if="screen === 'Inbox / Live Chat'" class="dash-card min-w-0 overflow-hidden">
          <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
              <h2>Live WhatsApp Inbox</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Connect Meta WhatsApp Cloud API. Incoming customer messages will arrive through webhook automatically.</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <span class="rounded-full bg-violet-100 px-4 py-2 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">{{ chatRows.length }} Conversations</span>
              <span v-if="totalUnreadChats" class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200">{{ totalUnreadChats }} Unread</span>
              <span v-if="currentSubscription" class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 dark:bg-white/10 dark:text-slate-200">Plan bought {{ relativeTime(currentSubscription.created_at) }}</span>
            </div>
          </div>

        <div class="mt-5 grid gap-4 rounded-[22px] border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5 sm:rounded-3xl sm:p-4 xl:grid-cols-[minmax(0,1fr)_minmax(280px,420px)]">
          <form class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" @submit.prevent="submitModule">
            <label v-for="field in whatsAppSetupFields" :key="field.name" class="grid gap-2 text-sm font-bold">
              <span>{{ field.label }}</span>
              <input v-model="moduleForm[field.name]" :type="field.type ?? 'text'" class="form-control" :placeholder="field.placeholder" />
              <span v-if="moduleForm.errors[field.name]" class="text-xs text-red-500">{{ moduleForm.errors[field.name] }}</span>
            </label>
            <div class="flex items-end">
              <button class="w-full rounded-2xl bg-violet-600 px-5 py-3 text-sm font-black text-white shadow-glow disabled:opacity-60" :disabled="moduleForm.processing">Connect WhatsApp</button>
            </div>
          </form>
          <div class="min-w-0 rounded-2xl bg-white p-4 text-sm dark:bg-[#10182b]">
            <h2>Webhook Setup</h2>
            <p class="mt-2 text-xs font-bold text-slate-500 dark:text-slate-400">Use this URL in Meta Webhooks for messages and status updates.</p>
            <div class="mt-3 overflow-x-auto rounded-xl bg-slate-100 p-3 text-xs font-black text-slate-700 dark:bg-white/10 dark:text-slate-200">{{ activeWebhookUrl }}</div>
            <p class="mt-3 text-xs text-slate-500">Verify token: use the same token you enter in the form.</p>
          </div>
        </div>

        <div class="mt-5 grid overflow-hidden rounded-[22px] border border-slate-200 bg-white dark:border-white/10 dark:bg-[#10182b] sm:rounded-3xl xl:h-[calc(100vh-330px)] xl:min-h-[620px] xl:max-h-[820px] xl:grid-cols-[360px_minmax(0,1fr)]">
          <aside class="flex min-h-0 min-w-0 flex-col border-b border-slate-200 bg-slate-50/80 p-3 dark:border-white/10 dark:bg-white/5 xl:border-b-0 xl:border-r">
            <div class="mb-3 flex items-center gap-2 rounded-2xl bg-white px-3 py-2 dark:bg-white/8">
              <MessageSquare class="size-4 text-slate-400" />
              <input class="min-w-0 flex-1 bg-transparent text-sm font-semibold outline-none placeholder:text-slate-400" placeholder="Search conversations..." />
            </div>
            <div class="app-scrollbar max-h-56 min-h-0 flex-1 overflow-y-auto pr-1 xl:max-h-none">
              <div v-for="chat in chatRows" :key="chat.id ?? chat.name" :class="['mb-2 flex min-w-0 items-center gap-2 rounded-2xl p-2 transition', activeChat?.id === chat.id ? 'bg-violet-600 text-white shadow-glow' : 'bg-white hover:bg-violet-50 dark:bg-white/8 dark:hover:bg-white/12']">
                <button type="button" class="flex min-w-0 flex-1 items-center gap-3 text-left" @click="openConversation(chat)">
                  <div class="grid size-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-rose-500 font-black text-white">{{ initial(chat.name) }}</div>
                  <div class="min-w-0">
                    <p class="truncate text-sm font-black">{{ chat.name }}</p>
                    <p :class="['truncate text-xs', activeChat?.id === chat.id ? 'text-white/70' : 'text-slate-500']">{{ chat.phone_number }}</p>
                    <p v-if="isBlocked(chat)" :class="['mt-1 text-[10px] font-black uppercase', activeChat?.id === chat.id ? 'text-red-100' : 'text-red-500']">Blocked</p>
                  </div>
                  <div class="ml-auto grid justify-items-end gap-1">
                    <span :class="['shrink-0 text-[11px]', activeChat?.id === chat.id ? 'text-white/70' : 'text-slate-400']">{{ relativeTime(chat.last_message_at) }}</span>
                    <span v-if="chat.unread_count" class="rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-black text-white">{{ chat.unread_count }}</span>
                  </div>
                </button>
                <button class="grid size-8 shrink-0 place-items-center rounded-xl bg-amber-500/10 text-[10px] font-black text-amber-500" type="button" @click="toggleContactBlock(chat)">{{ isBlocked(chat) ? 'Un' : 'Blk' }}</button>
                <button class="grid size-8 shrink-0 place-items-center rounded-xl bg-red-500/10 text-xs font-black text-red-500" type="button" @click="deleteChat(chat.id)">Del</button>
              </div>
              <div v-if="!chatRows.length" class="rounded-2xl border border-dashed border-slate-200 p-5 text-center text-sm font-bold text-slate-400 dark:border-white/10">
                No conversations yet. Add a contact from CRM and it will appear here.
              </div>
            </div>
          </aside>

          <section class="flex min-h-[520px] min-w-0 flex-col xl:min-h-0">
            <div class="shrink-0 flex items-center gap-2 border-b border-slate-200 p-3 dark:border-white/10 sm:gap-3 sm:p-4">
              <div class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-rose-500 font-black text-white sm:size-11">{{ initial(activeChat?.name ?? 'C') }}</div>
              <div class="min-w-0">
                <p class="truncate text-sm font-black">{{ activeChat?.name ?? 'Select a conversation' }}</p>
                <p class="truncate text-xs text-slate-500">{{ activeChat?.phone_number ?? 'Contact chat will show here' }}</p>
              </div>
              <span v-if="activeChat" :class="['ml-auto hidden rounded-full px-3 py-1 text-xs font-black sm:inline-flex', activeChatBlocked ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-200' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300']">{{ activeChatBlocked ? 'Blocked' : 'Open' }}</span>
              <button v-if="activeChat" class="rounded-xl bg-amber-50 px-2 py-2 text-[11px] font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-200 sm:px-3 sm:text-xs" type="button" @click="toggleContactBlock(activeChat)">{{ activeChatBlocked ? 'Unblock' : 'Block' }}</button>
              <button v-if="activeChat" class="rounded-xl bg-red-50 px-2 py-2 text-[11px] font-black text-red-600 dark:bg-red-500/10 sm:px-3 sm:text-xs" type="button" @click="deleteChat(activeChat.id)">Delete</button>
              <button v-if="activeChat?.contact_id" class="hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-black text-red-700 dark:bg-red-500/10 dark:text-red-200 sm:inline-flex" type="button" @click="deleteContact(activeChat.contact_id)">Delete Contact</button>
            </div>

            <div ref="messagesPanel" class="app-scrollbar min-h-0 flex-1 space-y-3 overflow-y-auto p-3 text-sm sm:p-4">
              <div v-for="message in messageRows" :key="message.id ?? message.body" :class="[message.direction === 'outbound' ? 'ml-auto bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white' : 'bg-slate-100 dark:bg-white/10', 'max-w-[92%] rounded-2xl p-3 sm:max-w-[86%]']">
                <a v-if="message.media_path && isImage(message.media_mime_type)" :href="message.media_path" target="_blank" class="mb-2 block overflow-hidden rounded-xl bg-black/10">
                  <img :src="message.media_path" class="max-h-72 w-full object-cover" alt="message attachment" />
                </a>
                <a v-else-if="message.media_path" :href="message.media_path" target="_blank" class="mb-2 flex items-center gap-2 rounded-xl bg-white/15 px-3 py-2 text-xs font-black">
                  <span>Document</span>
                  <span class="truncate">{{ mediaName(message) }}</span>
                </a>
                <p>{{ message.body }}</p>
                <div class="mt-2 flex flex-wrap items-center justify-end gap-2 text-[11px]">
                  <span :class="message.direction === 'outbound' ? 'text-white/75' : 'text-slate-500 dark:text-slate-400'">{{ dateTime(message.sent_at ?? message.created_at) }}</span>
                  <span :class="message.direction === 'outbound' ? 'text-white/60' : 'text-slate-400'">{{ relativeTime(message.sent_at ?? message.created_at) }}</span>
                  <button class="rounded-lg bg-black/10 px-2 py-1 font-black text-red-200 hover:bg-black/20" type="button" @click="deleteMessage(message.id)">Delete</button>
                  <span class="font-black uppercase">{{ cleanStatus(message.status) }}</span>
                  <span :class="message.direction === 'outbound' ? messageStatusClass(message.status) : 'font-black text-slate-400 dark:text-slate-500'">{{ messageTickIcon(message.status) }}</span>
                </div>
              </div>
              <div v-if="activeChat && !messageRows.length" class="grid min-h-[360px] place-items-center text-center">
                <div>
                  <MessageSquare class="mx-auto size-10 text-slate-300" />
                  <p class="mt-3 text-sm font-black text-slate-500">No messages yet</p>
                  <p class="mt-1 text-xs text-slate-400">Type below to start this conversation.</p>
                </div>
              </div>
            </div>

            <div class="shrink-0 flex gap-2 border-t border-slate-200 p-2 dark:border-white/10 sm:p-3">
              <input v-model="draft" class="min-w-0 flex-1 rounded-2xl bg-slate-100 px-3 py-3 text-sm font-semibold outline-none disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white/10 sm:px-4" :disabled="!activeChat || activeChatBlocked" :placeholder="activeChatBlocked ? 'Contact is blocked. Unblock to send a message.' : 'Type a message...'" @keyup.enter="sendMessage" />
              <label :class="['grid size-11 shrink-0 place-items-center rounded-2xl bg-slate-100 text-sm font-black text-slate-600 dark:bg-white/10 dark:text-white sm:size-12', activeChatBlocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer']">
                +
                <input class="hidden" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv" :disabled="activeChatBlocked" @change="selectAttachment" />
              </label>
              <button type="button" class="grid size-11 shrink-0 place-items-center rounded-2xl bg-violet-600 text-white disabled:opacity-50 sm:size-12" :disabled="!activeChat || sendingMessage || activeChatBlocked" @click.prevent="sendMessage"><Send class="size-5" /></button>
            </div>
            <div v-if="selectedAttachmentName" class="shrink-0 border-t border-slate-200 px-4 py-2 text-xs font-bold text-slate-500 dark:border-white/10">
              Attached: {{ selectedAttachmentName }}
              <button class="ml-2 text-red-500" type="button" @click="clearAttachment">remove</button>
            </div>
            <div v-if="page.props.errors?.attachment || page.props.errors?.body" class="shrink-0 border-t border-red-200 px-4 py-2 text-xs font-black text-red-500 dark:border-red-500/20">
              {{ page.props.errors?.attachment ?? page.props.errors?.body }}
            </div>
          </section>
        </div>
      </section>

      <section v-if="screen === 'Contacts CRM'" class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="grid min-w-0 gap-4">
          <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article v-for="stat in crmStats" :key="stat.label" class="dash-card">
              <p class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
              <p class="mt-2 text-2xl font-black">{{ stat.value }}</p>
              <p class="mt-1 text-xs font-black text-emerald-500">{{ stat.help }}</p>
            </article>
          </div>

          <div class="grid min-w-0 gap-4 xl:grid-cols-5">
            <article v-for="stage in crmStages" :key="stage.key" class="dash-card min-h-[320px]">
              <div class="mb-4 flex items-center justify-between gap-2">
                <h2>{{ stage.label }}</h2>
                <span class="rounded-full bg-violet-100 px-2 py-1 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">{{ pipelineFor(stage.key).length }}</span>
              </div>
              <div class="grid gap-3">
                <div v-for="contact in pipelineFor(stage.key)" :key="contact.id ?? contact.name" class="rounded-2xl bg-slate-50 p-3 dark:bg-white/8">
                  <div class="flex items-start gap-3">
                    <div class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-orange-300 to-pink-500 text-sm font-black text-white">{{ initial(contact.name) }}</div>
                    <div class="min-w-0">
                      <p class="truncate text-sm font-black">{{ contact.name }}</p>
                      <p class="truncate text-xs text-slate-500">{{ contact.phone_number }}</p>
                    </div>
                  </div>
                  <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                    <span class="font-black text-slate-500 dark:text-slate-400">{{ money(contact.deal_value ?? contact.value ?? 0) }}</span>
                    <button v-if="stage.key !== 'blocked'" class="rounded-lg bg-white px-3 py-1 font-black text-violet-700 disabled:opacity-50 dark:bg-white/10 dark:text-violet-200" :disabled="stageForm.processing" @click="moveContact(contact, nextStage(stage.key))">
                      Move
                    </button>
                    <button class="rounded-lg bg-amber-50 px-3 py-1 font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-200" @click="toggleContactBlock(contact)">{{ isBlocked(contact) ? 'Unblock' : 'Block' }}</button>
                    <button class="rounded-lg bg-red-50 px-3 py-1 font-black text-red-600 dark:bg-red-500/10" @click="deleteContact(contact.id)">Delete</button>
                  </div>
                </div>
                <p v-if="!pipelineFor(stage.key).length" class="rounded-2xl border border-dashed border-slate-200 p-4 text-sm font-bold text-slate-400 dark:border-white/10">No contacts in this stage.</p>
              </div>
            </article>
          </div>
        </div>

        <aside class="grid min-w-0 gap-4">
          <section class="dash-card">
            <h2>Follow-up Note</h2>
            <form class="mt-4 grid gap-3" @submit.prevent="saveNote">
              <label class="grid gap-2 text-sm font-bold">
                <span>Contact</span>
                <select v-model="noteForm.contact_id" class="form-control">
                  <option value="">Select contact</option>
                  <option v-for="contact in crmContacts" :key="contact.id ?? contact.name" :value="contact.id">{{ contact.name }}</option>
                </select>
              </label>
              <label class="grid gap-2 text-sm font-bold">
                <span>Note</span>
                <textarea v-model="noteForm.body" rows="5" class="form-control resize-none" placeholder="Call summary, requirements, quotation details..." />
                <span v-if="noteForm.errors.body" class="text-xs text-red-500">{{ noteForm.errors.body }}</span>
              </label>
              <label class="grid gap-2 text-sm font-bold">
                <span>Next Follow-up</span>
                <input v-model="noteForm.next_follow_up_at" type="datetime-local" class="form-control" />
              </label>
              <button class="rounded-2xl bg-violet-600 px-5 py-3 text-sm font-black text-white shadow-glow disabled:opacity-60" :disabled="noteForm.processing">Save Note</button>
            </form>
          </section>

          <section class="dash-card">
            <div class="flex items-center justify-between gap-3">
              <h2>Recent Notes</h2>
              <span class="text-xs font-black text-violet-600">{{ crmNotes.length }}</span>
            </div>
            <div class="mt-4 grid gap-3">
              <article v-for="note in crmNotes.slice(0, 6)" :key="note.id" class="rounded-2xl bg-slate-50 p-3 dark:bg-white/8">
                <p class="text-xs font-black text-violet-600">{{ note.contact_name }}</p>
                <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ note.body }}</p>
                <p class="mt-2 text-xs text-slate-400">{{ relativeTime(note.created_at) }}</p>
              </article>
              <p v-if="!crmNotes.length" class="text-sm font-bold text-slate-400">Notes yahan save hotay jayen ge.</p>
            </div>
          </section>
        </aside>
      </section>

      <section v-else-if="screen !== 'Profile' && screen !== 'Subscription Billing' && screen !== 'Inbox / Live Chat'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="card in moduleCards" :key="card.title" class="dash-card min-h-44">
          <div class="mb-5 grid size-11 place-items-center rounded-2xl bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">
            <component :is="card.icon" class="size-5" />
          </div>
          <h2>{{ card.title }}</h2>
          <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ card.text }}</p>
          <button class="mt-5 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-violet-100 hover:text-violet-700 dark:bg-white/10 dark:text-white dark:hover:bg-violet-500/20 dark:hover:text-violet-200" type="button" @click="openModuleCard(card)">Open</button>
        </article>
      </section>

      <section v-if="formFields.length" class="dash-card">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
          <div>
            <h2>{{ primaryAction }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Fill this form and save directly into the `whatsapp` database.</p>
          </div>
        </div>
        <div v-if="screen === 'Subscription Billing' && !recordsForScreen().length" class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800 dark:border-amber-400/20 dark:bg-amber-500/10 dark:text-amber-200">
          Your account is created, but CRM access is locked until a subscription is active. Choose a plan below to unlock the dashboard, contacts, inbox, automations and team tools.
        </div>
        <form class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="submitModule">
          <label v-for="field in formFields" :key="field.name" class="grid gap-2 text-sm font-bold">
            <span>{{ field.label }}</span>
            <select v-if="field.options" v-model="moduleForm[field.name]" class="form-control">
              <option v-for="option in field.options" :key="optionKey(option)" :value="optionValue(option)">{{ optionLabel(option) }}</option>
            </select>
            <input v-else v-model="moduleForm[field.name]" :type="field.type ?? 'text'" class="form-control" :placeholder="field.name === 'phone_number' ? phonePlaceholder : field.placeholder" />
            <span v-if="moduleForm.errors[field.name]" class="text-xs text-red-500">{{ moduleForm.errors[field.name] }}</span>
          </label>
          <div class="flex items-end">
            <button class="w-full rounded-2xl bg-violet-600 px-5 py-3 text-sm font-black text-white shadow-glow disabled:opacity-60" :disabled="moduleForm.processing">{{ primaryAction }}</button>
          </div>
        </form>
      </section>

      <section v-if="screen !== 'Inbox / Live Chat'" class="dash-card overflow-hidden">
        <div class="flex items-center justify-between gap-3">
          <h2>{{ pageTitle }} Records</h2>
          <input v-model="recordsSearch" class="w-full max-w-xs rounded-xl bg-slate-100 px-4 py-2 text-sm outline-none dark:bg-white/10" placeholder="Search records..." />
        </div>
        <div class="mt-5 overflow-x-auto">
          <table class="w-full min-w-[680px] text-left text-sm">
            <thead class="text-slate-500">
              <tr><th class="py-3">Name</th><th>Status</th><th>Owner</th><th>Updated</th><th>Action</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in filteredTableRows" :key="row.key" class="border-t border-slate-200 dark:border-white/10">
                <td class="py-4 font-black">{{ row.name }}</td>
                <td><span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">{{ row.status }}</span></td>
                <td>{{ row.owner }}</td>
                <td>{{ row.updated }}</td>
                <td><button class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-black transition hover:bg-violet-100 hover:text-violet-700 dark:bg-white/10 dark:hover:bg-violet-500/20 dark:hover:text-violet-200" type="button" @click="openRecord(row)">View</button></td>
              </tr>
            </tbody>
          </table>
          <p v-if="!filteredTableRows.length" class="py-8 text-center text-sm font-bold text-slate-400">No records found.</p>
        </div>
      </section>
    </section>

    <section v-else :class="['grid min-w-0 gap-4', isSuperAdmin ? '' : '2xl:grid-cols-[minmax(0,1fr)_420px]']">
      <div class="grid min-w-0 gap-4">
        <section class="relative overflow-hidden rounded-[24px] bg-gradient-to-br from-violet-700 via-violet-600 to-indigo-700 p-5 text-white shadow-glow sm:p-6">
          <div class="absolute inset-y-0 right-0 hidden w-[28%] bg-[radial-gradient(circle_at_58%_50%,rgba(255,255,255,.22),transparent_42%)] xl:block" />
          <div class="relative z-10 max-w-4xl xl:max-w-[calc(100%-160px)]">
            <h1 class="text-2xl font-black sm:text-3xl">Welcome back, {{ userName }}!</h1>
            <p class="mt-2 text-sm font-medium text-white/80">{{ isSuperAdmin ? 'Your platform revenue, customers and subscriptions are live below.' : `Your AI assistant is working hard for ${workspace?.name ?? 'your business'} today.` }}</p>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <article v-for="stat in statCards" :key="stat.label" class="min-w-0 rounded-2xl border border-white/15 bg-white/12 p-4 backdrop-blur-xl">
                <div class="mb-4 grid size-9 place-items-center rounded-xl bg-white/16">
                  <component :is="stat.icon" class="size-5" />
                </div>
                <p class="truncate text-xs font-bold text-white/75">{{ stat.label }}</p>
                <p class="mt-1 truncate text-2xl font-black">{{ stat.value }}</p>
                <p class="mt-1 text-xs font-black text-emerald-300">{{ isSuperAdmin ? stat.change : `+ ${stat.change}%` }}</p>
              </article>
            </div>
            <p v-if="!isSuperAdmin && currentSubscription" class="mt-4 inline-flex rounded-full bg-white/14 px-4 py-2 text-xs font-black text-white/85 ring-1 ring-white/15">
              {{ cleanStatus(currentSubscription.plan) }} plan bought {{ relativeTime(currentSubscription.created_at) }} - Renews {{ currentSubscription.renews_at ? new Date(currentSubscription.renews_at).toLocaleDateString() : 'not set' }}
            </p>
          </div>
          <div class="pointer-events-none absolute bottom-5 right-5 z-0 hidden xl:block 2xl:right-8">
            <div class="grid size-28 place-items-center rounded-[30px] bg-white/12 ring-1 ring-white/15 backdrop-blur-xl 2xl:size-32">
              <Bot class="size-16 text-cyan-200 2xl:size-20" />
            </div>
          </div>
        </section>

        <section v-if="isSuperAdmin" class="grid gap-4">
          <div class="dash-card border-violet-200 bg-violet-50/80 dark:border-violet-400/20 dark:bg-violet-500/10">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
              <div>
                <p class="text-xs font-black uppercase text-violet-600 dark:text-violet-300">Platform Owner</p>
                <h2 class="mt-1 text-xl font-black">John Doe Super Admin Control Center</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Manage all customer workspaces, users, subscriptions, renewals and plan limits from here.</p>
              </div>
              <span class="rounded-full bg-white px-4 py-2 text-xs font-black text-violet-700 shadow-sm dark:bg-white/10 dark:text-violet-200">admin@chatflow.test</span>
            </div>
            <div v-if="platformDetailStats.length" class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <article v-for="stat in platformDetailStats" :key="stat.label" class="rounded-2xl bg-white p-4 shadow-sm dark:bg-white/[.06]">
                <p class="text-xs font-black uppercase text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
                <p class="mt-2 text-2xl font-black">{{ stat.value }}</p>
                <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ stat.help }}</p>
              </article>
            </div>
          </div>

          <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(280px,.7fr)]">
            <section class="dash-card">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <h2>Platform Earnings</h2>
                  <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Paid invoice revenue from all customer subscriptions.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Last 30 days</span>
              </div>
              <svg viewBox="0 0 760 280" class="mt-4 h-56 w-full">
                <g stroke="currentColor" stroke-opacity=".14" stroke-width="1">
                  <path v-for="y in [56,112,168,224]" :key="y" :d="`M40 ${y} H730`" />
                </g>
                <path :d="platformRevenuePath" fill="none" stroke="#10b981" stroke-width="5" stroke-linecap="round" />
                <g fill="#10b981"><circle v-for="point in platformRevenuePoints" :key="`${point.x}-${point.y}`" :cx="point.x" :cy="point.y" r="4" /></g>
                <g class="text-[11px] font-bold text-slate-400">
                  <text v-for="label in platformRevenueLabels" :key="label.text" :x="label.x" y="266" text-anchor="middle" fill="currentColor">{{ label.text }}</text>
                </g>
              </svg>
            </section>

            <section class="dash-card">
              <h2>Plan Breakdown</h2>
              <div class="mt-4 grid gap-3">
                <article v-for="plan in platformPlanBreakdown" :key="`${plan.plan}-${plan.status}`" class="rounded-2xl bg-slate-50 p-3 dark:bg-white/[.06]">
                  <div class="flex items-center justify-between gap-3">
                    <p class="font-black">{{ cleanStatus(plan.plan) }}</p>
                    <span :class="['rounded-full px-2 py-1 text-xs font-black', plan.status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300']">{{ cleanStatus(plan.status) }}</span>
                  </div>
                  <p class="mt-2 text-2xl font-black">{{ plan.total }}</p>
                </article>
                <p v-if="!platformPlanBreakdown.length" class="text-sm font-bold text-slate-400">No subscription data yet.</p>
              </div>
            </section>
          </div>

          <section v-if="platformExpiringSoon.length" class="dash-card border-amber-200 bg-amber-50/80 dark:border-amber-400/20 dark:bg-amber-500/10">
            <h2>Renewal Reminders</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              <article v-for="item in platformExpiringSoon" :key="`${item.workspace_name}-${item.renews_at}`" class="rounded-2xl bg-white p-4 dark:bg-white/[.06]">
                <p class="font-black">{{ item.workspace_name }}</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ cleanStatus(item.plan) }} renews on {{ new Date(item.renews_at).toLocaleDateString() }}</p>
              </article>
            </div>
          </section>

          <div class="grid gap-4 xl:grid-cols-3">
            <section class="dash-card xl:col-span-2">
              <h2>Recent Payments</h2>
              <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                  <thead class="text-slate-500">
                    <tr><th class="py-3">Invoice</th><th>Workspace</th><th>Amount</th><th>Status</th><th>Paid</th></tr>
                  </thead>
                  <tbody>
                    <tr v-for="invoice in platformRecentInvoices" :key="invoice.id" class="border-t border-slate-200 dark:border-white/10">
                      <td class="py-3 font-black">{{ invoice.number ?? `INV-${invoice.id}` }}</td>
                      <td>{{ invoice.workspace_name }}</td>
                      <td>${{ Number(invoice.amount_paid || invoice.amount_due || 0).toLocaleString() }}</td>
                      <td><span :class="['rounded-full px-3 py-1 text-xs font-black', invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300']">{{ cleanStatus(invoice.status) }}</span></td>
                      <td>{{ invoice.paid_at ? new Date(invoice.paid_at).toLocaleDateString() : 'Pending' }}</td>
                    </tr>
                  </tbody>
                </table>
                <p v-if="!platformRecentInvoices.length" class="py-6 text-center text-sm font-bold text-slate-400">No payments yet.</p>
              </div>
            </section>

            <section class="dash-card">
              <h2>Recent Users</h2>
              <div class="mt-4 grid gap-3">
                <article v-for="user in platformUsers.slice(0, 6)" :key="user.id" class="flex min-w-0 items-center gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-white/[.06]">
                  <div class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-pink-500 text-xs font-black text-white">{{ initial(user.name) }}</div>
                  <div class="min-w-0">
                    <p class="truncate text-sm font-black">{{ user.name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ user.email }}</p>
                    <p class="truncate text-xs text-violet-500">{{ user.workspace_name ?? 'No workspace' }}</p>
                  </div>
                </article>
                <p v-if="!platformUsers.length" class="text-sm font-bold text-slate-400">No users yet.</p>
              </div>
            </section>
          </div>

          <div class="dash-card overflow-hidden">
            <div class="flex items-center justify-between gap-3">
              <h2>Customer Workspaces</h2>
              <input v-model="platformSearch" class="w-full max-w-xs rounded-xl bg-slate-100 px-4 py-2 text-sm outline-none dark:bg-white/10" placeholder="Search customers..." />
            </div>
            <div class="mt-5 overflow-x-auto">
              <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="text-slate-500">
                  <tr><th class="py-3">Workspace</th><th>Owner</th><th>Plan</th><th>Status</th><th>Renews</th><th>Control</th></tr>
                </thead>
                <tbody>
                  <tr v-for="workspaceRow in filteredPlatformWorkspaces" :key="workspaceRow.id" class="border-t border-slate-200 dark:border-white/10">
                    <td class="py-4">
                      <p class="font-black">{{ workspaceRow.name }}</p>
                      <p class="text-xs text-slate-500">{{ workspaceRow.slug }}</p>
                    </td>
                    <td>
                      <p class="font-bold">{{ workspaceRow.owner_name ?? 'No owner' }}</p>
                      <p class="text-xs text-slate-500">{{ workspaceRow.owner_email ?? 'Not assigned' }}</p>
                    </td>
                    <td><span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-black text-violet-700 dark:bg-violet-500/15 dark:text-violet-200">{{ cleanStatus(workspaceRow.plan) }}</span></td>
                    <td><span :class="['rounded-full px-3 py-1 text-xs font-black', workspaceRow.subscription_status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300']">{{ cleanStatus(workspaceRow.subscription_status ?? 'not active') }}</span></td>
                    <td>{{ workspaceRow.renews_at ? new Date(workspaceRow.renews_at).toLocaleDateString() : 'Not set' }}</td>
                    <td>
                      <div class="flex gap-2">
                        <select v-model="workspaceRow.adminPlan" class="dashboard-select">
                          <option value="starter">Starter</option>
                          <option value="pro">Pro</option>
                          <option value="agency">Agency</option>
                        </select>
                        <select v-model="workspaceRow.adminStatus" class="dashboard-select">
                          <option value="active">Active</option>
                          <option value="expired">Expired</option>
                          <option value="canceled">Canceled</option>
                        </select>
                        <button class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-black text-white shadow-glow disabled:opacity-60" :disabled="adminSubscriptionForm.processing" type="button" @click="updateCustomerSubscription(workspaceRow)">Save</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p v-if="!filteredPlatformWorkspaces.length" class="py-8 text-center text-sm font-bold text-slate-400">No customers found.</p>
            </div>
          </div>
        </section>

        <div v-if="!isSuperAdmin" class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(260px,.7fr)]">
          <section class="dash-card min-w-0">
            <div class="flex items-center justify-between gap-3">
              <h2>Messages Overview</h2>
              <select v-model="selectedChartPeriod" class="dashboard-select shrink-0" @change="changeChartPeriod">
                <option v-for="option in chartPeriodOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
              </select>
            </div>
            <svg viewBox="0 0 760 300" class="mt-4 h-56 w-full sm:h-72">
              <g stroke="currentColor" stroke-opacity=".16" stroke-width="1">
                <path v-for="y in [60,120,180,240]" :key="y" :d="`M40 ${y} H730`" />
              </g>
              <path :d="receivedChartPath" fill="none" stroke="#7c3aed" stroke-width="5" stroke-linecap="round" />
              <path :d="sentChartPath" fill="none" stroke="#14b8a6" stroke-width="5" stroke-linecap="round" />
              <g fill="#7c3aed"><circle v-for="point in receivedChartPoints" :key="`r-${point.x}-${point.y}`" :cx="point.x" :cy="point.y" r="5" /></g>
              <g fill="#14b8a6"><circle v-for="point in sentChartPoints" :key="`s-${point.x}-${point.y}`" :cx="point.x" :cy="point.y" r="5" /></g>
              <g class="text-[11px] font-bold text-slate-400">
                <text v-for="label in chartLabels" :key="label.text" :x="label.x" y="286" text-anchor="middle" fill="currentColor">{{ label.text }}</text>
              </g>
            </svg>
            <div class="mt-2 flex items-center gap-5 text-xs font-bold text-slate-500 dark:text-slate-400">
              <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-violet-600" /> Received</span>
              <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-teal-500" /> Sent</span>
            </div>
          </section>

          <section class="dash-card min-w-0">
            <div class="flex items-center justify-between"><h2>Top Channels</h2><MoreHorizontal class="size-5 text-slate-400" /></div>
            <div class="mx-auto mt-6 grid size-36 place-items-center rounded-full sm:size-40" :style="channelConicStyle">
              <div class="grid size-24 place-items-center rounded-full bg-white text-center shadow-inner dark:bg-[#10182b] sm:size-28">
                <p class="text-xl font-black sm:text-2xl">{{ totalMessages }}</p>
                <p class="text-xs text-slate-500">Total</p>
              </div>
            </div>
            <div class="mt-6 space-y-3 text-sm">
              <p v-for="channel in channels" :key="channel.name" class="flex items-center gap-2">
                <span :class="['size-2 rounded-full', channel.color]" />
                <span class="font-bold">{{ channel.name }}</span>
                <span class="ml-auto text-slate-500">{{ channel.value }}</span>
              </p>
            </div>
          </section>
        </div>

        <div v-if="!isSuperAdmin" class="grid min-w-0 gap-4 xl:grid-cols-2">
          <section class="dash-card dark-card min-w-0">
            <div class="flex items-center justify-between"><h2>Top Performing Channels</h2><MoreHorizontal class="size-5 text-slate-400" /></div>
            <div class="mt-6 space-y-4">
              <div v-for="channel in channels" :key="channel.name" class="grid grid-cols-[96px_minmax(0,1fr)_56px] items-center gap-3 text-sm sm:grid-cols-[110px_minmax(0,1fr)_64px]">
                <span class="truncate font-bold">{{ channel.name }}</span>
                <span class="h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                  <span class="block h-full rounded-full bg-gradient-to-r from-violet-600 to-indigo-500" :style="{ width: channel.width }" />
                </span>
                <span class="text-right font-black">{{ channel.value }}</span>
              </div>
            </div>
          </section>

          <section class="dash-card dark-card min-w-0">
            <div class="flex items-center justify-between"><h2>Recent Leads</h2><a href="/app/contacts" class="text-xs font-black text-violet-600 hover:text-violet-500">View all</a></div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
              <div v-for="lead in leadRows" :key="lead.id ?? lead.name" class="flex min-w-0 items-center gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-white/8">
                <div class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-orange-300 to-pink-500 text-sm font-black text-white">{{ initial(lead.name) }}</div>
                <div class="min-w-0">
                  <p class="truncate text-sm font-black">{{ lead.name }}</p>
                  <p class="truncate text-xs text-slate-500">{{ lead.phone_number }}</p>
                </div>
                <span class="ml-auto shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ cleanStatus(lead.status) }}</span>
              </div>
            </div>
          </section>
        </div>
      </div>

      <aside v-if="!isSuperAdmin" class="grid min-w-0 gap-4">
        <section class="dash-card min-w-0">
          <div class="flex items-center justify-between gap-3"><h2>WhatsApp Accounts</h2><button class="shrink-0 rounded-xl bg-violet-100 px-3 py-2 text-xs font-black text-violet-700">+ Add New</button></div>
          <div class="mt-4 space-y-3">
            <div v-for="account in accountRows" :key="account.id ?? account.name" class="flex min-w-0 items-center gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-white/8">
              <div class="grid size-11 shrink-0 place-items-center rounded-full bg-whatsapp text-white"><Phone class="size-5" /></div>
              <div class="min-w-0">
                <p class="truncate text-sm font-black">{{ account.name }}</p>
                <p class="truncate text-xs text-slate-500">{{ account.phone_number }}</p>
                <p class="text-xs font-black text-emerald-500">{{ cleanStatus(account.status) }}</p>
              </div>
              <span class="ml-auto rounded-full bg-emerald-500 px-2 py-0.5 text-xs font-black text-white">{{ account.id ?? 1 }}</span>
            </div>
          </div>
        </section>

        <section class="dash-card min-w-0">
          <div class="flex items-center justify-between"><h2>Recent Activity</h2><a href="/app/activity" class="text-xs font-black text-violet-600 hover:text-violet-500">View all</a></div>
          <div class="mt-4 space-y-3">
            <div v-for="activity in activityRows" :key="activity.id ?? activity.description" class="flex min-w-0 items-center gap-3 text-sm">
              <div class="grid size-8 shrink-0 place-items-center rounded-xl bg-emerald-500 text-white"><CheckCircle2 class="size-4" /></div>
              <p class="min-w-0 flex-1 truncate font-bold">{{ activity.description }}</p>
              <span class="shrink-0 text-xs text-slate-500">{{ relativeTime(activity.created_at) }}</span>
            </div>
          </div>
        </section>

        <section class="dash-card min-w-0 overflow-hidden">
          <div class="flex items-center justify-between">
            <h2>Inbox <span class="rounded-full bg-pink-100 px-2 py-1 text-xs text-pink-600">{{ chatRows.length }}</span></h2>
            <MoreVertical class="size-5 text-slate-400" />
          </div>
          <div class="mt-4 grid grid-cols-4 gap-1 rounded-2xl bg-slate-100 p-1 text-center text-[11px] font-black dark:bg-white/10 sm:text-xs">
            <button v-for="filter in dashboardInboxFilters" :key="filter.value" :class="['rounded-xl py-2 transition', dashboardInboxFilter === filter.value ? 'bg-white text-violet-600 shadow-sm dark:bg-violet-600 dark:text-white' : 'text-slate-500 hover:text-violet-600 dark:text-slate-300']" type="button" @click="dashboardInboxFilter = filter.value">{{ filter.label }}</button>
          </div>
          <div class="mt-4 grid min-h-[500px] overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10 2xl:grid-cols-[minmax(0,.9fr)_minmax(0,1.1fr)]">
            <div class="min-w-0 border-slate-200 bg-slate-50/60 p-2 dark:border-white/10 dark:bg-white/5 2xl:border-r">
              <div v-for="chat in filteredDashboardChats" :key="chat.id ?? chat.name" class="mb-2 flex min-w-0 items-center gap-3 rounded-2xl bg-white p-3 shadow-sm dark:bg-white/8">
                <div class="grid size-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-rose-500 text-white">{{ initial(chat.name) }}</div>
                <div class="min-w-0"><p class="truncate text-sm font-black">{{ chat.name }}</p><p class="truncate text-xs text-slate-500">{{ chat.phone_number }}</p><p v-if="isBlocked(chat)" class="text-[10px] font-black uppercase text-red-500">Blocked</p></div>
                <span class="ml-auto shrink-0 text-[11px] text-slate-400">{{ relativeTime(chat.last_message_at) }}</span>
              </div>
              <div v-if="!filteredDashboardChats.length" class="grid min-h-40 place-items-center rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center dark:border-white/10 dark:bg-white/[.04]">
                <div>
                  <MessageSquare class="mx-auto size-8 text-slate-400" />
                  <p class="mt-3 text-sm font-black text-slate-700 dark:text-slate-200">{{ dashboardEmptyTitle }}</p>
                  <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ dashboardEmptySubtitle }}</p>
                </div>
              </div>
            </div>
            <div class="hidden min-w-0 flex-col bg-white dark:bg-[#10182b] 2xl:flex">
              <div class="flex items-center gap-3 border-b border-slate-200 p-3 dark:border-white/10">
                <div class="grid size-10 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-rose-500 text-white">{{ initial(activeChat?.name ?? 'E') }}</div>
                <div class="min-w-0"><p class="truncate text-sm font-black">{{ activeChat?.name ?? 'Emily Johnson' }}</p><p class="truncate text-xs text-slate-500">{{ activeChat?.phone_number ?? '+1 (556) 123-4567' }}</p></div>
              </div>
              <div class="flex-1 space-y-3 p-4 text-sm">
              <div v-for="message in messageRows" :key="message.id ?? message.body" :class="[message.direction === 'outbound' ? 'ml-auto bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white' : 'bg-slate-100 dark:bg-white/10', 'max-w-[86%] rounded-2xl p-3']">
                <p>{{ message.body }}</p>
                <div class="mt-2 flex flex-wrap items-center justify-end gap-2 text-[10px]">
                  <span :class="message.direction === 'outbound' ? 'text-white/70' : 'text-slate-500 dark:text-slate-400'">{{ dateTime(message.sent_at ?? message.created_at) }}</span>
                  <span class="font-black uppercase">{{ cleanStatus(message.status) }}</span>
                  <span :class="message.direction === 'outbound' ? messageStatusClass(message.status) : 'font-black text-slate-400 dark:text-slate-500'">{{ messageTickIcon(message.status) }}</span>
                </div>
              </div>
              <div v-if="!filteredDashboardChats.length" class="grid min-h-64 place-items-center text-center">
                <div>
                  <MessageSquare class="mx-auto size-10 text-slate-400" />
                  <p class="mt-3 text-sm font-black text-slate-700 dark:text-slate-200">{{ dashboardEmptyTitle }}</p>
                  <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ dashboardEmptySubtitle }}</p>
                </div>
              </div>
              </div>
              <div class="flex gap-2 border-t border-slate-200 p-3 dark:border-white/10">
                <input v-model="draft" class="min-w-0 flex-1 rounded-xl bg-slate-100 px-3 text-sm outline-none disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white/10" :disabled="activeChatBlocked" :placeholder="activeChatBlocked ? 'Contact is blocked.' : 'Type a message...'" @keyup.enter="sendMessage" />
                <button class="grid size-11 shrink-0 place-items-center rounded-xl bg-violet-600 text-white disabled:opacity-60" :disabled="messageForm.processing || activeChatBlocked" @click="sendMessage"><Send class="size-5" /></button>
              </div>
            </div>
          </div>
        </section>
      </aside>
    </section>

    <div v-if="viewRecord" class="fixed inset-0 z-[80] grid place-items-end bg-slate-950/55 p-3 backdrop-blur-sm sm:place-items-center sm:p-5" @click.self="closeRecord">
      <section class="app-scrollbar max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-[24px] border border-white/80 bg-white p-4 shadow-glass dark:border-white/10 dark:bg-[#10182b] sm:p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="text-xs font-black uppercase text-violet-600 dark:text-violet-300">{{ pageTitle }} Detail</p>
            <h2 class="mt-2 truncate text-2xl font-black">{{ viewRecord.name }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ viewRecord.status }} - {{ viewRecord.updated }}</p>
          </div>
          <button class="grid size-10 shrink-0 place-items-center rounded-2xl bg-slate-100 text-slate-500 hover:text-slate-900 dark:bg-white/10 dark:text-slate-300 dark:hover:text-white" type="button" @click="closeRecord">x</button>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <div v-for="field in recordDetailFields" :key="field.label" class="rounded-2xl bg-slate-50 p-4 dark:bg-white/[.06]">
            <p class="text-xs font-black uppercase text-slate-400">{{ field.label }}</p>
            <p class="mt-2 break-words text-sm font-bold text-slate-800 dark:text-slate-100">{{ field.value }}</p>
          </div>
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 p-4 dark:border-white/10">
          <h3 class="text-sm font-black">Raw Record</h3>
          <pre class="app-scrollbar mt-3 max-h-64 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100">{{ prettyRecord(viewRecord.raw) }}</pre>
        </div>
      </section>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Bot, CheckCircle2, MessageSquare, MoreHorizontal, MoreVertical, Phone, Send, ShieldCheck, UserPlus, Users } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

type Row = Record<string, any>;

const props = defineProps<{ screen: string; workspace?: Row | null; dashboard?: Row; module?: Row; isSuperAdmin?: boolean; platform?: Row | null }>();
const page = usePage();
const draft = ref('');
const selectedConversationId = ref<number | string | null>(null);
const selectedChartPeriod = ref(props.dashboard?.chartPeriod ?? 'week');
const dashboardInboxFilter = ref('all');
const recordsSearch = ref('');
const platformSearch = ref('');
const selectedAttachmentName = ref('');
const selectedAttachmentFile = ref<File | null>(null);
const sendingMessage = ref(false);
const messagesPanel = ref<HTMLElement | null>(null);
const localMessages = ref<Row[]>([]);
const isPollingLiveData = ref(false);
const viewRecord = ref<Row | null>(null);
let liveDataPoller: ReturnType<typeof window.setInterval> | null = null;
const moduleForm = useForm<Record<string, any>>({
  name: '',
  country_code: '+92',
  phone_number: '',
  email: '',
  status: 'new_lead',
  deal_value: 0,
  plan: 'pro',
  role: 'agent',
  trigger: '',
  audience_count: 100,
  title: '',
  type: 'document',
  provider: 'shopify',
  phone_number_id: '',
  access_token: '',
  verify_token: 'chatflow_verify_token',
  workspace_name: props.workspace?.name ?? 'ChatFlow AI Demo',
  timezone: props.workspace?.timezone ?? 'Asia/Karachi',
});
const checkoutForm = useForm<Record<string, any>>({ plan: 'pro' });
const adminSubscriptionForm = useForm<Record<string, any>>({ plan: 'pro', status: 'active' });
const messageForm = useForm<Record<string, any>>({ body: '', attachment: null });
const stageForm = useForm<Record<string, any>>({ status: '' });
const noteForm = useForm<Record<string, any>>({ contact_id: '', body: '', next_follow_up_at: '' });
const profileForm = useForm<Record<string, any>>({
  name: page.props.auth?.user?.name ?? 'John Doe',
  email: page.props.auth?.user?.email ?? 'admin@chatflow.test',
});

const userName = computed(() => page.props.auth?.user?.name ?? 'John');
const isSuperAdmin = computed(() => Boolean(props.isSuperAdmin));
const isDashboard = computed(() => ['Dashboard Overview', 'Dashboard'].includes(props.screen));
const shouldPollLiveData = computed(() => ['Dashboard Overview', 'Dashboard', 'Inbox / Live Chat', 'Contacts CRM'].includes(props.screen));
const pageTitle = computed(() => props.screen.replace(' / Live Chat', ''));
const pageSubtitle = computed(() => pageCopy[props.screen] ?? 'Manage this workspace module with responsive tools, filters, records and team-ready workflows.');
const primaryAction = computed(() => actionCopy[props.screen] ?? 'Create New');
const statIcons: Row = { messages: MessageSquare, ai: Bot, leads: UserPlus, rate: ShieldCheck };
const statCards = computed(() => {
  if (isSuperAdmin.value && platformStats.value.length) {
    return platformStats.value.slice(0, 4).map((stat: Row, index: number) => ({
      label: stat.label,
      value: stat.value,
      change: index === 3 ? 'real' : 'live',
      key: ['messages', 'leads', 'ai', 'rate'][index] ?? 'messages',
      icon: [Users, MessageSquare, ShieldCheck, UserPlus][index] ?? MessageSquare,
    }));
  }

  return (props.dashboard?.stats ?? fallbackStats).map((stat: Row) => ({ ...stat, icon: statIcons[stat.key] ?? MessageSquare }));
});
const platformStats = computed(() => props.platform?.stats ?? []);
const platformDetailStats = computed(() => platformStats.value.slice(4));
const platformRevenueSeries = computed(() => props.platform?.revenueSeries ?? []);
const platformPlanBreakdown = computed(() => props.platform?.planBreakdown ?? []);
const platformExpiringSoon = computed(() => props.platform?.expiringSoon ?? []);
const platformUsers = computed(() => props.platform?.users ?? []);
const platformSubscriptions = computed(() => props.platform?.subscriptions ?? []);
const platformRecentInvoices = computed(() => props.platform?.recentInvoices ?? []);
const platformRevenueMax = computed(() => Math.max(1, ...platformRevenueSeries.value.map((row: Row) => Number(row.value ?? 0))));
const platformRevenuePoints = computed(() => {
  const rows = platformRevenueSeries.value;
  const width = 680;
  const left = 40;
  const top = 36;
  const height = 205;
  const denominator = Math.max(1, rows.length - 1);

  return rows.map((row: Row, index: number) => ({
    x: left + (width / denominator) * index,
    y: top + height - (Number(row.value ?? 0) / platformRevenueMax.value) * height,
  }));
});
const platformRevenuePath = computed(() => chartPath(platformRevenuePoints.value));
const platformRevenueLabels = computed(() => {
  const points = platformRevenuePoints.value;
  if (!points.length) return [];
  const step = Math.max(1, Math.ceil(points.length / 5));

  return points
    .map((point: Row, index: number) => ({ x: point.x, text: platformRevenueSeries.value[index]?.label ?? '' }))
    .filter((_, index: number) => index % step === 0 || index === points.length - 1);
});
const platformWorkspaces = computed(() => (props.platform?.workspaces ?? []).map((workspace: Row) => ({
  ...workspace,
  adminPlan: workspace.plan ?? 'starter',
  adminStatus: workspace.subscription_status ?? 'expired',
})));
const filteredPlatformWorkspaces = computed(() => {
  const query = platformSearch.value.trim().toLowerCase();
  if (!query) return platformWorkspaces.value;

  return platformWorkspaces.value.filter((workspace: Row) => JSON.stringify(workspace).toLowerCase().includes(query));
});
const totalMessages = computed(() => statCards.value[0]?.value ?? '12,458');
const accountRows = computed(() => props.dashboard?.accounts ?? fallbackAccounts);
const leadRows = computed(() => props.dashboard?.leads ?? fallbackLeads);
const activityRows = computed(() => props.dashboard?.activities ?? fallbackActivities);
const chatRows = computed(() => props.dashboard?.conversations ?? fallbackChats);
const totalUnreadChats = computed(() => chatRows.value.reduce((sum: number, chat: Row) => sum + Number(chat.unread_count ?? 0), 0));
const dashboardInboxFilters = [
  { label: 'All', value: 'all' },
  { label: 'Unread', value: 'unread' },
  { label: 'Assigned', value: 'assigned' },
  { label: 'Resolved', value: 'resolved' },
];
const dashboardEmptyFilterLabel = computed(() => dashboardInboxFilters.find((filter) => filter.value === dashboardInboxFilter.value)?.label ?? 'Selected');
const dashboardEmptyTitle = computed(() => `${dashboardEmptyFilterLabel.value} record not found`);
const dashboardEmptySubtitle = computed(() => {
  if (dashboardInboxFilter.value === 'all') return 'No conversations are available yet.';
  if (dashboardInboxFilter.value === 'unread') return 'No unread conversations are available right now.';
  if (dashboardInboxFilter.value === 'assigned') return 'No assigned conversations are available right now.';
  if (dashboardInboxFilter.value === 'resolved') return 'No resolved conversations are available right now.';
  return 'Try another filter or add a new conversation.';
});
const filteredDashboardChats = computed(() => {
  if (dashboardInboxFilter.value === 'unread') return chatRows.value.filter((chat: Row) => Number(chat.unread_count ?? 0) > 0);
  if (dashboardInboxFilter.value === 'assigned') return chatRows.value.filter((chat: Row) => chat.assigned_to || chat.assignee_id || chat.status === 'assigned');
  if (dashboardInboxFilter.value === 'resolved') return chatRows.value.filter((chat: Row) => ['resolved', 'closed'].includes(chat.status));
  return chatRows.value;
});
const activeChat = computed(() => chatRows.value.find((chat: Row) => chat.id === selectedConversationId.value) ?? chatRows.value[0]);
const messageRows = computed(() => {
  if (!props.dashboard?.messages) return [...fallbackMessages, ...localMessages.value];
  const messages = activeChat.value?.id
    ? props.dashboard.messages.filter((message: Row) => message.conversation_id === activeChat.value.id)
    : props.dashboard.messages;

  return [...messages, ...localMessages.value];
});
const crmContacts = computed(() => props.module?.contacts ?? fallbackLeads);
const crmNotes = computed(() => props.module?.notes ?? []);
const billingPlans = computed(() => props.module?.paymentPlans ?? fallbackPlans);
const invoiceRows = computed(() => props.module?.invoices ?? []);
const currentSubscription = computed(() => props.dashboard?.currentSubscription ?? props.module?.subscriptions?.[0] ?? null);
const activeChatBlocked = computed(() => isBlocked(activeChat.value));
const paymentGatewayLabel = computed(() => (props.module?.paymentGateway === 'stripe' ? 'Stripe secure checkout' : 'Demo checkout for local testing'));
const phonePlaceholder = computed(() => `${moduleForm.country_code || '+92'} 300 0000000`);
const firstAccount = computed(() => (props.module?.accounts?.[0] ?? props.dashboard?.accounts?.[0] ?? null));
const activeWebhookUrl = computed(() => firstAccount.value?.id ? `${window.location.origin}/api/v1/webhooks/whatsapp/${firstAccount.value.id}` : 'Connect an account to generate webhook URL');
const chatApiUrl = computed(() => `${window.location.origin}/api/v1/chats/messages`);
const crmStages = [
  { key: 'new_lead', label: 'New Leads' },
  { key: 'interested', label: 'Interested' },
  { key: 'follow_up', label: 'Follow Up' },
  { key: 'won', label: 'Won' },
  { key: 'blocked', label: 'Blocked' },
];
const crmStats = computed(() => {
  const totalValue = crmContacts.value.reduce((sum: number, contact: Row) => sum + Number(contact.deal_value ?? contact.value ?? 0), 0);
  return [
    { label: 'Contacts', value: crmContacts.value.length.toLocaleString(), help: 'Live from database' },
    { label: 'Pipeline Value', value: money(totalValue), help: 'Open + won deals' },
    { label: 'Won Deals', value: pipelineFor('won').length.toLocaleString(), help: 'Converted customers' },
    { label: 'Follow-ups', value: pipelineFor('follow_up').length.toLocaleString(), help: 'Needs action' },
  ];
});

const channels = computed(() => props.dashboard?.channels ?? fallbackChannels);
const chartPeriodOptions = [
  { label: 'This Week', value: 'week' },
  { label: 'This Month', value: 'month' },
  { label: 'Last 90 Days', value: 'quarter' },
];
const messageSeries = computed(() => props.dashboard?.messageSeries?.length ? props.dashboard.messageSeries : fallbackMessageSeries);
const chartMax = computed(() => Math.max(1, ...messageSeries.value.flatMap((row: Row) => [Number(row.received ?? 0), Number(row.sent ?? 0)])));
const receivedChartPoints = computed(() => chartPoints('received'));
const sentChartPoints = computed(() => chartPoints('sent'));
const receivedChartPath = computed(() => chartPath(receivedChartPoints.value));
const sentChartPath = computed(() => chartPath(sentChartPoints.value));
const chartLabels = computed(() => {
  const points = receivedChartPoints.value;
  if (!points.length) return [];
  const step = Math.max(1, Math.ceil(points.length / 5));

  return points
    .map((point: Row, index: number) => ({ x: point.x, text: messageSeries.value[index]?.label ?? '' }))
    .filter((_, index: number) => index % step === 0 || index === points.length - 1);
});
const channelConicStyle = computed(() => {
  let cursor = 0;
  const stops = channels.value.map((channel: Row) => {
    const start = cursor;
    cursor += Number(channel.percent ?? 0);
    return `${channel.hex ?? '#7c3aed'} ${start}% ${Math.max(start + 1, cursor)}%`;
  });

  return { background: `conic-gradient(${stops.join(', ')})` };
});

const countryOptions = [
  { label: '🇵🇰 Pakistan (+92)', value: '+92' },
  { label: '🇮🇳 India (+91)', value: '+91' },
  { label: '🇦🇪 UAE (+971)', value: '+971' },
  { label: '🇸🇦 Saudi Arabia (+966)', value: '+966' },
  { label: '🇬🇧 United Kingdom (+44)', value: '+44' },
  { label: '🇺🇸 United States (+1)', value: '+1' },
  { label: '🇨🇦 Canada (+1)', value: '+1' },
  { label: '🇦🇺 Australia (+61)', value: '+61' },
  { label: '🇹🇷 Turkey (+90)', value: '+90' },
  { label: '🇧🇩 Bangladesh (+880)', value: '+880' },
];
const allCountryOptions = `
Afghanistan|+93
Albania|+355
Algeria|+213
American Samoa|+1684
Andorra|+376
Angola|+244
Anguilla|+1264
Antigua and Barbuda|+1268
Argentina|+54
Armenia|+374
Aruba|+297
Australia|+61
Austria|+43
Azerbaijan|+994
Bahamas|+1242
Bahrain|+973
Bangladesh|+880
Barbados|+1246
Belarus|+375
Belgium|+32
Belize|+501
Benin|+229
Bermuda|+1441
Bhutan|+975
Bolivia|+591
Bosnia and Herzegovina|+387
Botswana|+267
Brazil|+55
British Virgin Islands|+1284
Brunei|+673
Bulgaria|+359
Burkina Faso|+226
Burundi|+257
Cambodia|+855
Cameroon|+237
Canada|+1
Cape Verde|+238
Cayman Islands|+1345
Central African Republic|+236
Chad|+235
Chile|+56
China|+86
Colombia|+57
Comoros|+269
Congo|+242
Costa Rica|+506
Croatia|+385
Cuba|+53
Curacao|+599
Cyprus|+357
Czech Republic|+420
Denmark|+45
Djibouti|+253
Dominica|+1767
Dominican Republic|+1809
Ecuador|+593
Egypt|+20
El Salvador|+503
Equatorial Guinea|+240
Eritrea|+291
Estonia|+372
Eswatini|+268
Ethiopia|+251
Fiji|+679
Finland|+358
France|+33
French Guiana|+594
French Polynesia|+689
Gabon|+241
Gambia|+220
Georgia|+995
Germany|+49
Ghana|+233
Gibraltar|+350
Greece|+30
Greenland|+299
Grenada|+1473
Guadeloupe|+590
Guam|+1671
Guatemala|+502
Guinea|+224
Guinea-Bissau|+245
Guyana|+592
Haiti|+509
Honduras|+504
Hong Kong|+852
Hungary|+36
Iceland|+354
India|+91
Indonesia|+62
Iran|+98
Iraq|+964
Ireland|+353
Israel|+972
Italy|+39
Ivory Coast|+225
Jamaica|+1876
Japan|+81
Jordan|+962
Kazakhstan|+7
Kenya|+254
Kiribati|+686
Kuwait|+965
Kyrgyzstan|+996
Laos|+856
Latvia|+371
Lebanon|+961
Lesotho|+266
Liberia|+231
Libya|+218
Liechtenstein|+423
Lithuania|+370
Luxembourg|+352
Macau|+853
Madagascar|+261
Malawi|+265
Malaysia|+60
Maldives|+960
Mali|+223
Malta|+356
Marshall Islands|+692
Martinique|+596
Mauritania|+222
Mauritius|+230
Mexico|+52
Micronesia|+691
Moldova|+373
Monaco|+377
Mongolia|+976
Montenegro|+382
Montserrat|+1664
Morocco|+212
Mozambique|+258
Myanmar|+95
Namibia|+264
Nauru|+674
Nepal|+977
Netherlands|+31
New Caledonia|+687
New Zealand|+64
Nicaragua|+505
Niger|+227
Nigeria|+234
North Korea|+850
North Macedonia|+389
Northern Mariana Islands|+1670
Norway|+47
Oman|+968
Pakistan|+92
Palau|+680
Palestine|+970
Panama|+507
Papua New Guinea|+675
Paraguay|+595
Peru|+51
Philippines|+63
Poland|+48
Portugal|+351
Puerto Rico|+1787
Qatar|+974
Reunion|+262
Romania|+40
Russia|+7
Rwanda|+250
Saint Kitts and Nevis|+1869
Saint Lucia|+1758
Saint Vincent and the Grenadines|+1784
Samoa|+685
San Marino|+378
Sao Tome and Principe|+239
Saudi Arabia|+966
Senegal|+221
Serbia|+381
Seychelles|+248
Sierra Leone|+232
Singapore|+65
Slovakia|+421
Slovenia|+386
Solomon Islands|+677
Somalia|+252
South Africa|+27
South Korea|+82
South Sudan|+211
Spain|+34
Sri Lanka|+94
Sudan|+249
Suriname|+597
Sweden|+46
Switzerland|+41
Syria|+963
Taiwan|+886
Tajikistan|+992
Tanzania|+255
Thailand|+66
Timor-Leste|+670
Togo|+228
Tonga|+676
Trinidad and Tobago|+1868
Tunisia|+216
Turkey|+90
Turkmenistan|+993
Turks and Caicos Islands|+1649
Tuvalu|+688
Uganda|+256
Ukraine|+380
United Arab Emirates|+971
United Kingdom|+44
United States|+1
Uruguay|+598
Uzbekistan|+998
Vanuatu|+678
Vatican City|+379
Venezuela|+58
Vietnam|+84
Yemen|+967
Zambia|+260
Zimbabwe|+263
`
  .trim()
  .split('\n')
  .map((row) => {
    const [country, code] = row.split('|');
    return { label: `${country} (${code})`, value: code };
  });
const whatsAppSetupFields = [
  { name: 'name', label: 'Account Name', placeholder: 'Main Business' },
  { name: 'phone_number', label: 'WhatsApp Number', placeholder: '+92 300 0000000' },
  { name: 'phone_number_id', label: 'Phone Number ID', placeholder: 'Meta phone number ID' },
  { name: 'access_token', label: 'Access Token', placeholder: 'Meta permanent access token', type: 'password' },
  { name: 'verify_token', label: 'Verify Token', placeholder: 'chatflow_verify_token' },
];

const moduleCards = computed(() => [
  { title: `${pageTitle.value} Overview`, text: `${recordsForScreen().length} live records in this module. Latest data refreshes through dashboard polling.`, icon: MessageSquare, kind: 'overview' },
  { title: 'Smart Filters', text: 'Search, segment and prioritize records with fast workspace-level filtering.', icon: ShieldCheck, kind: 'filters' },
  { title: 'Automation Ready', text: 'Connect this module with AI replies, workflows, notifications and activity logs.', icon: Bot, kind: 'automation' },
]);

const tableRows = computed(() => recordsForScreen().map((row: Row) => ({
  key: `${props.screen}-${row.id ?? row.email ?? row.name ?? row.title ?? JSON.stringify(row).slice(0, 32)}`,
  name: row.name ?? row.title ?? row.provider ?? row.plan ?? row.description ?? row.email ?? 'Record',
  status: cleanStatus(row.status ?? row.role ?? row.stage ?? 'active'),
  owner: row.owner_name ?? row.email ?? userName.value,
  updated: relativeTime(row.updated_at ?? row.created_at),
  raw: row,
})));
const filteredTableRows = computed(() => {
  const query = recordsSearch.value.trim().toLowerCase();
  if (!query) return tableRows.value;

  return tableRows.value.filter((row: Row) => JSON.stringify(row.raw ?? row).toLowerCase().includes(query));
});
const recordDetailFields = computed(() => {
  if (!viewRecord.value) return [];
  const raw = viewRecord.value.raw ?? {};
  const fields = [
    ['Name', viewRecord.value.name],
    ['Status', viewRecord.value.status],
    ['Owner', viewRecord.value.owner],
    ['Updated', viewRecord.value.updated],
    ['Email', raw.email],
    ['Phone', raw.phone_number],
    ['Plan', raw.plan],
    ['Role', raw.role],
    ['Provider', raw.provider],
    ['Value', raw.deal_value ?? raw.value],
    ['Created', raw.created_at ? new Date(raw.created_at).toLocaleString() : null],
  ];

  return fields
    .filter(([, value]) => value !== undefined && value !== null && value !== '')
    .map(([label, value]) => ({ label, value: String(value) }));
});

const profileFields = computed(() => [
  { key: 'name', label: 'Full Name', value: userName.value },
  { key: 'email', label: 'Email', value: page.props.auth?.user?.email ?? 'admin@chatflow.test' },
]);

const securityItems = ['Two-factor authentication', 'Active devices', 'Password update'];
const pageCopy: Row = {
  'Inbox / Live Chat': 'Handle realtime WhatsApp conversations, assignments, notes, unread messages and AI-assisted replies.',
  'Contacts CRM': 'Manage customers, leads, deal values, tags, owner assignment and follow-up activity.',
  'Broadcast Campaigns': 'Create segmented WhatsApp campaigns with scheduling, delivery tracking and retries.',
  'AI Automations': 'Build trigger-based workflows for replies, assignments, tags, delays and lead capture.',
  'AI Training': 'Train your assistant with FAQs, documents, URLs and business knowledge sources.',
  Analytics: 'Track messages, response rate, campaign performance, AI usage and conversion trends.',
  'Team Management': 'Invite members, assign roles, manage permissions and organize support teams.',
  'Subscription Billing': 'Review plan usage, invoices, feature limits and billing history.',
  Notifications: 'Manage realtime alerts, email notifications and channel preferences.',
  Integrations: 'Connect Shopify, WooCommerce, Zapier, Stripe, Slack, Telegram and Google Sheets.',
  Settings: 'Configure workspace identity, WhatsApp preferences, security and AI behavior.',
  Profile: 'Update your personal information, account security and workspace identity.',
  'API Keys': 'Create and rotate API keys with scoped abilities and usage monitoring.',
  'Activity Logs': 'Audit workspace events, user actions, security changes and system updates.',
};
const actionCopy: Row = {
  'Inbox / Live Chat': 'New Message',
  'Contacts CRM': 'Add Contact',
  'Broadcast Campaigns': 'Create Campaign',
  'AI Automations': 'Create Automation',
  'AI Training': 'Add Source',
  Analytics: 'Export Report',
  'Team Management': 'Invite Member',
  'Subscription Billing': 'Manage Plan',
  Notifications: 'Add Rule',
  Integrations: 'Connect App',
  Settings: 'Save Settings',
  Profile: 'Save Profile',
  'API Keys': 'Create API Key',
  'Activity Logs': 'Export Logs',
};

const formConfig: Row = {
  'Contacts CRM': {
    route: '/app/contacts',
    fields: [
      { name: 'name', label: 'Name', placeholder: 'Customer name' },
      { name: 'country_code', label: 'Country', options: allCountryOptions },
      { name: 'phone_number', label: 'Phone', placeholder: '+92 300 0000000' },
      { name: 'email', label: 'Email', type: 'email', placeholder: 'customer@email.com' },
      { name: 'status', label: 'Status', options: ['new_lead', 'interested', 'follow_up', 'won'] },
      { name: 'deal_value', label: 'Deal Value', type: 'number', placeholder: '2500' },
    ],
    defaults: { country_code: '+92', status: 'new_lead', deal_value: 0 },
  },
  'Subscription Billing': {
    route: '/app/billing/checkout',
    fields: [
      { name: 'plan', label: 'Plan', options: ['starter', 'pro', 'agency'] },
    ],
    defaults: { plan: 'pro', status: 'active' },
  },
  'Team Management': {
    route: '/app/team',
    fields: [
      { name: 'name', label: 'Name', placeholder: 'Agent name' },
      { name: 'email', label: 'Email', type: 'email', placeholder: 'agent@company.com' },
      { name: 'role', label: 'Role', options: ['manager', 'agent', 'viewer'] },
    ],
    defaults: { role: 'agent' },
  },
  'AI Automations': {
    route: '/app/automations',
    fields: [
      { name: 'name', label: 'Automation Name', placeholder: 'Price reply flow' },
      { name: 'trigger', label: 'Trigger Keyword', placeholder: 'price' },
    ],
    defaults: {},
  },
  'Broadcast Campaigns': {
    route: '/app/broadcasts',
    fields: [
      { name: 'name', label: 'Campaign Name', placeholder: 'June Promo' },
      { name: 'audience_count', label: 'Audience Count', type: 'number', placeholder: '1000' },
    ],
    defaults: { audience_count: 100 },
  },
  'AI Training': {
    route: '/app/training',
    fields: [
      { name: 'title', label: 'Source Title', placeholder: 'Product FAQ' },
      { name: 'type', label: 'Type', options: ['document', 'url', 'faq'] },
    ],
    defaults: { type: 'document' },
  },
  Integrations: {
    route: '/app/integrations',
    fields: [{ name: 'provider', label: 'Provider', options: ['shopify', 'woocommerce', 'zapier', 'stripe', 'telegram', 'slack', 'google_sheets'] }],
    defaults: { provider: 'shopify' },
  },
  'API Keys': {
    route: '/app/api-keys',
    fields: [{ name: 'name', label: 'Key Name', placeholder: 'Production API Key' }],
    defaults: {},
  },
  Settings: {
    route: '/app/settings',
    fields: [
      { name: 'workspace_name', label: 'Workspace Name', placeholder: 'My Business' },
      { name: 'timezone', label: 'Timezone', placeholder: 'Asia/Karachi' },
    ],
    defaults: { workspace_name: props.workspace?.name ?? 'ChatFlow AI Demo', timezone: props.workspace?.timezone ?? 'Asia/Karachi' },
  },
  'Inbox / Live Chat': {
    route: '/app/whatsapp-accounts',
    fields: [
      { name: 'name', label: 'WhatsApp Account', placeholder: 'Main Business' },
      { name: 'phone_number', label: 'Phone Number', placeholder: '+92 300 0000000' },
      { name: 'phone_number_id', label: 'Phone Number ID', placeholder: 'Meta phone number ID' },
      { name: 'access_token', label: 'Access Token', placeholder: 'Meta token' },
      { name: 'verify_token', label: 'Verify Token', placeholder: 'chatflow_verify_token' },
    ],
    defaults: { verify_token: 'chatflow_verify_token' },
  },
};

const formFields = computed(() => ['Subscription Billing', 'Inbox / Live Chat'].includes(props.screen) ? [] : (formConfig[props.screen]?.fields ?? []));
const currentForm = computed(() => formConfig[props.screen] ?? null);

function initial(name: string) {
  return name?.charAt(0)?.toUpperCase() ?? 'C';
}

function cleanStatus(status?: string) {
  return (status ?? 'connected').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function optionValue(option: Row | string) {
  return typeof option === 'string' ? option : option.value;
}

function optionKey(option: Row | string) {
  return typeof option === 'string' ? option : option.label;
}

function optionLabel(option: Row | string) {
  return typeof option === 'string' ? cleanStatus(option) : option.label;
}

function relativeTime(value?: string) {
  if (!value) return 'now';
  const diff = Math.max(1, Math.round((Date.now() - new Date(value).getTime()) / 60000));
  if (diff < 60) return `${diff} min ago`;
  return `${Math.round(diff / 60)} hr ago`;
}

function dateTime(value?: string) {
  if (!value) return 'Not set';
  return new Date(value).toLocaleString([], {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function money(value: number | string) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(Number(value) || 0);
}

function isBlocked(row?: Row | null) {
  return row?.status === 'blocked' || row?.contact_status === 'blocked';
}

function pipelineFor(status: string) {
  return crmContacts.value.filter((contact: Row) => (contact.status ?? contact.stage ?? 'new_lead') === status);
}

function nextStage(status: string) {
  const order = ['new_lead', 'interested', 'follow_up', 'won'];
  const index = order.indexOf(status);
  return order[Math.min(index + 1, order.length - 1)] ?? 'interested';
}

function moveContact(contact: Row, status: string) {
  if (!contact.id) return;
  stageForm.status = status;
  stageForm.post(`/app/contacts/${contact.id}/stage`, { preserveScroll: true });
}

function toggleContactBlock(contact: Row) {
  const contactId = contact.contact_id ?? contact.id;
  if (!contactId) return;
  const blocked = !isBlocked(contact);
  if (!confirm(blocked ? 'Block this contact? New messages from this contact will be ignored.' : 'Unblock this contact?')) return;
  router.post(`/app/contacts/${contactId}/block`, { blocked }, {
    preserveScroll: true,
    preserveState: true,
    only: ['dashboard', 'module'],
  });
}

function saveNote() {
  if (!noteForm.contact_id && crmContacts.value[0]?.id) noteForm.contact_id = crmContacts.value[0].id;
  if (!noteForm.contact_id) return;
  noteForm.post(`/app/contacts/${noteForm.contact_id}/notes`, {
    preserveScroll: true,
    onSuccess: () => noteForm.reset('body', 'next_follow_up_at'),
  });
}

function startCheckout(plan: string) {
  checkoutForm.plan = plan;
  checkoutForm.post('/app/billing/checkout', { preserveScroll: true });
}

function updateCustomerSubscription(workspace: Row) {
  if (!workspace?.id) return;
  adminSubscriptionForm.plan = workspace.adminPlan ?? workspace.plan ?? 'starter';
  adminSubscriptionForm.status = workspace.adminStatus ?? workspace.subscription_status ?? 'active';
  adminSubscriptionForm.post(`/app/admin/workspaces/${workspace.id}/subscription`, {
    preserveScroll: true,
    only: ['platform', 'dashboard'],
  });
}

function selectAttachment(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0] ?? null;
  selectedAttachmentFile.value = file;
  messageForm.attachment = file;
  selectedAttachmentName.value = file?.name ?? '';
}

function clearAttachment() {
  selectedAttachmentFile.value = null;
  messageForm.attachment = null;
  selectedAttachmentName.value = '';
}

function openConversation(chat: Row) {
  selectedConversationId.value = chat.id;
  if (chat.unread_count) {
    router.post(`/app/conversations/${chat.id}/read`, {}, { preserveScroll: true, preserveState: true });
  }
}

function sendMessage() {
  if (!draft.value.trim() && !selectedAttachmentFile.value) return;
  if (activeChatBlocked.value) return;
  if (!activeChat.value?.id) {
    localMessages.value.push({ id: `local-${Date.now()}`, direction: 'outbound', body: draft.value.trim() });
    draft.value = '';
    return;
  }

  sendingMessage.value = true;
  router.post(`/app/conversations/${activeChat.value.id}/messages`, {
    body: draft.value.trim(),
    attachment: selectedAttachmentFile.value,
  }, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      draft.value = '';
      messageForm.reset();
      clearAttachment();
      localMessages.value = [];
    },
    onFinish: () => {
      sendingMessage.value = false;
    },
  });
}

function deleteChat(conversationId: number | string) {
  if (!conversationId || !confirm('Delete this chat and all messages?')) return;
  router.delete(`/app/conversations/${conversationId}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedConversationId.value === conversationId) selectedConversationId.value = null;
    },
  });
}

function deleteMessage(messageId: number | string) {
  if (!messageId || !confirm('Delete this message?')) return;
  router.delete(`/app/messages/${messageId}`, { preserveScroll: true });
}

function deleteContact(contactId: number | string) {
  if (!contactId || !confirm('Delete this contact and related chat?')) return;
  router.delete(`/app/contacts/${contactId}`, { preserveScroll: true });
}

function isImage(mime?: string) {
  return (mime ?? '').startsWith('image/');
}

function mediaName(message: Row) {
  try {
    return JSON.parse(message.media_metadata ?? '{}')?.original_name ?? message.body ?? 'Attachment';
  } catch {
    return message.body ?? 'Attachment';
  }
}

function chartPoints(key: 'received' | 'sent') {
  const rows = messageSeries.value;
  const width = 680;
  const left = 40;
  const top = 46;
  const height = 210;
  const denominator = Math.max(1, rows.length - 1);

  return rows.map((row: Row, index: number) => ({
    x: left + (width / denominator) * index,
    y: top + height - (Number(row[key] ?? 0) / chartMax.value) * height,
  }));
}

function chartPath(points: Row[]) {
  if (!points.length) return '';
  return points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(1)} ${point.y.toFixed(1)}`).join(' ');
}

function changeChartPeriod() {
  router.get('/app/dashboard', { chart_period: selectedChartPeriod.value }, {
    preserveScroll: true,
    preserveState: true,
    only: ['dashboard'],
  });
}

function openRecord(row: Row) {
  viewRecord.value = row;
}

function openModuleCard(card: Row) {
  if (card.kind === 'filters') {
    recordsSearch.value = '';
    document.querySelector<HTMLInputElement>('input[placeholder="Search records..."]')?.focus();
    return;
  }

  openRecord({
    key: `module-${card.kind}`,
    name: card.title,
    status: 'Live',
    owner: userName.value,
    updated: 'now',
    raw: {
      module: pageTitle.value,
      records: recordsForScreen().length,
      latest_record: recordsForScreen()[0] ?? null,
      card: card.kind,
    },
  });
}

function closeRecord() {
  viewRecord.value = null;
}

function prettyRecord(row: Row) {
  return JSON.stringify(row ?? {}, null, 2);
}

function messageTickIcon(status?: string) {
  if (status === 'failed') return '!';
  if (status === 'delivered' || status === 'read') return '✓✓';
  return '✓';
}

function messageStatusIcon(status?: string) {
  return ({ sent: '✓', delivered: '✓✓', read: '✓✓', failed: '!' } as Row)[status ?? 'sent'] ?? '✓';
}

function messageStatusClass(status?: string) {
  if (status === 'read') return 'font-black text-sky-300';
  if (status === 'failed') return 'font-black text-red-300';
  return 'font-black text-white/80';
}

function scrollChatToBottom() {
  nextTick(() => {
    if (!messagesPanel.value) return;
    messagesPanel.value.scrollTop = messagesPanel.value.scrollHeight;
  });
}

watch(
  () => [activeChat.value?.id, messageRows.value.length, messageRows.value.at(-1)?.id, messageRows.value.at(-1)?.status],
  scrollChatToBottom,
  { immediate: true },
);

watch(
  () => props.dashboard?.chartPeriod,
  (period) => {
    if (period) selectedChartPeriod.value = period;
  },
);

watch(
  () => activeChat.value?.unread_count,
  (unread) => {
    if (props.screen !== 'Inbox / Live Chat' || !selectedConversationId.value || !unread) return;
    router.post(`/app/conversations/${selectedConversationId.value}/read`, {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['dashboard'],
    });
  },
);

function refreshLiveData() {
  if (!shouldPollLiveData.value || isPollingLiveData.value) return;
  isPollingLiveData.value = true;
  router.reload({
    only: ['dashboard', 'module'],
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onFinish: () => {
      isPollingLiveData.value = false;
    },
  });
}

onMounted(() => {
  if (!shouldPollLiveData.value) return;
  liveDataPoller = window.setInterval(refreshLiveData, 3500);
});

onUnmounted(() => {
  if (!liveDataPoller) return;
  window.clearInterval(liveDataPoller);
  liveDataPoller = null;
});

function recordsForScreen() {
  const data = props.module ?? {};
  const map: Row = {
    'Contacts CRM': data.contacts ?? [],
    'Subscription Billing': data.subscriptions ?? [],
    'Team Management': data.team ?? [],
    'AI Automations': data.automations ?? [],
    'Broadcast Campaigns': data.broadcasts ?? [],
    'AI Training': data.training ?? [],
    Integrations: data.integrations ?? [],
    'API Keys': data.apiKeys ?? [],
    'Activity Logs': data.activity ?? [],
    'Inbox / Live Chat': data.accounts ?? [],
    Settings: [props.workspace].filter(Boolean),
    Profile: [page.props.auth?.user].filter(Boolean),
  };

  if (map[props.screen]) return map[props.screen];

  return [
    { name: `${pageTitle.value} Item 01`, status: 'active', owner_name: userName.value, updated_at: new Date().toISOString() },
  ];
}

function submitModule() {
  if (props.screen === 'Profile') {
    profileForm.post('/app/profile', { preserveScroll: true });
    return;
  }

  const config = currentForm.value;
  if (!config) return;
  Object.entries(config.defaults ?? {}).forEach(([key, value]) => {
    if (moduleForm[key] === undefined || moduleForm[key] === '') moduleForm[key] = value;
  });
  if (props.screen === 'Contacts CRM' && moduleForm.phone_number && !String(moduleForm.phone_number).trim().startsWith('+')) {
    moduleForm.phone_number = `${moduleForm.country_code} ${String(moduleForm.phone_number).trim()}`;
  }
  moduleForm.post(config.route, {
    preserveScroll: true,
    onSuccess: () => {
      moduleForm.reset();
      Object.entries(config.defaults ?? {}).forEach(([key, value]) => {
        moduleForm[key] = value;
      });
    },
  });
}

const fallbackStats = [
  { label: 'Total Messages', value: '12,458', change: '18.6', key: 'messages' },
  { label: 'AI Replies Sent', value: '10,245', change: '21.4', key: 'ai' },
  { label: 'Leads Captured', value: '2,356', change: '28.1', key: 'leads' },
  { label: 'Response Rate', value: '92.6%', change: '6.3', key: 'rate' },
];
const fallbackAccounts = [
  { id: 1, name: 'Main Business', phone_number: '+1 (556) 123-4567', status: 'connected' },
  { id: 2, name: 'Support Line', phone_number: '+1 (556) 987-6543', status: 'connected' },
];
const fallbackLeads = [
  { id: 1, name: 'Emily Johnson', phone_number: '+1 (556) 123-4567', status: 'interested' },
  { id: 2, name: 'Michael Smith', phone_number: '+1 (865) 987-6543', status: 'new_lead' },
  { id: 3, name: 'Sarah Wilson', phone_number: '+1 (555) 456-7890', status: 'follow_up' },
];
const fallbackActivities = [
  { id: 1, description: 'New lead captured from WhatsApp', created_at: new Date().toISOString() },
  { id: 2, description: 'AI training data updated', created_at: new Date(Date.now() - 900000).toISOString() },
  { id: 3, description: 'Broadcast sent successfully', created_at: new Date(Date.now() - 3600000).toISOString() },
];
const fallbackChannels = [
  { name: 'WhatsApp', value: '8,720', raw: 8720, width: '78%', percent: 78, color: 'bg-violet-600', hex: '#7c3aed' },
  { name: 'Website', value: '1,486', raw: 1486, width: '52%', percent: 12, color: 'bg-sky-500', hex: '#38bdf8' },
  { name: 'Facebook', value: '747', raw: 747, width: '26%', percent: 6, color: 'bg-indigo-500', hex: '#6366f1' },
  { name: 'Instagram', value: '495', raw: 495, width: '18%', percent: 4, color: 'bg-pink-500', hex: '#ec4899' },
];
const fallbackMessageSeries = [
  { label: 'Mon', received: 12, sent: 4 },
  { label: 'Tue', received: 30, sent: 18 },
  { label: 'Wed', received: 18, sent: 8 },
  { label: 'Thu', received: 33, sent: 18 },
  { label: 'Fri', received: 24, sent: 10 },
  { label: 'Sat', received: 15, sent: 6 },
  { label: 'Sun', received: 20, sent: 12 },
];
const fallbackChats = [
  { id: 1, name: 'Emily Johnson', phone_number: '+1 (556) 123-4567', last_message_at: new Date().toISOString() },
  { id: 2, name: 'Michael Smith', phone_number: '+1 (865) 987-6543', last_message_at: new Date(Date.now() - 1200000).toISOString() },
  { id: 3, name: 'Sarah Wilson', phone_number: '+1 (555) 456-7890', last_message_at: new Date(Date.now() - 2700000).toISOString() },
];
const fallbackMessages = [
  { id: 1, direction: 'inbound', body: 'Hi, I need help with my order status.' },
  { id: 2, direction: 'outbound', body: 'Sure. Please provide your order number.' },
  { id: 3, direction: 'inbound', body: 'My order number is #4567' },
];
const fallbackPlans = [
  { key: 'starter', name: 'Starter', price: 19, features: ['1 WhatsApp account', '1,000 messages/month', '2 team members'] },
  { key: 'pro', name: 'Pro', price: 49, features: ['3 WhatsApp accounts', '10,000 messages/month', '10 team members'] },
  { key: 'agency', name: 'Agency', price: 99, features: ['10 WhatsApp accounts', '100,000 messages/month', '50 team members'] },
];
const stripeTestCards = [
  { label: 'Successful payment', number: '4242 4242 4242 4242' },
  { label: 'Requires authentication', number: '4000 0025 0000 3155' },
  { label: 'Declined payment', number: '4000 0000 0000 9995' },
];
</script>
