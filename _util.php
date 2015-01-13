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

	function trackError($error) {
		return 'd14:failure reason' . strlen($error) . ':' . $error . 'e';
	}

	function trackPeers($list, $complete, $incomplete, $noPeerId) {
		$peerDir = '';
		foreach($list as $peer) {
			$peerId = '';
			if(!$noPeerId) {
				$peerIdHex = hex2bin($peer[2]);
				$peerId = '7:peer id' . strlen($peerIdHex) . ':' . $peerIdHex;
			}
			$peerDir .= 'd2:ip' . strlen($peer[0]) . ':' . $peer[0] . $peerId . '4:porti' . $peer[1] . 'ee';
		}
		return 'd8:intervali' . __INTERVAL . 'e12:min intervali' . __INTERVAL_MIN . 'e8:completei' . $complete . 'e10:incompletei' . $incomplete . 'e5:peersl' . $peerDir . 'ee';
	}

	function validateInt($key, $canBeEmpty = false) {
		if(!isset($_GET[$key])) {
			if($canBeEmpty) {
				return 0;
			} else {
				die(trackError('Invalid request, missing data'));
			}
		}

		$value = $_GET[$key];
		if(!ctype_digit($value)) {
			die(trackError('Invalid request, unknown data type'));
		}

		return (int)$value;
	}

	function validateConstrainedInt($key, $min, $max) {
		$value = validateInt($key);
		if($value < $min || $value > $max) {
			die(trackError('Invalid request, value out of bounds'));
		}

		return $value;
	}

	function validateString($key, $canBeEmpty = false) {
		if(!isset($_GET[$key])) {
			if($canBeEmpty) {
				return '';
			} else {
				die(trackError('Invalid request, missing data'));
			}
		}

		$value = $_GET[$key];
		if(!is_string($value)) {
			die(trackError('Invalid request, unknown data type'));
		}
		if(strlen($value) > 80) {
			die(trackError('Invalid request, parameter too long'));
		}

		return $value;
	}

	function validateFixedLengthString($key, $length = 20) {
		$value = validateString($key);
		if(strlen($value) != $length) {
			die(trackError('Invalid request, length on fixed argument not correct'));
		}

		return $value;
	}