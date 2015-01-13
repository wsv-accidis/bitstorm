CREATE TABLE `bit_peer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(40) NOT NULL,
  `user_agent` varchar(80) DEFAULT NULL,
  `ip_address` varchar(40) NOT NULL,
  `key_hash` char(40) NOT NULL,
  `port` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_key` (`hash`,`key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bit_peer_torrent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `peer_id` int(10) unsigned NOT NULL,
  `torrent_id` int(10) unsigned NOT NULL,
  `uploaded` bigint(20) unsigned DEFAULT NULL,
  `downloaded` bigint(20) unsigned DEFAULT NULL,
  `remain` bigint(20) unsigned DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  `stopped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_torrent` (`peer_id`,`torrent_id`),
  KEY `update_torrent` (`torrent_id`,`stopped`,`last_updated`,`remain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bit_torrent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
