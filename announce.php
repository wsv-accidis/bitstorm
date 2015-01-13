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

require('_config.php');
require('_util.php');
require('_data.php');

//Use the correct content-type
header("Content-type: Text/Plain");

connect();

//Inputs that are needed, do not continue without these
valdata('peer_id', true);
valdata('port');
valdata('info_hash', true);

//Make sure we have something to use as a key
if (!isset($_GET['key'])) {
	$_GET['key'] = '';
}

$downloaded = isset($_GET['uploaded']) ? intval($_GET['uploaded']) : 0;
$uploaded = isset($_GET['uploaded']) ? intval($_GET['uploaded']) : 0;
$left = isset($_GET['left']) ? intval($_GET['left']) : 0;

//Validate key as well
valdata('key');

//Do we have a valid client port?
if (!ctype_digit($_GET['port']) || $_GET['port'] < 1 || $_GET['port'] > 65535) {
	die(track('Invalid client port'));
}

//Hack to get comatibility with trackon
if ($_GET['port'] == 999 && substr($_GET['peer_id'], 0, 10) == '-TO0001-XX') {
	die("d8:completei0e10:incompletei0e8:intervali600e12:min intervali60e5:peersld2:ip12:72.14.194.184:port3:999ed2:ip11:72.14.194.14:port3:999ed2:ip12:72.14.194.654:port3:999eee");
}

$pk_peer = update_peer();
$pk_torrent = update_torrent();

//User agent is required
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
	$_SERVER['HTTP_USER_AGENT'] = "N/A";
}
if (!isset($_GET['uploaded'])) {
	$_GET['uploaded'] = 0;
}
if (!isset($_GET['downloaded'])) {
	$_GET['downloaded'] = 0;
}
if (!isset($_GET['left'])) {
	$_GET['left'] = 0;
}

$pk_peer_torrent = update_peer_torrent($pk_peer);

//Did the client stop the torrent?
if (isset($_GET['event']) && $_GET['event'] === 'stopped') {
    stopped_peer($pk_peer_torrent);
    die(track(array(), 0, 0)); //The RFC says its OK to return an empty string when stopping a torrent however some clients will whine about it so we return an empty dictionary
}

$numwant = __MAX_PPR; //Can be modified by client

//Set number of peers to return
if (isset($_GET['numwant']) && ctype_digit($_GET['numwant']) && $_GET['numwant'] <= __MAX_PPR && $_GET['numwant'] >= 0) {
	$numwant = (int)$_GET['numwant'];
}

$reply = get_peers($pk_torrent, $pk_peer, $numwant);
list($seeders, $leechers) = get_counts($pk_torrent);

die(track($reply, $seeders[0], $leechers[0]));
