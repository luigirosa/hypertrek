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
 * Modifica pagina
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require
 * 20090228: tolto htmlentities da IMDb
 * 20090301: gestione log modifiche
 * 20091122: sistemata la gestione dei caratteri nei form
 * 20100109: cambio della struttura del menu
 * 20100131: nuova struttura del menu e rimozione indice analitico
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

$idpagina = 0;

$atipi[0]['label'] = 'Generica'; $atipi[0]['id'] = 0;
$atipi[1]['label'] = 'Episodio'; $atipi[1]['id'] = 1;
$atipi[2]['label'] = 'Pianeta'; $atipi[2]['id'] = 2;
$atipi[3]['label'] = '404'; $atipi[3]['id'] = 3;
$atipi[4]['label'] = 'Specie'; $atipi[4]['id'] = 4;
$atipi[5]['label'] = 'Personaggio'; $atipi[5]['id'] = 5;
$atipi[6]['label'] = 'Quante volte'; $atipi[6]['id'] = 6;
$atipi[7]['label'] = 'Cast di una serie'; $atipi[7]['id'] = 7;
$atipi[8]['label'] = 'Recurring'; $atipi[8]['id'] = 8;
$atipi[9]['label'] = 'Tabella riassuntiva'; $atipi[9]['id'] = 9;
$atipi[10]['label'] = 'Libro'; $atipi[10]['id'] = 10;
$atipi[11]['label'] = 'Mappa del sito'; $atipi[11]['id'] = 11;
$atipi[12]['label'] = 'Attore'; $atipi[12]['id'] = 12;
$atipi[13]['label'] = 'Astronave'; $atipi[13]['id'] = 13;
$atipi[14]['label'] = 'Timeline ST'; $atipi[14]['id'] = 14;
$atipi[15]['label'] = 'Statistiche'; $atipi[15]['id'] = 15;
$atipi[16]['label'] = 'Copertina'; $atipi[16]['id'] = 16;
$atipi[17]['label'] = 'Elencopuntato'; $atipi[17]['id'] = 17;
$atipi[18]['label'] = 'Eventi trek'; $atipi[18]['id'] = 18;
$atipi[19]['label'] = 'Organizzazioni'; $atipi[19]['id'] = 19;
$atipi[20]['label'] = 'Log'; $atipi[20]['id'] = 20;
$atipi[21]['label'] = 'Astronave classe'; $atipi[21]['id'] = 21;
$atipi[22]['label'] = 'Astronave flotta'; $atipi[22]['id'] = 22;

if (isset($_GET['idpagina'])) $idpagina = $_GET['idpagina'];

if (isset($_POST['idpagina']) and $_POST['idpagina'] != ''){
	$idpagina = $_POST['idpagina'];
	//cancello?
	if (isset($_POST['xxx'])) {
		//$db->query("DELETE FROM analitico WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM capitoli WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM episodivalori WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM guest WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM immaginipagine WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM piepagina WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM quantevolte WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM riferimenti WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM testi WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM modifiche WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM paginemenu WHERE idpagina='$idpagina'");
		$db->query("DELETE FROM pagine WHERE idpagina='$idpagina'");
	} else {
		$aqry = array();
		if (isset($_POST['hidden'])) {
			$aqry[] = 'hidden=1';
		} else {
			$aqry[] = 'hidden=0';
		}
		$aqry[] = "lastmod='" . time() . "'";
		$aqry[] = "tag='" . strtolower(trim($db->escape_string($_POST['tag']))) . "'";
		$aqry[] = "chiavesort='" . $db->escape_string(trim($_POST['chiavesort'])) . "'";
		$aqry[] = "titolo='" . $db->escape_string(trim($_POST['titolo'])) . "'";
		$aqry[] = "memoryalpha='" . $db->escape_string(urlencode(trim($_POST['memoryalpha']))) . "'";
		$aqry[] = "originale='" . $db->escape_string(trim($_POST['originale'])) . "'";
		$aqry[] = "titolondx='" . $db->escape_string(trim($_POST['titolondx'])) . "'";
		$aqry[] = "tagprima='" . $db->escape_string(trim($_POST['tagprima'])) . "'";
		$aqry[] = "tagdopo='" . $db->escape_string(trim($_POST['tagdopo'])) . "'";
		$aqry[] = "serie='" . $db->escape_string(trim($_POST['serie'])) . "'";
		$aqry[] = "stagione='" . $db->escape_string(trim($_POST['stagione'])) . "'";
		$aqry[] = "idsezione='" . $db->escape_string(trim($_POST['idsezione'])) . "'";
		$aqry[] = "tipo='" . $db->escape_string(trim($_POST['tipo'])) . "'";
		$aqry[] = "imdb='" . $db->escape_string(trim($_POST['imdb'])) . "'";
		if ($idpagina == '0') {
			$qq = $db->query("INSERT INTO pagine SET " . implode(',', $aqry));
			$idpagina = $db->insert_id;
			loggamodifica($idpagina, 'creata');
		} else {
			$db->query("UPDATE pagine SET " . implode(',', $aqry) . " WHERE idpagina='$idpagina'");
			loggamodifica($idpagina, 'modifica dati della pagina');
		}
	}
}

