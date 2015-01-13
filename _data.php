<?php
/*
 * Bitstorm 2 - A small and fast BitTorrent tracker
 * Copyright 2011 Peter Caprioli
 * Copyright 2015 Wilhelm Svenselius
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function connect() {
    //Connect to the MySQL server
    @mysql_connect(__DB_SERVER, __DB_USERNAME, __DB_PASSWORD) or die(track('Database connection failed'));

    //Select the database
    @mysql_select_db(__DB_DATABASE) or die(track('Unable to select database'));
}

function update_peer() {
    mysql_query('INSERT INTO `peer` (`hash`, `user_agent`, `ip_address`, `key`, `port`) '
        . "VALUES ('" . mysql_real_escape_string(bin2hex($_GET['peer_id'])) . "', '" . mysql_real_escape_string(substr($_SERVER['HTTP_USER_AGENT'], 0, 80))
        . "', INET_ATON('" . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . "'), '" . mysql_real_escape_string(sha1($_GET['key'])) . "', " . intval($_GET['port']) . ") "
        . 'ON DUPLICATE KEY UPDATE `user_agent` = VALUES(`user_agent`), `ip_address` = VALUES(`ip_address`), `port` = VALUES(`port`), `id` = LAST_INSERT_ID(`peer`.`id`)')
    or die(track('Cannot update peer: ' . mysql_error()));
    $pk_peer = mysql_insert_id();
    return $pk_peer;
}

function update_torrent() {
    mysql_query("INSERT INTO `torrent` (`hash`) VALUES ('" . mysql_real_escape_string(bin2hex($_GET['info_hash'])) . "') "
        . "ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(`id`)") or die(track('Cannot update torrent' . mysql_error())); // ON DUPLICATE KEY UPDATE is just to make mysql_insert_id work
    $pk_torrent = mysql_insert_id();
    return $pk_torrent;
}

function update_peer_torrent($pk_peer)
{
    mysql_query('INSERT INTO `peer_torrent` (`peer_id`, `torrent_id`, `uploaded`, `downloaded`, `left`, `last_updated`) '
        . 'SELECT ' . $pk_peer . ', `torrent`.`id`, ' . intval($_GET['uploaded']) . ', ' . intval($_GET['downloaded']) . ', ' . intval($_GET['left']) . ', UTC_TIMESTAMP() '
        . 'FROM `torrent` '
        . "WHERE `torrent`.`hash` = '" . mysql_real_escape_string(bin2hex($_GET['info_hash'])) . "' "
        . 'ON DUPLICATE KEY UPDATE `uploaded` = VALUES(`uploaded`), `downloaded` = VALUES(`downloaded`), `left` = VALUES(`left`), `last_updated` = VALUES(`last_updated`), '
        . '`id` = LAST_INSERT_ID(`peer_torrent`.`id`)')
    or die(track(mysql_error()));
    $pk_peer_torrent = mysql_insert_id();
    return $pk_peer_torrent;
}

function stopped_peer($pk_peer_torrent)
{
    mysql_query("UPDATE `peer_torrent` SET `stopped` = TRUE WHERE `id` = " . $pk_peer_torrent) or die (track(mysql_error()));
}

function get_peers($pk_torrent, $pk_peer, $numwant)
{
    $q = mysql_query('SELECT INET_NTOA(peer.ip_address), peer.port, peer.hash '
        . 'FROM peer_torrent '
        . 'JOIN peer ON peer.id = peer_torrent.peer_id '
        . 'WHERE peer_torrent.torrent_id = ' . $pk_torrent . ' AND peer_torrent.stopped = FALSE '
        . 'AND peer_torrent.last_updated >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ' . (__INTERVAL + __TIMEOUT) . ' SECOND) '
        . 'AND peer.id != ' . $pk_peer . ' '
        . 'ORDER BY RAND() '
        . 'LIMIT ' . $numwant) or die(track(mysql_error()));

    $reply = array(); //To be encoded and sent to the client

    while ($r = mysql_fetch_array($q)) { //Runs for every client with the same infohash
        $reply[] = array($r[0], $r[1], $r[2]); //ip, port, peerid
    }

    return $reply;
}

function get_counts($pk_torrent)
{
    $q = mysql_query('SELECT IFNULL(SUM(peer_torrent.left > 0), 0) AS leech, IFNULL(SUM(peer_torrent.left = 0), 0) AS seed '
        . 'FROM peer_torrent '
        . 'WHERE peer_torrent.torrent_id = ' . $pk_torrent . ' AND `peer_torrent`.`stopped` = FALSE '
        . 'AND peer_torrent.last_updated >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ' . (__INTERVAL + __TIMEOUT) . ' SECOND) '
        . 'GROUP BY `peer_torrent`.`torrent_id`') or die(track(mysql_error()));

    $seeders = 0;
    $leechers = 0;

    if ($r = mysql_fetch_array($q)) {
        $seeders = $r[1];
        $leechers = $r[0];
        return array($seeders, $leechers);
    }

    return array($seeders, $leechers);
}
