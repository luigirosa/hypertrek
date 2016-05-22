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
 * Modifica notizie
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: aggiunta chiusura html; require_once -> require; cambiato il parse del POST
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

// Verifico se sono stato chiamato con i campi da aggiornare
if (count($_POST) > 0) {
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$db->query("DELETE FROM notizie WHERE idnotizia='$id'");
			} else {
				$a = array();
				$a[] = "data='" . mktime($x['dh'], $x['dn'], 0, $x['dm'], $x['dg'], $x['da']) . "'";
				$a[] = "scadenza='" . mktime($x['sh'], $x['sn'], 0, $x['sm'], $x['sg'], $x['sa']) . "'";
				$a[] = "testo='" . $db->escape_string(trim($x['testo'])) . "'";
				$db->query("UPDATE notizie SET " . implode(',', $a) . " WHERE idnotizia='$id'");
			}
		}
	}
	if ('' != trim($_POST['testo'])) {
		$a = array();
		$a[] = "data='" . mktime($_POST['dh'], $_POST['dn'], 0, $_POST['dm'], $_POST['dg'], $_POST['da']) . "'";
		$a[] = "scadenza='" . mktime($_POST['sh'], $_POST['sn'], 0, $_POST['sm'], $_POST['sg'], $_POST['sa']) . "'";
		$a[] = "testo='" . $db->escape_string(trim($_POST['testo'])) . "'";
		$db->query("INSERT INTO notizie SET idnotiziasorgente='2'," . implode(',', $a));
		
	}
}

$q = $db->query("SELECT * FROM notizie JOIN notiziesorgenti ON notizie.idnotiziasorgente=notiziesorgenti.idnotiziasorgente WHERE rssurl='' ORDER BY data DESC");

intestazione();

echo "\n<form method='post' action='notizie.php' target='main'>";
echo "\n<table border='0'>";

echo "\n<tr><th align='center'>Fonte</th><th align='center'>Data</th><th align='center'>Scadenza</th><th align='center'>Testo</th><th align='center'>Del</th></tr>";

// nuovo record 
echo "\n<tr>";
// fonte
echo "<td align='center'>HyperTrek</td>";
// data
echo "<td align='center' valign='top'>";
echo "<input type='text' name='dg' size='2' value='" . date('j') . "'>/";
echo "<input type='text' name='dm' size='2' value='" . date('n') . "'>/";
echo "<input type='text' name='da' size='4' value='" . date('Y') . "'><br>";
echo "<input type='text' name='dh' size='2' value='" . date('G') . "'>:";
echo "<input type='text' name='dn' size='2' value='" . date('i') . "'>";
echo "</td>";
// scadenza
echo "<td align='center' valign='top'>";
echo "<input type='text' name='sg' size='2' value='" . date('j') . "'>/";
echo "<input type='text' name='sm' size='2' value='" . date('n') . "'>/";
echo "<input type='text' name='sa' size='4' value='" . date('Y') . "'><br>";
echo "<input type='text' name='sh' size='2' value='" . date('G') . "'>:";
echo "<input type='text' name='sn' size='2' value='" . date('i') . "'>";
echo "</td>";
// testo
echo "\n<td><textarea rows='6' cols='80' name='testo'></textarea></td>";
echo "\n<td align='center' valign='top'>New</td>";
echo "\n</tr>";


while ($r = $q->fetch_array()) {
	$id = $r['idnotizia'];
	echo "\n<tr>";
	// fonte
	echo "<td align='center'>$r[iconatxt]</td>";
	// data
	echo "<td align='center' valign='top'>";
	echo "<input type='text' name='p[$id][dg]' size='2' value='" . date('j', $r['data']) . "'>/";
	echo "<input type='text' name='p[$id][dm]' size='2' value='" . date('n', $r['data']) . "'>/";
	echo "<input type='text' name='p[$id][da]' size='4' value='" . date('Y', $r['data']) . "'><br>";
	echo "<input type='text' name='p[$id][dh]' size='2' value='" . date('G', $r['data']) . "'>:";
	echo "<input type='text' name='p[$id][dn]' size='2' value='" . date('i', $r['data']) . "'>";
	echo "</td>";
	// scadenza
	echo "<td align='center' valign='top'>";
	echo "<input type='text' name='p[$id][sg]' size='2' value='" . date('j', $r['scadenza']) . "'>/";
	echo "<input type='text' name='p[$id][sm]' size='2' value='" . date('n', $r['scadenza']) . "'>/";
	echo "<input type='text' name='p[$id][sa]' size='4' value='" . date('Y', $r['scadenza']) . "'><br>";
	echo "<input type='text' name='p[$id][sh]' size='2' value='" . date('G', $r['scadenza']) . "'>:";
	echo "<input type='text' name='p[$id][sn]' size='2' value='" . date('i', $r['scadenza']) . "'>";
	echo "</td>";
	// testo
	echo "\n<td><textarea rows='6' cols='80' name='p[$id][testo]'>$r[testo]</textarea></td>";
	echo "\n<td align='center' valign='top'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table><p><input type='submit' value='Modifica'></p></form>";

echo "</body></html>";

### END OF FILE ###