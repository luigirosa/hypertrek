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
 * Include con la classe pagina
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20160522: GitHub
 */
 
//protezione
if(!defined('HYPERTREK')) {
	header ('Location: https://hypertrek.info/');
	die();
}

/**
 * Classe che visualizza la pagina online
 *
 * La classe pagina contiene il condice per la visualizzazione
 * online delle pagine di HT.<br>
 * Il costruttore dell'oggetto inizializza le variabile, mentre
 * l'unico metodo esportato, scrivi, visualizza la pagina.
 *
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20100131: nuova struttura dei menu
 * 20160522: GitHub
 * 
 */

class ht_pagina {
	private $aPagina;      // array con i campi della tabella pagine
	private $aRifTitolo;   // Titolo riferimenti all'interno della pagina
	private $aRifTesto;    // Testo riferimenti all'interno della pagina
	private $aSezione;     // array con i campi della tabella sezioni
	private $aSetup;       // array con i dati di setup
	private $idpagina;     // id SQL della pagina o anno se la pagina e' della timeline, utilizzare SOLAMENTE in fase di inizializzazione
	private $rwdb;         // database read/write
	private $rquri;        // Request URI, serve per loggarlo in caso di bad request
	private $ref;          // Referrer, serve per loggarlo in caso di bad request
	private $motd;         // messaggio del giorno da visualizzare in basso a destra
	private $dbro;         // database read-only
	private $dbrw;         // database read-write
	private $istimelinest; // flag che mi dice se la pagina che devo visualizzate e' della timeline di star trek
	private $iseventitrek; // flag che mi dice se la pagina che devo visualizzate e' della timeline degli eventi trek
	private $skin;         // path relativo alla root della skin corrente

	/**
	 * Costruttore
	 * 
	 * Inizializzaione delle variabili interne all'oggetto e 
	 * decisione su quale sia la pagina da visualizzare in base
	 * ai parametri passati.
	 * 
	 * @param string $tag tag della pagina
	 * @param string $ruri usato per loggare eventuali errori
	 * @param string $rref usato per loggare eventuali errori
	 * @global string $path_stili path dei fogli stile
	 * @global string $path_icone path delle icone
	 * @global resource $ht_db_rw handle della risorsa del database MySQL abilitato alla scrittura
	 * @global resource $ht_db_ro handle della risorsa del database MySQL read-only
	 * @global array $Setup array con le varie voci di configurazione del programma
	 * @global object $xajax oggetto xajax
	 *
	 * 20100131: nuova struttura dei menu
	 *
	 */
	function __construct($tag, $ruri, $rref) {
		global $path_stili, $path_icone, $ht_db_ro, $ht_db_rw, $Setup, $TopMenu, $xajax;
		$this->aSetup       = $Setup;
		$this->aTopMenu     = $TopMenu;
		$this->aRifTitolo   = array();
		$this->aRifTesto    = array();
		$this->rquri        = $ruri;
		$this->ref          = $rref;
		$this->motd         = '';
		$this->dbro         = $ht_db_ro;
		$this->dbrw         = $ht_db_rw;
		$this->istimelinest = FALSE;
		$this->iseventitrek = FALSE;
		$this->ajax         = $xajax;
		$this->skin         = $this->aSetup['docroot'] . 'skin/' . $_SESSION['skin'] . '/';
		// iniziamo con i default
		$this->idpagina = $this->aSetup['idpaginadefault'];
		// se mi e' stato passato un tag
		if('' != $tag) {
			// se e' un tag della timeline, verifico se esiste l'anno, non la pagina
			if ('ttt' == substr($tag,0 , 3)) {
				$anno = substr($tag, 3);
				$qr = $this->dbro->query("SELECT anno FROM timelinest WHERE anno='$anno'");
				if ($qr->num_rows > 0) {
					$this->istimelinest = TRUE;
					$this->idpagina = $anno;
				} else {
					$this->loggaBad('TST ' . $tag);
				}
				$this->aggiornaStatistiche($anno, TRUE);
			} elseif ('tte' == substr($tag,0 , 3)) {
				$anno = substr($tag, 3);
				$qr = $this->dbro->query("SELECT anno FROM eventitrek WHERE anno='$anno'");
				if ($qr->num_rows > 0) {
					$this->iseventitrek = TRUE;
					$this->idpagina = $anno;
				} else {
					$this->loggaBad('TET ' . $tag);
				}
				$this->aggiornaStatistiche($anno, TRUE);
			} else {
				$qr = $this->dbro->query("SELECT idpagina FROM pagine WHERE tag='$tag'");
				if ($qr->num_rows > 0) {
					$r = $qr->fetch_array();
					$this->idpagina = $r[0];
				} else {
					$this->loggaBad('TAG ' . $tag);
				}
				$this->aggiornaStatistiche($this->idpagina, FALSE);
			}
		}
		// vedo se e' il caso di aggiornare le ultime modifiche
		$this->aggiornaUltimi();
	}


