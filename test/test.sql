SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  `surname` varchar(100) COLLATE utf8_turkish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

INSERT INTO `users` (`id`, `name`, `surname`) VALUES
(1, '1726', '6158'),
(2, '9342', '9870'),
(3, '7648', '7834'),
(4, '7308', '6102'),
(5, '7966', '9099'),
(6, '4855', '2280'),
(7, '4871', '8713'),
(8, '5566', '9941'),
(9, '5746', '2979'),
(10, '9505', '8015');