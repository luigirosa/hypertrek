<?php

# HyperTrek 3.0.0
# https://hypertrek.info/
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.

/**
 * Forza aggiornamento LAST
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');
intestazione();

$db2->query("UPDATE store SET valore='***' WHERE tag='tsultimi'");

echo "\n<p>Flag restettato</p>";

echo "</body></html>";

### END OF FILE ###
