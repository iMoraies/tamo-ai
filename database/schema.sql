-- Estrutura do Tamo Aí para instalação em banco vazio.
SET NAMES utf8mb4;

CREATE TABLE `tamo_ai_activity_logs` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `module` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `related_id` int UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `old_data` mediumtext COLLATE utf8mb4_general_ci,
  `new_data` mediumtext COLLATE utf8mb4_general_ci,
  `ip_address` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_cash_box_movements` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED DEFAULT NULL,
  `receipt_id` int UNSIGNED DEFAULT NULL,
  `type` enum('entrada','saida') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entrada',
  `category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `movement_date` date NOT NULL,
  `payment_method` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_user_id` int UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tamo_ai_email_logs` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED DEFAULT NULL,
  `report_month` varchar(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `report_year` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `recipients_json` text COLLATE utf8mb4_general_ci NOT NULL,
  `message` mediumtext COLLATE utf8mb4_general_ci,
  `attachments_json` text COLLATE utf8mb4_general_ci,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'enviado',
  `error_message` text COLLATE utf8mb4_general_ci,
  `sent_by` int UNSIGNED DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_email_recipients` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'outro',
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ativo',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_events` (
  `id` int UNSIGNED NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmado',
  `event_date` date NOT NULL,
  `weekday` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(220) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neighborhood` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `set_count` int UNSIGNED NOT NULL DEFAULT '3',
  `interval_minutes` int UNSIGNED NOT NULL DEFAULT '30',
  `total_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_done` tinyint(1) NOT NULL DEFAULT '0',
  `payment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nao',
  `split_condition` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_members` int UNSIGNED NOT NULL DEFAULT '0',
  `day_color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sketch_model` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sound_contract` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viola_player` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drummer` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tamo_ai_event_attendance` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `status` enum('confirmado','fora') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmado',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tamo_ai_event_financials` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED NOT NULL,
  `contracted_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `received_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `received_status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendente',
  `received_date` date DEFAULT NULL,
  `received_method` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_expenses` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_freelancers` decimal(10,2) NOT NULL DEFAULT '0.00',
  `group_cash_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount_to_split` decimal(10,2) NOT NULL DEFAULT '0.00',
  `split_members_count` int UNSIGNED NOT NULL DEFAULT '0',
  `amount_per_member` decimal(10,2) NOT NULL DEFAULT '0.00',
  `manual_adjustment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_general_ci,
  `closed_at` datetime DEFAULT NULL,
  `closed_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_event_freelancers` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instrument` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tamo_ai_freelancers` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(160) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pix_key` varchar(180) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `default_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ativo',
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_materials` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(80) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'outro',
  `estimated_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `product_url` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendente',
  `urgency` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal',
  `priority` int UNSIGNED NOT NULL DEFAULT '0',
  `needed_until` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_payments` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED NOT NULL,
  `person_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'integrante',
  `member_id` int UNSIGNED DEFAULT NULL,
  `freelancer_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(160) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expected_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendente',
  `payment_method` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pix_key` varchar(180) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paid_at` date DEFAULT NULL,
  `receipt_id` int UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_receipts` (
  `id` int UNSIGNED NOT NULL,
  `event_id` int UNSIGNED DEFAULT NULL,
  `payment_id` int UNSIGNED DEFAULT NULL,
  `cash_entry_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(180) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(60) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'outros',
  `category` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'outro',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `payment_method` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pix',
  `paid_by` varchar(160) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `received_by` varchar(160) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `member_id` int UNSIGNED DEFAULT NULL,
  `freelancer_id` int UNSIGNED DEFAULT NULL,
  `file_original_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_mime` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `file_size` int UNSIGNED NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_general_ci,
  `uploaded_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_unavailabilities` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(180) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tamo_ai_users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','member') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_instrument` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tamo_ai_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_activity_logs_module` (`module`),
  ADD KEY `idx_tamo_ai_activity_logs_user` (`user_id`);

ALTER TABLE `tamo_ai_cash_box_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_cash_box_date` (`movement_date`),
  ADD KEY `idx_tamo_ai_cash_box_type` (`type`),
  ADD KEY `idx_tamo_ai_cash_event` (`event_id`),
  ADD KEY `idx_tamo_ai_cash_date_type` (`movement_date`,`type`);

ALTER TABLE `tamo_ai_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_email_logs_event` (`event_id`),
  ADD KEY `idx_tamo_ai_email_logs_status` (`status`);

ALTER TABLE `tamo_ai_email_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_email_recipients_status` (`status`);

ALTER TABLE `tamo_ai_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_events_date` (`event_date`),
  ADD KEY `idx_tamo_ai_events_status` (`status`),
  ADD KEY `idx_tamo_ai_events_client` (`client`);

ALTER TABLE `tamo_ai_event_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_tamo_ai_attendance_unique` (`event_id`,`user_id`),
  ADD KEY `idx_tamo_ai_attendance_event` (`event_id`),
  ADD KEY `idx_tamo_ai_attendance_user` (`user_id`);

ALTER TABLE `tamo_ai_event_financials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_tamo_ai_event_financials_event` (`event_id`);

ALTER TABLE `tamo_ai_event_freelancers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_freelancers_event` (`event_id`);

ALTER TABLE `tamo_ai_freelancers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_freelancers_status` (`status`);

ALTER TABLE `tamo_ai_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_materials_status` (`status`),
  ADD KEY `idx_tamo_ai_materials_urgency` (`urgency`),
  ADD KEY `idx_tamo_ai_materials_needed_until` (`needed_until`);

ALTER TABLE `tamo_ai_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_payments_event` (`event_id`),
  ADD KEY `idx_tamo_ai_payments_status` (`status`);

ALTER TABLE `tamo_ai_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_receipts_event` (`event_id`),
  ADD KEY `idx_tamo_ai_receipts_date` (`payment_date`),
  ADD KEY `idx_tamo_ai_receipts_type` (`type`,`category`);

ALTER TABLE `tamo_ai_unavailabilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tamo_ai_unavailabilities_user` (`user_id`),
  ADD KEY `idx_tamo_ai_unavailabilities_dates` (`start_date`,`end_date`);

ALTER TABLE `tamo_ai_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_tamo_ai_users_email` (`email`);

ALTER TABLE `tamo_ai_activity_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_cash_box_movements`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_email_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tamo_ai_email_recipients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tamo_ai_events`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_event_attendance`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_event_financials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_event_freelancers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_freelancers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_materials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `tamo_ai_receipts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_unavailabilities`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `tamo_ai_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
