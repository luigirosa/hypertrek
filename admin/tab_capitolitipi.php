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
 * Modifica tipi dei capitoli
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
		loggamodifica(0, 'modifica capitoli');
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$rr = $db->query("SELECT intestazione FROM capitolitipi WHERE idcapitolotipo='$id'")->fetch_array();
				loggamodifica(0, "rimosso capitolo $rr[intestazione]");
				$db->query("DELETE FROM capitolitipi WHERE idcapitolotipo='$id'");
			} else {
				$a = array();
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				$a[] = "intestazione='" . $db->escape_string(trim($x['intestazione'])) . "'";
				$a[] = "descrizione='" . $db->escape_string(trim($x['descrizione'])) . "'";
				$a[] = "tag='" . $db->escape_string(trim($x['tag'])) . "'";
				$a[] = "isbullet='" . $db->escape_string(trim($x['isbullet'])) . "'";
				$db->query("UPDATE capitolitipi SET " . implode(',', $a) . " WHERE idcapitolotipo='$id'");
			}
		}
	}
	if ('' != trim($_POST['tag'])) {
		$a = array();
		$a[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
		$a[] = "intestazione='" . $db->escape_string(trim($_POST['intestazione'])) . "'";
		$a[] = "descrizione='" . $db->escape_string(trim($_POST['descrizione'])) . "'";
		$a[] = "tag='" . $db->escape_string(trim($_POST['tag'])) . "'";
		$a[] = "isbullet='" . $db->escape_string(trim($_POST['isbullet'])) . "'";
		$db->query("INSERT INTO capitolitipi SET " . implode(',', $a));
		loggamodifica(0, "aggiunto capitolo $_POST[intestazione]");
	}
}

$q = $db->query("SELECT * FROM capitolitipi ORDER BY ordine");

intestazione();

echo "\n<form method='post' action='tab_capitolitipi.php' target='main'>";
echo "<p><input type='submit' value='Modifica'></p>";
echo "\n<table border='0'>";
echo "\n<tr><th align='center'>ordine</th><th align='center'>intestazione</th><th align='center'>descrizione</th><th align='center'>tag<th align='center'>bullet?</th><th align='center'>qt&agrave;</th><th align='center'>del</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' size='5' maxlength='6' name='ordine'></td>";
echo "\n<td><input type='text' size='20' maxlength='50' name='intestazione'></td>";
echo "\n<td><input type='text' size='20' maxlength='50' name='descrizione'></td>";
echo "\n<td><input type='text' size='20' maxlength='20' name='tag'></td>";
echo "\n<td><select name='isbullet'>";
echo "\n<option selected value='0'>No</option>";
echo "\n<option value='1'>S&igrave;</option>";
echo "</select></td>";
echo "\n<td align='center' colspan='2'>Nuovo</td>";
echo "\n</tr>";
while ($r = $q->fetch_array()) {
	$id = $r['idcapitolotipo'];
	echo "\n<tr>";
	echo "\n<td><input type='text' size='5' maxlength='6' name='p[$id][ordine]' value=\"$r[ordine]\"></td>";
	echo "\n<td><input type='text' size='20' maxlength='50' name='p[$id][intestazione]' value=\"$r[intestazione]\"></td>";
	echo "\n<td><input type='text' size='20' maxlength='50' name='p[$id][descrizione]' value=\"$r[descrizione]\"></td>";
	echo "\n<td><input type='text' size='20' maxlength='20' name='p[$id][tag]' value=\"$r[tag]\"></td>";
	//isbullet
	echo "\n<td><select name='p[$id][isbullet]'>";
	if ($r['isbullet'] == 0) {
		echo "\n<option selected value='0'>No</option>";
		echo "\n<option value='1'>S&igrave;</option>";
	} else {
		echo "\n<option value='0'>No</option>";
		echo "\n<option selected value='1'>S&igrave;</option>";
	}
	echo "</select></td>";
	$rr = $db->query("SELECT COUNT(*) FROM capitoli WHERE idcapitolotipo='$r[idcapitolotipo]'")->fetch_array();
	echo "\n<td align='right'>$rr[0]</td>";
	echo "\n<td align='center'>";
	if ($rr[0] < 1) {
		echo "<input type='checkbox' name='p[$id][xxx]'>";
	} else {
		echo '&nbsp;';
	}
	echo "</td>";
	echo "\n</tr>";
}

echo "</table><p><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###
