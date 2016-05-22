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
 * Modifica immagini mobili
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require; conversione chiamate SQL a oggetti; aggiunta chiusura HTML
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

$questotag= '';

function aggiornadati($valore, $key) {
	global $nuovi, $db;
	list($campo,$id) = explode('-', $key);
	if ('xxx' == $campo) {
		$db->query("DELETE FROM immaginidata WHERE idimmaginedata='$id'");
	} else {
		$db->query("UPDATE immaginidata SET $campo='$valore' WHERE idimmaginedata='$id'");
	}
}

function creaquery($valore, $campo) {
	global $qry;
	if (($valore != '') and ($campo != '')) {
		$qry .= "$campo=\"$valore\",";
	}
}

// Verifico se sono stato chiamato con i campi da aggiornare
if (isset($_POST['dati']) and $_POST['dati'] == 'modificati') {
	array_walk($_POST, 'aggiornadati');
}	

if (isset($_POST['dati']) and $_POST['dati'] == 'aggiunti') {
	if ('' != $_POST['tag']) {
		$qry = "INSERT INTO immaginidata SET tag='$_POST[tag]',idimmagine='$_POST[idimmagine]',mesegiorno='$_POST[mesegiorno]'";
		//echo "<br>$qry<br>";
		$db->query($qry);
		$questotag = $_POST['tag'];
	}
}

$q = $db->query("SELECT * FROM immaginidata ORDER BY tag,mesegiorno,idimmagine");

intestazione();

echo "\n<table border='0'>";
echo "\n<tr><th>id</th><th>tag</th><th>idimmagine</th><th>mesegiorno</th><th='center'>Del</th></tr>";

// nuovo record
echo "\n<form method='post' action='tab_immaginimobili.php' target='main'>";
echo "\n<input type='hidden' name='dati' value='aggiunti'>";

echo "\n<tr>";
echo "\n<td>&nbsp;</td>";
echo "\n<td><input type='text' name='tag' value='$questotag' size='10' maxlength='30'></td>";
echo "\n<td><input type='text' name='idimmagine' size='10' maxlength='10'></td>";
echo "\n<td><input type='text' name='mesegiorno' size='5' maxlength='5' value='rand'></td>";
echo "\n<td><input type='submit' value='Aggiungi'></td>";
echo "\n</tr>";

echo "\n</form><form method='post' action='tab_immaginimobili.php' target='main'>";
echo "\n<input type='hidden' name='dati' value='modificati'>";

while ($r = $q->fetch_array()) {
	$idt = $r['idimmaginedata'];
	echo "\n<tr>";
	echo "\n<td>$idt</td>";
	echo "\n<td><input type='text' name='tag-$idt' value='$r[tag]' size='10' maxlength='30'></td>";
	echo "\n<td><input type='text' name='idimmagine-$idt' value='$r[idimmagine]' size='10' maxlength='10'></td>";
	echo "\n<td><input type='text' name='mesegiorno-$idt' value='$r[mesegiorno]' size='5' maxlength='5'></td>";
	echo "\n<td align='center'><input type='checkbox' name='xxx-$idt'></td>";
	echo "\n</tr>";
}

echo "</table><p align='center'><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###
