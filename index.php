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
#
# Thanks to MediaWiki for a few lines of code and some ideas:
# Copyright (C) 2003 Brion Vibber <brion@pobox.com>
# http://www.mediawiki.org/

/**
 * Pagina principale
 *
 * Entry point della procedura di visualizzazione.<br>
 * Definisce HYPERTREK che consente di proteggere gli altri file da esecuzioni non volute.
 * Viene analizzato l'URL, sanificato e scomposto nelle varie parti per passare
 * i dati all'oggetto di costruzione della pagina.
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20091122: aggiunta definizione charset UTF8 per il dialogo con MySQL
 * 20100131: nuova struttura dei menu
 * 20120818: aggiunta funzione di cron.daily
 * 20151225: supporto https
 * 20160522: GitHub
 *
 */
 
// serve per impedire abusi
define('HYPERTREK', true);

// variabili di sessione 
session_start();

// misura di sicurezza, probabilmente poco utile, ma non si sa mai
@ini_set( 'allow_url_fopen', 0 );

// Variabili e definizioni globali
require('include/global.php');
require('include/menu.php');

// Dati di accesso al database
require('include/database.php');

// xajax
require('xajax_core/xajax.inc.php');
$xajax = new xajax();
$xajax->registerFunction("AJAXmenu");
$xajax->registerFunction("AJAXcerca");

// connessioni con le risorse dati
$ht_db_ro = new mysqli($ht_db_ro_host, $ht_db_ro_user, $ht_db_ro_pass, $ht_db_ro_database);
if (mysqli_connect_errno()) {
   echo "<html><head><title>HyperTrek</title><meta http-equiv='refresh' content='60'></head><body>Il sito &egrave; temporaneamente in manutenzione.</body></html>\n";
   die();
}
$ht_db_ro->set_charset('utf8');
$ht_db_rw = new mysqli($ht_db_rw_host, $ht_db_rw_user, $ht_db_rw_pass, $ht_db_rw_database);
if (mysqli_connect_errno()) {
   echo "<html><head><title>HyperTrek</title><meta http-equiv='refresh' content='60'></head><body>Il sito &egrave; temporaneamente in manutenzione.</body></html>\n";
   die();
}
$ht_db_rw->set_charset('utf8');

// cron.daily
$crondaily = FALSE;
if (file_exists($Setup['crondailyfile'])) {
	$oggi = date('d/m/Y');
	$mtime = date('d/m/Y', filemtime($Setup['crondailyfile']));
	if ($oggi != $mtime) $crondaily = TRUE;
} else {
	$crondaily = TRUE;
}
if ($crondaily) {
	// crea il sitemap per Gooooogle
	$fhinfo = fopen($Setup['sitemapname'], 'w');
	fwrite ($fhinfo, "<?xml version='1.0' encoding='UTF-8'?>\n");
	fwrite ($fhinfo, "<urlset xmlns='http://www.google.com/schemas/sitemap/0.84'>\n");
	fwrite ($fhinfo, "<url>\n");
	fwrite ($fhinfo, "<loc>https://hypertrek.info/</loc>\n");
	fwrite ($fhinfo, "<lastmod>" . date('Y-m-d') . "</lastmod>\n");
	fwrite ($fhinfo, "<changefreq>daily</changefreq>\n");
	fwrite ($fhinfo, "</url>\n");
	// pagine
	$q = $ht_db_ro->query("SELECT tag,lastmod FROM pagine WHERE hidden='0'");
	while ($r = $q->fetch_array()) {
		fwrite ($fhinfo, "<url>\n");
		fwrite ($fhinfo, "<loc>https://hypertrek.info/index.php/$r[tag]</loc>\n");
		fwrite ($fhinfo, "<lastmod>" . date('Y-m-d',$r['lastmod']) . "</lastmod>\n");
		fwrite ($fhinfo, "</url>\n");
	}
	// timeline
	$q = $ht_db_ro->query("SELECT DISTINCT anno FROM timelinest");
	while ($r = mysqli_fetch_array($q)) {
		$anno = $r['anno'];
		$rr = $ht_db_ro->query("SELECT lastmod FROM timelinest WHERE anno='$anno' ORDER BY lastmod DESC LIMIT 1")->fetch_array();
		fwrite ($fhinfo, "<url>\n");
		fwrite ($fhinfo, "<loc>https://hypertrek.info/index.php/ttt$anno</loc>\n");
		fwrite ($fhinfo, "<lastmod>" . date('Y-m-d',$rr['lastmod']) . "</lastmod>\n");
		fwrite ($fhinfo, "</url>\n");
	}
	fwrite ($fhinfo, "</urlset>\n");
	fclose($fhinfo);
	touch($Setup['crondailyfile']);
}

