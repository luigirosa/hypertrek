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
 * Modifica tabelle
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20100109: cambio della struttura del menu
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

$ttag = $_GET['ttag'];
$neworder= '';

function aggiornadati($valore, $key) {
	global $nuovi, $neworder, $db, $ttag;
	list($campo,$id) = explode('-', $key);
	if ($campo=="xxx") {
		loggamodifica(0, "cancellata riga da tabella $ttag");
		$db->query("DELETE FROM tabelle WHERE idtabella=$id");
	} else {
		$valore = $db->escape_string($valore);
		if ('istitolo' == $campo) $valore = 1;
		if ($id == 0) {
			if ('' != $valore) {
				$nuovi[] = "$campo='$valore'";
			}
			if ('tordine' == $campo) $neworder = $valore + 10;
		} else {
			$db->query("UPDATE tabelle SET $campo='$valore' WHERE idtabella=$id");
		}
	}
}

// Verifico se sono stato chiamato con i campi da aggiornare
if (isset($_POST['dati'])) {
	$ttag = $_POST['ttag'];
	$nuovi = array();
	$db->query("UPDATE tabelle SET istitolo='0' WHERE ttag='$ttag'");
	loggamodifica(0, "modifica tabella $ttag");
	array_walk($_POST, 'aggiornadati');
	if (count($nuovi) > 0) {
		$qry = "INSERT INTO tabelle SET ttag='$ttag'," . implode(',', $nuovi);
		$db->query($qry);	
	}
}

$q = $db->query("SELECT * FROM tabelle WHERE ttag='$ttag' ORDER BY tordine");

intestazione();

echo "\n<form method='post' action='tab_tabelle.php' target='main'>";
echo "\n<input type='hidden' name='dati' value='modificati'>";
if ('0' == $ttag) {
	echo "\n<p><input type='text' name='ttag' size='22' maxlength='20'></p>";
} else {
	echo "\n<input type='hidden' name='ttag' value='$ttag'>";
}
echo "<p align='center'><b>$ttag</b> <input type='submit' value='Modifica'></p>";
echo "\n<table border='0' cellpadding='0'>";
echo "\n<tr><th align='center'>ordine</th><th align='center'>prima</th><th align='center'>seconda</th><th align='center'>terza</th><th align='center'>quarta</th><th align='center'>Col.Centra<br>Titolo</th><th align='center'>X</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' name='tordine-0' size=3 maxlength=3 value='$neworder'></td>";
echo "\n<td><textarea rows='3' cols='30' name='prima-0'></textarea></td>";
echo "\n<td><textarea rows='3' cols='30' name='seconda-0'></textarea></td>";
echo "\n<td><textarea rows='3' cols='30' name='terza-0'></textarea></td>";
echo "\n<td><textarea rows='3' cols='30' name='quarta-0'></textarea></td>";
echo "\n<td align='center'><input type='text' name='qualicenter-0' size='4' maxlength='4'><br>";
echo "\n<input type='checkbox' name='istitolo-0'></td>";
echo "\n<td>New</td>";
echo "\n</tr>";
while ($r = $q->fetch_array()) {
	$idt = $r['idtabella'];
	echo "\n<tr>";
	echo "\n<td><input type='text' name='tordine-$idt' value='$r[tordine]' size=3 maxlength=3></td>";
	echo "\n<td><textarea rows='3' cols='30' name='prima-$idt'>$r[prima]</textarea></td>";
	echo "\n<td><textarea rows='3' cols='30' name='seconda-$idt'>$r[seconda]</textarea></td>";
	echo "\n<td><textarea rows='3' cols='30' name='terza-$idt'>$r[terza]</textarea></td>";
	echo "\n<td><textarea rows='3' cols='30' name='quarta-$idt'>$r[quarta]</textarea></td>";
	echo "\n<td align='center'><input type='text' name='qualicenter-$idt' value='$r[qualicenter]' size='4' maxlength='4'><br>";
	echo "<input type='checkbox' name='istitolo-$idt'";
	if (1 == $r['istitolo']) echo ' checked';
	echo "></td>";
	echo "\n<td align='center'><input type='checkbox' name='xxx-$idt'></td>";
	echo "\n</tr>";
}

echo "</table><p><input type='submit' value='Modifica'></p></form>";

### END OF FILE ###