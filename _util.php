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

//Bencoding function, returns a bencoded dictionary
//You may go ahead and enter custom keys in the dictionary in
//this function if you'd like.
function track($list, $c=0, $i=0) {
    if (is_string($list)) { //Did we get a string? Return an error to the client
        return 'd14:failure reason'.strlen($list).':'.$list.'e';
    }
    $p = ''; //Peer directory
    foreach($list as $d) { //Runs for each client
        $pid = '';
        if (!isset($_GET['no_peer_id'])) { //Send out peer_ids in the reply
            $real_id = hex2bin($d[2]);
            $pid = '7:peer id'.strlen($real_id).':'.$real_id;
        }
        $p .= 'd2:ip'.strlen($d[0]).':'.$d[0].$pid.'4:porti'.$d[1].'ee';
    }
    //Add some other paramters in the dictionary and merge with peer list
    $r = 'd8:intervali'.__INTERVAL.'e12:min intervali'.__INTERVAL_MIN.'e8:completei'.$c.'e10:incompletei'.$i.'e5:peersl'.$p.'ee';
    return $r;
}

//Do some input validation
function valdata($g, $fixed_size=false) {
    if (!isset($_GET[$g])) {
        die(track('Invalid request, missing data'));
    }
    if (!is_string($_GET[$g])) {
        die(track('Invalid request, unknown data type'));
    }
    if ($fixed_size && strlen($_GET[$g]) != 20) {
        die(track('Invalid request, length on fixed argument not correct'));
    }
    if (strlen($_GET[$g]) > 80) { //128 chars should really be enough
        die(track('Request too long'));
    }
}