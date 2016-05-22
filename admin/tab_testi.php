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
 * Modifica testi
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090308: allargate le textarea
 * 20090426: cambio formattazione
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
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$rr = $db->query("SELECT ordine FROM testi WHERE idtesto='$id'")->fetch_array();
				loggamodifica($idpagina, "cancellato testo ordine $rr[ordine]");
				$db->query("DELETE FROM testi WHERE idtesto='$id'");
			} else {
				$a = array();
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				// se e' valorizzato, taginclude deve iniziare e terminare con uno spazio
				if ('' != trim($x['taginclude'])) {
					$a[] = "taginclude=' " . $db->escape_string(trim($x['taginclude'])) . " '";
				} else {
					$a[] = "taginclude=''";
				}
				$a[] = "testo='" . $db->escape_string(trim($x['testo'])) . "'";
				$db->query("UPDATE testi SET " . implode(',', $a) . " WHERE idtesto='$id'");
			}
		}
	}
	if ($_POST['testo'] != '') {
		if (strpos($_POST['testo'], '#@@@#') === FALSE) {
			$a = array();
			$a[] = "idpagina='$idpagina'";
			$a[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
			// se e' valorizzato, taginclude deve iniziare e terminare con uno spazio
			if ('' != trim($_POST['taginclude'])) {
				$a[] = "taginclude=' " . $db->escape_string(trim($_POST['taginclude'])) . " '";
			}
			$a[] = "testo='" . $db->escape_string(trim($_POST['testo'])) . "'";
			$db->query("INSERT INTO testi SET " . implode(',', $a));
			loggamodifica($idpagina, "inserito testo ordine $_POST[ordine]");
		} else {
			$aTesti = explode('#@@@#', $_POST['testo']);
			$ordine = $db->escape_string(trim($_POST['ordine']));
			foreach ($aTesti as $testo) {
				$a = array();
				$a[] = "idpagina='$idpagina'";
				$a[] = "ordine='$ordine'";
				// se e' valorizzato, taginclude deve iniziare e terminare con uno spazio
				if ('' != trim($_POST['taginclude'])) {
					$a[] = "taginclude=' " . $db->escape_string(trim($_POST['taginclude'])) . " '";
				}
				$a[] = "testo='" . $db->escape_string(trim($testo)) . "'";
				$db->query("INSERT INTO testi SET " . implode(',', $a));
				$ordine += 10;
			}
			loggamodifica($idpagina, "inseriti testi multipli");
		}
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modifica testi');
}

// trovo l'ultimo ordine
$neworder = 10;
$q = $db->query("SELECT ordine FROM testi WHERE idpagina='$idpagina' ORDER BY ordine DESC LIMIT 1");
if ($q->num_rows > 0) {
	$r = $q->fetch_array();
	$neworder = $r[0] + 10;
}

$q = $db->query("SELECT * FROM testi WHERE idpagina='$idpagina' ORDER BY ordine");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_testi.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value='$idpagina'>";
echo "\n<p style='text-align: center; margin-top: 0; margin-bottom: 0;'><input type='submit' value='     Aggiorna     '></p>";

echo "\n<table border='0'>";
echo "\n<tr><th>Ordine<br>Taginclude</th><th>&nbsp;Testo - Usare #@@@# per separare le entry multiple</th><th align='center'>Del</th></tr>";
// nuovo record
echo "\n<tr>";
echo "\n<td valign='top'><input type='text' size='5' maxlength='6' name='ordine' value='$neworder'><br>";
echo "<input type='text' size='20' maxlength='250' name='taginclude'></td>";
echo "\n<td><textarea rows='9' cols='120' name='testo'></textarea></td>";
echo "\n<td align='center'>New</td>";
echo "\n</tr>";
while ($r = $q->fetch_array()) {
	$id = $r['idtesto'];
	echo "\n<tr>";
	$x = normalizzaform($r['ordine']);
	echo "\n<td valign='top'><input type='text' size='5' maxlength='6' name='p[$id][ordine]' value=\"$x\"><br>";
	$x = normalizzaform($r['taginclude']);
	echo "<input type='text' size='20' maxlength='250' name='p[$id][taginclude]' value=\"$x\"></td>";
	$x = normalizzaform($r['testo']);
	echo "\n<td><textarea rows='9' cols='120' name='p[$id][testo]'>$x</textarea>";
	echo "\n<td align='center' valign='top'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}
echo "</table>";
echo "\n<p style='text-align: center; margin-top: 0; margin-bottom: 0;'><input type='submit' value='     Aggiorna     '></p>";
echo "\n</form>";

include ('palette.php');

echo "\n</body></html>";

### END OF FILE ###