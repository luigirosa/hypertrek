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
 * Variabili globali
 *
 * Definizione delle variabili globali
 *
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20091122: charset UTF-8
 * 20100131: nuova struttura dei menu
 * 20111105: tolto il DTD dal setup e reso costante
 * 20120818: il sitemap viene creato autonomamente dal motore
 * 20151225: supporto https e baseurl
 * 20160522: GitHub, alcuni parametri di configurazione vengono trasferiti in setup.ini
 *
 */

if(!defined('HYPERTREK')) {
	header ('Location: https://hypertrek.info/');
	die();
}

/**
 * array $Setup
 * @global array $Setup parametri di configurazione della procedura di visualizzazione
 *
 */
 
// leggo alcuni parametri da setup.ini
$a = parse_ini_file('setup.ini', true);
$Setup['db_ro_host']     = $a['sql']['host1'];       // host db readonly
$Setup['db_ro_user']     = $a['sql']['user1'];       // utente db readonly
$Setup['db_ro_pass']     = $a['sql']['password1'];   // password db readonly
$Setup['db_ro_database'] = $a['sql']['database1'];   // database readonly
$Setup['db_rw_host']     = $a['sql']['host2'];       // host db readwrite
$Setup['db_rw_user']     = $a['sql']['user2'];       // utente db readwrite
$Setup['db_rw_pass']     = $a['sql']['password2'];   // password db readwrite
$Setup['db_rw_database'] = $a['sql']['database2'];   // database v

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
$Setup['baseurl']         = 'https://hypertrek.info'; // URL di base del sito senza slash finale

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

### END OF FILE ###