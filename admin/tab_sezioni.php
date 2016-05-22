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
 * Modifica sezioni
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

// tipi degli indici
$atipoindice = array();
 $atipoindice[0]['label'] = 'generico'                    ;  $atipoindice[0]['id'] = 0;		// tutte le voci in ordine alfabetico, no frills
 $atipoindice[1]['label'] = 'genericotop (con sottomenu)' ;  $atipoindice[1]['id'] = 1;		// tutte le voci in ordine alfabetico, con elenco dei sottomenu
 $atipoindice[2]['label'] = 'alfabeticotop (sottomenu)'   ;  $atipoindice[2]['id'] = 2;	// sottomenu in ordine alfabetico
 $atipoindice[3]['label'] = 'timeline'                    ;  $atipoindice[3]['id'] = 3;		// indice della timeline
 $atipoindice[4]['label'] = 'astronavitop'                ;  $atipoindice[4]['id'] = 4;	// topmenu astronavi
 $atipoindice[5]['label'] = 'astronaviprimo'              ;  $atipoindice[5]['id'] = 5;	// primo livello astronavi (specie)
 $atipoindice[6]['label'] = 'astronavisecondo'            ;  $atipoindice[6]['id'] = 6;	// secondo livello astronavi (classi)
 $atipoindice[7]['label'] = 'enttop'                      ;  $atipoindice[7]['id'] = 7;		// Enterprise - Top menu
 $atipoindice[8]['label'] = 'entit'                       ;  $atipoindice[8]['id'] = 8;		// Enterprise - Titoli italiani
 $atipoindice[9]['label'] = 'entus'                       ;  $atipoindice[9]['id'] = 9;		// Enterprise - Titoli originali
$atipoindice[10]['label'] = 'entprod'                     ; $atipoindice[10]['id'] = 10;		// Enterprise - Produzione
$atipoindice[11]['label'] = 'entmisc'                     ; $atipoindice[11]['id'] = 11;		// Enterprise - varie
$atipoindice[38]['label'] = 'enttoppg'                    ; $atipoindice[38]['id'] = 38;		// Enterprise - pagine singole nell'indice principale
$atipoindice[12]['label'] = 'tostop'                      ; $atipoindice[12]['id'] = 12;		// Serie Classica - Top menu
$atipoindice[13]['label'] = 'tosit'                       ; $atipoindice[13]['id'] = 13;		// Serie Classica - Titoli italiani
$atipoindice[14]['label'] = 'tosus'                       ; $atipoindice[14]['id'] = 14;		// Serie Classica - Titoli originali
$atipoindice[15]['label'] = 'tosprod'                     ; $atipoindice[15]['id'] = 15;		// Serie Classica - Produzione
$atipoindice[16]['label'] = 'tosmisc'                     ; $atipoindice[16]['id'] = 16;		// Serie Classica - sottoindici vari
$atipoindice[37]['label'] = 'tostoppg'                    ; $atipoindice[37]['id'] = 37;		// Serie Classica - pagine singole nell'indice principale
$atipoindice[17]['label'] = 'tastop'                      ; $atipoindice[17]['id'] = 17;		// Serie Animata - Top menu
$atipoindice[18]['label'] = 'tasit'                       ; $atipoindice[18]['id'] = 18;		// Serie Animata - Titoli italiani
$atipoindice[19]['label'] = 'tasus'                       ; $atipoindice[19]['id'] = 19;		// Serie Animata - Titoli originali
$atipoindice[20]['label'] = 'tasprod'                     ; $atipoindice[20]['id'] = 20;		// Serie Animata - Produzione
$atipoindice[21]['label'] = 'tasmisc'                     ; $atipoindice[21]['id'] = 21;		// Serie Animata - varie
$atipoindice[42]['label'] = 'tastoppg'                    ; $atipoindice[42]['id'] = 42;		// Serie Animata - pagine singole nell'indice principale
$atipoindice[22]['label'] = 'tngtop'                      ; $atipoindice[22]['id'] = 22;		// Next Generation - Top menu
$atipoindice[23]['label'] = 'tngit'                       ; $atipoindice[23]['id'] = 23;		// Next Generation - Titoli italiani
$atipoindice[24]['label'] = 'tngus'                       ; $atipoindice[24]['id'] = 24;		// Next Generation - Titoli originali
$atipoindice[25]['label'] = 'tngprod'                     ; $atipoindice[25]['id'] = 25;		// Next Generation - Produzione
$atipoindice[26]['label'] = 'tngmisc'                     ; $atipoindice[26]['id'] = 26;		// Next Generation - varie
$atipoindice[39]['label'] = 'tngtoppg'                    ; $atipoindice[39]['id'] = 39;		// Next Generation - pagine singole nell'indice principale
$atipoindice[27]['label'] = 'dsntop'                      ; $atipoindice[27]['id'] = 27;		// Deep Space Nine - Top menu
$atipoindice[28]['label'] = 'dsnit'                       ; $atipoindice[28]['id'] = 28;		// Deep Space Nine - Titoli italiani
$atipoindice[29]['label'] = 'dsnus'                       ; $atipoindice[29]['id'] = 29;		// Deep Space Nine - Titoli originali
$atipoindice[30]['label'] = 'dsnprod'                     ; $atipoindice[30]['id'] = 30;		// Deep Space Nine - Produzione
$atipoindice[31]['label'] = 'dsnmisc'                     ; $atipoindice[31]['id'] = 31;		// Deep Space Nine - varie
$atipoindice[40]['label'] = 'dsntoppg'                    ; $atipoindice[40]['id'] = 40;		// Deep Space Nine - pagine singole nell'indice principale
$atipoindice[32]['label'] = 'voytop'                      ; $atipoindice[32]['id'] = 32;		// Voyager - Top menu
$atipoindice[33]['label'] = 'voyit'                       ; $atipoindice[33]['id'] = 33;		// Voyager - Titoli italiani
$atipoindice[34]['label'] = 'voyus'                       ; $atipoindice[34]['id'] = 34;		// Voyager - Titoli originali
$atipoindice[35]['label'] = 'voyprod'                     ; $atipoindice[35]['id'] = 35;		// Voyager - Produzione
$atipoindice[36]['label'] = 'voymisc'                     ; $atipoindice[36]['id'] = 36;		// Voyager - varie
$atipoindice[41]['label'] = 'voytoppg'                    ; $atipoindice[41]['id'] = 41;		// Voyager - pagine singole nell'indice principale
$atipoindice[43]['label'] = 'indice'                      ; $atipoindice[43]['id'] = 43;      // indice

