# ChatFlow AI Backend Architecture

ChatFlow AI now has an enterprise backend scaffold for a multi-tenant AI WhatsApp SaaS.

## Modules

- Authentication: Laravel auth foundation, Sanctum-ready API tokens, device/session tables, security events.
- Tenancy: workspace isolation, teams, roles, permissions, usage records and plan restrictions.
- WhatsApp: Cloud API accounts, webhook verification, inbound queue jobs, outbound send action, media/templates.
- AI Engine: OpenAI/Claude-ready service layer, prompts, cached replies, token/cost records.
- Shared Inbox: conversations, messages, notes, labels, read/unread counters, Reverb events.
- CRM: contacts, leads, deal pipelines, notes, follow-ups and custom fields.
- Automation: triggers, conditions, actions, logs and queue-ready workflow execution.
- Broadcasts: campaigns, recipients, CSV imports, scheduled send and retry-ready jobs.
- Analytics: daily snapshots plus cached realtime summary.
- Billing: subscriptions, invoices, usage records, Cashier-ready dependency.
- Integrations: provider-neutral table for Shopify, WooCommerce, Zapier, Stripe, Telegram, Slack and Google Sheets.

## Setup

```bash
composer install
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

## API

Base URL:

```text
/api/v1
```

Standard response:

```json
{
  "success": true,
  "message": "Data fetched successfully",
  "data": {},
  "meta": {}
}
```

Key endpoints:

- `GET /api/v1/dashboard?workspace_id=1`
- `GET /api/v1/conversations?workspace_id=1`
- `POST /api/v1/conversations/{conversation}/messages`
- `GET /api/v1/contacts?workspace_id=1`
- `POST /api/v1/webhooks/whatsapp/{account}`
- `GET /api/v1/analytics/summary?workspace_id=1`

## Production Services

```env
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
CACHE_STORE=redis
SCOUT_DRIVER=meilisearch
FILESYSTEM_DISK=s3
```

Run workers:

```bash
php artisan queue:work
php artisan reverb:start
```
