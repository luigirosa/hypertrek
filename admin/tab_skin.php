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
 * Modifica skin
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20100109: cambio della struttura del menu
 *
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

include('global.php');

function aggiornadati($valore, $key) {
	global $db, $nuovi;
	list($campo,$id) = explode('-', $key);
	if ($campo=="xxx") {
		$db->query("DELETE FROM skin WHERE idskin=$id");
	} else {
		$valore = $db->escape_string($valore);
		if ('isdefault' == $campo) $valore = 1;
		if ('isvisibile' == $campo) $valore = 1;
		if ($id == 0) {
			if ('' != $valore) {
				$nuovi[] = "$campo='$valore'";
			}
		} else {
			$db->query("UPDATE skin SET $campo='$valore' WHERE idskin=$id");
		}
	}
}

// Verifico se sono stato chiamato con i campi da aggiornare
if (count($_POST) > 0 ) {
	$nuovi = array();
	$db->query("UPDATE skin SET isdefault='0',isvisibile='0'");
	array_walk($_POST, 'aggiornadati');
	if ($_POST['dir-0'] != '') {
		$qry = "INSERT INTO skin SET " . implode(',', $nuovi);
		//echo "<br>$qry<br>";
		$db->query($qry);	
	}
}

$q = $db->query("SELECT * FROM skin ORDER BY isdefault DESC, dir");

intestazione();

echo "\n<form method='post' action='tab_skin.php' target='main'>";

echo "<p align='center'><b>Skin</b> <input type='submit' value='Modifica'></p>";
echo "\n<table border='0' cellpadding='0'>";
echo "\n<tr><th align='center'>directory</th><th align='center'>nome</th><th align='center'>descrizione</th><th align='center'>autore</th><th align='center'>visibile</th><th align='center'>default</th><th align='center'>X</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td valign='top'><input type='text' name='dir-0' size='20' maxlength='20'></td>";
echo "\n<td valign='top'><input type='text' name='nome-0' size='30' maxlength='50'></td>";
echo "\n<td><textarea rows='3' cols='30' name='descrizione-0'></textarea></td>";
echo "\n<td valign='top'><input type='text' name='autore-0' size='30' maxlength='100'></td>";
echo "\n<td valign='top' align='center'><input type='checkbox' name='isvisibile-0'></td>";
echo "\n<td valign='top' align='center'><input type='checkbox' name='isdefault-0'></td>";
echo "\n<td align='center' valign='top'>New</td>";
echo "\n</tr>";
while ($r = $q->fetch_array()) {
	$id = $r['idskin'];
	echo "\n<tr>";
	echo "\n<td valign='top'><input type='text' name='dir-$id' value='$r[dir]' size='20' maxlength='20'></td>";
	echo "\n<td valign='top'><input type='text' name='nome-$id' value=\"$r[nome]\" size='30' maxlength='50'></td>";
	echo "\n<td><textarea rows='3' cols='30' name='descrizione-$id'>$r[descrizione]</textarea></td>";
	echo "\n<td valign='top'><input type='text' name='autore-$id' value=\"$r[autore]\" size='30' maxlength='100'></td>";
	echo "\n<td valign='top' align='center'><input type='checkbox' name='isvisibile-$id'";
	if (1 == $r['isvisibile']) echo ' checked';
	echo "></td>";
	echo "\n<td valign='top' align='center'><input type='checkbox' name='isdefault-$id'";
	if (1 == $r['isdefault']) echo ' checked';
	echo "></td>";
	echo "\n<td valign='top' align='center'><input type='checkbox' name='xxx-$id'></td>";
	echo "\n</tr>";
}

echo "</table><p><input type='submit' value='Modifica'></p></form>";

### END OF FILE ###