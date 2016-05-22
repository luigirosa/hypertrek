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
 * Modifica pagina timeline Star Trek
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20091122: tolte le chiamate alle vecchie librerie mysql e sistemata la gestione dei caratteri
 * 20100109: cambio della struttura del menu
 * 20100411: impostato defaut dell'anno
 *
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

// default per non provocare un warning
$anno = 'non ancora definito';

$aicone = array();
$aicone[0]['label'] = 'storia'; $aicone[0]['id'] = 2408;
$aicone[1]['label'] = 'ent';    $aicone[1]['id'] = 2409;
$aicone[2]['label'] = 'tos';    $aicone[2]['id'] = 2410;
$aicone[3]['label'] = 'tas';    $aicone[3]['id'] = 2411;
$aicone[4]['label'] = 'tng';    $aicone[4]['id'] = 2412;
$aicone[5]['label'] = 'dsn';    $aicone[5]['id'] = 2413;
$aicone[6]['label'] = 'voy';    $aicone[6]['id'] = 2414;
$aicone[7]['label'] = 'film';   $aicone[7]['id'] = 2415;
$aicone[8]['label'] = 'libri';  $aicone[8]['id'] = 2416;
$aicone[9]['label'] = 'noico';  $aicone[9]['id'] = 0;

// vedo come sono stato chiamato
if (isset($_GET['idtimeline'])) $nuovoanno = TRUE; else $nuovoanno = FALSE;
if (isset($_GET['anno'])) $anno = $_GET['anno'];

// Verifico se sono stato chiamato con i campi da aggiornare
if (count($_POST) > 0) {
	$lastmod = time();
	// aggiornamento
	if ('update' == $_POST['azione']) {
		if (isset($_POST['xxx'])) {
			$qry = "DELETE FROM timelinest WHERE idtimeline=$_POST[idtimeline]";
		} else {
			if ('' == $_POST['annodisplay']) $annodisplay = $db->escape_string($_POST['anno']); else $annodisplay = $db->escape_string($_POST['annodisplay']);
			if ('' == $_POST['stardate']) $stardate = 0; else $stardate = $db->escape_string($_POST['stardate']);
			$idicona = $db->escape_string($_POST['idicona']);
			$anno = $db->escape_string($_POST['anno']);
			$tordine = $db->escape_string($_POST['tordine']);
			$evento = $db->escape_string($_POST['evento']);
			$commento = $db->escape_string($_POST['commento']);
			$qry = "UPDATE timelinest 
			        SET anno='$anno',annodisplay='$annodisplay',tordine='$tordine',idicona='$idicona',evento='$evento',commento='$commento',
			        lastmod='$lastmod',stardate='$stardate'
			        WHERE idtimeline=$_POST[idtimeline]";
		}
	} else {
		if ('' == $_POST['annodisplay']) $annodisplay = $db->escape_string($_POST['anno']); else $annodisplay = $db->escape_string($_POST['annodisplay']);
		if ('' == $_POST['stardate']) $stardate = 0; else $stardate = $db->escape_string($_POST['stardate']);
		$idicona = $db->escape_string($_POST['idicona']);
		$anno = $db->escape_string($_POST['anno']);
		$tordine = $db->escape_string($_POST['tordine']);
		$evento = $db->escape_string($_POST['evento']);
		$commento = $db->escape_string($_POST['commento']);
		$qry = "INSERT INTO timelinest 
		        SET anno='$anno',annodisplay='$annodisplay',tordine='$tordine',idicona='$idicona',evento='$evento',commento='$commento',lastmod='$lastmod',
		        stardate=$stardate";
	}
	$db->query($qry);
	$anno = $_POST['anno'];
}

intestazione();

echo "<p align='center'><b>Timeline Star Trek: $anno</b></p>";
echo "\n<table border='0'>";
echo "\n<tr><th>anno<br>annodisplay</th><th>ordine<br>icona</th><th>evento<br>commento</th><th>stardate</th><th>delete<br>&nbsp;</th></tr>";

