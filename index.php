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
 * Entry point della procedura di visualizzazione.
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
 * 20160522: GitHub, setup.ini
 *
 */
 
// serve per impedire abusi
define('HYPERTREK', true);

// variabili di sessione 
session_start();

// misura di sicurezza, probabilmente poco utile, ma non si sa mai
@ini_set( 'allow_url_fopen', 0 );

//
// setup e varie cose globali
//

// leggo alcuni parametri da setup.ini
$a = parse_ini_file('setup.ini', true);
$Setup['db_ro_host']     = $a['sql']['host1'];       // host db readonly
$Setup['db_ro_user']     = $a['sql']['user1'];       // utente db readonly
$Setup['db_ro_pass']     = $a['sql']['password1'];   // password db readonly
$Setup['db_ro_database'] = $a['sql']['database1'];   // database readonly
$Setup['db_rw_host']     = $a['sql']['host2'];       // host db readwrite
$Setup['db_rw_user']     = $a['sql']['user2'];       // utente db readwrite
$Setup['db_rw_pass']     = $a['sql']['password2'];   // password db readwrite
$Setup['db_rw_database'] = $a['sql']['database2'];   // database readonly
$Setup['baseurl']        = $a['setup']['baseurl'];   // URL di base del sito senza slash finale

// e gli altri sono hardcoded
$Setup['versione']        = '3.0.0';                  // versione del software
$Setup['idpaginadefault'] = 10;                       // id della pagina di default
$Setup['favicon']         = '/static/favicon.ico';    // posizione della favicon
$Setup['charset']         = 'UTF-8';                  // definizione del charset
$Setup['titolo']          = 'HyperTrek | ';           // titolo HTML di default
$Setup['pathimmagini']    = 'immagini/';              // path delle immagini
$Setup['pathicone']       = '/icone/';                // path delle icone
$Setup['cssfile']         = 'hypertrek.css';          // foglio stile da utilizzare (si trova nella cartella della skin)
$Setup['docroot']         = '/';                      // root del sito
$Setup['durata']          = 86400;                    // numero di secondi di durata della pagina (24 ore)
$Setup['quantitopmenu']   = 25;                       // numero di icone del topmenu	
$Setup['giornilast']      = 864000;                   // numero di secondi di vecchiaia della pagina perche' compaia nelle ultime modifiche (10g)
$Setup['rsslast']         = 'ultimemodifiche.xml';    // nome del file con il feed RSS delle ultime modifiche
$Setup['idcampoflotta']   = 92;                       // id della tabella episodicampi del record che indica l'appartenenza ad una flotta di un'astronave
$Setup['idcampoclasse']   = 93;                       // id della tabella episodicampi del record che indica l'appartenenza ad una classe di un'astronave
$Setup['idcampoassegna']  = 85;                       // id della tabella episodicampi del record che indica l'assegnamento ad un'astronave; utilizzato per calcolare automaticamente l'appartenenza ad un'astronave
$Setup['idcampospecie']   = 84;                       // id della tabella episodicampi del record che indica la specie di appartenenza di un personaggio; utilizzato per calcolare automaticamente l'appartenenza alla specie e, quindi, il link nella pagina della specie
$Setup['idcampoorg']      = 91;                       // id della tabella episodicampi del record che indica l'organizzazione di appartenenza di un personaggio; utilizzato per calcolare automaticamente l'appartenenza all'organizzazione e, quindi, il link nella pagina della specie
$Setup['idsezionebase']   = 202;                      // id della sezione delle basi stellari; utilizzato per visualizzare automaticamente il personale della base
$Setup['cloudhm']         = 100;                      // quanti elementi devono essere visualizzati nella tag cloud
$Setup['cloudpxmin']      = 15;                       // dimensione minima in pixel del font nella tag cloud
$Setup['cloudpxmax']      = 40;                       // dimensione massima in pixel del font nella tag cloud
$Setup['sitemapname']     = 'sitemapinfo.xml';        // nome del file del sitemap
$Setup['crondailyfile']   = 'cron.daily';             // file di semaforo per eseguire le operazioni giornaliere

