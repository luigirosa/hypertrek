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
 * Modifica apparizioni guest
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: aggiunta chiusura HTML
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

if (isset($_GET['idpagina']))$idpagina = $_GET['idpagina'];
$neworder= 10;

// Verifico se sono stato chiamato con i campi da aggiornare
if (isset($_POST['idpagina'])) {
	$idpagina = $_POST['idpagina'];
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$db->query("DELETE FROM guest WHERE idguest='$id'");
			} else {
				$a = array();
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				$a[] = "idpaginapersonaggio='" . $db->escape_string(trim($x['idpaginapersonaggio'])) . "'";
				$a[] = "personaggio='" . $db->escape_string(trim($x['personaggio'])) . "'";
				$a[] = "idpaginacast='" . $db->escape_string(trim($x['idpaginacast'])) . "'";
				$db->query("UPDATE guest SET " . implode(',', $a) . " WHERE idguest='$id'");
			}
		}
	}
	if (($_POST['idpaginapersonaggio'] != '0') or ($_POST['personaggio'] != '')) {
		$a = array();
		$a[] = "idpagina='$idpagina'";
		$a[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
		$a[] = "idpaginapersonaggio='" . $db->escape_string(trim($_POST['idpaginapersonaggio'])) . "'";
		$a[] = "personaggio='" . $db->escape_string(trim($_POST['personaggio'])) . "'";
		$a[] = "idpaginacast='" . $db->escape_string(trim($_POST['idpaginacast'])) . "'";
		$db->query("INSERT INTO guest SET " . implode(',', $a));
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modificati guest');
}

$q = $db->query("SELECT * FROM guest WHERE idpagina='$idpagina' ORDER BY ordine");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_guest.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value='$idpagina'>";

echo "<p align='center'><input type='submit' value='Aggiorna'></p>";

echo "\n<table border='0'>";
echo "\n<tr><th>Ordine</th><th>Personaggio</th><th>Attore</th><th>Del</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' size='5' maxlength='5' name='ordine' value='$neworder'></td>";
//personaggio
echo "\n<td><select name='idpaginapersonaggio'>";
$qq = $db->query("SELECT idpagina,chiavesort FROM pagine WHERE ((idsezione>=93 and idsezione<=118) or idsezione=356 or idsezione=215 or idsezione=350 or idsezione=355) ORDER BY chiavesort");
echo "\n<option value='0'>Non in tabella</option>";
while ($rr = $qq->fetch_array()) {
	echo "<option value='$rr[idpagina]'>$rr[chiavesort]</option>";
}
echo "\n</select><br>";
echo "<input type='text' name='personaggio' size='40' maxlength='200'></td>";
//cast
echo "\n<td><select name='idpaginacast'>";
$qq = $db->query("SELECT idpagina,chiavesort FROM pagine WHERE (idsezione>=176 and idsezione<=201) ORDER BY chiavesort");
echo "\n<option value='0'>Selezionare</option>";
while ($rr = $qq->fetch_array()) {
	echo "<option value='$rr[idpagina]'>$rr[chiavesort]</option>";
}
echo "\n</select></td>";
echo "\n<td align='center'>Nuovo</td>";
echo "\n</tr>";

$neworderz = 0;
while ($r = $q->fetch_array()) {
	$id = $r['idguest'];
	echo "\n<tr>";
	echo "\n<td><input type='text' size='5' maxlength='5' name='p[$id][ordine]' value='$r[ordine]'></td>";
	$neworderz = $r['ordine'];
	//personaggio
	echo "\n<td><input type='text' size='7' maxlength='7' name='p[$id][idpaginapersonaggio]' value='$r[idpaginapersonaggio]'> ";
	$qq =$db->query("SELECT titolo FROM pagine WHERE idpagina=$r[idpaginapersonaggio]")->fetch_array();
	echo "$qq[0]<br>";
	echo "<input type='text' name='p[$id][personaggio]' size='40' maxlength='200' value=\"$r[personaggio]\"></td>";
	//cast
	echo "\n<td><input type='text' size='7' maxlength='7' name='p[$id][idpaginacast]' value='$r[idpaginacast]'> ";
	$qq = $db->query("SELECT titolo FROM pagine WHERE idpagina='$r[idpaginacast]'")->fetch_array();
	echo "$qq[0]<br>";
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}
if (0 == $neworder) $neworder = $neworderz + 5;

echo "</table><p><input type='submit' value='Aggiorna'></p></form>";

echo "</body></html>";

### END OF FILE ###
