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
 * Modifica riferimenti ad altre pagine
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20091122: sistemata la gestione dei caratteri nei form
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
	foreach($_POST['p'] as $id=>$x) {
		if (isset($x['xxx'])) {
			$rr = $db->query("SELECT categoria,riferimento FROM riferimenti WHERE idriferimento='$id'")->fetch_array();
			loggamodifica($idpagina, "cancellato riferimento $rr[categoria]: $rr[riferimento]");
			$db->query("DELETE FROM riferimenti WHERE idriferimento='$id'");
		} else {
			$a = array();
			$a[] = "categoria='" . $db->escape_string(trim($x['categoria'])) . "'";
			$a[] = "riferimento='" . $db->escape_string(trim($x['riferimento'])) . "'";
			$a[] = "backlink='" . $db->escape_string(trim($x['backlink'])) . "'";
			$db->query("UPDATE riferimenti SET " . implode(',', $a) . " WHERE idriferimento='$id'");
		}
	}
	if ($_POST['categoria'] != '' and $_POST['riferimento'] != '') {
	$a = array();
	$a[] = "idpagina='$idpagina'";
	$a[] = "riferimento='" . $db->escape_string(trim($_POST['riferimento'])) . "'";
	$a[] = "categoria='" . $db->escape_string(trim($_POST['categoria'])) . "'";
	$a[] = "backlink='" . $db->escape_string(trim($_POST['backlink'])) . "'";
	$db->query("INSERT INTO riferimenti SET " . implode(',', $a));
	loggamodifica($idpagina, "aggiunto riferimento $_POST[categoria]: $_POST[riferimento]");
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modifica riferimenti');
}

$q = $db->query("SELECT * FROM riferimenti WHERE idpagina='$idpagina' ORDER BY categoria,riferimento");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_riferimenti.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value='$idpagina'>";

echo "<p align='center'><input type='submit' value='Aggiorna'></p>";

echo "\n<table border='0'>";
echo "\n<tr><th>categoria</th><th>riferimento</th><th>backlink</th><th>delete</th></tr>";

// nuovo record
echo "\n<tr>";
echo "\n<td><input type='text' size='20' maxlength='25' name='categoria'></td>";
echo "\n<td><input type='text' size='50' maxlength='200' name='riferimento'></td>";
echo "\n<td><input type='text' size='20' maxlength='30' name='backlink'></td>";
echo "\n<td align='center'>Nuova</td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$id = $r['idriferimento'];
	echo "\n<tr>";
	$x = normalizzaform($r['categoria']);
	echo "\n<td><input type='text' size='20' maxlength='25' name='p[$id][categoria]' value=\"$x\"></td>";
	$x = normalizzaform($r['riferimento']);
	echo "\n<td><input type='text' size='50' maxlength='200' name='p[$id][riferimento]' value=\"$x\"></td>";
	$x = normalizzaform($r['backlink']);
	echo "\n<td><input type='text' size='20' maxlength='30' name='p[$id][backlink]' value=\"$x\"></td>";
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table>\n";
echo "<p><input type='submit' value='Aggiorna'></p></form>";

//mostro eventuali backlink
$r = $db->query("SELECT tag FROM pagine WHERE idpagina=$idpagina")->fetch_array();
$tag = $r[0];
$q = $db->query("SELECT pagine.titolo, pagine.tag FROM riferimenti JOIN pagine ON riferimenti.idpagina=pagine.idpagina WHERE backlink='$tag'");
if ($q->num_rows > 0) {
	echo "\n<p><b>Backlink</b>:<ul>";
	while ($r = $q->fetch_array()) {
		echo "\n<li>" . '{' . $r[0] .' | ' . $r[1] . '}</li>';
	}
	echo "\n</ul></p>";
}

echo "\n</body></html>";

### END OF FILE ###
