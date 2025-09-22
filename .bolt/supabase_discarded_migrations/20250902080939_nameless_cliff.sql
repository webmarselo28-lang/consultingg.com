-- ConsultingG Real Estate - Production Ready MySQL Database
-- For superhosting.bg deployment
-- Database: yogahonc_consultingg55
-- Generated: 2025-01-XX

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yogahonc_consultingg55`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `name`, `role`, `active`, `created_at`, `updated_at`) VALUES
('user-001', 'georgiev@consultingg.com', '$2y$10$tY8oqFKjgXqm7lrpT45iJOUZXkq8xi/rq.Eyl/gzrPCo/mSP6yO9G', 'Georgi Georgiev', 'admin', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` varchar(64) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `price`, `currency`, `transaction_type`, `property_type`, `city_region`, `district`, `address`, `area`, `bedrooms`, `bathrooms`, `floors`, `floor_number`, `terraces`, `construction_type`, `condition_type`, `heating`, `exposure`, `year_built`, `furnishing_level`, `has_elevator`, `has_garage`, `has_southern_exposure`, `new_construction`, `featured`, `active`, `created_at`, `updated_at`) VALUES
('prop-001', 'Луксозен апартамент в Симеоново', 'Прекрасен тристаен апартамент в престижния квартал Симеоново с панорамна гледка към Витоша. Апартаментът разполага с просторна всекидневна, две спални, две бани и голяма тераса. Идеален за семейство, търсещо комфорт и качество.', 290000.00, 'EUR', 'sale', '3-СТАЕН', 'София', 'Симеоново', 'ул. Симеоновско шосе 123', 145.50, 2, 2, 8, 5, 1, 'Тухла', 'Ново строителство', 'Локално', 'Юг', 2023, 'full', 1, 1, 1, 1, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-002', 'Къща в Драгалевци', 'Уютна двуетажна къща в подножието на Витоша. Идеална за семейство, търсещо спокойствие и близост до природата. Разполага с просторен двор, гараж и красива градина с барбекю зона.', 450000.00, 'EUR', 'sale', 'КЪЩА', 'София', 'Драгалевци', 'ул. Драгалевска 45', 220.00, 4, 3, 2, 0, 2, 'Тухла', 'След ремонт', 'Локално', 'Ю-З', 2018, 'partial', 0, 1, 1, 0, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-003', 'Офис в центъра', 'Модерен офис в сърцето на София, подходящ за различни бизнес дейности. Отлична локация с лесен достъп до транспорт и паркинг. Напълно оборудван с модерна техника.', 180000.00, 'EUR', 'sale', 'ОФИС', 'София', 'Център', 'бул. Витоша 85', 95.00, 0, 1, 12, 8, 0, 'Монолит', 'Обзаведен', 'ТЕЦ', 'Изток', 2020, 'full', 1, 0, 0, 1, 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-004', 'Мезонет в Бояна', 'Луксозен мезонет с градина в елитния квартал Бояна. Изключителна архитектура и висококачествени материали. Частна градина с басейн и зона за отдих.', 650000.00, 'EUR', 'sale', 'МЕЗОНЕТ', 'София', 'Бояна', 'ул. Боянско езеро 12', 180.00, 3, 2, 2, 0, 2, 'Тухла', 'Ново строителство', 'Локално', 'Ю-З', 2024, 'none', 0, 1, 1, 1, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-005', 'Апартамент под наем в Оборище', 'Уютен двустаен апартамент за дългосрочен наем в престижния квартал Оборище. Напълно обзаведен и готов за нанасяне. Близо до метростанция и търговски центрове.', 1200.00, 'EUR', 'rent', '2-СТАЕН', 'София', 'Оборище', 'ул. Шипка 15', 75.00, 1, 1, 6, 3, 1, 'Тухла', 'Обзаведен', 'ТЕЦ', 'Юг', 2015, 'full', 1, 0, 1, 0, 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('prop-006', 'Студио в Студентски град', 'Модерно студио, идеално за студенти или млади професионалисти. Близо до университета и метростанция.', 800.00, 'EUR', 'rent', '1-СТАЕН', 'София', 'Студентски град', 'бул. Св. Климент Охридски 8', 35.00, 0, 1, 10, 4, 1, 'Панел', 'Обзаведен', 'ТЕЦ', 'Изток', 2010, 'full', 0, 0, 0, 0, 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` varchar(64) NOT NULL,
  `property_id` varchar(64) NOT NULL,
  `image_url` varchar(512) NOT NULL,
  `image_path` varchar(512) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_url`, `image_path`, `alt_text`, `sort_order`, `is_main`, `created_at`, `updated_at`) VALUES
('img-001', 'prop-001', '/images/1_kachta_simeonovo.jpg', '/images/1_kachta_simeonovo.jpg', 'Луксозен апартамент в Симеоново - главна снимка', 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-001b', 'prop-001', '/images/2_kachta_simeonovo.jpg', '/images/2_kachta_simeonovo.jpg', 'Луксозен апартамент в Симеоново - всекидневна', 1, 0, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-001c', 'prop-001', '/images/3_kachta_simeonovo.jpg', '/images/3_kachta_simeonovo.jpg', 'Луксозен апартамент в Симеоново - спалня', 2, 0, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-002', 'prop-002', '/images/1_kachta_dragalevci.jpg', '/images/1_kachta_dragalevci.jpg', 'Къща в Драгалевци - фасада', 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-002b', 'prop-002', '/images/2_kachta_dragalevci.jpg', '/images/2_kachta_dragalevci.jpg', 'Къща в Драгалевци - градина', 1, 0, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-003', 'prop-003', '/images/1_ap_oborichte.jpg', '/images/1_ap_oborichte.jpg', 'Офис в центъра - работно пространство', 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-004', 'prop-004', '/images/2_kachta_dragalevci.jpg', '/images/2_kachta_dragalevci.jpg', 'Мезонет в Бояна - екстериор', 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-004b', 'prop-004', '/images/4_kachta_simeonovo.jpg', '/images/4_kachta_simeonovo.jpg', 'Мезонет в Бояна - басейн', 1, 0, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-004c', 'prop-004', '/images/5_kachta_simeonovo.jpg', '/images/5_kachta_simeonovo.jpg', 'Мезонет в Бояна - всекидневна', 2, 0, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-005', 'prop-005', '/images/1_ap_oborichte.jpg', '/images/1_ap_oborichte.jpg', 'Апартамент под наем в Оборище - главна', 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('img-006', 'prop-006', '/images/1_kachta_simeonovo.jpg', '/images/1_kachta_simeonovo.jpg', 'Студио в Студентски град - главна', 0, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(100) NOT NULL,
  `color` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `icon`, `color`, `sort_order`, `active`, `created_at`, `updated_at`) VALUES
('service-001', 'Продажба на имоти', 'Професионално съдействие при продажба на всички видове недвижими имоти', 'Home', 'from-blue-500 to-blue-600', 1, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('service-002', 'Инвестиционни консултации', 'Експертни съвети за инвестиции в недвижими имоти с висока доходност', 'TrendingUp', 'from-green-500 to-green-600', 2, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('service-003', 'Правна защита', 'Пълна правна защита и съдействие при всички сделки с имоти', 'Shield', 'from-purple-500 to-purple-600', 3, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('service-004', 'Управление на имоти', 'Професионално управление и поддръжка на вашите недвижими имоти', 'Users', 'from-orange-500 to-orange-600', 4, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('service-005', 'Оценка на имоти', 'Точна и професионална оценка на пазарната стойност на имотите', 'Award', 'from-red-500 to-red-600', 5, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('service-006', '24/7 Поддръжка', 'Непрекъсната поддръжка и консултации за всички ваши въпроси', 'Headphones', 'from-indigo-500 to-indigo-600', 6, 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` varchar(64) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `meta_description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `meta_description`, `active`, `created_at`, `updated_at`) VALUES
('page-001', 'home', 'Начало', '<h1>Добре дошли в ConsultingG Real Estate</h1><p>Водещата платформа за недвижими имоти в България с над 15 години опит.</p>', 'ConsultingG Real Estate - водещата платформа за недвижими имоти в България', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('page-002', 'about', 'За нас', '<h2>ConsultingG Real Estate</h2><p>Водещата компания за недвижими имоти в България с над 15 години опит в сферата. Нашият екип от професионалисти предлага пълен спектър от услуги - от търсене и оценка на имоти до правно съдействие при сделки.</p><h3>Нашата мисия</h3><p>Да предоставяме най-качествените услуги в областта на недвижимите имоти, като помагаме на клиентите ни да намерят перфектния дом или да направят печеливша инвестиция.</p><h3>Нашият екип</h3><p>Георги Георгиев - Управляващ партньор с над 15 години опит в недвижимите имоти. Експерт в инвестиционни консултации и управление на портфейли от имоти.</p>', 'Научете повече за ConsultingG Real Estate - водещата компания за недвижими имоти в България', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00'),
('page-003', 'contact', 'Контакти', '<h2>Свържете се с нас</h2><p>Нашият екип е на ваше разположение за всички въпроси, свързани с недвижими имоти.</p><h3>Офис адрес</h3><p>бул. Янко Съкъзов 16<br>София 1504, България</p><h3>Телефон</h3><p><a href="tel:0888825445">0888 825 445</a></p><h3>Имейл</h3><p><a href="mailto:office@consultingg.com">office@consultingg.com</a></p><h3>Работно време</h3><p>Понеделник - Петък: 09:00 - 18:00<br>Събота: 10:00 - 16:00<br>Неделя: Почивен ден</p>', 'Свържете се с ConsultingG Real Estate - телефон, имейл, адрес и работно време', 1, '2024-01-01 10:00:00', '2024-01-01 10:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_active` (`active`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_properties_transaction_type` (`transaction_type`),
  ADD KEY `idx_properties_property_type` (`property_type`),
  ADD KEY `idx_properties_city_region` (`city_region`),
  ADD KEY `idx_properties_district` (`district`),
  ADD KEY `idx_properties_price` (`price`),
  ADD KEY `idx_properties_area` (`area`),
  ADD KEY `idx_properties_active` (`active`),
  ADD KEY `idx_properties_featured` (`featured`),
  ADD KEY `idx_properties_created_at` (`created_at`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property_images_property_id` (`property_id`),
  ADD KEY `idx_property_images_sort_order` (`sort_order`),
  ADD KEY `idx_property_images_is_main` (`is_main`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_services_sort_order` (`sort_order`),
  ADD KEY `idx_services_active` (`active`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_key` (`slug`),
  ADD KEY `idx_pages_slug` (`slug`),
  ADD KEY `idx_pages_active` (`active`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `fk_property_images_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;