// Oggetto che genera la pagina
require('include/output.php');

// id dell'indice da visualizzare la ficco nella variabile superglobal $_SESSION e non se ne parla piu'
if (isset($_SESSION['ndx']) and is_numeric($_SESSION['ndx'])) {
	// nulla
} else {
	$_SESSION['ndx'] = 0;
}

// skin da utilizzare, anche questa nella $_SESSION e via
if (isset($_SESSION['skin'])) {
	$skin = strtolower(trim(substr($_SESSION['skin'], 0, 20)));
} else {
	//se non e' settata prendo quella di default
	$q = $ht_db_ro->query("SELECT dir FROM skin WHERE isdefault='1'");
	// ci dovrebbe essere solamente un record flaggato default
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		$skin = $r['dir'];
	} else {
		// non e' una bella cosa se nessun record e' flaggato default!
		$skin = 'standard';
	}
}
// se mi viene richiesto un cambio di skin
if (isset($_POST['skin'])) {
	$sk = strtolower(trim($ht_db_ro->escape_string(substr($_POST['skin'], 0, 20))));
	// dopo aver sanificato la stringa verifico che esista davvero
	$q = $ht_db_ro->query("SELECT dir FROM skin WHERE dir='$sk'");
	if ($q->num_rows > 0) {
		$skin = $sk;
	}
}
$_SESSION['skin'] = $skin;

// i valori vengono troncati per sicurezza. 
// il limite impostato corrisponde alla dimensione massima dei campi relativi nel database
// normalizzo pathinfo
if (isset($_SERVER['PATH_INFO'])) {
	$tag = strtolower(trim(substr($_SERVER['PATH_INFO'], 0, 70)));
} else {
	$tag = '';
}
// e toglo pure il primo carattere che e' uno slash
$tag = substr($tag, 1);
$uri = $ht_db_rw->escape_string($_SERVER['REQUEST_URI']);
if (isset($_SERVER['HTTP_REFERER'])) {
	$ref = $ht_db_rw->escape_string($_SERVER['HTTP_REFERER']);
} else {
	$ref = '';
}

// se il tag e' copertina, resetto l'indice e metto come pagina il main
if ('copertina' == $tag) {
	$_SESSION['ndx'] = 0;
	$tag = 'main';
}

// vedo se si applica la ridirezione, altrimenti visualizzo la pagina
if (stristr($_SERVER['SERVER_NAME'], 'hypertrek.org') === FALSE) {
	// non sono stato chiamato con il .org
	$pathquery = $ht_db_ro->escape_string($tag);
	$q = $ht_db_ro->query("SELECT * FROM ridirezioni WHERE da='$pathquery'");
	if ($q->num_rows > 0) {
		//ridirigo ad altra pagina
		$r = $q->fetch_array();
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: https://hypertrek.info/index.php/$r[a]");
	} else {
		// posso visualizzare
		$oPagina = new ht_pagina($tag, $uri, $ref);
		$oPagina->scrivi();
	}
} else {
	// sono stato chiamato con il .ORG, faccio capire al browser che bisogna usare il .INFO
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://hypertrek.info/index.php/$tag");
}

### END OF FILE ###