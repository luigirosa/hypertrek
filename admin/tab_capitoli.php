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
 * Modifica capitoli
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090307: corezione baco nell'aggiunta di un capitolo 
 * 20090308: allargate le textarea
 * 20090510: aggiunta la gestione dei nomi arbitrari dei capitoli
 * 20091122: sistemata la gestione dei caratteri nei form
 * 20091226: rimosso campo titoloalternativo
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
			$rr = $db->query("SELECT tag,intestazione,capitoli.ordine FROM capitoli JOIN capitolitipi ON capitoli.idcapitolotipo=capitolitipi.idcapitolotipo WHERE idcapitolo='$id'")->fetch_array();
			if (isset($x['xxx'])) {
				loggamodifica($idpagina, "cancellato capitolo tipo $rr[intestazione] ordine $rr[2]");
				$db->query("DELETE FROM capitoli WHERE idcapitolo='$id'");
			} else {
				$a = array();
				$a[] = "idcapitolotipo='" . $db->escape_string(trim($x['idcapitolotipo'])) . "'";
				$a[] = "ordine='" . $db->escape_string(trim($x['ordine'])) . "'";
				// se e' valorizzato, taginclude deve iniziare e terminare con uno spazio
				if ('' != trim($x['taginclude'])) {
					$a[] = "taginclude=' " . $db->escape_string(trim($x['taginclude'])) . " '";
				} else {
					$a[] = "taginclude=NULL";
				}
				$a[] = "testo='" . $db->escape_string(trim($x['testo'])) . "'";
				$db->query("UPDATE capitoli SET " . implode(',', $a) . " WHERE idcapitolo='$id'");
			}
		}
	}
	if ($_POST['testo'] != '' and $_POST['idcapitolotipo']) {
		$a = array();
		$a[] = "idpagina='$idpagina'";
		$a[] = "idcapitolotipo='" . $db->escape_string(trim($_POST['idcapitolotipo'])) . "'";
		$rr = $db->query("SELECT tag,intestazione FROM capitolitipi WHERE idcapitolotipo='" . $db->escape_string(trim($_POST['idcapitolotipo'])) . "'")->fetch_array();
		if ($_POST['ordine'] > 0) {
			$a[] = "ordine='" . $db->escape_string(trim($_POST['ordine'])) . "'";
		} else {
			$a[] = "ordine='10'";
		}
		// se e' valorizzato, taginclude deve iniziare e terminare con uno spazio
		if ('' != trim($_POST['taginclude'])) {
			$a[] = "taginclude=' " . $db->escape_string(trim($_POST['taginclude'])) . " '";
		}
		$a[] = "testo='" . $db->escape_string(trim($_POST['testo'])) . "'";
		$db->query("INSERT INTO capitoli SET " . implode(',', $a));
		loggamodifica($idpagina, "aggiunto capitolo tipo $rr[intestazione] ordine $_POST[ordine]");
	}
	toccapagina($idpagina);
	loggamodifica($idpagina, 'modifica capitoli');
}


// raccolgo una sola volta l'elenco dei capitoli
$aCapitoli = array();
$ndx = 0;
$qq = $db->query("SELECT idcapitolotipo,intestazione,tag FROM capitolitipi ORDER BY intestazione");
while ($rr = $qq->fetch_array()) {
	$aCapitoli[$ndx]['id'] = $rr['idcapitolotipo'];
	$aCapitoli[$ndx]['intestazione'] = $rr['intestazione'];
	$aCapitoli[$ndx]['tag'] = $rr['tag'];
	$ndx++;
}

$q = $db->query("SELECT idcapitolo,capitoli.idcapitolotipo AS idcapitolotipo,testo, capitoli.ordine AS ordine,taginclude,capitolitipi.intestazione FROM capitoli
                JOIN capitolitipi ON capitoli.idcapitolotipo=capitolitipi.idcapitolotipo
                WHERE capitoli.idpagina='$idpagina'
                ORDER BY capitolitipi.ordine,capitoli.ordine");

intestazione();
mostramenu($idpagina);

echo "\n<form method='post' action='tab_capitoli.php' target='main'>";
echo "\n<input type='hidden' name='idpagina' value=$idpagina>";
echo "\n<p align='center'><input type='submit' value='Aggiorna'><br>";
// vedo queli sono gli ultimi capitoli
$qq = $db->query("SELECT tag,capitoli.ordine FROM capitoli JOIN capitolitipi on capitoli.idcapitolotipo=capitolitipi.idcapitolotipo WHERE idpagina='$idpagina'
             ORDER BY capitoli.idcapitolotipo, capitoli.ordine DESC");
$last = '**';
while ($rr = $qq->fetch_array()){
	if ($last != $rr['tag']) {
		echo "$rr[tag]: $rr[ordine]&nbsp;&nbsp;&nbsp;";
	}
	$last = $rr['tag'];
}
echo "</p>";
echo "\n<table border='0'>";
echo "\n<tr><th>tipo, ordine, taginclude</th><th>testo</th><th>delete</th></tr>";

// nuovo record
echo "\n<tr>";
echo "\n<td valign='top'><select name='idcapitolotipo'>";
$qq = $db->query("SELECT idcapitolotipo,intestazione FROM capitolitipi ORDER BY intestazione");
echo "<option value='0'>Seleziona un tipo</option>";
while ($rr = $qq->fetch_array()) {
	echo "<option value='$rr[idcapitolotipo]'>$rr[intestazione]</option>";
}
echo "\n</select><br>";
echo "\n<input type='text' size='5' maxlength='6' name='ordine'><br>";
echo "\n<input type='text' size='20' maxlength='250' name='taginclude'></td>";
echo "\n<td><textarea rows='7' cols='120' name='testo'></textarea></td>";
echo "\n<td align='center' valign='top'>Nuovo</td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$id = $r['idcapitolo'];
	echo "\n<tr>";
	echo "\n<td valign='top'><input type='hidden' name='p[$id][idcapitolotipo]' value='$r[idcapitolotipo]'><b>$r[intestazione]</b><br>";
	$x = normalizzaform($r['ordine']);
	echo "\n<input type='text' size='5' maxlength='6' name='p[$id][ordine]' value=\"$x\"><br>";
	$x = normalizzaform($r['taginclude']);
	echo "\n<input type='text' size='20' maxlength='250' name='p[$id][taginclude]' value=\"$x\"></td>";
	$x = normalizzaform($r['testo']);
	echo "\n<td><textarea rows='5' cols='120' name='p[$id][testo]'>$x</textarea></td>";
	echo "\n<td align='center' valign='top'><input type='checkbox' name='p[$id][xxx]'></td>";
	echo "\n</tr>";
}

echo "</table><p align='center'><input type='submit' value='Aggiorna'></p></form>";

include ('palette.php');

echo "\n</body></html>";

### END OF FILE ###
