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
 * Modifica ridirezioni
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20100109: cambio della struttura del menu intestazione(); 
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

// Verifico se sono stato chiamato con i campi da aggiornare
if (count($_POST) > 0) {
	loggamodifica(0, 'modifica ridirezioni');
	foreach($_POST['p'] as $id=>$x) {
		if (isset($x['xxx'])) {
			$rr = $db->query("SELECT * FROM ridirezioni WHERE idridirezione='$id'")->fetch_array();
			loggamodifica(0, "cancellata ridirezione $rr[da] -> $rr[a]");
			$db->query("DELETE FROM ridirezioni WHERE idridirezione='$id'");
		} else {
			$a = array();
			$a[] = "da='" . $db->escape_string(trim($x['da'])) . "'";
			$a[] = "a='" . $db->escape_string(trim($x['a'])) . "'";
			$db->query("UPDATE ridirezioni SET " . implode(',', $a) . " WHERE idridirezione='$id'");
		}
	}
	if ($_POST['da'] != '' and $_POST['a'] != '') {
		$a = array();
		$a[] = "da='" . $db->escape_string(trim($_POST['da'])) . "'";
		$a[] = "a='" . $db->escape_string(trim($_POST['a'])) . "'";
		$db->query("INSERT INTO ridirezioni SET " . implode(',', $a));
		loggamodifica(0, "aggiunta ridirezione $_POST[da] -> $_POST[a]");
	}
}

$q = $db->query("SELECT * FROM ridirezioni ORDER BY da");

intestazione();

echo "\n<form method='post' action='ridirezioni.php' target='main'>";
echo "<p align='center'><input type='submit' value='Aggiorna'></p>";
echo "\n<table border='0'>";
echo "\n<tr><th align='center'>Dal tag</th><th align='center'>Al tag</th><th align='center'>Check</th><th align='center'>Del</th></tr>";

// nuovo record 
echo "\n<tr>";
// da
echo "<td><input type='text' name='da' size='30'></td>";
// a
echo "<td><input type='text' name='a' size='30'></td>";
// check
echo "\n<td align='center' colspan='2'>New</td>";
echo "\n</tr>";


while ($r = $q->fetch_array()) {
	$id = $r['idridirezione'];
	echo "\n<tr>";
	// da
	echo "<td><input type='text' name='p[$id][da]' size='30' value='$r[da]'></td>";
	// a
	echo "<td><input type='text' name='p[$id][a]' size='30' value='$r[a]'></td>";
	// check
	$res = "<font color='#c00000'>FAIL</font>";
	$qq = $db->query("SELECT tag FROM pagine WHERE tag='$r[a]'");
	if ($qq->num_rows > 0) $res = "<font color='#00c000'>OK</font>";
	echo "<td align='center'>$res</td>";
	// del
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table>";
echo "<p align='center'><input type='submit' value='Aggiorna'></p>";
echo "</form></body></html>";

### END OF FILE ###