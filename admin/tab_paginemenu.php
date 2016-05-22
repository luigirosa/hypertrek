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
 * Modifica apparizioni nei menu
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20100131: creato
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

// precarico i menu
$amenu = array();
$qm = $db->query("SELECT idmenu,sigla FROM menu ORDER BY sigla");
while ($rm = $qm->fetch_array()) $amenu[$rm['idmenu']] = $rm['sigla'];

if (isset($_GET['idpagina'])) $idpagina = $_GET['idpagina'];

if (isset($_POST['idpagina'])) {
	$idpagina = $_POST['idpagina'];
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				loggamodifica($idpagina, "Pagina rimossa dal menu " . $amenu[$id]);
				$db->query("DELETE FROM paginemenu WHERE idpagina='$idpagina' and idmenu='$id'");
			}
		}
	}
	if ('0' != $_POST['idmenu']) {
		$a = array();
		$a[] = "idpagina='$idpagina'";
		$a[] = "idmenu='" . $db->escape_string(trim($_POST['idmenu'])) . "'";
		$db->query("INSERT INTO paginemenu SET " . implode(',', $a));
		loggamodifica($idpagina, "Pagina aggiunta al menu " . $amenu[$_POST['idmenu']]);
	}
	toccapagina($idpagina);
}

$q = $db->query("SELECT * FROM paginemenu WHERE idpagina='$idpagina'");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_paginemenu.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value='$idpagina'>";
echo "<p align='center'><input type='submit' value='Modifica'></p>";
echo "\n<center><table border='0'>";
echo "\n<tr><th>Menu</th><th>Del</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td><select name='idmenu'><option selected value='0'>Aggiungi al menu...</option>";
foreach ($amenu as $idmenu=>$menu) echo "<option value='$idmenu'>$menu</option>";
echo "</select></td>";
echo "\n<td align='center'>New</td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$id = $r['idmenu'];
	echo "\n<tr>";
echo "\n<td>" . $amenu[$id] . "</td>";
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table></center><p align='center'><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###