// tipi delle sezioni
$tipopagina = array();
$tipopagina['generica']        =  0;   // pagina di testo generica
$tipopagina['episodio']        =  1;   // episodio
$tipopagina['pianeta']         =  2;   // pianeta
$tipopagina['errore404']       =  3;   // pagina da visualizzare per l'errore 404
$tipopagina['specie']          =  4;   // specie
$tipopagina['personaggio']     =  5;   // personaggio
$tipopagina['quantevolte']     =  6;   // quante volte...
$tipopagina['cast']            =  7;   // cast regolare di una serie
$tipopagina['recurring']       =  8;   // personaggi ricorrenti
$tipopagina['tabellariass']    =  9;   // tabella riassuntiva degli episodi
$tipopagina['libro']           = 10;   // libro
$tipopagina['albero']          = 11;   // albero di navigazione
$tipopagina['attore']          = 12;   // attore
$tipopagina['astronave']       = 13;   // astronave
$tipopagina['timelinest']      = 14;   // timeline di Star Trek
$tipopagina['statistiche']     = 15;   // pagina speciale delle statistiche
$tipopagina['copertina']       = 16;   // copertina
$tipopagina['elencopuntato']   = 17;   // pagina formata da un solo elenco puntato
$tipopagina['eventitrek']      = 18;   // eventi Trek
$tipopagina['organizzazioni']  = 19;   // organizzazioni
$tipopagina['log']             = 20;   // log modifiche
$tipopagina['astronaveclasse'] = 21;   // classe di astronavi
$tipopagina['astronaveflotta'] = 22;   // flotta di astronavi

// topmenu
// velocizza la visualizzazione del menu, che tanto non cambia spesso
// la query per ottenere questa tabella e'
// SELECT idimmaginemenu,indextag FROM sezioni WHERE istopmenu=1 ORDER BY sordine;
$TopMenu[1]['file']  = 'menu-ent.png';            $TopMenu[1]['tag']  = 'topent';            $TopMenu[1]['desc']  = 'Enterprise';
$TopMenu[2]['file']  = 'menu-tos.png';            $TopMenu[2]['tag']  = 'toptos';            $TopMenu[2]['desc']  = 'Serie Classica';
$TopMenu[3]['file']  = 'menu-tas.png';            $TopMenu[3]['tag']  = 'toptas';            $TopMenu[3]['desc']  = 'Serie Animata';
$TopMenu[4]['file']  = 'menu-phase2.png';         $TopMenu[4]['tag']  = 'topphase2';         $TopMenu[4]['desc']  = 'Phase II';
$TopMenu[5]['file']  = 'menu-tng.png';            $TopMenu[5]['tag']  = 'toptng';            $TopMenu[5]['desc']  = 'The Next Generation';
$TopMenu[6]['file']  = 'menu-dsn.png';            $TopMenu[6]['tag']  = 'topdsn';            $TopMenu[6]['desc']  = 'Deep Space Nine';
$TopMenu[7]['file']  = 'menu-voy.png';            $TopMenu[7]['tag']  = 'topvoy';            $TopMenu[7]['desc']  = 'Voyager';
$TopMenu[8]['file']  = 'menu-film.png';           $TopMenu[8]['tag']  = 'topfilm';           $TopMenu[8]['desc']  = 'Film';
$TopMenu[9]['file']  = 'menu-libri.png';          $TopMenu[9]['tag']  = 'toplibri';          $TopMenu[9]['desc']  = 'Libri';
$TopMenu[10]['file'] = 'menu-timeline.png';       $TopMenu[10]['tag'] = 'toptimelinest';     $TopMenu[10]['desc'] = 'Timeline di Star Trek';
$TopMenu[11]['file'] = 'menu-personaggi.png';     $TopMenu[11]['tag'] = 'toppersonaggi';     $TopMenu[11]['desc'] = 'Personaggi';
$TopMenu[12]['file'] = 'menu-specie.png';         $TopMenu[12]['tag'] = 'topspecie';         $TopMenu[12]['desc'] = 'Specie';
$TopMenu[13]['file'] = 'menu-organizzazioni.png'; $TopMenu[13]['tag'] = 'toporganizzazioni'; $TopMenu[13]['desc'] = 'Organizzazioni';
$TopMenu[14]['file'] = 'menu-tech.png';           $TopMenu[14]['tag'] = 'toptech';           $TopMenu[14]['desc'] = 'Sezione Tecnica';
$TopMenu[15]['file'] = 'menu-pianeti.png';        $TopMenu[15]['tag'] = 'toppianeti';        $TopMenu[15]['desc'] = 'Pianeti';
$TopMenu[16]['file'] = 'menu-ufp.png';            $TopMenu[16]['tag'] = 'topufp';            $TopMenu[16]['desc'] = 'Federazione';
$TopMenu[17]['file'] = 'menu-starfleet.png';      $TopMenu[17]['tag'] = 'topstarfleet';      $TopMenu[17]['desc'] = 'Flotta Stellare';
$TopMenu[18]['file'] = 'menu-astronavi.png';      $TopMenu[18]['tag'] = 'topastronavi';      $TopMenu[18]['desc'] = 'Astronavi';
$TopMenu[19]['file'] = 'menu-navigazione.png';    $TopMenu[19]['tag'] = 'topnavigazione';    $TopMenu[19]['desc'] = 'Navigazione';
$TopMenu[20]['file'] = 'menu-med.png';            $TopMenu[20]['tag'] = 'topmed';            $TopMenu[20]['desc'] = 'Sezione Medica';
$TopMenu[21]['file'] = 'menu-reboot.png';         $TopMenu[21]['tag'] = 'topreboot';         $TopMenu[21]['desc'] = 'Rebooted universe';
$TopMenu[22]['file'] = 'menu-mirror.png';         $TopMenu[22]['tag'] = 'topmirror';         $TopMenu[22]['desc'] = 'Universo dello Specchio';
$TopMenu[23]['file'] = 'menu-xr.png';             $TopMenu[23]['tag'] = 'topxr';             $TopMenu[23]['desc'] = 'Cast';
$TopMenu[24]['file'] = 'menu-timelinereal.png';   $TopMenu[24]['tag'] = 'topeventitrek';     $TopMenu[24]['desc'] = 'Eventi Trek';
$TopMenu[25]['file'] = 'menu-etc.png';            $TopMenu[25]['tag'] = 'topetc';            $TopMenu[25]['desc'] = 'Varie';
//$TopMenu[26]['file'] = 'menu-indice.png';         $TopMenu[26]['tag'] = 'topindice';         $TopMenu[26]['desc'] = 'Indice Analitico';

