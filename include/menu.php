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
 * Gestione dei menu
 *
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20100131: nuova struttura dei menu
 * 20160522: GitHub
 *
 */

if(!defined('HYPERTREK')) {
	header ('Location: https://hypertrek.info/');
	die();
}


/**
 * AJAXcerca($aValori)
 * 
 * Elabora le ricerche
 * 
 * @version 2.1.4 20090502
 */

function AJAXcerca($aValori) {
	global $ht_db_ro,$ht_db_rw,$oPagina;	
	if (!isset ($_SESSION)) session_start();
	$oResp = new xajaxResponse();
	$oResp->assign("subCerca","disabled",false);
	$oResp->assign("subCerca","value","Cerca");
	$cercato = strip_tags(trim($aValori['cerca']));
	$aPagine = array();
	$aTimelinest = array();
	$aEventitrek = array();
	$aOut = array();
	$b = '';
	$c = 0;
	if ('' != $cercato and strlen($cercato) > 2) {
		$oResp->assign("indiceintestazione","innerHTML", "<div class='indiceintestazionetxt'>Risultato della ricerca di <u>" . HT_accentate($cercato) ."</u></div>");
		$cercato = $ht_db_ro->escape_string($cercato);
		// titoli
		$qr = $ht_db_ro->query("SELECT idpagina FROM pagine WHERE titolo LIKE '%$cercato%'");
		while ($r = $qr->fetch_array()) {
			$aPagine[] = $r['idpagina'];
		}
		// testi
		$qr = $ht_db_ro->query("SELECT idpagina FROM testi WHERE testo LIKE '%$cercato%'");
		while ($r = $qr->fetch_array()) {
			$aPagine[] = $r['idpagina'];
		}
		// capitoli
		$qr = $ht_db_ro->query("SELECT idpagina FROM capitoli WHERE testo LIKE '%$cercato%'");
		while ($r = $qr->fetch_array()) {
			$aPagine[] = $r['idpagina'];
		}
		// timelinest
		$qr = $ht_db_ro->query("SELECT anno FROM timelinest WHERE evento LIKE '%$cercato%' OR commento LIKE '%$cercato%' OR anno LIKE '%$cercato%'");
		while ($r = $qr->fetch_array()) {
			$aTimelinest[] = $r['anno'];
		}
		// eventi trek
		$qr = $ht_db_ro->query("SELECT anno FROM eventitrek WHERE evento LIKE '%$cercato%' OR commento LIKE '%$cercato%' OR anno LIKE '%$cercato%'");
		while ($r = $qr->fetch_array()) {
			$aEventitrek[] = $r['anno'];
		}
		// quante volte
		$qr = $ht_db_ro->query("SELECT idpagina FROM quantevolte WHERE testo LIKE '%$cercato%'");
		while ($r = $qr->fetch_array()) {
			$aPagine[] = $r['idpagina'];
		}
		$aPagine = array_unique($aPagine);
		$aTimelinest = array_unique($aTimelinest);
		// popolo l'array delle pagine
		foreach ($aPagine as $pg) {
			$r = $ht_db_ro->query("SELECT tag,titolondx,chiavesort,hidden FROM pagine WHERE idpagina='$pg'")->fetch_array();
			if ('0' == $r['hidden']) {
				$aOut[] = "$r[chiavesort]|$r[titolondx]|$r[tag]";
				$c++;
			}
		}
		//aggiungo la timeline di Star TRek
		foreach ($aTimelinest as $pg) {
			$aOut[] = "$pg|$pg|ttt$pg";
			$c++;
		}
		//aggiungo gli eventi Trek
		foreach ($aEventitrek as $pg) {
			$aOut[] = "$pg|$pg|tte$pg";
			$c++;
		}
		// metto tutto in ordine
		asort($aOut);
		// visualizzo
		foreach ($aOut as $pg) {
			list($trash,$titolo,$tag) = explode('|', $pg);
			$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$titolo|$tag" . '}') . '</div>';
		}
		if (0 == $c) $b .= "\n<div class='indicetesto'>Nessuna pagina trovata.</div>"; 
			elseif (1 == $c) $b .= "\n<div class='indicetesto'>Una pagina trovata.</div>"; 
			else $b .= "\n<div class='indicetesto'>$c pagine trovate.</div>";
	} else {
		$oResp->assign("indiceintestazione","innerHTML", "<div class='indiceintestazionetxt'>Nessun risultato</div>");
		$b = "\n<div class='indicetesto'>Non &egrave; stata specificata una parola da cercare o la parola &egrave; troppo corta.</div>";
	}
	$ccc = $ht_db_rw->escape_string($aValori['cerca']);
	$ht_db_rw->query("INSERT INTO cerca SET dataora=NOW(),cerca='$ccc',ip='$_SERVER[REMOTE_ADDR]',trovati=$c");
	$oResp->assign("indicetesto","innerHTML", "<div class='indicetesto'>" . HT_accentate($b) . "</div>");
	return $oResp;
}


