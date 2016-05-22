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
 * Modifica campi degli episodi
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: aggiunta chiusura html; require_once -> require; cambiato il parse del POST
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

// Verifico se sono stato chiamato con i campi da aggiornare
if (count($_POST) > 0) {
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$db->query("DELETE FROM episodicampi WHERE idcampo='$id'");
			} else {
				$a = array();
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				$a[] = "etichetta='" . $db->escape_string(trim($x['etichetta'])) . "'";
				$a[] = "descrizione='" . $db->escape_string(trim($x['descrizione'])) . "'";
				$a[] = "categoria='" . $db->escape_string(trim($x['categoria'])) . "'";
				$a[] = "icona='" . $db->escape_string(trim($x['icona'])) . "'";
				$db->query("UPDATE episodicampi SET " . implode(',', $a) . " WHERE idcampo='$id'");
			}
		}
	}
	if ('' != trim($_POST['etichetta'])) {
		$a = array();
		$a[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
		$a[] = "etichetta='" . $db->escape_string(trim($_POST['etichetta'])) . "'";
		$a[] = "descrizione='" . $db->escape_string(trim($_POST['descrizione'])) . "'";
		$a[] = "categoria='" . $db->escape_string(trim($_POST['categoria'])) . "'";
		$a[] = "icona='" . $db->escape_string(trim($_POST['icona'])) . "'";
		$db->query("INSERT INTO episodicampi SET " . implode(',', $a));
	}
}

$q = $db->query("SELECT * FROM episodicampi ORDER BY ordine");

intestazione();

echo "\n<form method='post' action='tab_episodicampi.php' target='main'>";
echo "<p align='center'><input type='submit' value='Modifica'></p>";
echo "\n<table border='0'>";
echo "\n<tr><th align='center'>ordine</th><th align='center'>etichetta</th><th align='center'>descrizione</th><th align='center'>categoria<th align='center'>icona skin</th><th align='center'>del</th></tr>";
//nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' size='5' maxlength='6' name='ordine'></td>";
echo "\n<td><input type='text' size='15' maxlength='15' name='etichetta'></td>";
echo "\n<td><input type='text' size='40' maxlength='250' name='descrizione'></td>";
echo "\n<td><input type='text' size='20' maxlength='20' name='categoria'></td>";
echo "\n<td><input type='text' size='30' maxlength='50' name='icona'></td>";
echo "\n<td align='center'>New</td>";
echo "\n</tr>";
while ($r = $q->fetch_array()) {
	$id = $r['idcampo'];
	echo "\n<tr>";
	echo "\n<td><input type='text' size='5' maxlength='6' name='p[$id][ordine]' value=\"$r[ordine]\"></td>";
	echo "\n<td><input type='text' size='15' maxlength='15' name='p[$id][etichetta]' value=\"$r[etichetta]\"></td>";
	echo "\n<td><input type='text' size='40' maxlength='250' name='p[$id][descrizione]' value=\"$r[descrizione]\"></td>";
	echo "\n<td><input type='text' size='20' maxlength='20' name='p[$id][categoria]' value=\"$r[categoria]\"></td>";
	echo "\n<td><input type='text' size='30' maxlength='50' name='p[$id][icona]' value=\"$r[icona]\"></td>";
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table><p align='center'><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###
