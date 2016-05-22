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
 * Modifica pie' di pagina
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

function aggiornadati($valore, $key) {
	global $nuovi,$db;
	list($campo,$id) = explode('-', $key);
	$valore = $db->escape_string($valore);
	if ($campo=="xxx") {
		$db->query("DELETE FROM piepagina WHERE idpiepagina=$id");
	} else {
		if ($id == 0) {
			$nuovi[$campo] = $valore;
		 } else {
			$db->query("UPDATE piepagina SET $campo='$valore' WHERE idpiepagina=$id");
		}
	}
}

// Verifico se sono stato chiamato con i campi da aggiornare
if (isset($_POST['dati'])) {
	$idpagina = $_POST['idpagina'];
	$nuovi = array();
	array_walk($_POST, 'aggiornadati');
	if ($nuovi['testo'] != '') {
		$db->query("INSERT INTO piepagina SET idpagina=$idpagina,ppordine='$nuovi[ppordine]',testo='$nuovi[testo]'");
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modifica piedipagina');
}

$q = $db->query("SELECT * FROM piepagina WHERE idpagina=$idpagina ORDER BY ppordine");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_piepagina.php' target='main'>";
echo "\n<input type='hidden' name='dati' value='modificati'>";
echo "\n<input type='hidden' name='idpagina' value=$idpagina>";
echo "\n<table border='0'>";
echo "\n<tr><th>ordine</th><th>testo</th><th>delete</th></tr>";
while ($r = $q->fetch_array()) {
	$idpiepagina = $r['idpiepagina'];
	echo "\n<tr>";
	echo "\n<td><input type='text' size='5' maxlength='6' name='ppordine-$idpiepagina' value='$r[ppordine]'></td>";
	echo "\n<td><textarea rows='10' cols='50' name='testo-$idpiepagina'>$r[testo]</textarea></td>";
	echo "\n<td align='center'><input type='checkbox' name='xxx-$idpiepagina'></td>";
	echo "\n</tr>";
}
// nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' size='5' maxlength='6' name='ppordine-0'></td>";
echo "\n<td><textarea rows='5' cols='50' name='testo-0'></textarea></td>";
echo "\n<td align='center'>Nuovo</td>";
echo "\n</tr>";

echo "</table><p><input type='submit' value='Modifica'></p></form>";

### END OF FILE ###