intestazione();

if ($idpagina > 0) {
	$r = $db->query("SELECT * FROM pagine WHERE idpagina='$idpagina'")->fetch_array();
	mostramenu($idpagina);
} else {
	$r['idpagina'] = 0;
	$r['lastmod'] = 0;
	$r['titolo'] = '';
	$r['titolondx'] = '';
	$r['chiavesort'] = '';
	$r['imdb'] = '';
	$r['memoryalpha'] = '';
	$r['idsezione'] = 0;
	$r['tipo'] = 0;
	$r['originale'] = '';
	$r['hidden'] = 0;
	$r['tagprima'] = '';
	$r['tagdopo'] = '';
	$r['serie'] = '';
	$r['stagione'] = '';
}

if (isset($_GET['tag'])) {
	$r['idpagina'] = 0;
	$r['tag'] = $_GET['tag'];
}


echo "\n<form method='post' action='tab_pagine.php' target='main'>";
echo "<input type='hidden' name='idpagina' value='$r[idpagina]'>";
echo "\n<table>";
//idpagina
echo "\n<tr><td>idpagina</td>";
echo "<td>$r[idpagina]</td></tr>";
//Lastmod
echo "\n<tr><td>Ultima modifica</td>";
echo "<td>" . date('j.n.Y H:i:s', $r['lastmod']) . "</td></tr>";
//titolo
echo "\n<tr><td>titolo</td>";
$x = normalizzaform($r['titolo']);
echo "<td><input type='text' size='80' maxlength='80' name='titolo' value=\"$x\"></td></tr>";
//titolo dell'indice
echo "\n<tr><td>titolo dell'indice</td>";
$x = normalizzaform($r['titolondx']);
echo "<td><input type='text' size='90' maxlength='90' name='titolondx' value=\"$x\"></td></tr>";
//chiavesort
echo "\n<tr><td>chiave di sort</td>";
$x = normalizzaform($r['chiavesort']);
echo "<td><input type='text' size='30' maxlength='30' name='chiavesort' value=\"$x\"></td></tr>";
//tag
echo "\n<tr><td>tag</td>";
echo "<td><input type='text' size='30' maxlength='30' name='tag' value=\"$r[tag]\"></td></tr>";
//imdb
echo "\n<tr><td>IMDb</td>";
echo "<td><input type='text' size='50' maxlength='50' name='imdb' value=\"" . urldecode($r['imdb']) . "\"></td></tr>";
//Memory Alpha
echo "\n<tr><td>Memory Alpha</td>";
echo "<td><input type='text' size='50' maxlength='100' name='memoryalpha' value=\"" . urldecode($r['memoryalpha']) . "\"></td></tr>";
//sezione
echo "\n<tr><td>sezione</td>";
echo "<td><select name='idsezione'>";
echo "<option value='0'>Nessuna</option>";
$qq = $db->query("SELECT idsezione,sigla FROM sezioni ORDER BY sigla");
while ($rr = $qq->fetch_array()) {
	echo "<option value='$rr[idsezione]'";
	if ($rr['idsezione'] == $r['idsezione']) echo ' selected';
	echo ">$rr[sigla]</option>";
}
echo "</select></td></tr>";		
//tipo
echo "\n<tr><td>tipo</td>";
echo "<td><select name='tipo'>";
foreach ($atipi as $tp) {
	echo "<option value='$tp[id]'";
	if ($tp['id'] == $r['tipo']) echo ' selected';
	echo ">$tp[label]</option>";
}
echo "</select>\n</td></tr>";

//originale
echo "\n<tr><td>originale</td>";
$x = normalizzaform($r['originale']);
echo "<td><input type='text' size='80' maxlength='80' name='originale' value=\"$x\"></td></tr>";

//hidden
echo "\n<tr><td>hidden</td>";
echo "<td><input type='checkbox' name='hidden'";
if (1 == $r['hidden']) echo " checked";
echo "></td></tr>";

//episodio precedente
echo "\n<tr><td>Pagina precedente</td>";
$x = normalizzaform($r['tagprima']);
echo "<td><input type='text' size='30' maxlength='30' name='tagprima' value=\"$x\"></td></tr>";

//episodio successivo
echo "\n<tr><td>Pagina successiva</td>";
$x = normalizzaform($r['tagdopo']);
echo "<td><input type='text' size='30' maxlength='30' name='tagdopo' value=\"$x\"></td></tr>";

//serie
echo "\n<tr><td>Serie</td>";
$x = normalizzaform($r['serie']);
echo "<td><input type='text' size='4' maxlength='3' name='serie' value=\"$x\"></td></tr>";

//stagione
echo "\n<tr><td>Stagione</td>";
$x = normalizzaform($r['stagione']);
echo "<td><input type='text' size='3' maxlength='1' name='stagione' value=\"$x\"></td></tr>";

//delete
echo "\n<tr><td>Cancella la pagina</td>";
echo "<td><input type='checkbox' name='xxx'></td></tr>";

echo "</table><p><input type='submit' value='Modifica'></p></form>";

echo "\n</body></html>";


### END OF FILE ###