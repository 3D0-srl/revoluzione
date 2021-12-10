CREATE TABLE `product_feature` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orderView` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `product_feature`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `product_feature`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

CREATE TABLE `product_feature_lang` (
  `id_product_feature` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `lang` varchar(3) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE TABLE `product_feature_value` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_product_feature` bigint(20) UNSIGNED NOT NULL,
  `orderView` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `product_feature_value`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `product_feature_value`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

CREATE TABLE `product_feature_value_lang` (
  `id_product_feature_value` bigint(20) UNSIGNED NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  `lang` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `product_feature_association` (
  `id_product` bigint(20) UNSIGNED NOT NULL,
  `id_feature_value` bigint(20) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
