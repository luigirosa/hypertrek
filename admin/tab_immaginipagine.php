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
 * Modifica immagini assegnate alle pagine
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require; aggiunta chiusura HTML
 * 20090404: passagio a mysqli a oggetti, ottimizzazione e aggiornamento sistema di update
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

if (isset($_GET['idpagina'])) $idpagina = $_GET['idpagina'];

// Verifico se sono stato chiamato con i campi da aggiornare
if (isset($_POST['idpagina'])) {
	$idpagina = $_POST['idpagina'];
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$db->query("DELETE FROM immaginipagine WHERE idimmaginepagina='$id'");
				loggamodifica($idpagina, 'rimossa immagine');
			} else {
				$a = array();
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				$a[] = "idimmagine='" . $db->escape_string(trim($x['idimmagine'])) . "'";
				$a[] = "tagrotante='" . $db->escape_string(trim($x['tagrotante'])) . "'";
				$db->query("UPDATE immaginipagine SET " . implode(',', $a) . " WHERE idimmaginepagina='$id'");
			}
		}
	}
	if ($_POST['idimmagine'] > 0) {
		$a = array();
		$a[] = "idpagina='$idpagina'";
		$ordine = $db->escape_string(trim($_POST['ordine']));
		if ($ordine < 1) $ordine = 10;
		$a[] = "ordine='$ordine'";
		$a[] = "idimmagine='" . $db->escape_string(trim($_POST['idimmagine'])) . "'";
		$a[] = "tagrotante='" . $db->escape_string(trim($_POST['tagrotante'])) . "'";
		$db->query("INSERT INTO immaginipagine SET " . implode(',', $a));
		loggamodifica($idpagina, 'aggiunta immagine');
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modificate immagini');
}

$q = $db->query("SELECT * FROM immaginipagine WHERE idpagina='$idpagina' ORDER BY ordine");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_immaginipagine.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value=$idpagina>";
echo "\n<table border='0'>";
echo "\n<tr><th>immagine</th><th>ordine</th><th>tag rotante</th><th>delete</th></tr>";

// nuovo record
echo "\n<tr>";
//immagine
echo "\n<td><select name='idimmagine'>";
$qq = $db->query("SELECT idimmagine,file,descrizione FROM immagini ORDER BY descrizione,file");
echo "\n<option selected value='0'>Nessuna</option>";
while ($rr = $qq->fetch_array()) {
	echo "\n<option value='$rr[idimmagine]'";
	echo ">$rr[descrizione] ($rr[file])</option>";
}
echo "\n</select></td>";
echo "\n<td><input type='text' size='5' maxlength='6' name='ordine'></td>";
echo "\n<td><input type='text' size='10' maxlength='30' name='tagrotante'></td>";
echo "\n<td align='center'>Nuova</td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$id = $r['idimmaginepagina'];
	echo "\n<tr>";
	//immagine
	echo "\n<td><select name='p[$id][idimmagine]'>";
	$qq = $db->query("SELECT idimmagine,file,descrizione FROM immagini ORDER BY descrizione,file");
	echo "\n<option value='0'";
	if (0 == $r['idimmagine']) echo " selected";
	echo ">Nessuna</option>";
	while ($rr = $qq->fetch_array()) {
		echo "\n<option value='$rr[idimmagine]'";
  		if ($rr['idimmagine'] == $r['idimmagine']) echo " selected";
		echo ">$rr[descrizione] ($rr[file])</option>";
	}
	echo "\n</select></td>";
	echo "\n<td><input type='text' size='5' maxlength='6' name='p[$id][ordine]' value='$r[ordine]'></td>";
	echo "\n<td><input type='text' size='10' maxlength='30' name='p[$id][tagrotante]' value='$r[tagrotante]'></td>";
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table><p><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###
