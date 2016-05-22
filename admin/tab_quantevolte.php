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
 * Modifica quante volte...
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20100109: cambio della struttura del menu
 *
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

if (isset($_GET['idpagina'])) $idpagina = $_GET['idpagina'];
$neworder= '';

function aggiornadati($valore, $key) {
	global $nuovi,$neworder,$db;
	list($campo,$id) = explode('-', $key);
	$valore = $db->escape_string($valore);
	if ($campo=="xxx") {
		$db->query("DELETE FROM quantevolte WHERE idquantevolte=$id");
	} else {
		if ($id == 0) {
			$nuovi[$campo] = $valore;
			if ('qvordine' == $campo) $neworder = $valore + 10;
		 } else {
			$db->query("UPDATE quantevolte SET $campo='$valore' WHERE idquantevolte=$id");
		}
	}
}

// Verifico se sono stato chiamato con i campi da aggiornare
if (isset($_POST['dati'])) {
	$idpagina = $_POST['idpagina'];
	$nuovi = array();
	array_walk($_POST, 'aggiornadati');
	if ($nuovi['testo'] != '' ) {
		$db->query("INSERT INTO quantevolte SET nascosto=$nuovi[nascosto],idpagina=$idpagina,qvordine='$nuovi[qvordine]',classifica='$nuovi[classifica]',testo='$nuovi[testo]',backlink='$nuovi[backlink]'");
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modificate quante volte');
}

$q = $db->query("SELECT * FROM quantevolte WHERE idpagina=$idpagina ORDER BY qvordine DESC");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_quantevolte.php' target='main'>";
echo "\n<input type='hidden' name='dati' value='modificati'>";
echo "\n<input type='hidden' name='idpagina' value=$idpagina>";
echo "\n<table border='0'>";
echo "\n<tr><th>ordine</th><th>testo</th><th>classifica, backlink</th><th>hidd</th><th>delete</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' size='5' maxlength='6' name='qvordine-0' value='$neworder'></td>";
echo "\n<td><textarea rows='5' cols='80' name='testo-0'></textarea></td>";
echo "\n<td><input type='text' size='30' maxlength='50' name='classifica-0'><br>";
echo "<input type='text' size='30' maxlength='20' name='backlink-0'></td>";
echo "\n<td><input type='text' size='1' maxlength='1' name='nascosto-0' value='0'></td>";
echo "\n<td align='center'>Nuovo</td>";
echo "\n</tr>";
echo "\n<tr><td align='center' colspan='5'><input type='submit' value='  Modifica  '></td></tr>";
while ($r = $q->fetch_array()) {
	$idquantevolte = $r['idquantevolte'];
	echo "\n<tr>";
	echo "\n<td><input type='text' size='5' maxlength='6' name='qvordine-$idquantevolte' value='$r[qvordine]'></td>";
	echo "\n<td><textarea rows='5' cols='80' name='testo-$idquantevolte'>$r[testo]</textarea></td>";
	echo "\n<td><input type='text' size='30' maxlength='50' name=\"classifica-$idquantevolte\" value=\"$r[classifica]\"><br>";
	echo "<input type='text' size='30' maxlength='20' name=\"backlink-$idquantevolte\" value=\"$r[backlink]\"></td>";
	echo "\n<td><input type='text' size='1' maxlength='1' name='nascosto-$idquantevolte' value='$r[nascosto]'></td>";
	echo "\n<td align='center'><input type='checkbox' name='xxx-$idquantevolte'></td>";
	echo "\n</tr>";
}
echo "</table></form>";

### END OF FILE ###