/**
 * AJAXmenu($menutag)
 * 
 * Wrapper per usare la funzione di generazione del menu via AJAX
 * @version 2.2.0 
 *
 * @param string $menutag tag del menu da visualizzare
 * @param int $idindice indice da visualizzare
 *
 * 20100131: funzione creata
 *
 */

function AJAXmenu($menutag) {
	//error_log($menutag);
	$oResp = new xajaxResponse();
	$oResp->assign("indiceintestazione","innerHTML", HT_menu($menutag, 0, 'titolo'));
	$oResp->assign("indicetesto","innerHTML", HT_menu($menutag, 0, 'testo'));
	return $oResp;
}

/**
 * HT_menu($menutag, $idmenu, $azione)
 * 
 * Funzione principale di generazione del menu
 * @version 2.2.0 
 * 
 * @param string $menutag tag del menu da visualizzare
 * @param int $idindice indice da visualizzare
 * @param string $azione indica cosa deve ritornare la funzione
 *
 * 20100131: funzione creata
 * 20100201: Corretto baco che non visualizzava le voci nel giusto ordine
 *
 */
function HT_menu($menutag, $idmenu = 0, $azione) {
	global $ht_db_ro, $oPagina, $ht_db_rw; //, $HT_indice;
	// non so se serva davvero
	if (!isset ($_SESSION)) session_start();
	// buffer di ritorno
	$b = '';
	// flag che mi dice se devo visualizzare il lastmod
	$lastmod = FALSE;
	// tiriamo su il record
	if ('' != $menutag) {
		$qm = $ht_db_ro->query("SELECT * FROM menu WHERE tag='$menutag'");
	} else {
		if ($idmenu > 0) {
			$qm = $ht_db_ro->query("SELECT * FROM menu WHERE idmenu='$idmenu'");
		} else {
			$lastmod = TRUE;
		}
	}
	if (!$lastmod) {
		$rm = $qm->fetch_array();
		// reimposto il valore dell'indice corrente per potermelo portar dietro tra una pagina e l'altra
		$_SESSION['ndx'] = $rm['idmenu'];
	} else {
		$_SESSION['ndx'] = 0;
	}
	switch ($azione) {
		case 'titolo':
			//verifico se viene richiesto il lastmod
			if ($lastmod) {
				$b .= "\n<div class='indiceintestazionetxt'>Ultime modifiche</div>";
			} else {
				$b .= "\n<div class='indiceintestazionetxt'>$rm[sigla]</div>";
			}
			break;	
		case 'testo':
			if ($lastmod) {
				// ultime modifiche al sito
				$old = '*#*';
				$qrd = $ht_db_rw->query("SELECT lastmod,riga FROM ultimi ORDER BY lastmod DESC");
				if ($qrd->num_rows > 0) {
					while ($rd = $qrd->fetch_array()) {
						$oggi = date('j.n.Y', $rd['lastmod']);
						// rottura
						if ($oggi != $old){
							//if ('*#*' != $old) echo '</p>';
							$b .= "\n<div class='lastdata'>$oggi</div>";
							$old = $oggi;
						}
						$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink($rd['riga']) . '</div>';
					}
				}
			} else {
				if ($rm['parentid'] > 0) {
					$r = $ht_db_ro->query("SELECT sigla,tag FROM menu WHERE idmenu='$rm[parentid]' ORDER BY ordine")->fetch_array();
					$b .= "\n<div class='indicetestomenu'>" . $oPagina->espandiLink( '{&nbsp;&nbsp;&#8657;&nbsp;' . $r['sigla'] . '&nbsp;&#8657;|:' . $r['tag'] . '}') . '</div>';
				}
				// vedo se questo menu ha altre voci
				$q = $ht_db_ro->query("SELECT * FROM menu WHERE parentid='$rm[idmenu]' AND idmenu<>'$rm[idmenu]' ORDER BY ordine");
				while ($r = $q->fetch_array()) {
					$b .= "\n<div class='indicetestomenu'>" . $oPagina->espandiLink( '{' . $r['sigla'] . '|:' . $r['tag'] . '}') . '</div>';
				}
				// adesso vediamo se ci sono delle pagine
				if ('' == $rm['macro']) {
					$q = $ht_db_ro->query("SELECT tag,titolo,titolondx FROM paginemenu JOIN pagine ON paginemenu.idpagina=pagine.idpagina WHERE idmenu='$rm[idmenu]' ORDER BY chiavesort");
					while ($r = $q->fetch_array()) {
						if ('' == $r['titolondx']) {
							$t = $r['titolo'];
						} else {
							$t = $r['titolondx'];
						}
						$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$t|$r[tag]" . '}') . '</div>';
					}
				} else {
					// MACRO DELL'INDICE
					switch ($rm['macro']) {
						// Enterprise - titoli italiani
						case 'entita':
							$b .= menuTitoliItaliani('ent');
						break;
						// Enterprise - titoli originali
						case 'entori':
							$b .=  menuTitoliOriginali('ent');
						break;
						// Enterprise - produzione
						case 'entprod':
							$b .= menuTitoliProduzione('ent');
						break;
						// Serie Classica - titoli italiani
						case 'tosita':
							$b .= menuTitoliItaliani('tos');
						break;
						// Serie Classica - titoli originali
						case 'tosori':
							$b .=  menuTitoliOriginali('tos');
						break;
						// Serie Classica - produzione
						case 'tosprod':
							$b .= menuTitoliProduzione('tos');
						break;
						// Serie Animata - titoli italiani
						case 'tasita':
							$b .= menuTitoliItaliani('tas');
						break;
						// Serie Animata - titoli originali
						case 'tasori':
							$b .=  menuTitoliOriginali('tas');
						break;
						// Serie Animata - produzione
						case 'tasprod':
							$b .= menuTitoliProduzione('tas');
						break;
						// The Next Generation - titoli italiani
						case 'tngita':
							$b .= menuTitoliItaliani('tng');
						break;
						// The Next Generation - titoli originali
						case 'tngori':
							$b .=  menuTitoliOriginali('tng');
						break;
						// The Next Generation - produzione
						case 'tngprod':
							$b .= menuTitoliProduzione('tng');
						break;
						// Deep Space Nine - titoli italiani
						case 'dsnita':
							$b .= menuTitoliItaliani('dsn');
						break;
						// Deep Space Nine - titoli originali
						case 'dsnori':
							$b .=  menuTitoliOriginali('dsn');
						break;
						// Deep Space Nine - produzione
						case 'dsnprod':
							$b .= menuTitoliProduzione('dsn');
						break;
						// Voyager - titoli italiani
						case 'voyita':
							$b .= menuTitoliItaliani('voy');
						break;
						// Voyager - titoli originali
						case 'voyori':
							$b .=  menuTitoliOriginali('voy');
						break;
						// Voyager - produzione
						case 'voyprod':
							$b .= menuTitoliProduzione('voy');
						break;
						// Timeline di Star Trek
						case 'timelinest':
							$qrd = $ht_db_ro->query("SELECT DISTINCT anno,annodisplay FROM timelinest ORDER BY anno");
							while ($rd = $qrd->fetch_array()) {
								$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$rd[annodisplay] | ttt$rd[anno]" . '}') . '</div>';
							}
						break;
						// Timeline eventi Trek
						case 'eventitrek':
						$qrd = $ht_db_ro->query("SELECT DISTINCT anno,annodisplay FROM eventitrek ORDER BY anno");
						while ($rd = $qrd->fetch_array()) {
							$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$rd[annodisplay] | tte$rd[anno]" . '}') . '</div>';
						}
						break;
					}
				}
				if ($rm['parentid'] > 0) {
					$r = $ht_db_ro->query("SELECT sigla,tag FROM menu WHERE idmenu='$rm[parentid]'")->fetch_array();
					$b .= "\n<div class='indicetestomenu'>" . $oPagina->espandiLink( '{&nbsp;&nbsp;&#8657;&nbsp;' . $r['sigla'] . '&nbsp;&#8657;|:' . $r['tag'] . '}') . '</div>';
				}
			}
		break;	
	} 
	//return HT_accentate($b);
	return $b;
}


