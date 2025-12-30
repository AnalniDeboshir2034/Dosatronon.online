-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Дек 20 2025 г., 17:29
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `catalog`
--

-- --------------------------------------------------------

--
-- Структура таблицы `medicator`
--

CREATE TABLE `medicator` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `d_dosing` varchar(255) NOT NULL,
  `performance` varchar(255) NOT NULL,
  `pressure` varchar(255) NOT NULL,
  `temperature` varchar(255) NOT NULL,
  `connections` varchar(255) NOT NULL,
  `m_seal` varchar(255) NOT NULL,
  `m_case` varchar(255) NOT NULL,
  `dop` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `diag` varchar(255) NOT NULL,
  `pdf` varchar(255) NOT NULL,
  `opis` varchar(1000) NOT NULL,
  `filtr` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `medicator`
--

INSERT INTO `medicator` (`id`, `name`, `d_dosing`, `performance`, `pressure`, `temperature`, `connections`, `m_seal`, `m_case`, `dop`, `img`, `diag`, `pdf`, `opis`, `filtr`) VALUES
(1, 'Dosatron DIA4AL VF', ' 0.2 — 2 % [1:500 — 1:50]', '10 л/ч — 2.5 м³/ч [0.16 — 41.66 л/мин]', '0.3 — 6 бар', '5 — 40 °C', 'G¾» наружная', 'VITON – для кислот, масел, ветеринарных препаратов, ароматических веществ и пестицидов', 'Полиацеталь', '-', 'medikator.jpg', 'diag.jpg', 'pasp.pdf', 'DIA4AL (серия Animal Health Line) – это новейшая разработка компании Dosatron и новое слово в неэлектрическом пропорциональном дозировании. Уникальный мембранный двигатель позволяет решить ряд принципиальных проблем дозирования препаратов в птицеводстве и свиноводсте. Дозаторон может работать при чрезвычайно малом расходе воды, что делает его незаменимым для поения молодняка. Низкое рабочее давление позволяет использовать его даже в системе с водонапорным баком на высоте всего 1.5 метра. А конструкция двигателя с минимальным количеством деталей подверженных трению, делает возможным применение дозатрона DIA4AL даже если поступающая вода имеет высокое содержание минеральных примесей.', 'DIA'),
(2, 'Dosatron D07RE125AF', '0.15 — 1.25 % [1:666 — 1:80]', '5 л/ч — 0.7 м³/ч [0.08 — 11.66 л/мин]', '0.3 — 6 бар', '5 — 40 °C', 'G¾\" наружная', 'AFLAS – уплотнения, устойчивые к щелочам, для дозирования жидкостей со значением pH более 8', 'Полиацеталь', 'Встроенный by-pass', 'D07RE125AF.jpg', 'diag.jpg', 'pasp.pdf', 'Серия D07 Compact – это самые малогабаритные неэлектрические пропорциональные дозаторы. Их используют в тех случаях, когда не требуется высокая производительность и сильно ограничено пространство для установки. Часто находят свое применение в составе более сложных агрегатов.', 'D07'),
(3, 'Dosatron D07RE5AF', '0.8 — 5.5 % [1:128 — 1:28]', '5 л/ч — 0.7 м³/ч [0.08 — 11.66 л/мин]', '0.3 — 6 бар', '5 — 40 °C', 'G¾\" наружная', 'AFLAS – уплотнения, устойчивые к щелочам, для дозирования жидкостей со значением pH более 8', 'Полиацеталь', 'Встроенный by-pass', 'D07RE5AF.jpg', 'diag.jpg', 'pasp.pdf', 'Серия D07 Compact – это самые малогабаритные неэлектрические пропорциональные дозаторы. Их используют в тех случаях, когда не требуется высокая производительность и сильно ограничено пространство для установки. Часто находят свое применение в составе более сложных агрегатов.', 'D07');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `medicator`
--
ALTER TABLE `medicator`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `medicator`
--
ALTER TABLE `medicator`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
