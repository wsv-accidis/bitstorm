Bitstorm
========
This is a fork of the **Bitstorm** BitTorrent tracker originally by [Peter Caprioli](https://stormhub.org/tracker/ui.php)
and with MySQL support originally implemented by [Josh Duff](https://code.google.com/p/bitstorm/).

This version consists adds support for whitelisting of torrents, to prevent abuse of the tracker. I also rewrote
parts of the code to better suit my preferences, at the cost of some simplicity (it's no longer just one file).

## License
Bitstorm is distributed under the terms of the **GNU General Public License version 3 or later**.

## Requirements
Bitstorm requires very little in terms of hardware. As for software, it requires PHP with MySQL support, and
MySQL. Please make sure you use the latest version of each so as to avoid any security issues.

## Installation
1. Set up your MySQL server with an account for Bitstorm to use. It needs SELECT, INSERT and UPDATE rights.

2. Using an administrative account, run the bitstorm.sql script on your MySQL server. Comment out the
   line that starts with CREATE DATABASE if you would like to use an existing database.

3. Make a copy of _config.sample.php and name it _config.php (it must be named exactly this). Update this copy
   with the appropriate connection settings for your database, and any other settings you want.

4. Upload all .php files to a folder on your web server. (You can skip _config.sample.php, if you want.)

5. Try loading announce.php in a web browser and watch for any PHP errors. If you see a string that looks like
   **d14:failure reason29:Invalid request, missing datae** then everything is working fine.