/**
 * menuTitoliOriginali($serie)
 * 
 * Crea l'elenco dei titoli originali di una serie
 * @version 2.2.0
 *
 * @param string $serie serie da visualizzare
 *
 * 20100131: funzione creata
 *
 */
function menuTitoliOriginali($serie) {
	global $ht_db_ro, $oPagina;
	$b = '';
	$qrd = $ht_db_ro->query("SELECT titolondx,tag FROM pagine WHERE serie='$serie' AND hidden='0' ORDER BY chiavesort");
	while ($rd = $qrd->fetch_array()) {
		$b .=  "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$rd[titolondx]|$rd[tag]" . '}') . '</div>';
	}
	return $b;
}


/**
 * menuTitoliItaliani($serie)
 * 
 * Crea l'elenco dei titoli italiani di una serie
 * @version 2.2.0
 *
 * @param string $serie serie da visualizzare
 *
 * 20100131: funzione creata
 *
 */
function menuTitoliItaliani($serie) {
	global $ht_db_ro, $oPagina;
	$b = '';
	$qrd = $ht_db_ro->query("SELECT pagine.tag,episodivalori.valore 
	                         FROM pagine 
	                         JOIN episodivalori 
	                         ON pagine.idpagina=episodivalori.idpagina 
	                         WHERE serie='$serie' AND idcampo='11' AND hidden='0' ORDER BY episodivalori.valore");
	while ($rd = $qrd->fetch_array()) {
		$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$rd[valore]|$rd[tag]" . '}') . '</div>';
	}
	$b .= "\n<div class='indicetesto'>Criterio di ordinamento da migliorare</div>";
	return $b;
}


/**
 * menuTitoliProduzione($serie)
 * 
 * Crea l'elenco dei titoli in ordine di produzione di una serie
 * @version 2.2.0
 *
 * @param string $serie serie da visualizzare
 *
 * 20100131: funzione creata
 *
 */
function menuTitoliProduzione($serie) {
	global $ht_db_ro,$oPagina;
	$b = '';
	// innanzi tutto i[l] pilot
	$qrd =$ht_db_ro->query("SELECT pagine.tag,episodivalori.valore,pagine.idpagina,pagine.titolondx,(episodivalori.valore +1) AS ordine 
	                        FROM pagine 
	                        JOIN episodivalori 
	                        ON pagine.idpagina=episodivalori.idpagina 
	                        WHERE serie='$serie' AND idcampo='42' AND stagione='P' AND hidden='0' ORDER BY ordine");
	if ($qrd->num_rows > 0) {
		$b .= "\n<div class='lastdata'>Pilot</div>";
		while ($rd = $qrd->fetch_array()) {
			$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$rd[valore] $rd[titolondx]|$rd[tag]" . '}') . '</div>';
		}
	}
	//stagione regolare
	$qrd = $ht_db_ro->query("SELECT stagione,pagine.tag,episodivalori.valore,pagine.idpagina,pagine.titolondx,(episodivalori.valore +1) AS ordine 
	                         FROM pagine 
	                         JOIN episodivalori 
	                         ON pagine.idpagina=episodivalori.idpagina 
	                         WHERE serie='$serie' AND idcampo='42' AND stagione<>'P' AND hidden='0' ORDER BY ordine");
	$stagione = "*";
	while ($rd = $qrd->fetch_array()) {
		// rottura di stagione
		if ($rd['stagione'] != $stagione) {
			if ('' != $rd['stagione']) {
				$b .= "\n<div class='lastdata'>$rd[stagione]&ordf; stagione</div>";
			}
		}
		$b .= "\n<div class='indicetesto'>" . $oPagina->espandiLink('{' . "$rd[valore] $rd[titolondx]|$rd[tag]" . '}') . '</div>';
		$stagione = $rd['stagione'];
	}
	return $b;
}


/**
 * HT_accentate($s)
 * 
 * Sostituisce le lettere accentate con i relativi codici HTML. Serve ad aggirare un problema di IE7 con la libreria xajax.
 * 
 * @version 2.0 20080727
 * @param string $s stringa da analizzare
 */

function HT_accentate($s) {
	$aFrom = array('à', 'È', 'è', 'é', 'ì', 'ò', 'ù', 'ú', '«', '»', '²', 'ö', 'ñ', 'ü');
	$aTo = array('&agrave;', '&Egrave;', '&egrave;', '&eacute;', '&igrave;', '&ograve;', '&ugrave;', '&uacute;', '&laquo;', '&raquo;', '2', '&ouml;', '&ntilde;', '&uuml;');
	$retval = str_replace($aFrom, $aTo, $s);
	return $retval;
}


### END OF FILE ###