// default dell'intestazione se e' un anno nuovo
if ($nuovoanno) {
	$neworder = 10;
	$anno = '';
	$annodisplay = '';
} else {
	// trovo l'ultimo ordine e mi becco il velore dell'annodisplay
	$r = $db->query("SELECT tordine,annodisplay FROM timelinest WHERE anno='$anno' ORDER BY tordine DESC LIMIT 1")->fetch_array();
	$neworder = $r['tordine'] + 10;
	$annodisplay = $r['annodisplay'];
	$q = $db->query("SELECT * FROM timelinest WHERE anno='$anno' ORDER BY tordine");
	while ($r = $q->fetch_array()) {
		echo "\n<tr>";
		echo "\n<form method='post' action='tab_timelinest.php?anno=$anno' target='main' name='ttt-$r[idtimeline]'>";
		echo "\n<input type='hidden' name='idtimeline' value='$r[idtimeline]'>";
		echo "\n<input type='hidden' name='azione' value='update'>";
		// anno + annodisplay
		$x = normalizzaform($r['anno']);
		echo "\n<td><input type='text' size='10' maxlength='20' name='anno' value=\"$x\"><br>";
		$x = normalizzaform($r['annodisplay']);
		echo "\n<input type='text' size='10' maxlength='30' name='annodisplay' value=\"$x\"></td>";
		// ordine + icona
		$x = normalizzaform($r['tordine']);
		echo "\n<td><input type='text' size='6' maxlength='5' name='tordine' value=\"$x\"><br>";
		echo "<select name='idicona'>";
		foreach ($aicone as $ico) {
			echo "<option value='$ico[id]'";
	        	if ($ico['id'] == $r['idicona']) {
				echo " selected";
			}
        	echo ">$ico[label]</option>";
		}
		echo "</select>\n</td>";
		// evento+commento
		$x = normalizzaform($r['evento']);
		echo "\n<td><textarea rows='4' cols='80' name='evento'>$x</textarea><br>";
		$x = normalizzaform($r['commento']);
		echo "\n<textarea rows='2' cols='80' name='commento'>$x</textarea></td>";
		// stardate
		$x = normalizzaform($r['stardate']);
		echo "\n<td><input type='text' size='8' maxlength='10' name='stardate' value=\"$x\"></td>";
		// stile+delete
		echo "\n<td><input type='checkbox' name='xxx'><br>";
		echo "\n<input type='submit' value='Ok'></form></td>";
		echo "\n</tr>";
	}
}

// nuovo record
echo "\n<tr>";
echo "\n<form method='post' action='tab_timelinest.php' target='main' name='ttt-new'>";
echo "\n<input type='hidden' name='azione' value='insert'>";
// anno + annodisplay
echo "\n<td><input type='text' size='10' maxlength='20' name='anno' value='$anno'><br>";
echo "\n<input type='text' size='10' maxlength='30' name='annodisplay' value='$annodisplay'></td>";
// ordine + icona
echo "\n<td><input type='text' size='6' maxlength='5' name='tordine' value='$neworder'><br>";
echo "<select name='idicona'>";
foreach ($aicone as $ico) {
	echo "<option value='$ico[id]'";
	if ($ico['id'] == 0) {
		echo " selected";
	}
	echo ">$ico[label]</option>";
}
echo "</select>\n</td>";
// evento+commento
echo "\n<td><textarea rows='4' cols='80' name='evento'></textarea><br>";
echo "\n<textarea rows='2' cols='80' name='commento'></textarea></td>";
// stardate
echo "\n<td><input type='text' size='8' maxlength='10' name='stardate' value='0'></td>";
// stile+delete
echo "\n<td>&nbsp;<br>";
echo "\n<input type='submit' value='Ok'></form></td>";
echo "\n</tr>";

echo "</table>";

echo "\n<p></p></form>";

include ('palette.php');

echo "</body></html>";

### END OF FILE ###