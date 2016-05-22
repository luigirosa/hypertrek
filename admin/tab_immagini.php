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
 * Modifica immagini
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require; conversione chiamate SQL a oggetti; aggiunta chiusura HTML
 * 20091216: migliorato il codice di aggiornamento database
 * 20091220: in caso di cancellazione, cancella anche eventuali record da immaginipagine
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

if (isset($_GET['idimmagine'])) $idimmagine = $_GET['idimmagine'];

if (isset($_POST['idimmagine'])){
	$idimmagine = $_POST['idimmagine'];
	//cancello?
	if (isset($_POST['xxx'])) {
		$db->query("DELETE FROM immaginipagine WHERE idimmagine='$idimmagine'");
		$db->query("DELETE FROM immagini WHERE idimmagine='$idimmagine'");
	} else {
		$a = array();
		$a[] = "file='" . $db->escape_string($_POST['file']) . "'";
		$a[] = "imgtag='" . $db->escape_string($_POST['imgtag']) . "'";
		$a[] = "descrizione='" . $db->escape_string($_POST['descrizione']) . "'";
		$a[] = "altezza='" . $db->escape_string($_POST['altezza']) . "'";
		$a[] = "larghezza='" . $db->escape_string($_POST['larghezza']) . "'";
		if (0 == $idimmagine) {
			$db->query("INSERT INTO immagini SET " . implode(',', $a));
		} else {
			$db->query("UPDATE immagini SET " . implode(',', $a) . " WHERE idimmagine='$idimmagine'");
		}		
	}
}

if ($idimmagine > 0) $r = $db->query("SELECT * FROM immagini WHERE idimmagine='$idimmagine'")->fetch_array();

intestazione();

echo "\n<form method='post' action='tab_immagini.php' target='main'>";
echo "<input type='hidden' name='idimmagine' value='$idimmagine'>";
echo "\n<table>";
//idimmagine
echo "\n<tr><td>idimmagine</td>";
echo "<td>$idimmagine</td></tr>";
//file
echo "\n<tr><td>file</td>";
echo "<td><input type='text' size='50' maxlength='50' name='file' value='$r[file]'></td></tr>";
//descrizione
echo "\n<tr><td>descrizione</td>";
echo "<td><input type='text' size='50' maxlength='150' name='descrizione' value=\"$r[descrizione]\"></td></tr>";
//larghezza
echo "\n<tr><td>larghezza</td>";
echo "<td><input type='text' size='4' maxlength='5' name='larghezza' value='$r[larghezza]'></td></tr>";
//altezza
echo "\n<tr><td>altezza</td>";
echo "<td><input type='text' size='4' maxlength='5' name='altezza' value='$r[altezza]'></td></tr>";
//imgtag
echo "\n<tr><td>imgtag</td>";
echo "<td><input type='text' size='30' maxlength='30' name='imgtag' value=\"$r[imgtag]\"></td></tr>";
//delete
echo "\n<tr><td>Cancella </td>";
echo "<td><input type='checkbox' name='xxx'></td></tr>";

echo "</table><p><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###