// gestione menu
require('include/menu.php');

// xajax
require('xajax_core/xajax.inc.php');
$xajax = new xajax();
$xajax->registerFunction("AJAXmenu");
$xajax->registerFunction("AJAXcerca");

// connessioni con le risorse dati
$ht_db_ro = new mysqli($Setup['db_ro_host'], $Setup['db_ro_user'], $Setup['db_ro_pass'], $Setup['db_ro_database']);
if (mysqli_connect_errno()) {
   echo "<html><head><title>HyperTrek</title><meta http-equiv='refresh' content='60'></head><body>Il sito &egrave; temporaneamente in manutenzione.</body></html>\n";
   die();
}
$ht_db_ro->set_charset('utf8');
$ht_db_rw = new mysqli($Setup['db_rw_host'], $Setup['db_rw_user'], $Setup['db_rw_pass'], $Setup['db_rw_database']);
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
	fwrite ($fhinfo, "<loc>" . $Setup['baseurl'] . "/</loc>\n");
	fwrite ($fhinfo, "<lastmod>" . date('Y-m-d') . "</lastmod>\n");
	fwrite ($fhinfo, "<changefreq>daily</changefreq>\n");
	fwrite ($fhinfo, "</url>\n");
	// pagine
	$q = $ht_db_ro->query("SELECT tag,lastmod FROM pagine WHERE hidden='0'");
	while ($r = $q->fetch_array()) {
		fwrite ($fhinfo, "<url>\n");
		fwrite ($fhinfo, "<loc>" . $Setup['baseurl'] . "/index.php/$r[tag]</loc>\n");
		fwrite ($fhinfo, "<lastmod>" . date('Y-m-d',$r['lastmod']) . "</lastmod>\n");
		fwrite ($fhinfo, "</url>\n");
	}
	// timeline
	$q = $ht_db_ro->query("SELECT DISTINCT anno FROM timelinest");
	while ($r = mysqli_fetch_array($q)) {
		$anno = $r['anno'];
		$rr = $ht_db_ro->query("SELECT lastmod FROM timelinest WHERE anno='$anno' ORDER BY lastmod DESC LIMIT 1")->fetch_array();
		fwrite ($fhinfo, "<url>\n");
		fwrite ($fhinfo, "<loc>" . $Setup['baseurl'] . "/index.php/ttt$anno</loc>\n");
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
		header("Location: " . $Setup['baseurl'] . "/index.php/$r[a]");
	} else {
		// posso visualizzare
		$oPagina = new ht_pagina($tag, $uri, $ref);
		$oPagina->scrivi();
	}
} else {
	// sono stato chiamato con il .ORG, faccio capire al browser che bisogna usare il .INFO
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: " . $Setup['baseurl'] . "/index.php/$tag");
}

### END OF FILE ###