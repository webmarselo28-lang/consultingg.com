-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Време на генериране: 18 авг 2025 в 08:46
-- Версия на сървъра: 10.6.23-MariaDB-log
-- Версия на PHP: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данни: `yogahonc_consultingg55`
--

-- --------------------------------------------------------

--
-- Структура на таблица `pages`
--

CREATE TABLE `pages` (
  `id` varchar(36) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `meta_description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Схема на данните от таблица `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `meta_description`, `active`, `created_at`, `updated_at`) VALUES
('page-001', 'home', 'Начало', '<h1>Добре дошли в ConsultingG Real Estate</h1><p>Водещата платформа за недвижими имоти в България с над 15 години опит.</p>', 'ConsultingG Real Estate - водещата платформа за недвижими имоти в България', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('page-002', 'about', 'За нас', '<h2>ConsultingG Real Estate</h2><p>Водещата компания за недвижими имоти в България с над 15 години опит в сферата. Нашият екип от професионалисти предлага пълен спектър от услуги - от търсене и оценка на имоти до правно съдействие при сделки.</p><h3>Нашата мисия</h3><p>Да предоставяме най-качествените услуги в областта на недвижимите имоти, като помагаме на клиентите ни да намерят перфектния дом или да направят печеливша инвестиция.</p>', 'Научете повече за ConsultingG Real Estate - водещата компания за недвижими имоти в България', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('page-003', 'contact', 'Контакти', '<h2>Свържете се с нас</h2><p>Нашият екип е на ваше разположение за всички въпроси, свързани с недвижими имоти.</p><h3>Офис адрес</h3><p>бул. Янко Съкъзов 16<br>София 1504, България</p><h3>Телефон</h3><p><a href=\"tel:0888825445\">0888 825 445</a></p><h3>Имейл</h3><p><a href=\"mailto:office@consultingg.com\">office@consultingg.com</a></p>', 'Свържете се с ConsultingG Real Estate - телефон, имейл, адрес и работно време', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('page-004', 'services', 'Услуги', '<h2>Нашите услуги</h2><p>Предлагаме пълен спектър от професионални услуги в областта на недвижимите имоти.</p><ul><li>Продажба на имоти</li><li>Инвестиционни консултации</li><li>Правна защита</li><li>Управление на имоти</li><li>Оценка на имоти</li><li>24/7 Поддръжка</li></ul>', 'Професионални услуги за недвижими имоти от ConsultingG Real Estate', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Структура на таблица `properties`
--

CREATE TABLE `properties` (
  `id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'EUR',
  `transaction_type` enum('sale','rent') NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `city_region` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `area` decimal(8,2) NOT NULL,
  `bedrooms` int(11) DEFAULT 0,
  `bathrooms` int(11) DEFAULT 0,
  `floors` int(11) DEFAULT NULL,
  `floor_number` int(11) DEFAULT NULL,
  `terraces` int(11) DEFAULT 0,
  `construction_type` varchar(50) DEFAULT NULL,
  `condition_type` varchar(50) DEFAULT NULL,
  `heating` varchar(50) DEFAULT NULL,
  `exposure` varchar(50) DEFAULT NULL,
  `year_built` int(11) DEFAULT NULL,
  `furnishing_level` varchar(50) DEFAULT NULL,
  `has_elevator` tinyint(1) DEFAULT 0,
  `has_garage` tinyint(1) DEFAULT 0,
  `has_southern_exposure` tinyint(1) DEFAULT 0,
  `new_construction` tinyint(1) DEFAULT 0,
  `featured` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Схема на данните от таблица `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `price`, `currency`, `transaction_type`, `property_type`, `city_region`, `district`, `address`, `area`, `bedrooms`, `bathrooms`, `floors`, `floor_number`, `terraces`, `construction_type`, `condition_type`, `heating`, `exposure`, `year_built`, `furnishing_level`, `has_elevator`, `has_garage`, `has_southern_exposure`, `new_construction`, `featured`, `active`, `created_at`, `updated_at`) VALUES
('prop-001', 'Луксозен апартамент в Симеоново', 'Прекрасен тристаен апартамент в престижния квартал Симеоново с панорамна гледка към Витоша. Апартаментът разполага с просторна всекидневна, две спални, две бани и голяма тераса. Идеален за семейство, търсещо комфорт и качество.', 290000.00, 'EUR', 'sale', '3-СТАЕН', 'София', 'Симеоново', 'ул. Симеоновско шосе 123', 145.50, 2, 2, 8, 5, 1, 'Тухла', 'Ново строителство', 'Локално', 'Юг', 2023, 'full', 1, 1, 1, 1, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-002', 'Къща в Драгалевци', 'Уютна двуетажна къща в подножието на Витоша. Идеална за семейство, търсещо спокойствие и близост до природата. Разполага с просторен двор, гараж и красива градина с барбекю зона.', 450000.00, 'EUR', 'sale', 'КЪЩА', 'София', 'Драгалевци', 'ул. Драгалевска 45', 220.00, 4, 3, 2, 0, 2, 'Тухла', 'След ремонт', 'Локално', 'Ю-З', 2018, 'partial', 0, 1, 1, 0, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-003', 'Офис в центъра', 'Модерен офис в сърцето на София, подходящ за различни бизнес дейности. Отлична локация с лесен достъп до транспорт и паркинг. Напълно оборудван с модерна техника.', 180000.00, 'EUR', 'sale', 'ОФИС', 'София', 'Център', 'бул. Витоша 85', 95.00, 0, 1, 12, 8, 0, 'Монолит', 'Обзаведен', 'ТЕЦ', 'Изток', 2020, 'full', 1, 0, 0, 1, 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-004', 'Мезонет в Бояна', 'Луксозен мезонет с градина в елитния квартал Бояна. Изключителна архитектура и висококачествени материали. Частна градина с басейн и зона за отдих.', 650000.00, 'EUR', 'sale', 'МЕЗОНЕТ', 'София', 'Бояна', 'ул. Боянско езеро 12', 180.00, 3, 2, 2, 0, 2, 'Тухла', 'Ново строителство', 'Локално', 'Ю-З', 2024, 'none', 0, 1, 1, 1, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-005', 'Апартамент под наем в Оборище', 'Уютен двустаен апартамент за дългосрочен наем в престижния квартал Оборище. Напълно обзаведен и готов за нанасяне. Близо до метростанция и търговски центрове.', 1200.00, 'EUR', 'rent', '2-СТАЕН', 'София', 'Оборище', 'ул. Шипка 15', 75.00, 1, 1, 6, 3, 1, 'Тухла', 'Обзаведен', 'ТЕЦ', 'Юг', 2015, 'full', 1, 0, 1, 0, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Структура на таблица `property_images`
--

CREATE TABLE `property_images` (
  `id` varchar(36) NOT NULL,
  `property_id` varchar(36) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Схема на данните от таблица `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_url`, `image_path`, `alt_text`, `sort_order`, `is_main`, `created_at`) VALUES
('img-001', 'prop-001', '/uploads/properties/prop-001/img-1.jpg', '/uploads/properties/prop-001/img-1.jpg', 'Луксозен апартамент в Симеоново - главна снимка', 0, 1, '2024-01-01 10:00:00'),
('img-002', 'prop-001', '/uploads/properties/prop-001/img-2.jpg', '/uploads/properties/prop-001/img-2.jpg', 'Луксозен апартамент в Симеоново - всекидневна', 1, 0, '2024-01-01 10:00:00'),
('img-003', 'prop-001', '/uploads/properties/prop-001/img-3.jpg', '/uploads/properties/prop-001/img-3.jpg', 'Луксозен апартамент в Симеоново - спалня', 2, 0, '2024-01-01 10:00:00'),
('img-004', 'prop-002', '/uploads/properties/prop-002/img-1.jpg', '/uploads/properties/prop-002/img-1.jpg', 'Къща в Драгалевци - фасада', 0, 1, '2024-01-01 10:00:00'),
('img-005', 'prop-002', '/uploads/properties/prop-002/img-2.jpg', '/uploads/properties/prop-002/img-2.jpg', 'Къща в Драгалевци - градина', 1, 0, '2024-01-01 10:00:00'),
('img-006', 'prop-002', '/uploads/properties/prop-002/img-3.jpg', '/uploads/properties/prop-002/img-3.jpg', 'Къща в Драгалевци - интериор', 2, 0, '2024-01-01 10:00:00'),
('img-007', 'prop-003', '/uploads/properties/prop-003/img-1.jpg', '/uploads/properties/prop-003/img-1.jpg', 'Офис в центъра - работно пространство', 0, 1, '2024-01-01 10:00:00'),
('img-008', 'prop-003', '/uploads/properties/prop-003/img-2.jpg', '/uploads/properties/prop-003/img-2.jpg', 'Офис в центъра - конферентна зала', 1, 0, '2024-01-01 10:00:00'),
('img-009', 'prop-003', '/uploads/properties/prop-003/img-3.jpg', '/uploads/properties/prop-003/img-3.jpg', 'Офис в центъра - рецепция', 2, 0, '2024-01-01 10:00:00'),
('img-010', 'prop-004', '/uploads/properties/prop-004/img-1.jpg', '/uploads/properties/prop-004/img-1.jpg', 'Мезонет в Бояна - екстериор', 0, 1, '2024-01-01 10:00:00'),
('img-011', 'prop-004', '/uploads/properties/prop-004/img-2.jpg', '/uploads/properties/prop-004/img-2.jpg', 'Мезонет в Бояна - басейн', 1, 0, '2024-01-01 10:00:00'),
('img-012', 'prop-004', '/uploads/properties/prop-004/img-3.jpg', '/uploads/properties/prop-004/img-3.jpg', 'Мезонет в Бояна - всекидневна', 2, 0, '2024-01-01 10:00:00'),
('img-013', 'prop-005', '/uploads/properties/prop-005/img-1.jpg', '/uploads/properties/prop-005/img-1.jpg', 'Апартамент под наем в Оборище - главна', 0, 1, '2024-01-01 10:00:00'),
('img-014', 'prop-005', '/uploads/properties/prop-005/img-2.jpg', '/uploads/properties/prop-005/img-2.jpg', 'Апартамент под наем в Оборище - кухня', 1, 0, '2024-01-01 10:00:00'),
('img-015', 'prop-005', '/uploads/properties/prop-005/img-3.jpg', '/uploads/properties/prop-005/img-3.jpg', 'Апартамент под наем в Оборище - баня', 2, 0, '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Структура на таблица `sections`
--

CREATE TABLE `sections` (
  `id` varchar(36) NOT NULL,
  `page_id` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `section_type` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_data`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Схема на данните от таблица `sections`
--

INSERT INTO `sections` (`id`, `page_id`, `title`, `content`, `section_type`, `sort_order`, `active`, `meta_data`, `created_at`, `updated_at`) VALUES
('sect-001', 'page-001', 'Hero Section', '<h1>Намерете своя перфектен дом</h1><p>Водещата платформа за недвижими имоти в България с над 15 години опит.</p>', 'hero', 1, 1, '{\"background_image\": \"/images/hero-bg.jpg\", \"cta_text\": \"Започнете търсенето\", \"cta_link\": \"/properties\"}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-002', 'page-001', 'Features Section', '<h2>Защо да изберете нас?</h2><div class=\"grid\"><div><h3>15+ години опит</h3><p>Професионален екип с богат опит</p></div><div><h3>1000+ доволни клиенти</h3><p>Хиляди успешно реализирани сделки</p></div></div>', 'features', 2, 1, '{\"layout\": \"grid\", \"columns\": 2}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-003', 'page-002', 'About Hero', '<h1>За ConsultingG Real Estate</h1><p>Водещата компания за недвижими имоти в България</p>', 'hero', 1, 1, '{\"background_color\": \"#f8f9fa\"}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-004', 'page-002', 'Company Info', '<h2>Нашата история</h2><p>От 2008 година ConsultingG Real Estate е водещ играч на българския пазар на недвижими имоти. Започнахме като малка семейна компания и днес сме един от най-доверените партньори в сферата.</p>', 'about', 2, 1, '{\"show_stats\": true}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-005', 'page-003', 'Contact Hero', '<h1>Свържете се с нас</h1><p>Нашият екип е на ваше разположение</p>', 'hero', 1, 1, '{\"background_color\": \"#e3f2fd\"}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-006', 'page-003', 'Contact Info', '<div class=\"contact-grid\"><div><h3>Офис</h3><p>бул. Янко Съкъзов 16<br>София 1504, България</p></div><div><h3>Телефон</h3><p>0888 825 445</p></div><div><h3>Имейл</h3><p>office@consultingg.com</p></div></div>', 'contact', 2, 1, '{\"show_map\": true, \"map_coordinates\": \"42.6977,23.3219\"}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-007', 'page-004', 'Services Hero', '<h1>Нашите услуги</h1><p>Пълен спектър от професионални услуги</p>', 'hero', 1, 1, '{\"background_color\": \"#f3e5f5\"}', '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('sect-008', 'page-004', 'Services List', '<div class=\"services-grid\"><div><h3>Продажба на имоти</h3><p>Професионално съдействие при продажба</p></div><div><h3>Инвестиционни консултации</h3><p>Експертни съвети за инвестиции</p></div><div><h3>Правна защита</h3><p>Пълна правна защита при сделки</p></div></div>', 'services', 2, 1, '{\"layout\": \"grid\", \"columns\": 3}', '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Структура на таблица `users`
--

CREATE TABLE `users` (
  `id` varchar(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Схема на данните от таблица `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `role`, `active`, `created_at`, `updated_at`) VALUES
('user-001', 'georgiev@consultingg.com', '$2y$10$tY8oqFKjgXqm7lrpT45iJOUZXkq8xi/rq.Eyl/gzrPCo/mSP6yO9G', 'Administrator', 'admin', 1, '2025-08-09 08:30:08', '2025-08-17 10:41:16');

--
-- Indexes for dumped tables
--

--
-- Индекси за таблица `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`active`);

--
-- Индекси за таблица `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_property_type` (`property_type`),
  ADD KEY `idx_city_region` (`city_region`),
  ADD KEY `idx_district` (`district`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_area` (`area`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индекси за таблица `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_is_main` (`is_main`);

--
-- Индекси за таблица `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_id` (`page_id`),
  ADD KEY `idx_section_type` (`section_type`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_active` (`active`);

--
-- Индекси за таблица `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_active` (`active`);

--
-- Ограничения за дъмпнати таблици
--

--
-- Ограничения за таблица `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `fk_property_images_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения за таблица `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `fk_sections_page_id` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