if (isset($_GET['idsezione'])) $idsezione = $_GET['idsezione'];

if (isset($_POST['idsezione'])){
	$idsezione = $_POST['idsezione'];
	//cancello?
	if (isset($_POST['xxx'])) {
		$db->query("DELETE FROM sezioni WHERE idsezione=$idsezione");
	} else {
		$aqry = array();
		if (isset($_POST['istopmenu'])) {
			$aqry[] = 'istopmenu=1';
		} else {
			$aqry[] = 'istopmenu=0';
		}
		$aqry[] = "sordine='" . $db->escape_string(trim($_POST['sordine'])) . "'";
		$aqry[] = "nome='" . $db->escape_string(trim($_POST['nome'])) . "'";
		$aqry[] = "sigla='" . $db->escape_string(trim($_POST['sigla'])) . "'";
		$aqry[] = "riferimento='" . $db->escape_string(trim($_POST['riferimento'])) . "'";
		$aqry[] = "icona='" . $db->escape_string(trim($_POST['icona'])) . "'";
		$aqry[] = "indextag='" . $db->escape_string(trim($_POST['indextag'])) . "'";
		$aqry[] = "tipoindice='" . $db->escape_string(trim($_POST['tipoindice'])) . "'";
		$aqry[] = "toptag='" . $db->escape_string(trim($_POST['toptag'])) . "'";
		$aqry[] = "padretag='" . $db->escape_string(trim($_POST['padretag'])) . "'";
		
		if ($idsezione == '0') {
			$qq = $db->query("INSERT INTO sezioni SET " . implode(',', $aqry));
			$idsezione = $db->insert_id;
		} else {
			$db->query("UPDATE sezioni SET " . implode(',', $aqry) . " WHERE idsezione=$idsezione");
		}
	}
}

if ($idsezione > 0) {
	$r = $db->query("SELECT * FROM sezioni WHERE idsezione='$idsezione'")->fetch_array();
} else {
	$idsezione = '0';
}

intestazione();

echo "\n<form method='post' action='tab_sezioni.php' target='main'>";
echo "<input type='hidden' name='idsezione' value='$idsezione'>";
echo "\n<table>";
//idsezione
echo "\n<tr><td>idsezione</td>";
echo "<td>$idsezione</td></tr>";
// sordine
echo "\n<tr><td>ordine di visualizzazione</td>";
$x = normalizzaform($r['sordine']);
echo "<td><input type='text' size='10' maxlength='10' name='sordine' value=\"$x\"></td></tr>";
// nome
echo "\n<tr><td>nome</td>";
$x = normalizzaform($r['nome']);
echo "<td><input type='text' size='60' maxlength='60' name='nome' value=\"$x\"></td></tr>";
// sigla
echo "\n<tr><td>sigla</td>";
$x = normalizzaform($r['sigla']);
echo "<td><input type='text' size='30' maxlength='50' name='sigla' value=\"$x\"></td></tr>";
// riferimento
echo "\n<tr><td>riferimento</td>";
$x = normalizzaform($r['riferimento']);
echo "<td><input type='text' size='30' maxlength='30' name='riferimento' value=\"$x\"></td></tr>";
// icona
echo "\n<tr><td>icona</td>";
$x = normalizzaform($r['icona']);
echo "<td><input type='text' size='50' maxlength='50' name='icona' value=\"$x\"></td></tr>";
// is top menu?
echo "\n<tr><td>top menu</td>";
echo "<td><input type='checkbox' name='istopmenu'";
if (1 == $r['istopmenu']) echo " checked";
echo "></td></tr>";
// indextag
echo "\n<tr><td>indextag</td>";
$x = normalizzaform($r['indextag']);
echo "<td><input type='text' size='20' maxlength='20' name='indextag' value=\"$x\"></td></tr>";
//tipo indice
echo "\n<tr><td>tipo indice</td>";
echo "<td><select name='tipoindice'>";
foreach ($atipoindice as $tp) {
	echo "\n<option value='$tp[id]'";
	if ($tp['id'] == $r['tipoindice']) {
		echo " selected";
	}
	echo ">$tp[label]</option>";
}
echo "</select>\n</td></tr>";
// padretag
echo "\n<tr><td>padretag</td>";
$x = normalizzaform($r['padretag']);
echo "<td><input type='text' size='20' maxlength='20' name='padretag' value=\"$x\"></td></tr>";
// toptag
echo "\n<tr><td>toptag</td>";
$x = normalizzaform($r['toptag']);
echo "<td><input type='text' size='20' maxlength='20' name='toptag' value=\"$x\"></td></tr>";

//delete
echo "\n<tr><td>Cancella la sezione</td>";
echo "<td><input type='checkbox' name='xxx'></td></tr>";

echo "</table><p><input type='submit' value='Modifica'></p></form>";
echo "<body></html>";

### END OF FILE ###
