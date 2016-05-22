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
 * Modifica valori episodi
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
	if (isset($_POST['p'])) {
		foreach($_POST['p'] as $id=>$x) {
			if (isset($x['xxx'])) {
				$rr = $db->query("SELECT descrizione FROM episodivalori JOIN episodicampi ON episodivalori.idcampo=episodicampi.idcampo WHERE idvalore='$id'")->fetch_array();
				loggamodifica($idpagina, "cancellato $rr[descrizione]");
				$db->query("DELETE FROM episodivalori WHERE idvalore='$id'");
			} else {
				$a = array();
				$a[] = "valore='" . $db->escape_string(trim($x['valore'])) . "'";
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				$a[] = "idpaginacast='" . $db->escape_string(trim($x['idpaginacast'])) . "'";
				$db->query("UPDATE episodivalori SET " . implode(',', $a) . " WHERE idvalore='$id'");
			}
		}
	}
	if ($_POST['idcampo'] != 0) {
		$a = array();
		$a[] = "idpagina='$idpagina'";
		$a[] = "idcampo='" . $db->escape_string(trim($_POST['idcampo'])) . "'";
		$a[] = "valore='" . $db->escape_string(trim($_POST['valore'])) . "'";
		$a[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
		$a[] = "idpaginacast='" . $db->escape_string(trim($_POST['idpaginacast'])) . "'";
		$db->query("INSERT INTO episodivalori SET " . implode(',', $a));
		$rr = $db->query("SELECT descrizione FROM episodicampi WHERE idcampo='$_POST[idcampo]'")->fetch_array();
		loggamodifica($idpagina, "aggiunto $rr[descrizione]");
	} else {
		if (substr($_POST['valore'], 0, 3) == '###') {
			$a = explode(' ', $_POST['valore']);
			foreach ($a as $z) {
				if ('HUM' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Umano|umani}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('HUMA' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Umana|umani}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('BAJ' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Bajoriano|bajoriani}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('KLI' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Klingon|klingon}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('ROM' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Romulano|romulani}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('CAR' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Cardassiano|cardassiani}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('VUL' == $z) $db->query("INSERT INTO episodivalori SET idcampo='84',valore='{Vulcaniano|vulcaniani}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('EX' == $z) $db->query("INSERT INTO episodivalori SET idcampo='85',valore='{2152|ttt2152} <i>{Enterprise NX-01|assfenterprisenx01}</i>',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('E' == $z) $db->query("INSERT INTO episodivalori SET idcampo='85',valore='{2266|ttt2266} <i>{USS Enterprise|assfenterprise}</i>',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('ED' == $z) $db->query("INSERT INTO episodivalori SET idcampo='85',valore='{2367 | ttt2367} <i>{USS Enterprise | assfenterprised}</i>',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('DSN' == $z) $db->query("INSERT INTO episodivalori SET idcampo='85',valore='{2373|ttt2373} <i>{Deep Space Nine | ds9}</i> ',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('VOY' == $z) $db->query("INSERT INTO episodivalori SET idcampo='85',valore='<i>{USS Voyager|assfvoyager}</i>',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('SF' == $z) $db->query("INSERT INTO episodivalori SET idcampo='91',valore='{Flotta Stellare | :starfleet}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('MACO' == $z) $db->query("INSERT INTO episodivalori SET idcampo='91',valore='{MACO | maco}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('MAQ' == $z) $db->query("INSERT INTO episodivalori SET idcampo='91',valore='{Maquis | maquis}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('ENS' == $z) $db->query("INSERT INTO episodivalori SET idcampo='86',valore='Guardiamarina',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('TEN' == $z) $db->query("INSERT INTO episodivalori SET idcampo='86',valore='Tenente',ordine='10',idpagina='$idpagina',idpaginacast=0");
				if ('ZZZ' == $z) {
					$db->query("INSERT INTO episodivalori SET idcampo='92',valore='{Astronavi varie| astronavivarie}',ordine='10',idpagina='$idpagina',idpaginacast=0");
					//$db->query("INSERT INTO episodivalori SET idcampo='93',valore='{Classe Suurok| classesuurok}',ordine='10',idpagina='$idpagina',idpaginacast=0");
				}
			}
			loggamodifica($idpagina, 'aggiunti valori');
		}
	}
	//aggiorno anche la data di aggiornamento
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modifica valori');
}

$q = $db->query("SELECT * FROM episodivalori WHERE idpagina='$idpagina' ORDER BY idcampo,ordine");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_episodivalori.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value=$idpagina>";
echo "\n<p align='center'><input type='submit' value='Aggiorna'></p>";
echo "\n<table border='0'>";
echo "\n<tr><th>Etichetta</th><th>Valore</th><th>Ordine</th><th>Cast</th><th>Delete</th></tr>";
// nuovo record
echo "\n<tr>";
//etichetta
echo "\n<td><select name='idcampo'>";
$qq = $db->query("SELECT idcampo,etichetta FROM episodicampi ORDER BY etichetta");
echo "\n<option selected value='0'>New</option>";
while ($rr = $qq->fetch_array()) {
	echo "\n<option value='$rr[idcampo]'>$rr[etichetta]</option>";
}
echo "\n</select></td>";
echo "\n<td><input type='text' size='50' maxlength='250' name='valore'></td>";
echo "\n<td><input type='text' size='10' maxlength='10' name='ordine' value='10'></td>";
//cast
echo "\n<td><select name='idpaginacast'>";
echo "\n<option selected value='0'>Selezionare</option>";
$qq = $db->query("SELECT idpagina,chiavesort FROM pagine WHERE (idsezione>=176 and idsezione<=201) ORDER BY chiavesort");
while ($rr = $qq->fetch_array()) {
	echo "<option value='$rr[idpagina]'>$rr[chiavesort]</option>";
}
echo "\n</select></td>";
echo "\n<td align='center'>New</td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$id = $r['idvalore'];
	echo "\n<tr>";
	//etichetta, read only 
	$rr = $db->query("SELECT etichetta FROM episodicampi WHERE idcampo='$r[idcampo]'")->fetch_array();
	echo "\n<td align='right'>$rr[0]</td>";
	$x = normalizzaform($r['valore']);
	echo "\n<td><input type='text' size='50' maxlength='250' name='p[$id][valore]' value=\"$x\"></td>";
	$x = normalizzaform($r['ordine']);
	echo "\n<td><input type='text' size='10' maxlength='10' name='p[$id][ordine]' value=\"$x\"></td>";
	$x = normalizzaform($r['idpaginacast']);
	echo "\n<td><input type='text' size='10' maxlength='10' name='p[$id][idpaginacast]' value=\"$x\"></td>";
	echo "\n<td align='center'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table><p align='center'><input type='submit' value='Aggiorna'></p></form>";

echo "\n<p><b>Rapidi</b> (###):<br>";
echo "<br> Specie: HUM BAJ KLI ROM CAR VUL";
echo "<br> Assegnamenti: EX E ED DSN VOY";
echo "<br> Organizzazioni: SF MACO MAQ";
echo "<br> Gradi: ENS TEN";
echo "</p>";

echo "\n</body></html>";

### END OF FILE ###
