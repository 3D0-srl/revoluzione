CREATE TABLE amazon_store (
    name varchar(100) DEFAULT NULL,
    merchantId varchar(200) DEFAULT NULL,
    id bigint(20) UNSIGNED NOT NULL,
    marketplace text,
    token varchar(300) DEFAULT NULL,
    statusPaid varchar(100) DEFAULT NULL,
    statusSent varchar(100) DEFAULT NULL,
    categories text,
    mapping_profile text
);

ALTER TABLE amazon_store ADD PRIMARY KEY (id), ADD UNIQUE KEY id (id);
ALTER TABLE amazon_store MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE amazon_carrier (
    id_amazon varchar(100) NOT NULL,
    id_marion bigint(20) UNSIGNED NOT NULL,
    id_store bigint(20) UNSIGNED NOT NULL
);
CREATE TABLE amazon_carrier_exit (
    id_store bigint(20) UNSIGNED NOT NULL,
    id_marion bigint(20) UNSIGNED NOT NULL,
    id_amazon varchar(100) NOT NULL,
    market varchar(100) DEFAULT NULL
);

CREATE TABLE amazon_feed_sync (
    FeedSubmissionId varchar(50) NOT NULL,
    FeedType varchar(50) DEFAULT NULL,
    FeedProcessingStatus varchar(50) DEFAULT NULL,
    timestamp timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    marketplace varchar(50) DEFAULT NULL,
    id_store bigint(20) UNSIGNED DEFAULT NULL,
    successes int(11) DEFAULT '0' DEFAULT NULL,
    errors int(11) DEFAULT '0' DEFAULT NULL,
    warnings int(11) DEFAULT '0' DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE amazon_report_sync (
    ReportRequestId varchar(50) NOT NULL,
    ReportType varchar(50) DEFAULT NULL,
    ReportProcessingStatus varchar(50) DEFAULT NULL,
    timestamp timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    id_store bigint(20) UNSIGNED DEFAULT NULL,
    marketplace varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE amazon_product (
    id bigint(20) UNSIGNED NOT NULL,
    id_account bigint(20) UNSIGNED DEFAULT NULL,
    marketplace varchar(50) DEFAULT NULL,
    id_product bigint(20) UNSIGNED NOT NULL,
    parent_description tinyint(1) DEFAULT '0',
    new_product tinyint(1) DEFAULT '0',
    price double DEFAULT NULL,
    disable_sync tinyint(1) DEFAULT '0',
    bullet_1 varchar(200) DEFAULT NULL,
    bullet_2 varchar(200) DEFAULT NULL,
    bullet_3 varchar(200) DEFAULT NULL
);

ALTER TABLE amazon_product ADD UNIQUE KEY id (id);


ALTER TABLE amazon_product MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE TABLE amazon_order_log (
  id bigint(20) UNSIGNED NOT NULL,
  marketplace varchar(50) DEFAULT NULL,
  timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  success int(11) NOT NULL DEFAULT '0',
  error int(11) NOT NULL DEFAULT '0',
  tot int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE amazon_order_log
  ADD UNIQUE KEY id (id);


ALTER TABLE amazon_order_log
  MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;


CREATE TABLE amazon_order_log_error (
  id_log bigint(20) UNSIGNED NOT NULL,
  error_type varchar(50) DEFAULT NULL,
  error_message varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
COMMIT;


CREATE TABLE amazon_order (
    id_marion bigint(20) UNSIGNED NOT NULL,
    id_amazon varchar(100) NOT NULL,
    date datetime DEFAULT NULL,
    market varchar(100) DEFAULT NULL,
    id_account bigint(20) UNSIGNED DEFAULT NULL,
    ack tinyint(1) DEFAULT '0',
    sent tinyint(1) DEFAULT '0'
  );
CREATE TABLE amazon_order_item (
    id_order varchar(100) DEFAULT NULL,
    product varchar(100) DEFAULT NULL,
    quantity int(11) DEFAULT NULL,
    price double DEFAULT NULL,
    amazon_item_id varchar(100) DEFAULT NULL
  );

CREATE TABLE amazon_marketplace_setting (
  id_store bigint(20) UNSIGNED NOT NULL,
  marketplace varchar(50) NOT NULL,
  setting_key varchar(100) NOT NULL,
  setting_value longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
			
			