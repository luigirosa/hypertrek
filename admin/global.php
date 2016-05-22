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
 * Funzioni e valori comuni
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090301: prima versione
 * 20091122: sistemata l'intestazione
 * 20100131: nuova struttura del menu, rimozione indice analitico
 * 20160522: GitHub
 * 
 */

// leggo alcuni parametri da setup.ini
$a = parse_ini_file('setup.ini', true);
$Setup['db_ro_host']     = $a['sql']['host1'];           // host db readonly
$Setup['db_ro_user']     = $a['sql']['user1'];           // utente db readonly
$Setup['db_ro_pass']     = $a['sql']['password1'];       // password db readonly
$Setup['db_ro_database'] = $a['sql']['database1'];       // database readonly
$Setup['db_rw_host']     = $a['sql']['host2'];           // host db readwrite
$Setup['db_rw_user']     = $a['sql']['user2'];           // utente db readwrite
$Setup['db_rw_pass']     = $a['sql']['password2'];       // password db readwrite
$Setup['db_rw_database'] = $a['sql']['database2'];       // database readonly
$Setup['descrizione']    = $a['sql']['descrizione'];     // descrizione della connessione

// collegamento al DB
$my_desc = $Setup['descrizione'];
$db = new mysqli($Setup['db_ro_host'], $Setup['db_ro_user'], $Setup['db_ro_pass'], $Setup['db_ro_database']);
if (mysqli_connect_errno()) {
	echo "\nErrore creazione oggetto database: " . mysqli_connect_error();
	die();
}
$db->set_charset('utf8');
$db2 = new mysqli($Setup['db_rw_host'], $Setup['db_rw_user'], $Setup['db_rw_pass'], $Setup['db_rw_database']);
if (mysqli_connect_errno()) {
	echo "\nErrore creazione oggetto database2: " . mysqli_connect_error();
	die();
}
$db->set_charset('utf8');

set_time_limit(0);

// misura di sicurezza, probabilmente poco utile, ma non si sa mai
@ini_set( 'allow_url_fopen', 0 );
header("Content-Type: text/html; charset=UTF-8");


/**
 * normalizzaform($testo)
 * 
 * Normalizza un testo per il form
 * 
 */
function normalizzaform($testo) {
	$retval = str_replace('"', '&quot;', $testo);
	return ($retval);
}	


/**
 * toccapagina($id)
 * 
 * Aggiorna il timestamp con la data di ultima modifica alla pagina
 *
 * @global object $db database mysql
 * 
 * 20090301 prima versione
 *
 */
function toccapagina($id) {
	global $db;
	$db->query("UPDATE pagine SET lastmod='" . time() . "' WHERE idpagina='$id'");
}	


/**
 * loggamodifica($id,$testo,$replace)
 * 
 * Aggiorna il log delle modifiche
 * 
 * @global object $db database mysql
 *
 * 20090301 prima versione
 *
 */
function loggamodifica($id, $testo, $replace=FALSE) {
	global $db;
	$testo = $db->escape_string($testo);
	$r = $db->query("SELECT * FROM modifiche ORDER BY idmodifica DESC LIMIT 1")->fetch_array();
	$adesso = time();
	if ($r['idpagina'] == $id) {
		if ($replace) {
			$db->query("UPDATE modifiche SET dataora='$adesso',modifica='$testo' WHERE idmodifica='$r[idmodifica]'");
		} else {
			if(stristr($db->escape_string($r['modifica']), $testo) === FALSE) {
				$testo = $db->escape_string($r['modifica']) . "; $testo";
				$db->query("UPDATE modifiche SET dataora='$adesso',modifica='$testo' WHERE idmodifica='$r[idmodifica]'");
			}
		}
	} else {
		$db->query("INSERT INTO modifiche SET dataora='$adesso',modifica='$testo',idpagina='$id'");
	}
}	


/**
 * mostramenu($idpagina)
 * 
 * Mostra il menu
 * 
 * 20100109: prima versione
 * 20100411: aggiunto link {} nell'intestazione
 * 
 */
function mostramenu($idpagina) {
	global $db;
	$largh = "width='10%'";
	echo "\n<table border='0' width='100%'>";
	echo "\n<tr>";
	$r = $db->query("SELECT titolo,tag FROM pagine WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td colspan='10' align='center'>$r[tag] - <b>$r[titolo]</b> - $idpagina - " . '{' . $r['titolo'] . ' | ' . $r['tag'] . "}</td>";
	echo "\n</tr>";
	echo "\n<tr>";
	echo "\n<td><a target='main' href='tab_pagine.php?idpagina=$idpagina'>Titolo</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM testi WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_testi.php?idpagina=$idpagina'>Testi standard ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM capitoli WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_capitoli.php?idpagina=$idpagina'>Capitoli ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM episodivalori WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_episodivalori.php?idpagina=$idpagina'>Tabella di sx ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM guest WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_guest.php?idpagina=$idpagina'>Guest star ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM immaginipagine WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_immaginipagine.php?idpagina=$idpagina'>Immagini ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM riferimenti WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_riferimenti.php?idpagina=$idpagina&edit=1'>Riferimenti ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM quantevolte WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_quantevolte.php?idpagina=$idpagina'>Quante volte ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM piepagina WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_piepagina.php?idpagina=$idpagina'>Pi&eacute; di pagina ($r[0])</a></td>";
	$r = $db->query("SELECT COUNT(*) FROM paginemenu WHERE idpagina='$idpagina'")->fetch_array();
	echo "\n<td><a target='main' href='tab_paginemenu.php?idpagina=$idpagina'>Menu ($r[0])</a></td>";
	echo "\n</tr>";
	echo "\n</table>";
}

/**
 * intestazione()
 * 
 * Mostra l'intestazione
 * 
 * 20100109: prima versione
 * 
 */
function intestazione() {
	echo '<!doctype HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	echo '<html lang="it"><head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	echo '<link rel="STYLESHEET" href="admin.css" type="text/css">';
	echo '</head><body>';
}

### END OF FILE ###