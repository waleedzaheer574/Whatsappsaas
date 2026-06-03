CREATE DATABASE IF NOT EXISTS `whatsapp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `whatsapp`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (`key` VARCHAR(255) NOT NULL, `value` MEDIUMTEXT NOT NULL, `expiration` INT NOT NULL, PRIMARY KEY (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `cache_locks` (`key` VARCHAR(255) NOT NULL, `owner` VARCHAR(255) NOT NULL, `expiration` INT NOT NULL, PRIMARY KEY (`key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `jobs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `queue` VARCHAR(255) NOT NULL, `payload` LONGTEXT NOT NULL, `attempts` TINYINT UNSIGNED NOT NULL, `reserved_at` INT UNSIGNED NULL, `available_at` INT UNSIGNED NOT NULL, `created_at` INT UNSIGNED NOT NULL, PRIMARY KEY (`id`), KEY `jobs_queue_index` (`queue`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `job_batches` (`id` VARCHAR(255) NOT NULL, `name` VARCHAR(255) NOT NULL, `total_jobs` INT NOT NULL, `pending_jobs` INT NOT NULL, `failed_jobs` INT NOT NULL, `failed_job_ids` LONGTEXT NOT NULL, `options` MEDIUMTEXT NULL, `cancelled_at` INT NULL, `created_at` INT NOT NULL, `finished_at` INT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `failed_jobs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `uuid` VARCHAR(255) NOT NULL, `connection` TEXT NOT NULL, `queue` TEXT NOT NULL, `payload` LONGTEXT NOT NULL, `exception` LONGTEXT NOT NULL, `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workspaces` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `plan` VARCHAR(255) NOT NULL DEFAULT 'pro',
  `timezone` VARCHAR(255) NOT NULL DEFAULT 'Asia/Karachi',
  `settings` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workspaces_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `workspace_user` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role` VARCHAR(255) NOT NULL DEFAULT 'admin',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workspace_user_workspace_id_user_id_unique` (`workspace_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teams` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `description` TEXT NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `roles` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NULL, `name` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL, `is_system` TINYINT(1) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `roles_workspace_slug_unique` (`workspace_id`,`slug`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `permissions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL, `group` VARCHAR(255) NOT NULL DEFAULT 'general', `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `permissions_slug_unique` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `permission_role` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `permission_id` BIGINT UNSIGNED NOT NULL, `role_id` BIGINT UNSIGNED NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `permission_role_unique` (`permission_id`,`role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `role_user` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `role_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `role_user_unique` (`workspace_id`,`role_id`,`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `team_user` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `team_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NOT NULL, `role` VARCHAR(255) NOT NULL DEFAULT 'agent', `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `team_user_unique` (`team_id`,`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_accounts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(255) NOT NULL,
  `provider` VARCHAR(255) NOT NULL DEFAULT 'meta',
  `status` VARCHAR(255) NOT NULL DEFAULT 'connected',
  `quality_rating` VARCHAR(255) NOT NULL DEFAULT 'high',
  `last_synced_at` TIMESTAMP NULL,
  `settings` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_accounts_workspace_id_index` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `avatar` VARCHAR(255) NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'new_lead',
  `source` VARCHAR(255) NOT NULL DEFAULT 'whatsapp',
  `deal_value` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `owner_name` VARCHAR(255) NULL,
  `tags` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contacts_workspace_phone_unique` (`workspace_id`,`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `workspace_id` BIGINT UNSIGNED NOT NULL,
  `whatsapp_account_id` BIGINT UNSIGNED NOT NULL,
  `contact_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(255) NOT NULL DEFAULT 'open',
  `priority` VARCHAR(255) NOT NULL DEFAULT 'normal',
  `assigned_to` VARCHAR(255) NULL,
  `unread_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_message_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_workspace_id_index` (`workspace_id`),
  KEY `conversations_contact_id_index` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id` BIGINT UNSIGNED NOT NULL,
  `direction` VARCHAR(255) NOT NULL,
  `sender_type` VARCHAR(255) NOT NULL DEFAULT 'contact',
  `body` TEXT NOT NULL,
  `message_type` VARCHAR(255) NOT NULL DEFAULT 'text',
  `status` VARCHAR(255) NOT NULL DEFAULT 'sent',
  `ai_generated` TINYINT(1) NOT NULL DEFAULT 0,
  `metadata` JSON NULL,
  `sent_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_index` (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leads` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `contact_id` BIGINT UNSIGNED NULL, `title` VARCHAR(255) NOT NULL, `stage` VARCHAR(255) NOT NULL DEFAULT 'new', `value` DECIMAL(12,2) NOT NULL DEFAULT 0, `score` TINYINT UNSIGNED NOT NULL DEFAULT 0, `next_follow_up_at` TIMESTAMP NULL, `custom_fields` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `deal_pipelines` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `stages` JSON NOT NULL, `is_default` TINYINT(1) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `contact_notes` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `contact_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NULL, `body` TEXT NOT NULL, `is_internal` TINYINT(1) NOT NULL DEFAULT 1, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `labels` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `color` VARCHAR(255) NOT NULL DEFAULT '#7C3AED', `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `labelables` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `label_id` BIGINT UNSIGNED NOT NULL, `labelable_type` VARCHAR(255) NOT NULL, `labelable_id` BIGINT UNSIGNED NOT NULL, PRIMARY KEY (`id`), KEY `labelables_morph_index` (`labelable_type`,`labelable_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `conversation_notes` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `conversation_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NULL, `body` TEXT NOT NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `message_media` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `message_id` BIGINT UNSIGNED NOT NULL, `disk` VARCHAR(255) NOT NULL DEFAULT 's3', `path` VARCHAR(255) NOT NULL, `mime_type` VARCHAR(255) NULL, `size` BIGINT UNSIGNED NOT NULL DEFAULT 0, `metadata` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_automations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `trigger` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'active', `runs_count` INT UNSIGNED NOT NULL DEFAULT 0, `success_rate` DECIMAL(5,2) NOT NULL DEFAULT 0, `flow` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `automation_triggers` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `automation_id` BIGINT UNSIGNED NOT NULL, `type` VARCHAR(255) NOT NULL, `config` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `automation_conditions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `automation_id` BIGINT UNSIGNED NOT NULL, `field` VARCHAR(255) NOT NULL, `operator` VARCHAR(255) NOT NULL, `value` VARCHAR(255) NULL, `config` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `automation_actions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `automation_id` BIGINT UNSIGNED NOT NULL, `type` VARCHAR(255) NOT NULL, `sort_order` INT UNSIGNED NOT NULL DEFAULT 0, `config` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `automation_logs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `automation_id` BIGINT UNSIGNED NULL, `conversation_id` BIGINT UNSIGNED NULL, `status` VARCHAR(255) NOT NULL, `context` JSON NULL, `error` TEXT NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_training_sources` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `title` VARCHAR(255) NOT NULL, `type` VARCHAR(255) NOT NULL DEFAULT 'document', `status` VARCHAR(255) NOT NULL DEFAULT 'indexed', `chunks_count` INT UNSIGNED NOT NULL DEFAULT 0, `trained_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `ai_prompts` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `type` VARCHAR(255) NOT NULL DEFAULT 'reply', `tone` VARCHAR(255) NOT NULL DEFAULT 'friendly', `system_prompt` LONGTEXT NOT NULL, `is_active` TINYINT(1) NOT NULL DEFAULT 1, `settings` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `ai_responses` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `conversation_id` BIGINT UNSIGNED NULL, `provider` VARCHAR(255) NOT NULL DEFAULT 'openai', `model` VARCHAR(255) NULL, `prompt` LONGTEXT NOT NULL, `response` LONGTEXT NULL, `prompt_tokens` INT UNSIGNED NOT NULL DEFAULT 0, `completion_tokens` INT UNSIGNED NOT NULL DEFAULT 0, `cost` DECIMAL(10,6) NOT NULL DEFAULT 0, `metadata` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `broadcast_campaigns` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'draft', `audience_count` INT UNSIGNED NOT NULL DEFAULT 0, `sent_count` INT UNSIGNED NOT NULL DEFAULT 0, `delivered_count` INT UNSIGNED NOT NULL DEFAULT 0, `replied_count` INT UNSIGNED NOT NULL DEFAULT 0, `scheduled_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `campaigns` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `type` VARCHAR(255) NOT NULL DEFAULT 'broadcast', `status` VARCHAR(255) NOT NULL DEFAULT 'draft', `audience_filter` JSON NULL, `scheduled_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `broadcasts` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `campaign_id` BIGINT UNSIGNED NOT NULL, `contact_id` BIGINT UNSIGNED NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'queued', `body` TEXT NOT NULL, `variables` JSON NULL, `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0, `sent_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `csv_imports` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `filename` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'pending', `total_rows` INT UNSIGNED NOT NULL DEFAULT 0, `processed_rows` INT UNSIGNED NOT NULL DEFAULT 0, `mapping` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `analytics_daily_snapshots` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `date` DATE NOT NULL, `received_messages` INT UNSIGNED NOT NULL DEFAULT 0, `sent_messages` INT UNSIGNED NOT NULL DEFAULT 0, `ai_replies` INT UNSIGNED NOT NULL DEFAULT 0, `leads_captured` INT UNSIGNED NOT NULL DEFAULT 0, `response_rate` DECIMAL(5,2) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `analytics_workspace_date_unique` (`workspace_id`,`date`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `activity_logs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `type` VARCHAR(255) NOT NULL, `description` VARCHAR(255) NOT NULL, `properties` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_templates` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `whatsapp_account_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `language` VARCHAR(255) NOT NULL DEFAULT 'en', `category` VARCHAR(255) NOT NULL DEFAULT 'utility', `status` VARCHAR(255) NOT NULL DEFAULT 'pending', `components` JSON NOT NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `webhooks` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NULL, `provider` VARCHAR(255) NOT NULL, `event` VARCHAR(255) NOT NULL, `secret` VARCHAR(255) NULL, `url` VARCHAR(255) NULL, `is_active` TINYINT(1) NOT NULL DEFAULT 1, `headers` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `webhook_deliveries` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `webhook_id` BIGINT UNSIGNED NULL, `event` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'pending', `attempts` SMALLINT UNSIGNED NOT NULL DEFAULT 0, `payload` JSON NOT NULL, `response` TEXT NULL, `delivered_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `stripe_id` VARCHAR(255) NULL, `plan` VARCHAR(255) NOT NULL DEFAULT 'starter', `status` VARCHAR(255) NOT NULL DEFAULT 'trialing', `trial_ends_at` TIMESTAMP NULL, `renews_at` TIMESTAMP NULL, `ends_at` TIMESTAMP NULL, `limits` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `subscriptions_stripe_id_unique` (`stripe_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `invoices` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `stripe_invoice_id` VARCHAR(255) NULL, `number` VARCHAR(255) NULL, `amount_due` DECIMAL(12,2) NOT NULL DEFAULT 0, `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0, `currency` VARCHAR(3) NOT NULL DEFAULT 'usd', `status` VARCHAR(255) NOT NULL DEFAULT 'draft', `hosted_url` VARCHAR(255) NULL, `paid_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `invoices_stripe_invoice_id_unique` (`stripe_invoice_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `usage_records` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `metric` VARCHAR(255) NOT NULL, `quantity` BIGINT UNSIGNED NOT NULL DEFAULT 0, `period_start` DATE NOT NULL, `period_end` DATE NOT NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), KEY `usage_records_workspace_metric_period_index` (`workspace_id`,`metric`,`period_start`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_keys` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `token_hash` VARCHAR(255) NOT NULL, `abilities` JSON NULL, `last_used_at` TIMESTAMP NULL, `expires_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `api_keys_token_hash_unique` (`token_hash`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `connected_integrations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NOT NULL, `provider` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'connected', `credentials` JSON NULL, `settings` JSON NULL, `last_synced_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`), UNIQUE KEY `connected_integrations_workspace_provider_unique` (`workspace_id`,`provider`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `security_events` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `workspace_id` BIGINT UNSIGNED NULL, `user_id` BIGINT UNSIGNED NULL, `type` VARCHAR(255) NOT NULL, `ip_address` VARCHAR(45) NULL, `user_agent` TEXT NULL, `metadata` JSON NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `devices` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `ip_address` VARCHAR(45) NULL, `user_agent` TEXT NULL, `last_seen_at` TIMESTAMP NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'admin@chatflow.test', '$2y$10$ywbpiF6hTBKmd6f2Ridak.qrKU.UEIRlujNYUdkPg0VgbeMXtBSnq', NOW(), NOW());

INSERT IGNORE INTO `workspaces` (`id`, `name`, `slug`, `plan`, `timezone`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'ChatFlow AI Demo', 'chatflow-main', 'pro', 'Asia/Karachi', JSON_OBJECT('theme','system'), NOW(), NOW());

INSERT IGNORE INTO `workspace_user` (`workspace_id`, `user_id`, `role`, `created_at`, `updated_at`) VALUES
(1, 1, 'owner', NOW(), NOW());

INSERT IGNORE INTO `whatsapp_accounts` (`id`, `workspace_id`, `name`, `phone_number`, `provider`, `status`, `quality_rating`, `last_synced_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Main Business', '+1 (556) 123-4567', 'meta', 'connected', 'high', NOW(), NOW(), NOW()),
(2, 1, 'Support Line', '+1 (556) 987-6543', 'meta', 'connected', 'high', NOW(), NOW(), NOW());

INSERT IGNORE INTO `contacts` (`id`, `workspace_id`, `name`, `phone_number`, `status`, `source`, `deal_value`, `owner_name`, `tags`, `created_at`, `updated_at`) VALUES
(1, 1, 'Emily Johnson', '+1 (556) 123-4567', 'interested', 'whatsapp', 2400, 'John Doe', JSON_ARRAY('demo','whatsapp'), NOW(), NOW()),
(2, 1, 'Michael Smith', '+1 (865) 987-6543', 'new_lead', 'whatsapp', 5800, 'John Doe', JSON_ARRAY('demo','whatsapp'), NOW(), NOW()),
(3, 1, 'Sarah Wilson', '+1 (555) 456-7890', 'follow_up', 'whatsapp', 1200, 'John Doe', JSON_ARRAY('demo','whatsapp'), NOW(), NOW());

INSERT IGNORE INTO `conversations` (`id`, `workspace_id`, `whatsapp_account_id`, `contact_id`, `status`, `priority`, `assigned_to`, `unread_count`, `last_message_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'open', 'high', 'John Doe', 2, NOW(), NOW(), NOW());

INSERT IGNORE INTO `messages` (`conversation_id`, `direction`, `sender_type`, `body`, `message_type`, `status`, `ai_generated`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 'inbound', 'contact', 'Hi, I need help with my order status.', 'text', 'read', 0, NOW(), NOW(), NOW()),
(1, 'outbound', 'ai', 'Sure! I can help you with that. Please provide your order number.', 'text', 'delivered', 1, NOW(), NOW(), NOW());
