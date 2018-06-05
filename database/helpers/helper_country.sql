-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.26-log - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.4.0.5187
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table world.helper_country
CREATE TABLE IF NOT EXISTS `helper_country` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(150) NOT NULL,
  `dial_code` int(11) NOT NULL,
  `currency_name` varchar(20) NOT NULL,
  `currency_symbol` varchar(20) NOT NULL,
  `currency_code` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8;

-- Dumping data for table world.helper_country: ~246 rows (approximately)
/*!40000 ALTER TABLE `helper_country` DISABLE KEYS */;
INSERT INTO `helper_country` (`ID`, `code`, `name`, `dial_code`, `currency_name`, `currency_symbol`, `currency_code`) VALUES
	(1, 'AF', 'Afghanistan', 93, 'Afghan afghani', '؋', 'AFN'),
	(2, 'AL', 'Albania', 355, 'Albanian lek', 'L', 'ALL'),
	(3, 'DZ', 'Algeria', 213, 'Algerian dinar', 'د.ج', 'DZD'),
	(4, 'AS', 'American Samoa', 1684, '', '', ''),
	(5, 'AD', 'Andorra', 376, 'Euro', '€', 'EUR'),
	(6, 'AO', 'Angola', 244, 'Angolan kwanza', 'Kz', 'AOA'),
	(7, 'AI', 'Anguilla', 1264, 'East Caribbean dolla', '$', 'XCD'),
	(8, 'AQ', 'Antarctica', 0, '', '', ''),
	(9, 'AG', 'Antigua And Barbuda', 1268, 'East Caribbean dolla', '$', 'XCD'),
	(10, 'AR', 'Argentina', 54, 'Argentine peso', '$', 'ARS'),
	(11, 'AM', 'Armenia', 374, 'Armenian dram', '', 'AMD'),
	(12, 'AW', 'Aruba', 297, 'Aruban florin', 'ƒ', 'AWG'),
	(13, 'AU', 'Australia', 61, 'Australian dollar', '$', 'AUD'),
	(14, 'AT', 'Austria', 43, 'Euro', '€', 'EUR'),
	(15, 'AZ', 'Azerbaijan', 994, 'Azerbaijani manat', '', 'AZN'),
	(16, 'BS', 'Bahamas The', 1242, '', '', ''),
	(17, 'BH', 'Bahrain', 973, 'Bahraini dinar', '.د.ب', 'BHD'),
	(18, 'BD', 'Bangladesh', 880, 'Bangladeshi taka', '৳', 'BDT'),
	(19, 'BB', 'Barbados', 1246, 'Barbadian dollar', '$', 'BBD'),
	(20, 'BY', 'Belarus', 375, 'Belarusian ruble', 'Br', 'BYR'),
	(21, 'BE', 'Belgium', 32, 'Euro', '€', 'EUR'),
	(22, 'BZ', 'Belize', 501, 'Belize dollar', '$', 'BZD'),
	(23, 'BJ', 'Benin', 229, 'West African CFA fra', 'Fr', 'XOF'),
	(24, 'BM', 'Bermuda', 1441, 'Bermudian dollar', '$', 'BMD'),
	(25, 'BT', 'Bhutan', 975, 'Bhutanese ngultrum', 'Nu.', 'BTN'),
	(26, 'BO', 'Bolivia', 591, 'Bolivian boliviano', 'Bs.', 'BOB'),
	(27, 'BA', 'Bosnia and Herzegovina', 387, 'Bosnia and Herzegovi', 'KM or КМ', 'BAM'),
	(28, 'BW', 'Botswana', 267, 'Botswana pula', 'P', 'BWP'),
	(29, 'BV', 'Bouvet Island', 0, '', '', ''),
	(30, 'BR', 'Brazil', 55, 'Brazilian real', 'R$', 'BRL'),
	(31, 'IO', 'British Indian Ocean Territory', 246, 'United States dollar', '$', 'USD'),
	(32, 'BN', 'Brunei', 673, 'Brunei dollar', '$', 'BND'),
	(33, 'BG', 'Bulgaria', 359, 'Bulgarian lev', 'лв', 'BGN'),
	(34, 'BF', 'Burkina Faso', 226, 'West African CFA fra', 'Fr', 'XOF'),
	(35, 'BI', 'Burundi', 257, 'Burundian franc', 'Fr', 'BIF'),
	(36, 'KH', 'Cambodia', 855, 'Cambodian riel', '៛', 'KHR'),
	(37, 'CM', 'Cameroon', 237, 'Central African CFA ', 'Fr', 'XAF'),
	(38, 'CA', 'Canada', 1, 'Canadian dollar', '$', 'CAD'),
	(39, 'CV', 'Cape Verde', 238, 'Cape Verdean escudo', 'Esc or $', 'CVE'),
	(40, 'KY', 'Cayman Islands', 1345, 'Cayman Islands dolla', '$', 'KYD'),
	(41, 'CF', 'Central African Republic', 236, 'Central African CFA ', 'Fr', 'XAF'),
	(42, 'TD', 'Chad', 235, 'Central African CFA ', 'Fr', 'XAF'),
	(43, 'CL', 'Chile', 56, 'Chilean peso', '$', 'CLP'),
	(44, 'CN', 'China', 86, 'Chinese yuan', '¥ or 元', 'CNY'),
	(45, 'CX', 'Christmas Island', 61, '', '', ''),
	(46, 'CC', 'Cocos (Keeling) Islands', 672, 'Australian dollar', '$', 'AUD'),
	(47, 'CO', 'Colombia', 57, 'Colombian peso', '$', 'COP'),
	(48, 'KM', 'Comoros', 269, 'Comorian franc', 'Fr', 'KMF'),
	(49, 'CG', 'Congo', 242, '', '', ''),
	(50, 'CD', 'Congo The Democratic Republic Of The', 242, '', '', ''),
	(51, 'CK', 'Cook Islands', 682, 'New Zealand dollar', '$', 'NZD'),
	(52, 'CR', 'Costa Rica', 506, 'Costa Rican colón', '₡', 'CRC'),
	(53, 'CI', 'Cote D\'Ivoire (Ivory Coast)', 225, '', '', ''),
	(54, 'HR', 'Croatia (Hrvatska)', 385, '', '', ''),
	(55, 'CU', 'Cuba', 53, 'Cuban convertible pe', '$', 'CUC'),
	(56, 'CY', 'Cyprus', 357, 'Euro', '€', 'EUR'),
	(57, 'CZ', 'Czech Republic', 420, 'Czech koruna', 'Kč', 'CZK'),
	(58, 'DK', 'Denmark', 45, 'Danish krone', 'kr', 'DKK'),
	(59, 'DJ', 'Djibouti', 253, 'Djiboutian franc', 'Fr', 'DJF'),
	(60, 'DM', 'Dominica', 1767, 'East Caribbean dolla', '$', 'XCD'),
	(61, 'DO', 'Dominican Republic', 1809, 'Dominican peso', '$', 'DOP'),
	(62, 'TP', 'East Timor', 670, 'United States dollar', '$', 'USD'),
	(63, 'EC', 'Ecuador', 593, 'United States dollar', '$', 'USD'),
	(64, 'EG', 'Egypt', 20, 'Egyptian pound', '£ or ج.م', 'EGP'),
	(65, 'SV', 'El Salvador', 503, 'United States dollar', '$', 'USD'),
	(66, 'GQ', 'Equatorial Guinea', 240, 'Central African CFA ', 'Fr', 'XAF'),
	(67, 'ER', 'Eritrea', 291, 'Eritrean nakfa', 'Nfk', 'ERN'),
	(68, 'EE', 'Estonia', 372, 'Euro', '€', 'EUR'),
	(69, 'ET', 'Ethiopia', 251, 'Ethiopian birr', 'Br', 'ETB'),
	(70, 'XA', 'External Territories of Australia', 61, '', '', ''),
	(71, 'FK', 'Falkland Islands', 500, 'Falkland Islands pou', '£', 'FKP'),
	(72, 'FO', 'Faroe Islands', 298, 'Danish krone', 'kr', 'DKK'),
	(73, 'FJ', 'Fiji Islands', 679, '', '', ''),
	(74, 'FI', 'Finland', 358, 'Euro', '€', 'EUR'),
	(75, 'FR', 'France', 33, 'Euro', '€', 'EUR'),
	(76, 'GF', 'French Guiana', 594, '', '', ''),
	(77, 'PF', 'French Polynesia', 689, 'CFP franc', 'Fr', 'XPF'),
	(78, 'TF', 'French Southern Territories', 0, '', '', ''),
	(79, 'GA', 'Gabon', 241, 'Central African CFA ', 'Fr', 'XAF'),
	(80, 'GM', 'Gambia The', 220, '', '', ''),
	(81, 'GE', 'Georgia', 995, 'Georgian lari', 'ლ', 'GEL'),
	(82, 'DE', 'Germany', 49, 'Euro', '€', 'EUR'),
	(83, 'GH', 'Ghana', 233, 'Ghana cedi', '₵', 'GHS'),
	(84, 'GI', 'Gibraltar', 350, 'Gibraltar pound', '£', 'GIP'),
	(85, 'GR', 'Greece', 30, 'Euro', '€', 'EUR'),
	(86, 'GL', 'Greenland', 299, '', '', ''),
	(87, 'GD', 'Grenada', 1473, 'East Caribbean dolla', '$', 'XCD'),
	(88, 'GP', 'Guadeloupe', 590, '', '', ''),
	(89, 'GU', 'Guam', 1671, '', '', ''),
	(90, 'GT', 'Guatemala', 502, 'Guatemalan quetzal', 'Q', 'GTQ'),
	(91, 'XU', 'Guernsey and Alderney', 44, '', '', ''),
	(92, 'GN', 'Guinea', 224, 'Guinean franc', 'Fr', 'GNF'),
	(93, 'GW', 'Guinea-Bissau', 245, 'West African CFA fra', 'Fr', 'XOF'),
	(94, 'GY', 'Guyana', 592, 'Guyanese dollar', '$', 'GYD'),
	(95, 'HT', 'Haiti', 509, 'Haitian gourde', 'G', 'HTG'),
	(96, 'HM', 'Heard and McDonald Islands', 0, '', '', ''),
	(97, 'HN', 'Honduras', 504, 'Honduran lempira', 'L', 'HNL'),
	(98, 'HK', 'Hong Kong S.A.R.', 852, '', '', ''),
	(99, 'HU', 'Hungary', 36, 'Hungarian forint', 'Ft', 'HUF'),
	(100, 'IS', 'Iceland', 354, 'Icelandic króna', 'kr', 'ISK'),
	(101, 'IN', 'India', 91, 'Indian rupee', '₹', 'INR'),
	(102, 'ID', 'Indonesia', 62, 'Indonesian rupiah', 'Rp', 'IDR'),
	(103, 'IR', 'Iran', 98, 'Iranian rial', '﷼', 'IRR'),
	(104, 'IQ', 'Iraq', 964, 'Iraqi dinar', 'ع.د', 'IQD'),
	(105, 'IE', 'Ireland', 353, 'Euro', '€', 'EUR'),
	(106, 'IL', 'Israel', 972, 'Israeli new shekel', '₪', 'ILS'),
	(107, 'IT', 'Italy', 39, 'Euro', '€', 'EUR'),
	(108, 'JM', 'Jamaica', 1876, 'Jamaican dollar', '$', 'JMD'),
	(109, 'JP', 'Japan', 81, 'Japanese yen', '¥', 'JPY'),
	(110, 'XJ', 'Jersey', 44, 'British pound', '£', 'GBP'),
	(111, 'JO', 'Jordan', 962, 'Jordanian dinar', 'د.ا', 'JOD'),
	(112, 'KZ', 'Kazakhstan', 7, 'Kazakhstani tenge', '', 'KZT'),
	(113, 'KE', 'Kenya', 254, 'Kenyan shilling', 'Sh', 'KES'),
	(114, 'KI', 'Kiribati', 686, 'Australian dollar', '$', 'AUD'),
	(115, 'KP', 'Korea North', 850, '', '', ''),
	(116, 'KR', 'Korea South', 82, '', '', ''),
	(117, 'KW', 'Kuwait', 965, 'Kuwaiti dinar', 'د.ك', 'KWD'),
	(118, 'KG', 'Kyrgyzstan', 996, 'Kyrgyzstani som', 'лв', 'KGS'),
	(119, 'LA', 'Laos', 856, 'Lao kip', '₭', 'LAK'),
	(120, 'LV', 'Latvia', 371, 'Euro', '€', 'EUR'),
	(121, 'LB', 'Lebanon', 961, 'Lebanese pound', 'ل.ل', 'LBP'),
	(122, 'LS', 'Lesotho', 266, 'Lesotho loti', 'L', 'LSL'),
	(123, 'LR', 'Liberia', 231, 'Liberian dollar', '$', 'LRD'),
	(124, 'LY', 'Libya', 218, 'Libyan dinar', 'ل.د', 'LYD'),
	(125, 'LI', 'Liechtenstein', 423, 'Swiss franc', 'Fr', 'CHF'),
	(126, 'LT', 'Lithuania', 370, 'Euro', '€', 'EUR'),
	(127, 'LU', 'Luxembourg', 352, 'Euro', '€', 'EUR'),
	(128, 'MO', 'Macau S.A.R.', 853, '', '', ''),
	(129, 'MK', 'Macedonia', 389, '', '', ''),
	(130, 'MG', 'Madagascar', 261, 'Malagasy ariary', 'Ar', 'MGA'),
	(131, 'MW', 'Malawi', 265, 'Malawian kwacha', 'MK', 'MWK'),
	(132, 'MY', 'Malaysia', 60, 'Malaysian ringgit', 'RM', 'MYR'),
	(133, 'MV', 'Maldives', 960, 'Maldivian rufiyaa', '.ރ', 'MVR'),
	(134, 'ML', 'Mali', 223, 'West African CFA fra', 'Fr', 'XOF'),
	(135, 'MT', 'Malta', 356, 'Euro', '€', 'EUR'),
	(136, 'XM', 'Man (Isle of)', 44, '', '', ''),
	(137, 'MH', 'Marshall Islands', 692, 'United States dollar', '$', 'USD'),
	(138, 'MQ', 'Martinique', 596, '', '', ''),
	(139, 'MR', 'Mauritania', 222, 'Mauritanian ouguiya', 'UM', 'MRO'),
	(140, 'MU', 'Mauritius', 230, 'Mauritian rupee', '₨', 'MUR'),
	(141, 'YT', 'Mayotte', 269, '', '', ''),
	(142, 'MX', 'Mexico', 52, 'Mexican peso', '$', 'MXN'),
	(143, 'FM', 'Micronesia', 691, 'Micronesian dollar', '$', ''),
	(144, 'MD', 'Moldova', 373, 'Moldovan leu', 'L', 'MDL'),
	(145, 'MC', 'Monaco', 377, 'Euro', '€', 'EUR'),
	(146, 'MN', 'Mongolia', 976, 'Mongolian tögrög', '₮', 'MNT'),
	(147, 'MS', 'Montserrat', 1664, 'East Caribbean dolla', '$', 'XCD'),
	(148, 'MA', 'Morocco', 212, 'Moroccan dirham', 'د.م.', 'MAD'),
	(149, 'MZ', 'Mozambique', 258, 'Mozambican metical', 'MT', 'MZN'),
	(150, 'MM', 'Myanmar', 95, 'Burmese kyat', 'Ks', 'MMK'),
	(151, 'NA', 'Namibia', 264, 'Namibian dollar', '$', 'NAD'),
	(152, 'NR', 'Nauru', 674, 'Australian dollar', '$', 'AUD'),
	(153, 'NP', 'Nepal', 977, 'Nepalese rupee', '₨', 'NPR'),
	(154, 'AN', 'Netherlands Antilles', 599, '', '', ''),
	(155, 'NL', 'Netherlands The', 31, '', '', ''),
	(156, 'NC', 'New Caledonia', 687, 'CFP franc', 'Fr', 'XPF'),
	(157, 'NZ', 'New Zealand', 64, 'New Zealand dollar', '$', 'NZD'),
	(158, 'NI', 'Nicaragua', 505, 'Nicaraguan córdoba', 'C$', 'NIO'),
	(159, 'NE', 'Niger', 227, 'West African CFA fra', 'Fr', 'XOF'),
	(160, 'NG', 'Nigeria', 234, 'Nigerian naira', '₦', 'NGN'),
	(161, 'NU', 'Niue', 683, 'New Zealand dollar', '$', 'NZD'),
	(162, 'NF', 'Norfolk Island', 672, '', '', ''),
	(163, 'MP', 'Northern Mariana Islands', 1670, '', '', ''),
	(164, 'NO', 'Norway', 47, 'Norwegian krone', 'kr', 'NOK'),
	(165, 'OM', 'Oman', 968, 'Omani rial', 'ر.ع.', 'OMR'),
	(166, 'PK', 'Pakistan', 92, 'Pakistani rupee', '₨', 'PKR'),
	(167, 'PW', 'Palau', 680, 'Palauan dollar', '$', ''),
	(168, 'PS', 'Palestinian Territory Occupied', 970, '', '', ''),
	(169, 'PA', 'Panama', 507, 'Panamanian balboa', 'B/.', 'PAB'),
	(170, 'PG', 'Papua new Guinea', 675, 'Papua New Guinean ki', 'K', 'PGK'),
	(171, 'PY', 'Paraguay', 595, 'Paraguayan guaraní', '₲', 'PYG'),
	(172, 'PE', 'Peru', 51, 'Peruvian nuevo sol', 'S/.', 'PEN'),
	(173, 'PH', 'Philippines', 63, 'Philippine peso', '₱', 'PHP'),
	(174, 'PN', 'Pitcairn Island', 0, '', '', ''),
	(175, 'PL', 'Poland', 48, 'Polish złoty', 'zł', 'PLN'),
	(176, 'PT', 'Portugal', 351, 'Euro', '€', 'EUR'),
	(177, 'PR', 'Puerto Rico', 1787, '', '', ''),
	(178, 'QA', 'Qatar', 974, 'Qatari riyal', 'ر.ق', 'QAR'),
	(179, 'RE', 'Reunion', 262, '', '', ''),
	(180, 'RO', 'Romania', 40, 'Romanian leu', 'lei', 'RON'),
	(181, 'RU', 'Russia', 70, 'Russian ruble', '', 'RUB'),
	(182, 'RW', 'Rwanda', 250, 'Rwandan franc', 'Fr', 'RWF'),
	(183, 'SH', 'Saint Helena', 290, 'Saint Helena pound', '£', 'SHP'),
	(184, 'KN', 'Saint Kitts And Nevis', 1869, 'East Caribbean dolla', '$', 'XCD'),
	(185, 'LC', 'Saint Lucia', 1758, 'East Caribbean dolla', '$', 'XCD'),
	(186, 'PM', 'Saint Pierre and Miquelon', 508, '', '', ''),
	(187, 'VC', 'Saint Vincent And The Grenadines', 1784, 'East Caribbean dolla', '$', 'XCD'),
	(188, 'WS', 'Samoa', 684, 'Samoan tālā', 'T', 'WST'),
	(189, 'SM', 'San Marino', 378, 'Euro', '€', 'EUR'),
	(190, 'ST', 'Sao Tome and Principe', 239, 'São Tomé and Príncip', 'Db', 'STD'),
	(191, 'SA', 'Saudi Arabia', 966, 'Saudi riyal', 'ر.س', 'SAR'),
	(192, 'SN', 'Senegal', 221, 'West African CFA fra', 'Fr', 'XOF'),
	(193, 'RS', 'Serbia', 381, 'Serbian dinar', 'дин. or din.', 'RSD'),
	(194, 'SC', 'Seychelles', 248, 'Seychellois rupee', '₨', 'SCR'),
	(195, 'SL', 'Sierra Leone', 232, 'Sierra Leonean leone', 'Le', 'SLL'),
	(196, 'SG', 'Singapore', 65, 'Brunei dollar', '$', 'BND'),
	(197, 'SK', 'Slovakia', 421, 'Euro', '€', 'EUR'),
	(198, 'SI', 'Slovenia', 386, 'Euro', '€', 'EUR'),
	(199, 'XG', 'Smaller Territories of the UK', 44, '', '', ''),
	(200, 'SB', 'Solomon Islands', 677, 'Solomon Islands doll', '$', 'SBD'),
	(201, 'SO', 'Somalia', 252, 'Somali shilling', 'Sh', 'SOS'),
	(202, 'ZA', 'South Africa', 27, 'South African rand', 'R', 'ZAR'),
	(203, 'GS', 'South Georgia', 0, '', '', ''),
	(204, 'SS', 'South Sudan', 211, 'South Sudanese pound', '£', 'SSP'),
	(205, 'ES', 'Spain', 34, 'Euro', '€', 'EUR'),
	(206, 'LK', 'Sri Lanka', 94, 'Sri Lankan rupee', 'Rs or රු', 'LKR'),
	(207, 'SD', 'Sudan', 249, 'Sudanese pound', 'ج.س.', 'SDG'),
	(208, 'SR', 'Suriname', 597, 'Surinamese dollar', '$', 'SRD'),
	(209, 'SJ', 'Svalbard And Jan Mayen Islands', 47, '', '', ''),
	(210, 'SZ', 'Swaziland', 268, 'Swazi lilangeni', 'L', 'SZL'),
	(211, 'SE', 'Sweden', 46, 'Swedish krona', 'kr', 'SEK'),
	(212, 'CH', 'Switzerland', 41, 'Swiss franc', 'Fr', 'CHF'),
	(213, 'SY', 'Syria', 963, 'Syrian pound', '£ or ل.س', 'SYP'),
	(214, 'TW', 'Taiwan', 886, 'New Taiwan dollar', '$', 'TWD'),
	(215, 'TJ', 'Tajikistan', 992, 'Tajikistani somoni', 'ЅМ', 'TJS'),
	(216, 'TZ', 'Tanzania', 255, 'Tanzanian shilling', 'Sh', 'TZS'),
	(217, 'TH', 'Thailand', 66, 'Thai baht', '฿', 'THB'),
	(218, 'TG', 'Togo', 228, 'West African CFA fra', 'Fr', 'XOF'),
	(219, 'TK', 'Tokelau', 690, '', '', ''),
	(220, 'TO', 'Tonga', 676, 'Tongan paʻanga', 'T$', 'TOP'),
	(221, 'TT', 'Trinidad And Tobago', 1868, 'Trinidad and Tobago ', '$', 'TTD'),
	(222, 'TN', 'Tunisia', 216, 'Tunisian dinar', 'د.ت', 'TND'),
	(223, 'TR', 'Turkey', 90, 'Turkish lira', '', 'TRY'),
	(224, 'TM', 'Turkmenistan', 7370, 'Turkmenistan manat', 'm', 'TMT'),
	(225, 'TC', 'Turks And Caicos Islands', 1649, 'United States dollar', '$', 'USD'),
	(226, 'TV', 'Tuvalu', 688, 'Australian dollar', '$', 'AUD'),
	(227, 'UG', 'Uganda', 256, 'Ugandan shilling', 'Sh', 'UGX'),
	(228, 'UA', 'Ukraine', 380, 'Ukrainian hryvnia', '₴', 'UAH'),
	(229, 'AE', 'United Arab Emirates', 971, 'United Arab Emirates', 'د.إ', 'AED'),
	(230, 'GB', 'United Kingdom', 44, 'British pound', '£', 'GBP'),
	(231, 'US', 'United States', 1, 'United States dollar', '$', 'USD'),
	(232, 'UM', 'United States Minor Outlying Islands', 1, '', '', ''),
	(233, 'UY', 'Uruguay', 598, 'Uruguayan peso', '$', 'UYU'),
	(234, 'UZ', 'Uzbekistan', 998, 'Uzbekistani som', '', 'UZS'),
	(235, 'VU', 'Vanuatu', 678, 'Vanuatu vatu', 'Vt', 'VUV'),
	(236, 'VA', 'Vatican City State (Holy See)', 39, '', '', ''),
	(237, 'VE', 'Venezuela', 58, 'Venezuelan bolívar', 'Bs F', 'VEF'),
	(238, 'VN', 'Vietnam', 84, 'Vietnamese đồng', '₫', 'VND'),
	(239, 'VG', 'Virgin Islands (British)', 1284, '', '', ''),
	(240, 'VI', 'Virgin Islands (US)', 1340, '', '', ''),
	(241, 'WF', 'Wallis And Futuna Islands', 681, '', '', ''),
	(242, 'EH', 'Western Sahara', 212, '', '', ''),
	(243, 'YE', 'Yemen', 967, 'Yemeni rial', '﷼', 'YER'),
	(244, 'YU', 'Yugoslavia', 38, '', '', ''),
	(245, 'ZM', 'Zambia', 260, 'Zambian kwacha', 'ZK', 'ZMW'),
	(246, 'ZW', 'Zimbabwe', 263, 'Botswana pula', 'P', 'BWP');
/*!40000 ALTER TABLE `helper_country` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
