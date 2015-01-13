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

	header("Content-Type: text/plain");
	dbConnect();

	$peerId = validateFixedLengthString('peer_id');
	$port = validateConstrainedInt('port', 1, 65535);
	$infoHash = validateFixedLengthString('info_hash');
	$key = validateString('key', true);
	$downloaded = validateInt('downloaded', true);
	$uploaded = validateInt('uploaded', true);
	$left = validateInt('left', true);
	$numWant = validateInt('numwant', true);
	$noPeerId = isset($_GET['no_peer_id']);

	$peerPk = dbUpdatePeer($peerId, $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'], $key, $port);
	$torrentPk = dbUpdateTorrent($infoHash);
	$peerTorrentPk = dbUpdatePeerTorrent($peerPk, $uploaded, $downloaded, $left, $infoHash);

	if(isset($_GET['event']) && $_GET['event'] === 'stopped') {
		dbStoppedPeer($peerTorrentPk);
		// The RFC says its OK to return an empty string when stopping a torrent however some clients will whine about it so we return an empty dictionary
		die(trackPeers(array(), 0, 0, $noPeerId));
	}

	if($numWant <= 0 || $numWant > __MAX_PPR) {
		$numWant = __MAX_PPR;
	}

	$reply = dbGetPeers($torrentPk, $peerPk, $numWant);
	list($seeders, $leechers) = dbGetCounts($torrentPk);
	die(trackPeers($reply, $seeders, $leechers, $noPeerId));