	/**
	 * scrivi()
	 * 
	 * Funzione di output HTML via http - Invia anche alcuni header http
	 *
	 * @global array $tipopagina tipi di pagina
	 * 
	 * 20091226: spostato il titolo originale dal pie' dipagina a sotto al titolo
	 * 20100131: nuova struttura dei menu
	 * 20101121: corretto il link a memory alpha
	 * 20110214: tolti i dannati DIV e rimessa la tabella
	 * 20111105: aggiunto ALT alla dima
	 * 20111105: adattati i tag alle specifiche XHTML
	 * 20111105: corretto tag chiusura TD blocconavigazione
	 * 20151225: supporto https
	 * 20160522: GitHub
 	 * 
	 */
	function scrivi() {
		global $tipopagina;
		// vedo se la pagina e' della timeline
		if ($this->istimelinest) {
			$aAnno = $this->dbro->query("SELECT * FROM timelinest WHERE anno=$this->idpagina ORDER BY lastmod DESC LIMIT 1")->fetch_array();
			$this->aPagina['idpagina'] = $aAnno['anno'];
			$this->aPagina['tag'] = 'ttt' . $aAnno['anno'];
			$this->aPagina['lastmod'] = $aAnno['lastmod'];
			$this->aPagina['idsezione'] = 25;
			$this->aPagina['titolo'] = $aAnno['annodisplay'];
			$this->aPagina['tipo'] = $tipopagina['timelinest'];
			$this->aPagina['hidden'] = 0;
			// per evitare warning
			$this->aPagina['stagione'] = '';
			$this->aPagina['originale'] = '';
			// cerco l'anno precedente
			$q = $this->dbro->query("SELECT anno FROM timelinest WHERE anno<" . $this->idpagina . " ORDER BY anno DESC LIMIT 1");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$this->aPagina['tagprima'] = 'ttt' . $r['anno'];
			}
			// cerco l'anno successivo
			$q = $this->dbro->query("SELECT anno FROM timelinest WHERE anno>" . $this->idpagina . " ORDER BY anno LIMIT 1");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$this->aPagina['tagdopo'] = 'ttt' . $r['anno'];
			}
		} elseif ($this->iseventitrek) {
			$aAnno = $this->dbro->query("SELECT * FROM eventitrek WHERE anno=$this->idpagina ORDER BY lastmod DESC LIMIT 1")->fetch_array();
			$this->aPagina['idpagina'] = $aAnno['anno'];
			$this->aPagina['tag'] = 'tte' . $aAnno['anno'];
			$this->aPagina['lastmod'] = $aAnno['lastmod'];
			$this->aPagina['idsezione'] = 402;
			$this->aPagina['titolo'] = $aAnno['annodisplay'];
			$this->aPagina['tipo'] = $tipopagina['eventitrek'];
			$this->aPagina['hidden'] = 0;
			// per evitare warning
			$this->aPagina['stagione'] = '';
			$this->aPagina['originale'] = '';
			// cerco l'anno precedente
			$q = $this->dbro->query("SELECT anno FROM eventitrek WHERE anno<" . $this->idpagina . " ORDER BY anno DESC LIMIT 1");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$this->aPagina['tagprima'] = 'tte' . $r['anno'];
			}
			// cerco l'anno successivo
			$q = $this->dbro->query("SELECT anno FROM eventitrek WHERE anno>" . $this->idpagina . " ORDER BY anno LIMIT 1");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$this->aPagina['tagdopo'] = 'tte' . $r['anno'];
			}
		} else {	
			$this->aPagina = $this->dbro->query("SELECT * FROM pagine WHERE idpagina=$this->idpagina")->fetch_array();
			// se il campo IMDb e' valorizzato, aggiungo il riferimento
			if ('' != $this->aPagina['imdb']) {
				$this->aggiungiRiferimento('Link esterni', $this->espandiLink("{IMDb|@". $this->aPagina['imdb'] ."}"));
			}
			// se il campo Memory Alpha e' valorizzato, aggiungo il riferimento
			if ('' != $this->aPagina['memoryalpha']) {
				$this->aggiungiRiferimento('Link esterni', $this->espandiLink("{Memory Alpha|@http://memory-alpha.org/wiki/". $this->aPagina['memoryalpha'] ."}"));
			}
		}
		$this->aSezione = $this->dbro->query("SELECT * FROM sezioni WHERE idsezione=" . $this->aPagina['idsezione'])->fetch_array();
		// header HTTP e HTML
		$this->intestazione();
		echo "\n<body>";
		// anchor per tornare all'inizio
		echo "\n<a name='top'></a>";

		// master table
		echo "\n<table width='100%' border='0' cellpadding='0' cellspacing='0' class='master'>";

		echo "\n<tr>"; // riga con la barra di navigazione
		echo "\n<td colspan='3' align='left' class='blocconavigazione'>";
		// barra di navigazione che sta in una sua tabella a parte
		$this->topMenu();
		echo "\n</td>"; // blocconavigazione
		echo "\n</tr>"; // riga con la barra di navigazione

		echo "\n<tr>"; // riga con indice, testo e link
		
		// colonna di sinistra degli indici
		echo "\n<td width='16%' valign='top' align='left' class='bloccoindice'>";
		// dima per evitare che collassi la tabella
		echo "\n<img src='/static/dima.gif' border='0' align='left' alt='' />";
		echo "\n<div id='indiceintestazione'>";
		echo HT_menu('', $_SESSION['ndx'], 'titolo');
		echo "\n</div>"; // indiceintestazione
		echo "\n<div id='indicetesto'>";
		echo HT_menu('', $_SESSION['ndx'], 'testo');
		echo "</div>"; // indicetesto
		echo "\n</td>"; // colonna indici
		
		// colonna centrale con i dati
		echo "\n<td width='68%' valign='top' align='left' class='bloccodati'>";
		// la copertina non ha barra del titolo
		if ($this->aPagina['tipo'] != $tipopagina['copertina']) {
			// contenitore del titolo, icone sezione ed eventuali pulsanti avanti/indietro
			echo "\n<div class='testotitolo'>";
			// icona della sezione della pagina ed eventuale stagione
			echo $this->iconaSezione();
			// frecce di navigazione, se ci sono
			// va messa prima del titolo per poterla flottare a destra. BAH!
			echo $this->frecceNavigazione();
			// titolo della pagina
			echo "<span class='testotitolotesto'>" . $this->aPagina['titolo'] . "</span>";
			// nome originale inglese della scheda, se presente
			if ('' != $this->aPagina['originale']) {
				echo "<br /><span class='testotitolotestoori'>" . $this->aPagina['originale'] . "</span>";
			}
			echo "\n</div>"; // testotitolo
		}
		// decido la struttura dei dati da visualizzare
		switch ($this->aPagina['tipo']) {
		case $tipopagina['generica']:
			// solo se e' una base stellare calcolo gli assegnamenti
			if ($this->aSetup['idsezionebase'] == $this->aPagina['idsezione']) {
				$this->riferimentiEpisodivalori($this->aSetup['idcampoassegna'], 'Personaggi');
			}
			echo $this->testiStandard(TRUE);
			echo $this->capitoli();
			break;
		case $tipopagina['specie']:
			$this->riferimentiEpisodivalori($this->aSetup['idcampospecie'], 'Personaggi');
			echo $this->testiStandard(TRUE);
			echo $this->capitoli();
			break;
		case $tipopagina['organizzazioni']:
			$this->riferimentiEpisodivalori($this->aSetup['idcampoorg'], 'Personaggi');
			echo $this->testiStandard(TRUE);
			echo $this->capitoli();
			break;
		case $tipopagina['episodio']:
			echo $this->testiStandard();
			echo $this->datiEpisodio();
			echo $this->capitoli();
			break;
		case $tipopagina['timelinest']:
			echo $this->timelineST();
			break;
		case $tipopagina['eventitrek']:
			echo $this->eventiTrek();
			break;
		case $tipopagina['personaggio']:
			$this->calcolaAttoriPersonaggio();
			echo $this->datiEpisodio(TRUE, FALSE);
			echo $this->testiStandard(FALSE);
			$this->calcolaApparizioniPersonaggio();
			echo $this->capitoli();
			break;
		case $tipopagina['quantevolte']:
			echo $this->testiStandard(TRUE);
			echo $this->quanteVolte();
			echo $this->capitoli();
			break;
		case $tipopagina['cast']:
			echo $this->testiStandard();
			break;
		case $tipopagina['tabellariass']:
			break;
		case $tipopagina['libro']:
			echo $this->datiEpisodio(TRUE, FALSE);
			echo $this->testiStandard();
			echo $this->capitoli();
			break;
		case $tipopagina['attore']:
			echo $this->testiStandard(TRUE);
			echo $this->apparizioniCast();
			echo $this->capitoli();
			break;
		case $tipopagina['astronaveflotta']:
			$this->riferimentiEpisodivalori($this->aSetup['idcampoflotta'], 'Astronavi');
			echo $this->testiStandard(TRUE);
			echo $this->capitoli();
			break;
		case $tipopagina['astronaveclasse']:
			$this->riferimentiEpisodivalori($this->aSetup['idcampoclasse'], 'Astronavi');
			echo $this->datiEpisodio(TRUE, FALSE);
			echo $this->testiStandard(FALSE);
			echo $this->capitoli();
			break;
		case $tipopagina['astronave']:
			$this->riferimentiEpisodivalori($this->aSetup['idcampoassegna'], 'Equipaggio');
			echo $this->datiEpisodio(TRUE, FALSE);
			echo $this->testiStandard(FALSE);
			echo $this->capitoli();
			break;
		case $tipopagina['statistiche']:
			echo $this->statisticheDelSito();
			break;
		case $tipopagina['copertina']:
			echo $this->testiStandard();
			$this->ultime10Pagine();
			break;
		case $tipopagina['elencopuntato']:
			echo $this->capitoli('tuttapagina');
			break;
		case $tipopagina['pianeta']:
			echo $this->datiEpisodio(TRUE, FALSE);
			echo $this->testiStandard();
			echo $this->capitoli();
			break;
		case $tipopagina['recurring']:
			echo $this->recurringCharacters();
			break;
		case $tipopagina['errore404']:
			echo $this->testiStandard(TRUE);
			echo $this->capitoli();
			// devo metterlo qui perche' ci sono le cazzo di parentesi degli stili
			echo '<style type="text/css">
				   #goog-wm {margin-top: 2em; margin-left: 5px; margin-right: 5px; border: thin dotted #c00000;}
				   #goog-wm h3.closest-match { }
				   #goog-wm h3.closest-match a { }
				   #goog-wm h3.other-things { }
				   #goog-wm ul li { }
				   #goog-wm li.search-goog { display: block; }
				   </style>
				   <script type="text/javascript">
				   var GOOG_FIXURL_LANG = \'it\';
				   var GOOG_FIXURL_SITE = \'https://hypertrek.info/\';
				   </script>
				   <script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>';
			break;
		case $tipopagina['albero']:
			echo $this->testiStandard(TRUE);
			echo $this->mappaDelSito();
			echo $this->capitoli();
			break;
		case $tipopagina['log']:
			echo $this->testiStandard(TRUE);
			echo $this->logModifiche();
			break;
		}
		echo "\n</td>"; // bloccodati
		
		// link a destra
		echo "\n<td width='16%' valign='top' align='left' class='bloccoriferimenti'>";
		// form di ricerca
		echo "<form id='frmCerca' method='post' action='javascript:void(null);' onsubmit='HTcerca();'><div class='cerca'>";
		echo "<input class='cercatext' type='text' name='cerca' size='10' maxlength='30' /> ";
		echo "<input id='subCerca' class='cercabtn' type='submit' value='Cerca' />";
		echo "</div></form>";
		echo $this->scriviRiferimenti();
		echo "\n<br /><fb:like href=\"https://developers.facebook.com/\" width=\"450\" height=\"80\" />";
		echo "\n</td>"; // bloccoriferimenti

		echo "\n</tr></table>"; //master
		
		// footer
		echo $this->pieDiPagina();

		// chiusura HTML
		echo "\n</body>\n</html>";
	}


	/**
	 * intestazione()
	 * 
	 * Output degli header HTTP e HTML
	 *
	 * 20110313: aggiunto META che disabilita il DNS prefetching (http://siamogeek.com/2011/03/dns-prefetching-una-pessima-idea/)
	 * 20111105: adattati tag alle specifiche XHTML
	 * 20111105: aggiunti i META OpenGraph
	 * 20151225: supporto https
	 *
	 */
	private function intestazione() {
		global $tipopagina;
		// elaboro le richieste xajax
		$this->ajax->processRequest();
		// se e' la pagina speciale per la gestione dell'errore 404, mando un 404 prima di mandare altri header
		if ($this->aPagina['tipo'] == $tipopagina['errore404']) {
			header("HTTP/1.0 404 Not Found");
		}
		header("Content-Type: text/html; charset=" . $this->aSetup['charset']);
		header("Last-Modified: " . date('r',$this->aPagina['lastmod']));
		header("Expires: " . date('r', time() + $this->aSetup['durata']));
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo "\n<html lang='it' xmlns='http://www.w3.org/1999/xhtml' xmlns:og='http://ogp.me/ns#' xmlns:fb='https://www.facebook.com/2008/fbml'>";
		echo "\n<head>";
		echo "\n<title>" . $this->aSetup['titolo'] . strip_tags($this->aSezione['sigla']) . ' | ' . strip_tags($this->aPagina['titolo']) . '</title>';
		echo "\n<link rel='STYLESHEET' href='" . $this->skin . $this->aSetup['cssfile'] . "' type='text/css' />";
		echo "\n<link rel='SHORTCUT ICON' href='" . $this->aSetup['favicon'] . "' />";
		// meta per OpenGraph https://developers.facebook.com/docs/opengraph/
		echo "\n<meta property='og:title' content=\"" . strip_tags($this->aPagina['titolo']) . "\" />";
		echo "\n<meta property='og:type' content='tv_show' />";
		echo "\n<meta property='og:site_name' content='HyperTrek' />";
		echo "\n<meta property='og:locale' content='it_IT' />";
		echo "\n<meta property='fb:admins' content='hypertrek' />";
		echo "\n<meta property='og:image' content='http://ng.hypertrek.info/skin/standard/menu.png' />";
		echo "\n<meta property=\"og:url\" content=\"https://hypertrek.info/index.php/". $this->aPagina['tag'] . "\" />";
		echo "\n<meta http-equiv='Content-Type' content='text/html; charset=" . $this->aSetup['charset'] . "' />";
		echo "\n<meta http-equiv='x-dns-prefetch-control' content='off' />";
		echo "\n<meta name='generator' content='HyperTrek:NG Content Engine v" . $this->aSetup['versione'] . " by Luigi Rosa' />";
		echo "\n<link rel=\"meta\" href=\"https://hypertrek.info/labels.rdf\" type=\"application/rdf+xml\" title=\"ICRA labels\" />";
		echo "\n<meta http-equiv=\"pics-Label\" content='(pics-1.1 \"http://www.icra.org/pics/vocabularyv03/\" l gen true for \"https://hypertrek.info\" r (n 0 s 0 v 0 l 0 oa 0 ob 0 oc 0 od 0 oe 0 of 0 og 0 oh 0 c 0)  gen true for \"https://hypertrek.info\" r (n 0 s 0 v 0 l 0 oa 0 ob 0 oc 0 od 0 oe 0 of 0 og 0 oh 0 c 0) gen true for \"https://hypertrek.ifo\" r (n 0 s 0 v 0 l 0 oa 0 ob 0 oc 0 od 0 oe 0 of 0 og 0 oh 0 c 0)  gen true for \"https://hypertrek.info\" r (n 0 s 0 v 0 l 0 oa 0 ob 0 oc 0 od 0 oe 0 of 0 og 0 oh 0 c 0))' />";
		echo "\n<link rel='alternate' title='HyperTrek: ultime modifiche' href='https://hypertrek.info/" . $this->aSetup['rsslast'] . "' type='application/rss+xml' />";
		//funzioni AJAX
		$this->ajax->printJavascript('/');
		echo "<script type='text/javascript'>
		function HTcerca(){
			xajax.$('subCerca').disabled=true;
			xajax.$('subCerca').value=\"Attendere...\";
			xajax_AJAXcerca(xajax.getFormValues('frmCerca'));
			return false;
		}
		</script>";
		echo "<script type=\"text/javascript\">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-33771780-1']);
  _gaq.push(['_setDomainName', 'hypertrek.info']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>";
		echo "\n</head>";
	}


	/**
	 * topMenu()
	 * 
	 * Output della barra di navigazione
	 *
	 * 20100131: nuova struttura dei menu
	 * 20111105: adattato tag IMG alle specifiche XHTML
	 *
	 */
	private function topMenu() {
		echo "\n<div class='barranavigazionecont'>";
		// icona di apertura a sinistra
		$zz = $this->costruisciURLImmagineSkin('menu.png', 'HyperTrek') . ' />';
		// copertina e' un tag speciale per azzerare la visualizzazione degli indici
		echo "<span class='barranavigazionesx'>" . $this->espandiLink('{' . $zz . '| copertina}') . "</span>";
		// tappo a destra
		// la descrizione e' presa dalla skin
		$r = $this->dbro->query("SELECT autore FROM skin WHERE dir='$_SESSION[skin]'")->fetch_array();
		echo "<span class='barranavigazionedx'>" . $this->costruisciURLImmagineSkin('menu-destra.png', $r['autore']) . " /></span>";
		// icone
		echo "<span class='barranavigazionecn'>";
		for ($n = 1; $n <= $this->aSetup['quantitopmenu']; $n++) {
			$i = $this->costruisciURLImmagineSkin($this->aTopMenu[$n]['file'], $this->aTopMenu[$n]['desc']) . ' />';
			echo $this->espandiLink( '{' . $i . '|:' . $this->aTopMenu[$n]['tag'] . '}');
		}
		echo "</span>";
		echo "</div>";
	}


	/** 
	 * iconaSezione()
	 * 
	 * Icona della sezione ed eventuale icona della stagione.
	 * 
	 * 20100201: rimozione del link
	 * 20111105: adattato tag IMG alle specifiche XHTML
	 * 
	 */
	private function iconaSezione() {
		$b = "\n<div class='testoiconasezione'>";
		// c'e' l'icona della sezione?
		if ('' != trim($this->aSezione['icona'])) {
			$b .= $this->costruisciURLImmagineSkin($this->aSezione['icona'], $this->aSezione['nome']) . ' />';
		}
		// c'e' l'icona della stagione?
		if ('' != $this->aPagina['stagione']) {
			switch ($this->aPagina['stagione']) {
			case '1':
				$icona = 'ico-stagione1.png';
				$desc = 'Prima stagione';
				break;
			case '2':
				$icona = 'ico-stagione2.png';
				$desc = 'Seconda stagione';
				break;
			case '3':
				$icona = 'ico-stagione3.png';
				$desc = 'Terza stagione';
				break;
			case '4':
				$icona = 'ico-stagione4.png';
				$desc = 'Quarta stagione';
				break;
			case '5':
				$icona = 'ico-stagione5.png';
				$desc = 'Quinta stagione';
				break;
			case '6':
				$icona = 'ico-stagione6.png';
				$desc = 'Sesta stagione';
				break;
			case '7':
				$icona = 'ico-stagione7.png';
				$desc = 'Settima stagione';
				break;
			case 'P':
				$icona = 'ico-stagionepilot.png';
				$desc = 'Pilot';
				break;
			}
			$b .= $this->costruisciURLImmagineSkin($icona, $desc). ' />';
		}
		$b .= "\n</div>";
		return $b;
	}

	
	/** 
	 * frecceNavigazione()
	 * 
	 * Frecce di navigazione
	 * 
	 * 20111105: adattato tag IMG alle specifiche XHTML
	 *
	 */
	private function frecceNavigazione() {
		$b = "\n<div class='testofreccenavigazione'>";
		// se e' necessario visualizzare le icone <> di navigazione
		if ('' != $this->aPagina['tagprima'] or '' != $this->aPagina['tagdopo']) {
			// icona episodio precedente
			if ('' != $this->aPagina['tagprima']) {
				$tr = $this->dbro->query("SELECT titolo FROM pagine WHERE tag='" . $this->aPagina['tagprima'] . "'")->fetch_array();
				$zz = str_replace("'", '&#39;', $tr[0]);
				$b .= $this->espandiLink('{' . $this->costruisciURLImmagineSkin('ico-precedente.png', $zz) . ' />|' . $this->aPagina['tagprima'] . '}');
			} 
			$b .= "&nbsp;";
			// icona episodio successivo
			if ('' != $this->aPagina['tagdopo']) {
				$tr = $this->dbro->query("SELECT titolo FROM pagine WHERE tag='" . $this->aPagina['tagdopo'] . "'")->fetch_array();
				$zz = str_replace("'", '&#39;', $tr[0]);
				$b .= $this->espandiLink('{' . $this->costruisciURLImmagineSkin('ico-successivo.png', $zz) . ' />|' . $this->aPagina['tagdopo'] . '}');
			}
		}
		$b .= "\n</div>";
		return $b;
	}


	/**
	 * immaginiAltoDx()
	 * 
	 * Visualizza le immagini in alto a destra nella pagina
	 * 
	 * 20111105: adattati i tag alle specifiche XHTML
	 *
	 */

	private function immaginiAltoDx() {
		$b = '';
		$qrd = $this->dbro->query("SELECT * FROM immagini 
		                           RIGHT JOIN immaginipagine ON immagini.idimmagine=immaginipagine.idimmagine
		                           WHERE immaginipagine.idpagina=" . $this->aPagina['idpagina'] . " ORDER BY ordine");
		if ($qrd->num_rows > 0) {
			$aimg = array();
			while ($rd = $qrd->fetch_array()) {
				$bz = '';
				if ('' == $rd['tagrotante']) {
					$bz .= $this->costruisciURLimmagine($rd['file'], $rd['descrizione']);
					$bz .= " width='$rd[larghezza]' height='$rd[altezza]' class='altodx' />";
				} else {
					$imgtag = $this->espandiLink('{|#' . $rd['tagrotante'] . '}');
					$bz .= str_replace( '<img' , "<img class='altodx' ", $imgtag);
				}
				$aimg[] = $bz;
			}
			$b = "\n<span class='immaginialtodx'>\n";
			$b .= implode('<br />', $aimg);
			$b .= "\n</span>";
		} else {
			$b = '';
		}
		return $b;
	}


	/**
	 * datiEpisodio($showIMG, $fullWidth)
	 * 
	 * Visualizza la tabella di un episodio con i dati e i guest
	 * 
	 * 20091122: aggiunto span.episodiodatitesto e forzato un valign='top' se ci sono piu' righe
	 * 20111105: adattati i tag alle specifiche XHTML
	 *
	 */
	private function datiEpisodio($showIMG = FALSE, $fullWidth = TRUE) {
		$b = '';
		// contenitore esterno della tabelle con le tabelle dei dati e dei guest
		$b .= "\n<div class='episodiocontenitoretabellona'>";
		if ($showIMG) $b .= $this->immaginiAltoDx();
		if ($fullWidth) {
			$b .= "\n<table border='0' class='episodiotabellona' cellpadding='0' cellspacing='0' width='100%'><tr>";
		} else {
			$b .= "\n<table border='0' class='episodiotabellona' cellpadding='0' cellspacing='0'><tr>";
		}
		//dati episodio
		$aeti = array();
		$aval = array();
		$aimg = array();
		$aimgd = array();
		$conta = 0;
		$qrd = $this->dbro->query("SELECT etichetta,valore,tag,titolondx,idpaginacast,icona,descrizione FROM episodivalori
		                           JOIN episodicampi on episodivalori.idcampo=episodicampi.idcampo
		                           LEFT JOIN pagine on episodivalori.idpaginacast=pagine.idpagina
		                           WHERE episodivalori.idpagina=" . $this->aPagina['idpagina'] . " ORDER BY episodicampi.ordine,episodivalori.ordine");
		$b .= "\n<td valign='top' align='left'><table border='0' cellpadding='1' cellspacing='1' class='episodiodati'>";
		while ($rd = $qrd->fetch_array()) {
			//stabilisco se e' un valore 'cablato' oppure 'tabellato'
			if ( '' != $rd['tag'] ) {
				//gestisco la possibilita' di mettere uno pseudonimo
				if ('' == trim($rd['valore'])) {
					$valore = '{' . $rd['titolondx'] . '|' . $rd['tag'] . '}';
				} else {
					$valore = '{' . $rd['valore'] . '|' . $rd['tag'] . '}';
				}	
			} else {
				$valore = $rd['valore'];
			}
			// se e' un altro valore dello stesso campo accodo il valore e non incremento il contatore
			if (($conta > 0 ) and ($aeti[$conta] == $rd['etichetta'])) {
				$aval[$conta] .= "<br />$valore";
			} else {
				$conta++;
				$aeti[$conta] = $rd['etichetta'];
				$aimg[$conta] = $rd['icona'];
				$aimgd[$conta] = $rd['descrizione'];
				$aval[$conta] = $valore;
			}
		}
		for ($i=1; $i<=$conta; $i++) {
			$b .= "\n<tr><td valign='top'>";
			// vedo se c'e' un'icona per l'item che sto visualizzando
			if ('' == $aimg[$i]) {
				$b .=  $aeti[$i];			
			} else {
				$b .= $this->costruisciURLimmagineSkin($aimg[$i], $aimgd[$i]) . ' />';				
			}
			// se ci sono due righe, forzo un valkign='top'
			if (strpos($aval[$i], '<br />') !== false) {
				$b .= "</td><td valign='top'><span class='episodiodatitesto'>" . $this->espandiLink($aval[$i]) . "</span></td></tr>";
			} else {
				$b .= "</td><td><span class='episodiodatitesto'>" . $this->espandiLink($aval[$i]) . "</span></td></tr>";
			}
			
		}
		$b .= "\n</table></td>";

		// guest star
		$aper = array();
		$aatt = array();
		$conta = 0;
		$qrd = $this->dbro->query("SELECT personaggio,p1.tag AS ptag,p2.tag AS ctag,p1.titolondx AS ptitolondx,p2.titolondx AS ctitolondx FROM guest
		                           LEFT JOIN pagine AS p1 ON guest.idpaginapersonaggio=p1.idpagina
		                           LEFT JOIN pagine AS p2 ON guest.idpaginacast=p2.idpagina
		                           WHERE guest.idpagina=" . $this->aPagina['idpagina'] . " ORDER BY ordine");
		while ($rd = $qrd->fetch_array()){
			$attore= '{' . $rd['ctitolondx'] . '|' . $rd['ctag'] . '}';
			//gestisco la possibilita' di listare il personaggio con un nome diverso pur mantenendo il link
			if ( '' == $rd['ptag'] ) {
				$personaggio = $rd['personaggio'];
			} else {
				if ('' == trim($rd['personaggio'])) {
					// nome di default
					$personaggio = '{' . $rd['ptitolondx'] . '|' . $rd['ptag'] . '}';
				} else {
					// override, decido se devo espandere il nome con il meta '@@@'
					if (strpos($rd['personaggio'], '@@@') === FALSE) {
						$personaggio = '{' . $rd['personaggio'] . '|' . $rd['ptag'] . '}';
					} else {
						$z = '{' . $rd['ptitolondx'] . '|' . $rd['ptag'] . '}';
						$personaggio = str_replace('@@@', $z, $rd['personaggio']);
					}
				}
			}
			// se e' un altro attore per lo stesso personaggio accodo il valore e non incremento il contatore
			if (($conta > 0) and ($aper[$conta] == $personaggio)) {
				$aatt[$conta] .= "<br />$attore";
			} else {
				$conta++;
				$aper[$conta] = $personaggio;
				$aatt[$conta] = $attore;
			}
		}
		if ($conta > 0) {
			$b .= "\n<td valign='top' align='right'><table border='0' cellpadding='3' class='episodioguest' align='right'>";
			for ($i=1; $i<=$conta; $i++) {
				$b .= "\n<tr><td valign='top'>" . $this->espandiLink($aper[$i]) . "</td><td><b>" . $this->espandiLink($aatt[$i]) . "</b></td></tr>";
			}
			$b .= "\n</table></td>";
		}

		$b .= "\n</tr></table>"; // tabellona
		$b .= "\n</div>"; // episodiocontenitoretabellona
		return $b;
	}


	/**
	 * testiStandard($showIMG)
	 * 
	 * Visualizza le parti di testo generico senza intestazione
	 * 
	 * 20081026 prima versione
	 *
	 */
	private function testiStandard($showIMG = FALSE) {
		$b = "\n<div class='testostandardcontenitore'>";
		if ($showIMG) $b .= $this->immaginiAltoDx();
		$qrd = $this->dbro->query("SELECT * FROM testi WHERE idpagina=" . $this->aPagina['idpagina'] . " ORDER BY ordine");
		while ($rd = $qrd->fetch_array()){
			$aTesto = $this->estraiDIV($rd['testo']);
			if ('' == $aTesto['classe']) {
				$b .= "\n<div class='testostandard'>";
			} else {
				$b .= "\n<div class='$aTesto[classe]'>";
			}
			$b .= $this->espandiLink($aTesto['testo']) . "</div>";
		}
		// vediamo se ci sono riferimenti inversi
		$qrd = $this->dbro->query("SELECT * FROM testi WHERE taginclude like '% " . $this->aPagina['tag'] . " %'");
		while ($rd = $qrd->fetch_array()){
			$aTesto = $this->estraiDIV($rd['testo']);
			if ('' == $aTesto['classe']) {
				$b .= "\n<div class='testostandard'>";
			} else {
				$b .= "\n<div class='$aTesto[classe]'>";
			}
			$rrd = $this->dbro->query("SELECT tag,titolondx FROM pagine WHERE idpagina=$rd[idpagina]")->fetch_array();
			$b .= $this->espandiLink($aTesto['testo'] . ' [{' . $rrd['titolondx'] . '|' . $rrd['tag'] .'}]') . "</div>";
		}
		$b .= "\n</div>";
		return $b;
	}


	/**
	 * capitoli($tipo)
	 * 
	 * Visualizza le parti di testo con intestazione
	 * 
	 * @param string $tipo se assente mostra un normale paragrafo con intestazione se vale 'tuttapagina' mostra un elenco puntato a tutta pagina
	 *
	 * 20080511: riscrittura ex novo della funzione, aggiunta della gestione del titolo alternativo, messi in fondo i riferimenti estern da altre pagine, aggiunto sistema per far affondare le voci referenziate
	 * 20091226: rimozione della gestione del titlo alternativo
	 *
	 */
	private function capitoli($tipo='') {
		$b = "\n<div class='testocapitolo'>";
		// se il tipo e' a tutta pagina
		if ('tuttapagina' == $tipo) {
			$qrd = $this->dbro->query("SELECT ordine,testo FROM capitoli WHERE idpagina='" . $this->aPagina['idpagina'] . "' ORDER BY ordine");
			$b .=  "\n<ul class='capitolopunto'>";
			while ($rd = $qrd->fetch_array()){
				$b .=  "\n<li>" . $this->espandiLink($rd['testo']) . "</li>";
			}	
			$b .=  "\n</ul>";
		} else { // se e' un normale elenco di capitoli
			//array con i riferimenti ai capitoli da stampare
			$aCapitoli = array();
			$i = 0;
			$qrd = $this->dbro->query("SELECT idcapitolo,capitoli.idcapitolotipo AS tipo,capitoli.ordine AS cordine,capitolitipi.ordine AS tordine,isbullet,tag,intestazione
			                           FROM capitoli
			                           JOIN capitolitipi ON capitoli.idcapitolotipo=capitolitipi.idcapitolotipo 
			                           WHERE idpagina='" . $this->aPagina['idpagina'] ."'");
			while ($rd = $qrd->fetch_array()) {
				$aCapitoli[$i]['tordine'] = $rd['tordine'];
				$aCapitoli[$i]['cordine'] = $rd['cordine'];
				$aCapitoli[$i]['tipo'] = $rd['tipo'];
				$aCapitoli[$i]['id'] = $rd['idcapitolo'];
				$aCapitoli[$i]['isbullet'] = ($rd['isbullet'] == 1);
				$aCapitoli[$i]['referenziato'] = FALSE;
				$aCapitoli[$i]['tag'] = $rd['tag'];
				$aCapitoli[$i]['intestazione'] = $rd['intestazione'];
				$i++;
			}
			// vediamo se ci sono riferimenti inversi
			$qrd = $this->dbro->query("SELECT idcapitolo,capitoli.idcapitolotipo AS tipo,capitoli.ordine AS cordine,capitolitipi.ordine AS tordine,isbullet,tag,intestazione 
			                           FROM capitoli 
			                           LEFT JOIN capitolitipi ON capitoli.idcapitolotipo=capitolitipi.idcapitolotipo 
			                           WHERE taginclude like '% " . $this->aPagina['tag'] . " %'");
			while ($rd = $qrd->fetch_array()) {
				$aCapitoli[$i]['tordine'] = $rd['tordine'];
				$aCapitoli[$i]['cordine'] = $rd['cordine'] + 2000; // per far andare in fondo le voci referenziate
				$aCapitoli[$i]['tipo'] = $rd['tipo'];
				$aCapitoli[$i]['id'] = $rd['idcapitolo'];
				$aCapitoli[$i]['isbullet'] = ($rd['isbullet'] == 1);
				$aCapitoli[$i]['referenziato'] = TRUE;
				$aCapitoli[$i]['tag'] = $rd['tag'];
				$aCapitoli[$i]['intestazione'] = $rd['intestazione'];
				$i++;
			}
			// vediamo se c'e' qualcosa da visualizzare
			if (count($aCapitoli) > 0) {
				array_multisort($aCapitoli);
				$oldidtipo = 0;
				$oldbullet = 0;
				$old = 'sbiliguda';
				foreach ($aCapitoli as $xcap) {
					$new = $xcap['tipo'];
					if ($old != $new) {
						if ($oldbullet and $oldidtipo != 0) $b.= "\n</ul>";
						// titolo
						$b .= "\n<div class='testocapitolotitolo'>";
						$b .= "<a name='$xcap[tag]'>";
						$b .= "<span class='testocapitolotitolotesto'>$xcap[intestazione]</span>";
						$b .= "</a>";
						$this->aggiungiRiferimento(' Su questa pagina', "<a href='#$xcap[tag]'>$xcap[intestazione]</a>");
						$b .= "</div>"; //testocapitolotitolo
						// riferimento ed eventuale UL
						if ($xcap['isbullet']) $b.= "\n<ul class='capitolopunto'>";
					}
					$rd = $this->dbro->query("SELECT testo,idpagina FROM capitoli WHERE idcapitolo='$xcap[id]'")->fetch_array();
					$testo = $rd['testo'];
					if ($xcap['referenziato']) {
						$rrd = $this->dbro->query("SELECT tag,titolondx FROM pagine WHERE idpagina='$rd[idpagina]'")->fetch_array();
						$testo .= ' [{' . $rrd['titolondx'] . '|' . $rrd['tag'] .'}]';
					}
					if ($xcap['isbullet']) {
						$b .=  "\n<li>" . $this->espandiLink($testo) . "</li>";
					} else {
						$b .=  "\n<div class='testocapitolotesto'>" . $this->espandiLink($testo) . "</div>";
					}
					$old = $xcap['tipo'];
					$oldidtipo = $xcap['tipo'];
					$oldbullet = $xcap['isbullet'];
				}
				if ($oldbullet) $b.= "\n</ul>";
			}
		}
		$b .= "\n</div>"; // testocapitolo
		return $b;
	}


	/**
	 * aggiungiRiferimento($categoria, $riferimento)
	 * 
	 * Aggiunge un riferimento da mostrare nella colonna di destra
	 * @param string $categoria categoria del riferimento (intestazione)
	 * @param string $riferimento riferimento da aggiungere
	 * 
	 * 20061031 prima versione
	 */
	private function aggiungiRiferimento($categoria, $riferimento) {
		 array_push($this->aRifTitolo, $categoria) ;
		 array_push($this->aRifTesto, $riferimento) ;
	}


	/**
	 * scriviRiferimenti()
	 * 
	 * Mostra i riferimenti nella colonna di destra
	 * 
	 * 20100201: aggiunto il link ai menu di riferimento
	 * 
 	 */
	private function scriviRiferimenti() {
		$b = '';
		// aggiungo i riferimenti da database: link diretto
		if (!$this->istimelinest) {
			if (!$this->iseventitrek) {
				$qrd = $this->dbro->query("SELECT categoria,riferimento
				                           FROM riferimenti 
				                           WHERE idpagina=" . $this->aPagina['idpagina'] . "
				                           ORDER BY categoria,riferimento");
				while ($rd = $qrd->fetch_array()) {
					$this->aggiungiRiferimento($rd['categoria'], $this->espandiLink($rd['riferimento']));
				}
			}
		}
		// aggiungo i riferimenti da database: backlink standard
		$qrd = $this->dbro->query("SELECT idpagina FROM riferimenti WHERE backlink='" . $this->aPagina['tag'] . "'");
		// se trovo dei backlink, li elaboro
		while ($rd = $qrd->fetch_array()) {
			$r = $this->dbro->query("SELECT tag,titolondx,riferimento 
			                         FROM pagine 
			                         JOIN sezioni ON pagine.idsezione=sezioni.idsezione 
			                         WHERE hidden=0 and idpagina=$rd[idpagina]")->fetch_array();
			$this->aggiungiRiferimento($r['riferimento'], $this->espandiLink('{' . $r['titolondx'] . '|' . $r['tag'] .'}'));
		}
		// aggiungo i riferimenti da database: backlink da Quante Volte
		$qrd = $this->dbro->query("SELECT idpagina FROM quantevolte WHERE backlink='" . $this->aPagina['tag'] . "'");
		//se trovo dei backlink, li elaboro
		while ($rd = $qrd->fetch_array()) {
			$q = $this->dbro->query("SELECT tag,titolondx,riferimento 
			                         FROM pagine 
			                         JOIN sezioni ON pagine.idsezione=sezioni.idsezione 
			                         WHERE hidden=0 and idpagina=$rd[idpagina]");
			$r = $q->fetch_array();
			$this->aggiungiRiferimento($r['riferimento'], $this->espandiLink('{' . $r['titolondx'] . '|' . $r['tag'] .'}'));
		}
		// faccio tutto il cinema solo se ci sono degli elementi da visualizzare
		if (count($this->aRifTesto) > 0) {
			array_multisort($this->aRifTitolo, $this->aRifTesto);
			$old = '***';
			$oldecho = '***';
			$buffer = '';
			$separatore = ' &bull; ';
			$datogliere = -8;
			for ($n = 0; $n < count($this->aRifTesto); $n++) {
				// rottura 
				if ($this->aRifTitolo[$n] != $old and '***' != $old) {
					$buffer = substr($buffer, 0, $datogliere);
					$b .= "\n<div class='testoriferimento'><b>$old</b>: $buffer</div>";
					$buffer = '';
				}
				$old = $this->aRifTitolo[$n];
				$thisecho = $this->aRifTesto[$n] . $separatore;
				//output solamente se non sto riscrivendo la stessa cosa
				if ($thisecho !=$oldecho) {
					$buffer .= $thisecho;
				}
				$oldecho = $thisecho;
			}
			$buffer = substr($buffer, 0, $datogliere);
			$b .= "\n<div class='testoriferimento'><b>$old</b>: $buffer</div>";
		}
		// alla fine aggiungo i riferimenti ai menu relativi alla pagina
		$q = $this->dbro->query("SELECT tag,sigla FROM paginemenu JOIN menu on paginemenu.idmenu=menu.idmenu WHERE idpagina='" . $this->aPagina['idpagina'] . "' ORDER BY ordine");
		while ($r = $q->fetch_array()) {
			$b .= "\n<div class='testoriferimento'><b>" . $this->espandiLink('{' . $r['sigla'] . '|:' . $r['tag'] .'}') . "</b></div>";
		}
		return $b;
	}


	/**
	 * pieDiPagina()
	 * 
	 * Scrive il pie` di pagina
	 * 
	 * @global array $tipopagina tipi di pagina
	 *
	 * 20090308: aggiunto "modifica in tempo reale" per la pagina di tipo log e modifica dei badge
	 * 20090503: aggiunto "modifica in tempo reale" per la pagina della nuvola
	 * 20091226: rimosso il titolo originale
	 * 20111105: adattato tag IMG alle specifiche XHTML
	 * 20111105: disabilitate le medagliette
	 *
	 */
	private function pieDiPagina() {
		global $tipopagina;
		$b = '';
		$b .= "\n<table border='0' cellpadding='0' cellspacing='0' width='100%' class='piedipagina'>\n<tr>";
		// cella vuota
		$b .= "<td valign='top'>&nbsp;</td>";
		// pie` di pagina
		$b .= "<td valign='top'>";
		$qrd = $this->dbro->query("SELECT * FROM piepagina WHERE idpagina= " . $this->aPagina['idpagina'] . " ORDER BY ppordine");	
		if ($qrd->num_rows > 0) {
			while ($rd = $qrd->fetch_array()) {
				$b .= "<div class='piedipaginatesto'>" . $this->espandiLink($rd['testo']) . "</div>";
			}
		} else {
			$b .= "&nbsp;";
		}
		$b .= '</td>';
		// ultimo aggiornamento
		$b .= "<td valign='top'><div class='piedipaginaultimoaggiornamento'>";
		// scrivo la data solo se non e' la pagina delle statistiche o del log delle modifiche
		if (($this->aPagina['tag'] != 'nuvolapagine') and ($this->aPagina['tipo'] != $tipopagina['statistiche']) and ($this->aPagina['tipo'] != $tipopagina['log'])) {
			$b .= "I dati di questa pagina sono stati aggiornati l'ultima volta il " . date('j.n.Y', $this->aPagina['lastmod']) . " alle " . date('G:i', $this->aPagina['lastmod']) . '.';
		} else {
			$b .= "I dati di questa pagina sono stati aggiornati in tempo reale.";
		}
		$b .= "</div></td></tr>\n</table>";
		// esponiamo le medagliette
		//$b .= "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n<tr>";
		//$b .= "<td align='center' width='33%'>" . $this->espandiLink('{' . $this->costruisciURLimmagine('xajax_powered.png', 'xajax powered') . ' width="80" height="15" />|@http://xajaxproject.org/}') . '</td>';
		//$b .= "<td align='center' width='33%'>" . $this->espandiLink('{' . $this->costruisciURLimmagine('valid-html401.png', 'Valid HTML 4.01 Transitional') . ' width="80" height="15" />|@http://validator.w3.org/check?uri=referer}') . '</td>';
		//$b .= "<td align='center' width='33%'>" . $this->espandiLink('{' . $this->costruisciURLimmagine('vcss.png', 'Valid CSS!') . ' width="80" height="15" />|@http://jigsaw.w3.org/css-validator/check/referer }') . '</td>';
		//$b .= "</tr>\n</table>";
		// copyleft
		$b .= "<div class='gnufdl'>Sito membro della " . $this->espandiLink('{HyperAlliance | @http://www.hyperalliance.org/}.');
		$b .= "<br />Tutti i testi sono disponibili nel rispetto dei termini della " . $this->espandiLink('{GNU Free Documentation License | @http://www.gnu.org/copyleft/fdl.html}.') . '</div>';
		return $b;
	}


	/**
	 * espandiLink($testo)
	 * 
	 * Analizza il testo in input e lo restituisce in output dopo aver espanso le sequenze tra parentesi graffe:<br>
	 * { testo | tag } link a pagina<br>
	 * { testo | @url } link esterno<br>
	 * { testo | :tag } link al menu<br>
	 * { descrizione | >tag[+tag] } immagine nel testo allineata a destra con classe opportuna, opzionalmente thubnail con link a _blank e descrizione<br>
	 * { align[+descrizione] | *tag[+tag] } immagine nel testo, opzionalmente thubnail con link a _blank e descrizione<br>
	 * { align[+descrizione] | #tag } immagine nel testo con rotazione a seconda della data<br>
	 * { testo | %ntag } tabella, il testo viene incluso nel tag <TABLE>, n indica se e' una tabella a 2 o a 3 colonne<br>
	 * { align | !tag } esegue una 'macro'
	 * 
	 * @todo gestire link a oggetti non html/img all'interno del sito
	 *
	 * 20091220: modificato l'algoritmo di selezione casuale delle immagini
	 * 20100131: nuova struttura dei menu
	 * 20111105: adattati i tag alle specifiche XHTML
	 *
	 */
	function espandiLink($testo) {
		preg_match_all( "!(\{[^\{]+\})!i" , $testo , $aMatch );
		for ($i = 0; $i < count ($aMatch[1]); $i++)  {
			//toglie le parentesi graffe
			$sanitized = preg_replace("!(\{|\})!" , "" , $aMatch[1][$i]);
			// explode e' piu' veloce
			list ($hot, $tag) = explode('|' , $sanitized);
			$hot = trim($hot);
			$tag = trim($tag);
			// ho esploso la stringa, ora mi comporto di conseguenza
			// immagine allineata a destra nel testo
			if ('>' == substr($tag, 0, 1)) {
				// eventuale descrizione forzata
				$descforzata = $hot;
				$imgtag = substr($tag, 1);
				// verifico se dopo il pipe c'e' la specificazione di un thunbnail (tag1+tag2)
				if (($piu = strpos($tag, '+')) === false) {
					$r = $this->dbro->query("SELECT idimmagine FROM immagini WHERE imgtag='$imgtag'")->fetch_array();
					$newb = $this->costruisciURLimmagine($r['idimmagine'], $descforzata) . "align='right' class='allineatadestra' />";
				} else {
					$aImgTag = explode('+', $imgtag);
					// link all'immagine intera
					$r = $this->dbro->query("SELECT idimmagine FROM immagini WHERE imgtag='$imgtag'")->fetch_array();
					$newb = "<a target='_blank' href='" . $this->aSetup['docroot'] . $this->aSetup['pathimmagini'] . $r[0] . "' title=\"Click per vedere l'immagine intera\">";
					//thumbnail
					$r = $this->dbro->query("SELECT idimmagine FROM immagini WHERE imgtag='$aImgTag[0]'")->fetch_array();
					$newb .= $this->costruisciURLimmagine($r['idimmagine']) . "align='right' class='allineatadestra' /></a>";
				}
			}
			// immagine
			elseif ('*' == substr($tag, 0, 1)) {
				$descforzata='';
				$newhot = $hot;
				// verifico se la prima parte include l'indicazione della descrizione
				if (!(strpos($newhot, '+') === false)) {
					$aHot = explode('+', $newhot);
					$newhot = trim($aHot[0]);
					$descforzata = trim($aHot[1]);
				}
				$imgtag = substr($tag, 1);
				// verifico se dopo il pipe c'e' la specificazione di un thunbnail (tag1+tag2)
				if (($piu = strpos($tag, '+')) === false) {
					$r = $this->dbro->query("SELECT idimmagine FROM immagini WHERE imgtag='$imgtag'")->fetch_array();
					$newb = $this->costruisciURLimmagine($r['idimmagine'], $descforzata);
					if ('' != $newhot) $newb .= " align='$newhot'";
					$newb .= ' />';
				} else {
					$aImgTag = explode('+', $imgtag);
					// link all'immagine intera
					$r = $this->dbro->query("SELECT file FROM immagini WHERE imgtag='$aImgTag[1]'")->fetch_array();
					$newb = "<a target='_blank' href='" . $this->aSetup['docroot'] . $this->aSetup['pathimmagini'] . $r[0] . "' title=\"Click per vedere l'immagine intera\">";
					//thumbnail
					$r = $this->dbro->query("SELECT idimmagine FROM immagini WHERE imgtag='$aImgTag[0]'")->fetch_array();
					$newb .= $this->costruisciURLimmagine($r['idimmagine']);
					if ('' != $newhot) $newb .= " align='$newhot'";
					$newb .= ' /></a>';
				}
			// imamgine che cambia con la data o a caso
			} elseif ('#' == substr($tag, 0, 1)) {
				$descforzata='';
				$newhot = $hot;
				// verifico se la prima parte include l'indicazione della descrizione
				if (!(strpos($newhot, '+') === false)) {
					$aHot = explode('+', $newhot);
					$newhot = trim($aHot[0]);
					$descforzata = trim($aHot[1]);
				}
				$imgtag = substr($tag, 1);
				// vedo se il tag si riferisce ad un'immagine random
				$rcas = $this->dbro->query("SELECT mesegiorno FROM immaginidata WHERE tag='$imgtag' AND mesegiorno='rand'");
				if ($rcas->num_rows > 0) {
					// immagine casuale
					//$quale = rand(1, $rcas->num_rows);
					//$qcas = $this->dbro->query("SELECT idimmagine FROM immaginidata WHERE tag='$imgtag'");
					//for ($conta = 1; $conta <= $quale; $conta++) {
					//	$rcas = $qcas->fetch_array();
					//}
					$quale = rand(1, $rcas->num_rows) - 1; //l'offset del primo record di LIMIT e' zero, non uno
					$rcas = $this->dbro->query("SELECT idimmagine FROM immaginidata WHERE tag='$imgtag' LIMIT $quale,1")->fetch_array();
					$newb = $this->costruisciURLimmagine($rcas['idimmagine'], $descforzata);
					if ('' != $newhot) $newb .= " align='$newhot'";
					$newb .= ' />';
				} else {
					// immagine basata sulla data
					$oggi = date("m-d");
					$r = $this->dbro->query("SELECT idimmagine FROM immaginidata WHERE mesegiorno='$oggi' AND tag='$imgtag'");
					if ($r->num_rows > 0) {
						$rw = $r->fetch_array();
						$newb = $this->costruisciURLimmagine($rw['idimmagine'], $descforzata);
						if ('' != $newhot) $newb .= " align='$newhot'";
						$newb .= ' />';
					} else {
						$q = $this->dbro->query("SELECT idimmagine FROM immaginidata WHERE mesegiorno='*' AND tag='$imgtag'");
						$r = $q->fetch_array();
						$newb = $this->costruisciURLimmagine($r['idimmagine'], $descforzata);
						if ('' != $newhot) $newb .= " align='$newhot'";
						$newb .= ' />';
					}
				}
			// 'macro'
			} elseif ('!' == substr($tag, 0, 1)) {
				$macrotag = substr($tag, 1);
				$newb = $this->eseguiMacro($macrotag);
			// link esterno
			} elseif ('@' == substr($tag, 0, 1)) {
				$url = substr($tag, 1);
				$newb = "<a target='_blank' title='Link esterno a HyperTrek' class='esterno' href='$url'>";
				$newb .= $hot . "</a>";
			// tabella
			} elseif ('%' == substr($tag, 0, 1)) {
				$colonne = substr($tag, 1, 1);
				$tabtag = substr($tag, 2);
				$addtag = $hot;
				$qr = $this->dbro->query("SELECT * FROM tabelle WHERE ttag='$tabtag' ORDER BY tordine");
				$newb = "\n<table $addtag>";
				while ($r = $qr->fetch_array()) {
					// riassegno le variabili per evitare un comportamento di 
					// Internet Explorer in merito alle celle vuote
					if ('' != trim($r['prima'])) $prima = $r['prima']; else $prima = '&nbsp;';
					if ('' != trim($r['seconda'])) $seconda = $r['seconda']; else $seconda = '&nbsp;';
					if ('' != trim($r['terza'])) $terza = $r['terza']; else $terza = '&nbsp;';
					if ('' != trim($r['quarta'])) $quarta = $r['quarta']; else $quarta = '&nbsp;';
					$newb .= "\n<tr>";
					if (1 == $r['istitolo']) {
						$newb .= "\n<td colspan='$colonne' class='titolo'>";
						$newb .= $this->espandiLink($prima);
						$newb .= "\n</td>";
					} else {
						// 1
						if (strpos($r['qualicenter'], '1') === false ) {
							$newb .= "\n<td>";
						} else {
							$newb .= "\n<td align='center'>";
						}
						$newb .= $this->espandiLink($prima);
						$newb .= "</td>";
						// 2
						if (strpos($r['qualicenter'], '2') === false ) {
							$newb .= "\n<td>";
						} else {
							$newb .= "\n<td align='center'>";
						}
						$newb .= $this->espandiLink($seconda);
						$newb .= "</td>";
						if ($colonne >= 3) {
							// 3
							if (strpos($r['qualicenter'], '3') === false ) {
								$newb .= "\n<td>";
							} else {
								$newb .= "\n<td align='center'>";
							}
							$newb .= $this->espandiLink($terza);
							$newb .= "</td>";
						}
						if ($colonne >= 4) {
							// 4
							if (strpos($r['qualicenter'], '4') === false ) {
								$newb .= "\n<td>";
							} else {
								$newb .= "\n<td align='center'>";
							}
							$newb .= $this->espandiLink($quarta);
							$newb .= "</td>";
						}
					}
					$newb .= "\n</tr>";
				}
				$newb .= "\n</table>";
			// menu
			} elseif (':' == substr($tag, 0, 1)) {
				$idindice = 0;
				$tagindice = substr($tag, 1);
				$newb = "<a href='" . $this->costruisciURL($this->aPagina['tag'], '') . "' target='_top' onclick='xajax_AJAXmenu(\"$tagindice\"); return false' >$hot</a>";
			//tag normale
			} else {
				if (($anchorpos = strpos($tag, '#')) > 0) {
					$anchor = trim(substr($tag, $anchorpos + 1));
					$tag = trim(substr($tag, 0, $anchorpos));
				} else {
					$anchor ='';
				}
				// se il link punta a me stesso, lo tolgo
				if ($tag == $this->aPagina['tag'] and '' == $anchor) {
					$newb = $hot;
				} else {
					$newb = "<a href='" . $this->costruisciURL($tag, $anchor) . "' target='_top'>$hot</a>";
				}
			}
			$testo = str_replace( $aMatch[1][$i] , $newb, $testo);
		}
		return $testo;
	}


	/**
	 * apparizioniCast()
	 * 
	 * Genera la tabella con l'elenco delle apparizioni di un attore
	 * 
	 * 20080620 prima versione
	 *
	 */
	private function apparizioniCast() {
		$b = '';
		// chiavedisort ~ ruolo ~ episodio
		$apparizioni = array();
		// faccio passare i ruoli come regia, scrittore...
		// tutto 'sto cinema di select per cavare tre campi
		$qrd = $this->dbro->query("SELECT titolondx,descrizione,tag 
		                           FROM episodivalori
		                           JOIN pagine ON episodivalori.idpagina=pagine.idpagina
		                           JOIN episodicampi on episodivalori.idcampo=episodicampi.idcampo
		                           WHERE episodivalori.idpaginacast=" . $this->aPagina['idpagina'] );
		while ($rd = $qrd->fetch_array()) {
			array_push($apparizioni, $rd['descrizione'] . '~' . $rd['descrizione'] . '~{' . $rd['titolondx'] . '|' . $rd['tag'] . '}');
		}
		// faccio passare i ruoli come attore 
		// altro cinema di select per cavare tre campi
		$qrd = $this->dbro->query("SELECT personaggio,p1.tag as tag,p1.titolondx as titolondx,
		                                  p2.tag as ptag,p2.titolondx as ptitolondx,p2.chiavesort AS chiavesort
		                           FROM guest
		                           JOIN pagine as p1 ON guest.idpagina=p1.idpagina
		                           LEFT JOIN pagine as p2 ON guest.idpaginapersonaggio=p2.idpagina
		                           WHERE guest.idpaginacast=" . $this->aPagina['idpagina'] );
		while ($rd = $qrd->fetch_array()) {
			$z = '~{' . $rd['titolondx'] . '|' . $rd['tag'] . '}';
			if ('' == trim($rd['personaggio'])) {
				// nome di default
				$zz = $rd['chiavesort'] . '~{' . $rd['ptitolondx'] . '|' . $rd['ptag'] . '}' . $z;
			} else {
				if ('' == trim($rd['ptag'])) {
					// personaggio generico
					$zz = $rd['personaggio'] . '~' .$rd['personaggio'] . $z;
				} else {
					// personaggio con descrizione alternativa, decido se devo espandere il nome con il meta '@@@'
					if (strpos($rd['personaggio'], '@@@') === FALSE) {
						$zz = $rd['personaggio'] . '~{' . $rd['personaggio'] . '|' . $rd['ptag'] . '}' . $z;
					} else {
						$zzzz = '{' . $rd['ptitolondx'] . '|' . $rd['ptag'] . '}';
						$personaggio = str_replace('@@@', $zzzz, $rd['personaggio']);
						$zz = $rd['chiavesort'] . '~{' . $rd['ptitolondx'] . '|' . $rd['ptag'] . '}' . $z . " ($personaggio)";
					}
				}
			}	
			array_push($apparizioni,$zz);
		}
		//apparizioni in un'intera serie
		$qrf = $this->dbro->query("SELECT DISTINCT idcast,idpersonaggio,linkserie,tag,titolondx,chiavesort 
		                           FROM castfisso 
		                           JOIN pagine ON castfisso.idpersonaggio=pagine.idpagina
		                           WHERE idcast=" . $this->aPagina['idpagina']);
		while ($rf = $qrf->fetch_array()) {
			array_push($apparizioni, $rf['chiavesort'] . '~{' . $rf['titolondx'] . '|' . $rf['tag'] . '}~' . $rf['linkserie']);
		}
		// una volta popolato l'array, lo visualizzo
		sort($apparizioni);
		$old = '***';
		$buf = '';
		foreach ($apparizioni as $k => $a) {
			list ($scarta, $ruolo, $episodio) = explode('~', $a);
			if ($ruolo != $old) {
				if ('***' != $old) {
					$buf = substr($buf, 0, -1);
					$buf .= "</div>\n";
				}
				$buf.= "<div class='apparizionicast'><b>" . $this->espandiLink($ruolo) . '</b>:';
			}
			$old = $ruolo;
			$buf .= ' ' . $this->espandiLink($episodio) . ',';
		}
		$buf = substr($buf, 0, -1);
		$buf .= ".</div>\n";
		$b .= $buf;
		return $b;
	}


	/**
	 * calcolaApparizioniPersonaggio()
	 * 
	 * Calcola le apparizioni di un personaggio e popola i riferimenti 
	 * 
	 * 20061031 prima versione
	 *
	 */
	private function calcolaApparizioniPersonaggio() {
		$qrd = $this->dbro->query("SELECT p1.tag AS tag,p1.titolondx AS titolondx 
		                           FROM guest
		                           JOIN pagine AS p1 ON guest.idpagina=p1.idpagina
		                           WHERE guest.idpaginapersonaggio=" . $this->aPagina['idpagina'] . "
		                           ORDER BY titolondx");
		while ($rd = $qrd->fetch_array()) {
			$this->aggiungiRiferimento('Episodi', $this->espandiLink('{' . $rd['titolondx'] . '|' . $rd['tag'] . '}'));
		}	
		//eventuali apparizioni nel cast fisso
		$qrf = $this->dbro->query("SELECT DISTINCT idpersonaggio,linkserie 
		                           FROM castfisso 
		                           WHERE idpersonaggio=" . $this->aPagina['idpagina']);
		while ($rf = $qrf->fetch_array()) {
			$this->aggiungiRiferimento('Serie', $this->espandiLink($rf['linkserie']));
		}
	}


	/**
	 * calcolaAttoriPersonaggio()
	 * 
	 * Calcola gli attori che hanno interpretato quel personaggio
	 * 
	 * 20061031 prima versione
	 *
	 */
	private function calcolaAttoriPersonaggio() {
		$qrd = $this->dbro->query("SELECT DISTINCT idpaginapersonaggio,idpaginacast,titolondx,tag 
		                           FROM guest
		                           JOIN pagine ON guest.idpaginacast=pagine.idpagina
		                           WHERE guest.idpaginapersonaggio=" . $this->aPagina['idpagina']);
		while ($rd = $qrd->fetch_array()) {
			$this->aggiungiRiferimento('Interpreti', $this->espandiLink('{' . $rd['titolondx'] . '|' . $rd['tag'] . '}'));
		}
		//eventuali apparizioni nel cast fisso
		$qrf = $this->dbro->query("SELECT DISTINCT idcast,idpersonaggio,linkserie,tag,titolondx 
		                           FROM castfisso 
		                           JOIN pagine ON castfisso.idcast=pagine.idpagina 
		                           WHERE idpersonaggio=" . $this->aPagina['idpagina']);
		while ($rf = $qrf->fetch_array()) {
			$this->aggiungiRiferimento('Interpreti', $this->espandiLink('{' . $rf['titolondx'] . '|' . $rf['tag'] . '}'));
		}
	}


	/**
	 * costruisciURL($tag, $anc, $ndx)
	 * 
	 * Costruisce l'url completo in base al tag 
	 * @param string $tag tag della pagina target
	 * @param string $anc eventuale anchor
	 * 
	 * 20080619 prima versione
	 *
	 */
	private function costruisciURL($tag, $anc = '') {
		$retval = $this->aSetup['docroot'] . 'index.php/' . $tag;
		if ('' != $anc) {
			$retval .= "#$anc";
		} 
		return $retval;
	}
	

	/**
	 * costruisciURLimmagine($img, $dsc)
	 * 
	 * Costruisce l'url completo di un'immagine.<br>
	 * Se il primo parametro e' numerico lo tratta come l'ID dell'immagine da mostrare
	 *
	 * @param mixed $img tag o ID dell'immagine da mostrare
	 * @param string $dsc descrizione dell'immagine
	 *
	 * 20090307: aggiunto filtro per la rimozione dei tag HTML dalla descrizione delle immagini
	 * 20090405: aggiunto stripLink() alla descrizione
	 * 20151225: supporto https e baseurl
	 *
	 */
	private function costruisciURLimmagine($img, $dsc='') {
		$retval = "<img src='" . $this->aSetup['baseurl'] . $this->aSetup['docroot'];
		// se $img e' numerico lo considero l'ID dell'immagine da mostrare e becco tutti i dati dalla tabella
		if (is_numeric($img)) {
			$r = $this->dbro->query("SELECT * FROM immagini WHERE idimmagine=$img")->fetch_array();
			if (substr($r['file'], 0, 1) == '/') {
				$retval .= substr($r['file'], 1);
			} else {
				$retval .= $this->aSetup['pathimmagini'] . $r['file'];
			}
			// se non c'e' l'override della descrizione
			if ('' == $dsc) {
				$dsc = $r['descrizione'];
			} 
			// tolgo eventuali tag dalla descrizione 
			$dsc = strip_tags($dsc);
			$dsc = str_replace('"', '&#34;', $dsc);
			$dsc = $this->stripLink($dsc);
			$retval .= "' border='0' alt=\"$dsc\" title=\"$dsc\"";
			$retval .= " width='$r[larghezza]' height='$r[altezza]'";
		} else {
			// se $img inizia con un '/' significa che non considero la dir del path
			// ma e' un path assoluto all'interno del sito
			if (substr($img, 0, 1) == '/') {
				$retval .= substr($img, 1);
			} else {
				$retval .= $this->aSetup['pathimmagini'] . $img;
			}
			// tolgo eventuali tag dalla descrizione 
			$dsc = strip_tags($dsc);
			$dsc = str_replace('"', '&#34;', $dsc);
			$dsc = $this->stripLink($dsc);
			$retval .= "' border='0' alt=\"$dsc\" title=\"$dsc\"";
		}
		return $retval;
	}


	/**
	 * costruisciURLimmagineskin($img, $dsc)
	 * 
	 * Costruisce l'url completo di un'immagine della skin.
	 *
	 * @param string $img file dell'immagine da mostrare
	 * @param string $dsc descrizione dell'immagine
	 *
	 * 20090307: aggiunto filtro per la rimozione dei tag HTML dalla descrizione delle immagini
	 * 20090405: aggiunto stripLink() alla descrizione
	 * 20151225: supporto https e baseurl
	 *
	 */
	private function costruisciURLimmagineskin($img, $dsc='') {
		$imgpath = $this->skin . $img;
		// tolgo eventuali tag dalla descrizione 
		$dsc = strip_tags($dsc);
		$dsc = str_replace('"', '&#34;', $dsc);
		$dsc = $this->stripLink($dsc);
		// metto il punto davanti senno' mi va a prendere l'immagine nella root del file system
		$a = getimagesize ('.' . $imgpath);
		$retval = "<img border='0' src='" . $this->aSetup['baseurl'] . "$imgpath' alt=\"$dsc\" title=\"$dsc\" $a[3]";
		return $retval;
	}


	/**
	 * quanteVolte()
	 * 
	 * Visualizza il quante volte...
	 *
	 * 20080721 prima versione
	 *
	 */
	private function quanteVolte() {
		$b = '';
		$qrd = $this->dbro->query("SELECT * 
		                           FROM quantevolte
		                           WHERE nascosto=0 AND idpagina=" . $this->aPagina['idpagina'] . "
		                           ORDER BY qvordine");
		$b .= "\n<ol>";
		while ($rd = $qrd->fetch_array()) {
			$b .= "\n<li>" . $this->espandiLink($rd['testo']) . "</li>"; 
		}	
		$b .= "\n</ol>";
		// verifico se devo stilare la classifica
		$qrd = $this->dbro->query("SELECT classifica,COUNT(classifica) AS quante 
		                           FROM quantevolte 
		                           WHERE classifica<>'' AND idpagina=" . $this->aPagina['idpagina'] . "
		                           GROUP BY classifica 
		                           ORDER BY quante DESC,classifica");
		if ($qrd->num_rows > 1) {
			$b .= "\n<div class='qvclassificatitolo'><b>Classifica:</b></div>\n<ol class='qvclassifica'>";
			while ($rd = $qrd->fetch_array()) {
				$b .= "\n<li>" . $this->espandiLink($rd['classifica']) . ": $rd[quante]</li>"; 
			}
			$b .= "\n</ol>";
		}
		return $b;
	}
	

	/**
	 * statisticheDelSito()
	 * 
	 * Scrive le statistiche del sito
	 *
	 * 20091213: modificate le query da SELECT COUNT(campo) a SELECT COUNT(*)
	 *
	 */
	private function statisticheDelSito() {
		$b = '';
		// software
		$b .= "\n<div class='statistichetitolo'>HyperTrekNG</div>";
		$b .= "\n<div class='statistiche'>Versione del software: " . $this->aSetup['versione'] . "</div>";
		$b .= "\n<div class='statistiche'>Aggiornamento del motore di visualizzazione: " . date ('j.n.Y G:i:s', filemtime('include/output.php')) . "</div>";
		// pagine 
		$b .= "\n<div class='statistichetitolo'>Pagine</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM pagine")->fetch_array();
		$hmpagine = $r[0];
		$hmtimelinest = $this->dbro->query("SELECT DISTINCT anno FROM timelinest")->num_rows;
		$hmeventitrek = $this->dbro->query("SELECT DISTINCT anno FROM eventitrek")->num_rows;
		$hmtot = $hmpagine + $hmtimelinest + $hmeventitrek;
		$b .= "\n<div class='statistiche'>Pagine totali: $hmtot</div>";
		$r = $this->dbro->query("SELECT lastmod,tag,titolo FROM pagine WHERE hidden=0 ORDER BY lastmod LIMIT 1")->fetch_array();
		$b .= "\n<div class='statistiche'>Pagina pi&ugrave; vecchia: " . $this->espandiLink('{' . $r['titolo'] . '|' . $r['tag'] . '}' ) . ' aggiornata il ' . date('j.n.Y G:i:s', $r['lastmod']) . '</div>';
		$r = $this->dbro->query("SELECT lastmod,tag,titolo FROM pagine WHERE hidden=0 ORDER BY lastmod DESC LIMIT 1")->fetch_array();
		$b .= "\n<div class='statistiche'>Ultima pagina aggiornata: " . $this->espandiLink('{' . $r['titolo'] . '|' . $r['tag'] . '}' ) . ' aggiornata il ' . date('j.n.Y G:i:s', $r['lastmod']) . '</div>';
		$r = $this->dbro->query("SELECT lastmod,tag,titolo FROM pagine WHERE hidden=0 ORDER BY idpagina DESC LIMIT 1")->fetch_array();
		$b .= "\n<div class='statistiche'>Ultima pagina aggiunta: " . $this->espandiLink('{' . $r['titolo'] . '|' . $r['tag'] . '}' ) . ' aggiornata il ' . date('j.n.Y G:i:s', $r['lastmod']) . '</div>';
		// elementi delle pagine
		$b .= "\n<div class='statistichetitolo'>Elementi singoli delle pagine</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM testi")->fetch_array();
		$b .= "\n<div class='statistiche'>Paragrafi di testi generici: $r[0]</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM capitoli")->fetch_array();
		$b .= "\n<div class='statistiche'>Paragrafi all'interno dei capitoli: $r[0], di cui:<ul class='statistichepunto'>";
		$q = $this->dbro->query("SELECT intestazione,COUNT(*)  FROM capitoli JOIN capitolitipi ON capitoli.idcapitolotipo=capitolitipi.idcapitolotipo GROUP BY capitolitipi.idcapitolotipo");
		while ($r = $q->fetch_array()) {
			$b .= "<li>$r[0]: $r[1]</li>";
		}
		$b.="</ul></div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM immagini")->fetch_array();
		$b .= "\n<div class='statistiche'>Immagini (escluse quelle utilizzate per le skin): $r[0]</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM riferimenti")->fetch_array();
		$riferimenti = $r[0];
		$r = $this->dbro->query("SELECT COUNT(*) FROM riferimenti WHERE backlink<>''")->fetch_array();
		$riferimenti = $riferimenti + $r[0];
		$r = $this->dbro->query("SELECT COUNT(*) FROM quantevolte WHERE backlink<>''")->fetch_array();
		$riferimenti = $riferimenti + $r[0];
		$b .= "\n<div class='statistiche'>Riferimenti nella colonna di destra, esclusi quelli determinati automaticamente: $riferimenti</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM episodivalori")->fetch_array();
		$b .= "\n<div class='statistiche'>Valori delle tabelle degli episodi, dei libri, dei pianeti e dei personaggi: $r[0], di cui:<ul class='statistichepunto'>";
		$q = $this->dbro->query("SELECT descrizione,COUNT(*)  FROM episodivalori JOIN episodicampi ON episodivalori.idcampo=episodicampi.idcampo GROUP BY episodicampi.idcampo");
		while ($r = $q->fetch_array()) {
			$b .= $this->espandiLink("<li>$r[0]: $r[1]</li>");
		}
		$b.="</ul></div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM guest")->fetch_array();
		$b .= "\n<div class='statistiche'>Apparizioni delle guest star: $r[0]</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM piepagina")->fetch_array();
		$b .= "\n<div class='statistiche'>Pi&eacute; di pagine: $r[0]</div>";
		// timeline
		$b .= "\n<div class='statistichetitolo'>Timeline Star Trek</div>";
		$b .= "\n<div class='statistiche'>Anni registrati nella timeline di Star Trek: $hmtimelinest</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM timelinest")->fetch_array();
		$b .= "\n<div class='statistiche'>Voci della timeline di Star Trek: $r[0]</div>";
		// eventi trek
		$b .= "\n<div class='statistichetitolo'>Eventi Trek</div>";
		$b .= "\n<div class='statistiche'>Anni registrati nella timeline degli eventi Trek: $hmeventitrek</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM eventitrek")->fetch_array();
		$b .= "\n<div class='statistiche'>Voci della timeline degli eventi Trek: $r[0]</div>";
		// aggregatore
		$b .= "\n<div class='statistichetitolo'>Aggregatore di notizie</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM notiziesorgenti")->fetch_array();
		$b .= "\n<div class='statistiche'>Fonti delle notizie: $r[0]</div>";
		$r = $this->dbro->query("SELECT COUNT(*) FROM notizie")->fetch_array();
		$b .= "\n<div class='statistiche'>Numero di notizie: $r[0]</div>";
		$q = $this->dbro->query("SELECT iconatxt,lastupdate FROM notiziesorgenti WHERE lastupdate>0");
		while ($r = $q->fetch_array()) {
			$b .= "\n<div class='statistiche'>Le notizie di $r[iconatxt] sono aggiornate al " . date ('j.n.Y G:i:s', $r['lastupdate']) . "</div>";
		}
		return $b;
	}


	/**
	 * aggiornaStatistiche($idp, $flagtimelinest)
	 * 
	 * Aggiorna le statistiche di accesso
	 *
	 * @param integer $idp ID della pagina da conteggiare
	 * @param boolean $flagtimelinest flag per loggare una pagina della timeline di Star Trek
	 *
	 * 20091217: sistemate le query
	 *
	 */
	private function aggiornaStatistiche($idp, $flagtimelinest=FALSE) {
		if (!$flagtimelinest) {
			$q = $this->dbrw->query("SELECT accessi FROM contapagine WHERE idpagina='$idp'");
			if ($q->num_rows > 0 )  {
				$this->dbrw->query("UPDATE contapagine SET accessi=accessi+1,ultimo=NOW() WHERE idpagina='$idp'");
			} else {
				$this->dbrw->query("INSERT INTO contapagine SET idpagina='$idp',accessi=1,ultimo=NOW()");
			}
		} else {
			$q = $this->dbrw->query("SELECT accessi FROM contatimelinest WHERE anno='$idp'");
			if ($q->num_rows > 0 )  {
				$this->dbrw->query("UPDATE contatimelinest SET accessi=accessi+1,ultimo=NOW() WHERE anno='$idp'");
			} else {
				$this->dbrw->query("INSERT INTO contatimelinest SET anno='$idp',accessi=1,ultimo=NOW()");
			}
		}
	}


	/**
	 * loggaBad()
	 * 
	 * Logga un tag errato
	 *
	 * @param string $tg URL errato
	 *
	 * 20061031 prima versione
	 *
	 */
	private function loggaBad($tg) {
		$tg = $this->dbrw->escape_string($tg);
		$this->dbrw->query("INSERT INTO errori SET dataora=NOW(),tag='$tg',ip='$_SERVER[REMOTE_ADDR]',url='" . $this->rquri . "',ref='" . $this->ref . "'");
	}


	/**
	 * eseguiMacro($tg)
	 *
	 * @param string $tg macro da eseguire
	 * @return string l'output della macro
	 *
	 * Vengono eseguiti dei comandi in base ad un tag specifico.<br>
	 * La funzione e' richiamata da espandiLink()<br>
	 * Macro supportate:<br>
	 * - paginapiurecente: visualizza la data della pagina modificata piu' recentemente
	 * - legenda: visualizza la legenda delle abbreviazione delle tabelle degli episodi, pianeti, libri...
	 * - tab-xxx: tabella riassuntiva la cui idsezione e' xxx
	 * - randomquote: citazione casuale
	 * - notizie: tabella notizie
	 * - elencoskin: elenco delle skin con i pulsanti di scelta
	 * - cloud: stampa la nuvola dei piu' visti
	 * 
	 * 20090405: aggiunta macro 'cloud'
	 * 20090524: modificata macro 'randomquote' per espandere i link nella citazione
	 * 20111105: adattati i tag alle specifiche XHTML
	 * 
	 */
	private function eseguiMacro($tg) {
		$b = '';
		// tag cloud
		if ('cloud' == $tg) {
			$atag = array();
			$q = $this->dbrw->query("SELECT idpagina,accessi FROM contapagine ORDER BY accessi DESC LIMIT " . $this->aSetup['cloudhm']);
			while ($r = $q->fetch_array()) {
				$rr = $this->dbro->query("SELECT tag FROM pagine WHERE idpagina='$r[idpagina]'")->fetch_array();
				if ('main' != $rr['tag']) {
					if ('404' != $rr['tag']) {
						$atag[$rr['tag']] = $r['accessi'];
					}
				}
			}
			// calcolo il minimo e il massimo
			$maxval = max(array_values($atag));
			$minval = min(array_values($atag));
			// calcolo il range ed evito divisioni per zero
			$spread = $maxval - $minval;
			if (0 == $spread) $spread = 1;
			// calcolo gli step di incremento del font
			$step = ($this->aSetup['cloudpxmax'] - $this->aSetup['cloudpxmin']) / ($spread);
			// calcolo la nuvola
			$aout = array();
			foreach ($atag as $key => $value) {
				$size = round($this->aSetup['cloudpxmin'] + (($value - $minval) * $step));
				$rr = $this->dbro->query("SELECT titolondx FROM pagine WHERE tag='$key'")->fetch_array();
				$aout[] = '<span style="font-size: ' . $size . 'px;">' . '{' . $rr['titolondx'] . '|' . $key . '}</span> ';
			}
			// una bella agitatina
			shuffle($aout);
			foreach ($aout as $o) {
				$b .= $this->espandiLink($o);
			}
		}
		// pagina piu' recente
		if ('paginapiurecente' == $tg) {
			$mr = $this->dbro->query("SELECT lastmod FROM pagine ORDER BY lastmod DESC LIMIT 1")->fetch_array();
			$b .= date('Ym.d', $mr[0]);
		}
		// legenda delle abbreviazioni degli episodi, libri, pianeti
		if ('legenda' == $tg) {
			$mq = $this->dbro->query("SELECT etichetta,descrizione,icona,categoria FROM episodicampi ORDER BY categoria,descrizione");
			$oldcat = '*';
			while ($mr = $mq->fetch_array()) {
				if ($oldcat != $mr['categoria']) {
					if ($oldcat != '*') {
						$b .= "\n</table>";
					}
					$b .= "\n<div class='legendaintestazione'>$mr[categoria]</div>";
					$b .= "\n<table border='0' class='legenda'>";
				}
				$oldcat = $mr['categoria'];
				$b .= "\n<tr><td align='center'>";
				if ('' != $mr['icona']) {
					$b .= $this->costruisciURLimmagineSkin($mr['icona'], $mr['etichetta']) . ' />';
				} else {
					$b .= "<b>$mr[etichetta]</b> ";
				}
				$b .= "</td><td align='left'>" . $this->espandiLink($mr['descrizione']) . '</td></tr>';
			}
			$b .= "\n</table>";
		}
		// tabella episodi
		if (substr($tg, 0, 3) == 'tab') {
			$idsezione = substr($tg,4);
			$b .= "<p>Questa visualizzazione &egrave; <b>provvisoria</b>, verr&agrave; migliorata in seguito.</p>";
			$b .= "\n<table class='tabellaepisodi' border='1' cellpadding='1'>";
			$b .= "\n<tr>";
			$b .= "<th><b>Titolo US</b></th>";
			$b .= "<th><b>Prod.</b></th>";
			$b .= "<th><b>Seq.</b></th>";
			$b .= "<th><b>Data US</b></th>";
			$b .= "<th><b>Titolo IT</b></th>";
			$b .= "\n</tr>";
			$qe = $this->dbro->query("SELECT titolo,idpagina FROM pagine WHERE tipo=1 AND idsezione=$idsezione AND hidden=0");
			while ($re = $qe->fetch_array()) {
				$b .= "\n<tr>";
				//titolo US
				$b .= "\n<td>$re[titolo]</td>";
				//numero di produzione
				$qd = $this->dbro->query("SELECT valore FROM episodivalori WHERE idpagina=$re[idpagina] AND idcampo=42")->fetch_array();
				$b .= "\n<td>$qd[valore]</td>";
				//sequenza
				$qd = $this->dbro->query("SELECT valore FROM episodivalori WHERE idpagina=$re[idpagina] AND idcampo=1")->fetch_array();
				$b .= "\n<td>$qd[valore]</td>";
				//data US
				$qd = $this->dbro->query("SELECT valore FROM episodivalori WHERE idpagina=$re[idpagina] AND idcampo=2")->fetch_array();
				$b .= "\n<td>$qd[valore]</td>";
				//titolo IT
				$qd = $this->dbro->query("SELECT valore FROM episodivalori WHERE idpagina=$re[idpagina] AND idcampo=11")->fetch_array();
				$b .= "\n<td>$qd[valore]</td>";
				$b .= "\n</tr>";
			}
			$b .= "\n</table>";
		}
		// citazione casuale
		if ('randomquote' == $tg) {
			$cr = $this->dbro->query("SELECT idpagina,testo FROM capitoli WHERE idcapitolotipo=15 ORDER BY RAND() LIMIT 1")->fetch_array();
			$er = $this->dbro->query("SELECT titolo,tag FROM pagine WHERE idpagina=$cr[0]")->fetch_array();
			$b .= $this->espandiLink($cr[1]) . '<br />';
			$b .= '(' . $this->espandiLink('{' . $er[0] . '|' . $er[1] . '}') . ')';
		}
		// tabella notizie
		if ('notizie' == $tg) {
			$b .= "<table border='0' width='80%' cellspacing='3' align='center'>";
			$q = $this->dbro->query("SELECT * FROM notizie JOIN notiziesorgenti ON notizie.idnotiziasorgente=notiziesorgenti.idnotiziasorgente ORDER BY data DESC");
			while ($r = $q->fetch_array()) {
				$icn = '{' . $this->costruisciURLImmagineSkin($r['icona'], $r['iconatxt']) . ' />|@' . $r['iconaurl'] . '}';
				$b .= "\n<tr>";
				$b .= "<td align='center'>" . $this->espandiLink($icn) . '</td>';
				$b .= "<td>";
				if ($r['data'] > 1 ) {
					$b .= date('j.n.Y', $r['data']) . ' - ';
				}
				$b .= $this->espandiLink($r['testo']) . '</td>';
				$b .= '</tr>';
			}
			$b .= "\n</table>";
		}
		// elenco skin
		if ('skin' == $tg) {
			$q = $this->dbro->query("SELECT * FROM skin WHERE isvisibile=1 ORDER BY isdefault DESC,nome");
			while ($r = $q->fetch_array()) {
				$b .= "\n<div class='elencoskin'>";
				$b .= "<form name='$r[dir]' method='POST' action='" . $this->costruisciURL($this->aPagina['tag']) . "'>";
				$b .= "<b>";
				$b .= "$r[nome]</b> - $r[descrizione] ($r[autore]) ";
				if ($_SESSION['skin'] == $r['dir']) {
					// nulla
				} else {
					$b .= "<input type='hidden' name='skin' value='$r[dir]' />";
					$b .= "<input class='elencoskinbtn' type='submit' name='submit' value='Scegli questa skin' /></form>";
				}
				$b .= '</div>';
			}
		}
		return $b;
	}


	/**
	 * recurringCharacters()
	 * 
	 * Visualizza la pagina dei personaggi ricorrenti
	 *
	 * @todo migliorare la decisione della sezione da visualizzare
	 *
	 * 20080620 prima versione
	 *
	 */
	private function recurringCharacters() {
		$b = '';
		// stabilisco quale sia la sezione su cui filtrare le query
		// l'ID della sezione a cui viene assegnato $sezione e' quello degli episodi
		// calblarlo cosi' e' poco elegante, ma e' veloce
		if (320 == $this->aPagina['idsezione']) {
			$sezione = 17;
		} elseif (352 == $this->aPagina['idsezione']) {
			$sezione = 18;
		} elseif (340 == $this->aPagina['idsezione']) {
			$sezione = 21;
		} elseif (345 == $this->aPagina['idsezione']) {
			$sezione = 1;
		} elseif (349 == $this->aPagina['idsezione']) {
			$sezione = 22;
		}
		$qrpers = $this->dbro->query("SELECT guest.idpagina AS id,guest.idpaginapersonaggio AS idpgpers,pagine.idsezione,pgpers.titolo AS personaggio,
		                                     pgpers.tag AS perstag,pgpers.chiavesort,COUNT(guest.idpaginapersonaggio) AS quanti
		                              FROM guest
		                              JOIN pagine ON guest.idpagina=pagine.idpagina
		                              JOIN pagine AS pgpers ON guest.idpaginapersonaggio=pgpers.idpagina
		                              WHERE pagine.idsezione=$sezione
		                              GROUP BY guest.idpaginapersonaggio
		                              ORDER BY quanti DESC, pgpers.chiavesort");
		if ($qrpers->num_rows > 0) {
			$quanti = 100;
			$buf = '';
			// MySQL non accetta il risultato di COUNT() nella clausola WHERE
			while (($rpers = $qrpers->fetch_array()) and ($quanti > 1)) {
				$quanti = $rpers['quanti'];
				if ($quanti > 2){
					$buf .= "\n<dl><dt><b>" . '{' . $rpers['personaggio'] . '|' . $rpers['perstag'] . '}' . "</b> ($rpers[quanti]):</dt>\n<dd>";
					$qepers = $this->dbro->query("SELECT guest.idpagina,guest.idpaginacast,pagine.titolondx AS episodio,pagine.tag
					                              FROM guest
					                              JOIN pagine ON guest.idpagina=pagine.idpagina
					                              WHERE guest.idpaginapersonaggio=$rpers[idpgpers]
					                              ORDER BY pagine.tag");
					while ($epers = $qepers->fetch_array()) {
						$buf .= '{' . $epers['episodio'] . '|' . $epers['tag'] . '}, ';
					}
					$buf = substr($buf, 0, -2);
					$buf .= "</dd>\n</dl>";
				}
			}	
			$b = $this->espandiLink($buf);
		}
		return $b;
	}


	/**
	 * timelineST()
	 * 
	 * Visualizzazione di una pagina della timeline di Star Trek
	 *
	 * 20111105: adattato tag IMG alle specifiche XHTML
	 *
	 */
	private function timelineST() {
		$b = "\n<div class='timelinestcontenitore'>";
		$qrd = $this->dbro->query("SELECT * FROM timelinest WHERE anno=" . $this->aPagina['idpagina'] . " ORDER BY tordine");
		while ($rd = $qrd->fetch_array()){
			// analizzo il testo per vedere se ci sono delle indicazioni di DIV
			$aTesto = $this->estraiDIV($rd['evento']);
			if ('' == $aTesto['classe']) {
				$b .= "\n<div class='timelinest'>";
			} else {
				$b .= "\n<div class='$aTesto[classe]'>";
			}
			// e' specificata l'icona della fonte?
			if ($rd['idicona'] > 0) {
				$b .= $this->costruisciURLimmagine($rd['idicona']) . " class='timelinesticona' />";
			}
			if ($rd['stardate'] > 0) {
				$b .= "<b>Data Stellare $rd[stardate].</b> ";
			}
			$b .= $this->espandiLink($aTesto['testo']) . "</div>";
			// c'e' un commento?
			if ('' != $rd['commento']) {
				$b .= "\n<div class='timelinestcommento'>" . $this->espandiLink($rd['commento']) . "</div>";
			}
		}
		$b .= "\n</div>";
		return $b;
	}


	/**
	 * eventiTrek()
	 * 
	 * Visualizzazione di una pagina degli eventi Trek
	 *
	 * 20111105: adattato tag IMG alle specifiche XHTML
	 *
	 */
	private function eventiTrek() {
		$b = "\n<div class='eventitrekcontenitore'>";
		$qrd = $this->dbro->query("SELECT * FROM eventitrek WHERE anno=" . $this->aPagina['idpagina'] . " ORDER BY tordine");
		while ($rd = $qrd->fetch_array()){
			// analizzo il testo per vedere se ci sono delle indicazioni di DIV
			$aTesto = $this->estraiDIV($rd['evento']);
			if ('' == $aTesto['classe']) {
				$b .= "\n<div class='eventitrek'>";
			} else {
				$b .= "\n<div class='$aTesto[classe]'>";
			}
			// e' specificata l'icona della fonte?
			if ($rd['idicona'] > 0) {
				$b .= $this->costruisciURLimmagine($rd['idicona']) . " class='eventitrekicona' />";
			}
			$b .= $this->espandiLink($aTesto['testo']) . "</div>";
			// c'e' un commento?
			if ('' != $rd['commento']) {
				$b .= "\n<div class='eventitrekcommento'>" . $this->espandiLink($rd['commento']) . "</div>";
			}
		}
		$b .= "\n</div>";
		return $b;
	}


	/**
	 * readSetup($tag)
	 * 
	 * Legge un valore dalla tabella di setup
	 *
	 * @param string $tag tag da leggere
	 *
	 * 20080531 prima versione
	 *
	 */
	private function readSetup($tag) {
		$r = $this->dbro->query("SELECT * FROM setup WHERE tag='$tag'")->fetch_array();
		$retval = trim($r['valore']);
		return $retval;
	}


	/**
	 * readStore($tag)
	 * 
	 * Legge un valore dalla tabella di store
	 *
	 * @param string $tag tag da leggere
	 *
	 * 20080727 prima versione
	 *
	 */
	private function readStore($tag) {
		$r = $this->dbrw->query("SELECT * FROM store WHERE tag='$tag'")->fetch_array();
		$retval = trim($r['valore']);
		return $retval;
	}


	/**
	 * writeStore($tag, $valore)
	 * 
	 * Scrive un valore dalla tabella di store
	 *
	 * @param string $tag tag da scrivere
	 * @param string $valore valore da scrivere
	 *
	 * 20080727 prima versione
	 *
	 */
	private function writeStore($tag, $valore) {
		$valore = $this->dbrw->escape_string($valore);
		$this->dbrw->query("UPDATE store SET valore='$valore' WHERE tag='$tag'");
	}


	/**
	 * aggiornaUltimi()
	 * 
	 * Aggiorna le statistiche delle ultime modifiche e ricrea il file XML delle ultime modifiche
	 *
	 * 20091217: modifica traduzione codifica testi 
	 * 20151225: supporto https
	 *
	 */
	private function aggiornaUltimi() {
		$timetag = date('ymdH');
		if ($timetag != $this->readStore('tsultimi')) {
			$this->writeStore('tsultimi', $timetag);
			// facciamo pulizia
			$this->dbrw->query("TRUNCATE ultimi");
			$condizione = 'lastmod>(' . (time()-$this->aSetup['giornilast']) .')';
			// pagine normali
			$q = $this->dbro->query("SELECT tag,titolondx,lastmod,idsezione,titolo FROM pagine WHERE hidden='0' AND $condizione");
			while ($r = $q->fetch_array()) {
				$riga = $this->dbrw->escape_string('{' . $r['titolondx'] . '|' . $r['tag'] . '}');
				$titolo = $this->dbrw->escape_string($r['titolo']);
				$this->dbrw->query("INSERT INTO ultimi SET lastmod=$r[lastmod],riga='$riga',idsezione='$r[idsezione]',titolo='$titolo',tag='$r[tag]'");
			}
			// timeline Star Trek			
			$q = $this->dbro->query("SELECT DISTINCT anno FROM timelinest WHERE $condizione");
			while ($r = $q->fetch_array()) {
        $qq = $this->dbro->query("SELECT lastmod FROM timelinest WHERE anno='$r[anno]' ORDER BY lastmod DESC LIMIT 1;");
        if ($qq->num_rows > 0) {
                $rr = $qq->fetch_array();
                $riga = $this->dbrw->escape_string('{' . $r['anno'] . '|ttt' . $r['anno'] . '}');
                $this->dbrw->query("INSERT INTO ultimi SET lastmod=$rr[lastmod],riga='$riga',idsezione=25,titolo='Timeline di Star Trek: $r[anno]',tag='ttt$r[anno]'");
				}
			}
			// eventi Trek
			$q = $this->dbro->query("SELECT DISTINCT anno FROM eventitrek WHERE lastmod>$condizione");
			while ($r = $q->fetch_array()) {
				$qq = $this->dbro->query("SELECT lastmod FROM eventitrek WHERE anno='$r[anno]' ORDER BY lastmod DESC LIMIT 1;");
				if ($qq->num_rows > 0) {
					$rr = $qq->fetch_array();
					$riga = $this->dbrw->escape_string('{' . $r['anno'] . '|tte' . $r['anno'] . '}');
					$this->dbrw("INSERT INTO ultimi SET lastmod=$rr[lastmod],idsezione=402,riga='$riga',titolo='Eventi Trek: $r[anno]',tag='tte$r[anno]'");
				}
			}
			//e ora scrivo il file XML per il feed
			$buf = "<?xml version=\"1.0\"?>\n";
			$buf .= "<rss version=\"2.0\">\n";
			$buf .= "<channel>\n";
			$buf .= "<title>HyperTrek: ultimi aggiornamenti</title>\n";
			$buf .= "<description>Elenco delle pagine aggiornate negli ultimi " . $this->aSetup['giornilast']/86400 . " giorni</description>\n";
			$buf .= "<link>https://hypertrek.info</link>\n";
			$buf .= "<language>it-it</language>\n";
			$buf .= "<pubDate>" . date('r') . "</pubDate>\n";
			$buf .= "<lastBuildDate>" . date('r') . "</lastBuildDate>\n";
			$buf .= "<generator>HyperTrek Content Engine " . $this->aSetup['versione'] . "</generator>\n\n";
			$buf .= "<ttl>70</ttl>\n\n";
			$qrd = $this->dbrw->query("SELECT ultimi.*,riferimento FROM ultimi JOIN hypertrek.sezioni ON ultimi.idsezione=sezioni.idsezione ORDER BY lastmod DESC");
			// $trans = get_html_translation_table(HTML_ENTITIES);
			while ($rd = $qrd->fetch_array()) {
				$buf .= "\n<item>\n";
				$buf .= " <title>" . strip_tags($rd['titolo']) . "</title>\n";
				$buf .= " <description>" . strip_tags($rd['titolo']) . "</description>\n";
				$buf .= " <category>" . $rd['riferimento'] . "</category>\n";
				$buf .= " <link>https://hypertrek.info/index.php/$rd[tag]</link>\n";
				$buf .= " <guid>https://hypertrek.info/index.php/$rd[tag]</guid>\n";
				$buf .= " <pubDate>" . date('r', $rd['lastmod']) . "</pubDate>\n";
				$buf .= "</item>\n";
			}
			$buf .= "</channel>\n";
			$buf .= "</rss>\n";
			file_put_contents($this->aSetup['rsslast'], $buf);
		}
	}


	/**
	 * ultime10Pagine()
	 * 
	 * Aggiunge ai link a destra le ultime 10 pagine modificate
	 *
	 * 20080810 prima versione
	 *
	 */
	private function ultime10Pagine() {
		$q = $this->dbro->query("SELECT tag,titolondx FROM pagine WHERE hidden='0' ORDER BY lastmod DESC LIMIT 10");
		while ($r = $q->fetch_array()) {
			$this->aggiungiRiferimento('Ultime dieci pagine modificate', $this->espandiLink('{' . $r['titolondx'] . '|' . $r['tag'] . '}'));
		}
	}


	/**
	 * riferimentiEpisodivalori($idcampo, $etichetta)
	 * 
	 * Aggiunge i riferimenti nella tabella episodivalori
	 * 
	 * 20091226: creata la funzione
	 *
 	 */
	private function riferimentiEpisodivalori($idcampo, $etichetta) {
		// aggiungo i riferimenti da database: link diretto
		$q = $this->dbro->query("SELECT valore,idcampo,idpagina 
		                         FROM episodivalori 
		                         WHERE idcampo='$idcampo' AND valore LIKE '%" . $this->aPagina['tag'] . '}' . "%'");
		if ($q->num_rows > 0) {
			while ($r = $q->fetch_array()) {
				$rr = $this->dbro->query("SELECT idpagina,tag,titolondx FROM pagine WHERE idpagina='$r[idpagina]'")->fetch_array();
				$this->aggiungiRiferimento($etichetta, $this->espandiLink('{' . $rr['titolondx'] . '|' . $rr['tag'] . '}'));
			}
		}
	}


	/**
	 * estraiDIV($testo)
	 * 
	 * Estrae l'indicazione della classe del DIV dal testo e restituisce il testo pulito
	 * Restituisce un array con l'indicazione della classe e il testo bonificato
	 * 
	 * @param string $testo testo da analizzare
	 *
	 * 20081019 prima versione
	 *
 	 */
	private function estraiDIV($testo) {
		$retval = array();
		// pulizia
		$testo = trim($testo);
		// vediamo se c'e' l'indicazione del DIV
		if ('@@@' == substr($testo, 0, 3)) {
			list($tag,$testovero) = explode(' ', $testo, 2);
			// tolgo le chiocciole
			$tag = substr($tag, 3);
			// esplodo il valore
			list($campo,$classe) = explode('=', $tag);
			$campo = strtolower($campo);
			// se riconosco il comando, lo eseguo, altrimenti restituisco la stringa cosi' com'e'
			if ('div' == $campo) {
				$retval['classe'] = $classe;
				$retval['testo'] = $testovero;
			} else {
				$retval['classe'] = '';
				$retval['testo'] = $testo;
			}
		} else { // nessun @@@
			$retval['classe'] = '';
			$retval['testo'] = $testo;
		}
		return $retval;
	}


	/**
	 * logModifiche()
	 * 
	 * Mostra i log delle modifiche alle pagine
	 * 
	 * 20091212: modificata visualizzazione con data scritta una volta sola
	 * 20111105: adattati i tag alle specifiche XHTML
	 *
 	 */
	private function logModifiche() {
		$b = "\n<div class='logmodifiche'>";
		$q = $this->dbro->query("SELECT dataora,modifica,titolondx,tag FROM modifiche LEFT JOIN pagine ON modifiche.idpagina=pagine.idpagina ORDER BY dataora DESC");
		$vecchiadata = '*';
		while ($r = $q->fetch_array()) {
			$questadata = date('j.n.Y', $r['dataora']);
			if ($vecchiadata == '*') {
				$b .= "\n<p><b>$questadata</b><br />";
			} elseif ($vecchiadata != $questadata) {
				$b .= "</p>\n<p><b>$questadata</b><br />";
			}
			if ('' != $r['titolondx']) {
				$b .= "<b>" . $this->espandilink('{' . $r['titolondx'] . '|' . $r['tag'] . '}') . "</b>: ";
			}
			$b .= $this->espandilink($r['modifica']) . '<br />';
			$vecchiadata = $questadata;
		}
		$b .= "</p>\n</div'>";
		return $b;
	}


	/**
	 * stripLink($testo)
	 * 
	 * Analizza il testo in input e lo restituisce in output togliendo i link nelle sequenze tra parentesi graffe ma senza espanderli
	 * 
	 * @param string $testo testo da analizzare
	 *
	 * 20090405 prima versione
	 *
	 */
	function stripLink($testo) {
		preg_match_all( "!(\{[^\{]+\})!i" , $testo , $aMatch );
		for ($i = 0; $i < count ($aMatch[1]); $i++)  {
			//toglie le parentesi graffe
			$sanitized = preg_replace("!(\{|\})!" , "" , $aMatch[1][$i]);
			// explode e' piu' veloce
			list ($hot, $tag) = explode('|' , $sanitized);
			$newb = trim($hot);
			$testo = str_replace( $aMatch[1][$i] , $newb, $testo);
		}
		return $testo;
	}

}

### END OF FILE ###