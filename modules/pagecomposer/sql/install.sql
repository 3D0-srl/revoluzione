--
-- Struttura della tabella `page_advanced`
--

CREATE TABLE `page_advanced` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_layout` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_css` longtext,
  `custom_css_tmp` longtext,
  `custom_js_head` longtext,
  `custom_js_head_tmp` longtext,
  `custom_js_end` longtext,
  `custom_js_end_tmp` longtext
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indici per le tabelle `page_advanced`
--
ALTER TABLE `page_advanced`
  ADD UNIQUE KEY `id` (`id`);

--

--
-- AUTO_INCREMENT per la tabella `page_advanced`
--
ALTER TABLE `page_advanced`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;


--
-- Struttura della tabella `layout_page`
--

CREATE TABLE `layout_page` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `blocks` text,
  `template` varchar(50) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Dump dei dati per la tabella `layout_page`
--

INSERT INTO `layout_page` (`id`, `label`, `nome`, `blocks`, `template`, `locked`) VALUES
(1, 'top_content', 'Top - Content', '[\"top\",\"content\"]', 'layout1.htm', 0),
(2, 'fullpage', 'Full Page', '[\"content\"]', 'layout2.htm', 0),
(3, NULL, 'Footer', '[\"footer_content\"]', 'footer_preview.htm', 1),
(4, 'sidebar_left', 'Sidebar sx', '[\"top\",\"left\",\"content\"]', 'layout3.htm', 0),
(5, 'sidebar_right', 'Sidebar dx', '[\"top\",\"right\",\"content\"]', 'layout4.htm', 0),
(6, 'product_page', 'Pagina prodotto', '[\"block1\",\"block2\",\"block3\"]', 'product.htm', 0);


--
-- Indici per le tabelle `layout_page`
--
ALTER TABLE `layout_page`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `layout_page`
--
ALTER TABLE `layout_page`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;


--
-- Struttura della tabella `composition_page_tmp`
--

CREATE TABLE `composition_page_tmp` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent` bigint(20) UNSIGNED DEFAULT NULL,
  `position` int(3) UNSIGNED DEFAULT NULL,
  `id_adv_page` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `id_page` bigint(20) UNSIGNED DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `orderView` int(3) DEFAULT '1',
  `visibility` tinyint(1) DEFAULT '1',
  `module_function` varchar(100) DEFAULT NULL,
  `content` text,
  `id_html` varchar(50) DEFAULT NULL,
  `class_html` varchar(100) DEFAULT NULL,
  `parameters` text,
  `block` varchar(50) DEFAULT NULL,
  `cache` tinyint(1) DEFAULT '0',
  `animate_css` varchar(50) DEFAULT NULL,
  `background_url` varchar(300) DEFAULT NULL,
  `background_url_webp` varchar(300) DEFAULT NULL,
  `background_repeat` varchar(10) DEFAULT NULL,
  `background_position` varchar(20) DEFAULT NULL,
  `background_size` varchar(10) DEFAULT NULL,
  `background_attachment` varchar(10) DEFAULT NULL,
  `class_edit` varchar(100) DEFAULT NULL,
  `custom_name` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `detect_mobile_type` varchar(50) DEFAULT NULL,
  `enable_mobile` tinyint(1) DEFAULT '1',
  `enable_tablet` tinyint(1) DEFAULT '1',
  `enable_desktop` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Indici per le tabelle `composition_page_tmp`
--
ALTER TABLE `composition_page_tmp`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `composition_page_tmp`
--
ALTER TABLE `composition_page_tmp`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;


--
-- Struttura della tabella `composition_page`
--

CREATE TABLE `composition_page` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent` bigint(20) UNSIGNED DEFAULT NULL,
  `position` int(3) UNSIGNED DEFAULT NULL,
  `id_adv_page` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `id_page` bigint(20) UNSIGNED DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `orderView` int(3) DEFAULT '1',
  `visibility` tinyint(1) DEFAULT '1',
  `module_function` varchar(100) DEFAULT NULL,
  `content` text,
  `id_html` varchar(50) DEFAULT NULL,
  `class_html` varchar(100) DEFAULT NULL,
  `parameters` text,
  `block` varchar(50) DEFAULT NULL,
  `cache` tinyint(1) DEFAULT '0',
  `animate_css` varchar(50) DEFAULT NULL,
  `background_url` varchar(300) DEFAULT NULL,
  `background_url_webp` varchar(300) DEFAULT NULL,
  `background_repeat` varchar(10) DEFAULT NULL,
  `background_position` varchar(20) DEFAULT NULL,
  `background_size` varchar(10) DEFAULT NULL,
  `background_attachment` varchar(10) DEFAULT NULL,
  `class_edit` varchar(100) DEFAULT NULL,
  `custom_name` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `detect_mobile_type` varchar(50) DEFAULT NULL,
  `enable_mobile` tinyint(1) DEFAULT '1',
  `enable_tablet` tinyint(1) DEFAULT '1',
  `enable_desktop` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Indici per le tabelle `composition_page`
--
ALTER TABLE `composition_page`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `composition_page`
--
ALTER TABLE `composition_page`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

--
-- Struttura della tabella `pagecomposer_log`
--

CREATE TABLE `pagecomposer_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message` text,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `id_page` bigint(20) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Indici per le tabelle `pagecomposer_log`
--
ALTER TABLE `pagecomposer_log`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `pagecomposer_log`
--
ALTER TABLE `pagecomposer_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;
