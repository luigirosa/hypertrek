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
 * Modifica menu
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
$amenu[0] = 'Nessuno';
$qm = $db->query("SELECT idmenu,sigla FROM menu ORDER BY sigla");
while ($rm = $qm->fetch_array()) $amenu[$rm['idmenu']] = $rm['sigla'];

if (isset($_GET['idmenu'])) $idmenu = $_GET['idmenu'];

if (isset($_POST['idmenu'])){
	$idmenu = $_POST['idmenu'];
	//cancello?
	if (isset($_POST['xxx'])) {
		$db->query("DELETE FROM menu WHERE idmenu='$idmenu'");
	} else {
		$aqry = array();
		$aqry[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
		$aqry[] = "parentid='" . $db->escape_string(trim($_POST['parentid'])) . "'";
		$aqry[] = "nome='" . $db->escape_string(trim($_POST['nome'])) . "'";
		$aqry[] = "sigla='" . $db->escape_string(trim($_POST['sigla'])) . "'";
		$aqry[] = "riferimento='" . $db->escape_string(trim($_POST['riferimento'])) . "'";
		$aqry[] = "tag='" . $db->escape_string(trim($_POST['tag'])) . "'";
		$aqry[] = "macro='" . $db->escape_string(trim($_POST['macro'])) . "'";
		if ('0' == $idmenu) {
			$qq = $db->query("INSERT INTO menu SET " . implode(',', $aqry));
			$idmenu = $db->insert_id;
		} else {
			$db->query("UPDATE menu SET " . implode(',', $aqry) . " WHERE idmenu=$idmenu");
		}
	}
}

if ($idmenu > 0) {
	$r = $db->query("SELECT * FROM menu WHERE idmenu='$idmenu'")->fetch_array();
} else {
	$idmenu = '0';
	$r = array('parentid'=> 0, 'nome'=>'', 'sigla'=>'', 'riferimento'=>'', 'tag'=>'' );
}

intestazione();

echo "\n<form method='post' action='tab_menu.php' target='main'>";
echo "<input type='hidden' name='idmenu' value='$idmenu'>";
echo "\n<table>";
//idmenu
echo "\n<tr><td>idmenu</td>";
echo "<td>$idmenu</td></tr>";
// ordine
echo "\n<tr><td>ordine di visualizzazione</td>";
$x = normalizzaform($r['ordine']);
echo "<td><input type='text' size='10' maxlength='10' name='ordine' value=\"$x\"></td></tr>";
// parentid
echo "\n<tr><td>padre</td>";
echo "<td><select name='parentid'>";
foreach ($amenu as $idmenu=>$menu) {
	echo "<option value='$idmenu'";
	if ($idmenu == $r['parentid']) echo ' selected';
	echo ">$menu</option>";
}
echo "</select> <a href='tab_menu.php?idmenu=$r[parentid]'>" . $amenu[$r['parentid']] . "</a></td></tr>";
// nome
echo "\n<tr><td>nome</td>";
$x = normalizzaform($r['nome']);
echo "<td><input type='text' size='80' maxlength='80' name='nome' value=\"$x\"></td></tr>";
// sigla
echo "\n<tr><td>sigla</td>";
$x = normalizzaform($r['sigla']);
echo "<td><input type='text' size='50' maxlength='50' name='sigla' value=\"$x\"></td></tr>";
// riferimento
echo "\n<tr><td>riferimento</td>";
$x = normalizzaform($r['riferimento']);
echo "<td><input type='text' size='50' maxlength='50' name='riferimento' value=\"$x\"></td></tr>";
// tag
echo "\n<tr><td>tag</td>";
$x = normalizzaform($r['tag']);
echo "<td><input type='text' size='30' maxlength='30' name='tag' value=\"$x\"></td></tr>";
// macro
echo "\n<tr><td>macro</td>";
$x = normalizzaform($r['macro']);
echo "<td><input type='text' size='20' maxlength='20' name='macro' value=\"$x\"></td></tr>";
//delete
echo "\n<tr><td>Cancella il menu</td>";
echo "<td><input type='checkbox' name='xxx'></td></tr>";
echo "</table><p><input type='submit' value='Modifica'></p></form>";

echo "\n<table border='0'><tr><td valign='top'>";
$qf = $db->query("SELECT idmenu,sigla FROM menu WHERE parentid='$r[parentid]' ORDER BY ordine");
if ($qf->num_rows > 0) {
	echo "\n<p><b>Voci di menu dello stesso livello:</b><ul>";
	while ($rf = $qf->fetch_array()){
		echo "\n<li><a href='tab_menu.php?idmenu=$rf[idmenu]'>$rf[sigla]</a></li>";
	}
	echo "</ul></p>";
}
echo "</td><td valign='top'>";
$qf = $db->query("SELECT idmenu,sigla FROM menu WHERE parentid='$r[idmenu]' ORDER BY ordine");
if ($qf->num_rows > 0) {
	echo "\n<p><b>Voci di menu figlie:</b><ul>";
	while ($rf = $qf->fetch_array()){
		echo "\n<li><a href='tab_menu.php?idmenu=$rf[idmenu]'>$rf[sigla]</a></li>";
	}
	echo "</ul></p>";
}
echo "</td></tr></table>";

echo "<body></html>";

### END OF FILE ###
