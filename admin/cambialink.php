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
 * Modifica link
 * 
 * @author Luigi Rosa <lrosa@hypertrek.info>
 *
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: aggiunta chiusura html; require_once -> require
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

intestazione();

function controlla($buf,$cerca,$cambia) {
	global $nuovo;
	$retval = false;
	while ( !(strpos($buf, $cerca) === false) ) {
		$anchor = '';
		$aperta = strpos($buf, '{');
		$chiusa = strpos($buf, '}');
		$blocco = substr($buf, $aperta, ($chiusa - $aperta + 1) );
		$aBlocco = explode('|', $blocco);
		$aBlocco[0] = trim(substr($aBlocco[0], 1)); 	
		$aBlocco[1] = trim(substr($aBlocco[1], 0, -1));
		// ho esploso la stringa
		echo "\n     <!-- *$aBlocco[1]* *$cerca* -->";
		if ($aBlocco[1] == $cerca) {
			$retval = true;
			// cleanup
			$aBlocco[0] = str_replace("\n", '', $aBlocco[0]);
			$aBlocco[0] = str_replace("\r", '', $aBlocco[0]);
			$aBlocco[0] = str_replace("\t", '', $aBlocco[0]);
			$aBlocco[0] = trim($aBlocco[0]);
			$buf = str_replace($blocco, '{' . $aBlocco[0] . '|' . $cambia . '}' , $buf);
		}
	}
	$nuovo = $buf;
	return $retval;
}

function cercatag($dacercare, $buf) {
	global $questapagina,$contatore;
	preg_match_all( "!(\{[^\{]+\})!i" , $buf , $aMatch );
	for ($i = 0; $i < count ($aMatch[1]); $i++)  {
		$blocco = $aMatch[1][$i];
		//toglie le parentesi graffe
		$sanitized = preg_replace("!(\{|\})!" , "" , $aMatch[1][$i]);
		// explode e' piu' veloce
		list ($hot, $tag) = explode('|' , $sanitized);
		$hot = trim($hot);
		$tag = trim($tag);
		if ($tag == $dacercare) {
			echo "\n<br>$questapagina: <b>$blocco</b>";
			$contatore++;
		}
		$buf = str_replace($blocco, '', $buf);
	}
}

function cambiatag($dacercare, $dacambiare, $stringasql, $buf) {
	global $questapagina,$contatore, $db;
	preg_match_all( "!(\{[^\{]+\})!i" , $buf , $aMatch );
	$contaprima = $contatore;
	for ($i = 0; $i < count ($aMatch[1]); $i++)  {
		$blocco = $aMatch[1][$i];
		//toglie le parentesi graffe
		$sanitized = preg_replace("!(\{|\})!" , "" , $aMatch[1][$i]);
		// explode e' piu' veloce
		list ($hot, $tag) = explode('|' , $sanitized);
		$hot = trim($hot);
		$tag = trim($tag);
		if ($tag == $dacercare) {
			$hot = str_replace("\n", '', $hot);
			$hot = str_replace("\r", '', $hot);
			$hot = str_replace("\t", '', $hot);
			$nuovotag = '{' . $hot . ' | ' . $dacambiare . '}';
			//echo "\n<br>$questapagina: <b>$blocco</b>";
			$contatore++;
			$buf = str_replace($blocco, $nuovotag, $buf);
	        //echo "<p>$buf</p>";
		}
	}
	// il contatore si e' incrementato, aggiorno
	if ($contaprima != $contatore) {
		$buf = $db->escape_string($buf);
		$s = str_replace('@@@', $buf, $stringasql);
        echo "<p>$s;</p>";
		//commmit
		$db->query($s);
	}
	return ($buf);
}


// devo cambiare un tag
if (isset($_POST['azione']) and $_POST['azione'] == 'cambia') {
	$tagdacercare = trim($_POST['cerca']);
	$tagnuovo = trim($_POST['cambia']);
	$contatore = 0;
	//
	$qr = $db->query("SELECT * FROM capitoli JOIN pagine ON capitoli.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
        $questapagina = "$r[titolo] ($r[tag]) capitolo $r[idcapitolotipo] $r[ordine]";
        cambiatag($tagdacercare, $tagnuovo, "UPDATE capitoli SET testo='@@@' WHERE idcapitolo=$r[idcapitolo]", $r['testo']);
	}
	
	$qr = $db->query("SELECT * FROM piepagina JOIN pagine ON piepagina.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) pie` pagina $r[ordine]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE piepagina SET testo='@@@' WHERE idpiepagina=$r[idpiepagina]", $r['testo']);
	}

	$qr = $db->query("SELECT * FROM riferimenti JOIN pagine ON riferimenti.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) riferimento $r[categoria]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE riferimenti SET riferimento='@@@' WHERE idriferimento=$r[idriferimento]", $r['riferimento']);
		if ($r['backlink']==$tagdacercare){
			$db->query("UPDATE riferimenti SET backlink='$tagnuovo' WHERE idriferimento=$r[idriferimento]");
			$contatore++;
		}
	}

	$qr = $db->query("SELECT * FROM testi JOIN pagine ON testi.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) testo $r[ordine]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE testi SET testo='@@@' WHERE idtesto=$r[idtesto]", $r['testo']);
	}

	$qr = $db->query("SELECT * FROM quantevolte JOIN pagine ON quantevolte.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) testo quante volte $r[qvordine]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE quantevolte SET testo='@@@' WHERE idquantevolte=$r[idquantevolte]", $r['testo']);
		$questapagina = "$r[titolo] ($r[tag]) classifica quante volte $r[qvordine]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE quantevolte SET classifica='@@@' WHERE idquantevolte=$r[idquantevolte]", $r['classifica']);
	}

	$qr = $db->query("SELECT * FROM tabelle");
	while ($r = $qr->fetch_array()) {
		$questapagina = "tabella $r[ttag] riga $r[tordine] colonna 1";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE tabelle SET prima='@@@' WHERE idtabella=$r[idtabella]", $r['prima']);
		$questapagina = "tabella $r[ttag] riga $r[tordine] colonna 2";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE tabelle SET seconda='@@@' WHERE idtabella=$r[idtabella]", $r['seconda']);
		$questapagina = "tabella $r[ttag] riga $r[tordine] colonna 3";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE tabelle SET terza='@@@' WHERE idtabella=$r[idtabella]", $r['terza']);
	}

	$qr = $db->query("SELECT * FROM guest JOIN pagine ON guest.idpagina=pagine.idpagina WHERE guest.personaggio <>''");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) guest $r[personaggio]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE guest SET personaggio='@@@' WHERE idguest=$r[idguest]", $r['personaggio']);
	}

	$qr = $db->query("SELECT idcampo,descrizione FROM episodicampi");
	while ($r = $qr->fetch_array()) {
		$questapagina = "episodicampi $r[descrizione] ";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE episodicampi SET descrizione='@@@' WHERE idcampo=$r[idcampo]", $r['descrizione']);
	}

	$qr = $db->query("SELECT * FROM episodivalori JOIN pagine ON episodivalori.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) episodiovalore $r[valore]";
		cambiatag($tagdacercare, $tagnuovo, "UPDATE episodivalori SET valore='@@@' WHERE idvalore=$r[idvalore]", $r['valore']);
	}

	$qr = $db->query("SELECT idpagina,tag,tagprima,tagdopo FROM pagine");
	while ($r = $qr->fetch_array()) {
		if ($r['tagprima'] == $tagdacercare) {
			$db->query("UPDATE pagine SET tagprima='$tagnuovo' WHERE idpagina=$r[idpagina]");
			$contatore++;
		}
		if ($r['tagdopo'] == $tagdacercare) {
			$db->query("UPDATE pagine SET tagdopo='$tagnuovo' WHERE idpagina=$r[idpagina]");
			$contatore++;
		}
	}

	$qr = $db->query("SELECT idpagina,tag FROM pagine WHERE tag='$tagdacercare'");
	if ($r = $qr->fetch_array()) {
		$db->query("UPDATE pagine SET tag='$tagnuovo' WHERE idpagina=$r[idpagina]");
	}

	echo "<br>&nbsp;<br><b>$contatore</b> occorrenze<br>&nbsp;<br>";
}


// devo elencare le occorrenze di un tag
if (isset($_POST['azione']) and $_POST['azione'] == 'cerca') {
	$tagdacercare = trim($_POST['cerca']);
	$contatore = 0;
	//
	$qr = $db->query("SELECT * FROM capitoli JOIN pagine ON capitoli.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) capitolo $r[idcapitolotipo] $r[ordine]";
		cercatag($tagdacercare, $r['testo']);
	}
    
	$qr = $db->query("SELECT * FROM piepagina JOIN pagine ON piepagina.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) pie` pagina $r[ppordine]";
		cercatag($tagdacercare, $r['testo']);
	}
	
	$qr = $db->query("SELECT * FROM riferimenti JOIN pagine ON riferimenti.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
		$questapagina = "$r[titolo] ($r[tag]) riferimento $r[categoria]";
		cercatag($tagdacercare, $r['riferimento']);
		if ($r['backlink']==$tagdacercare){
			echo "\n<br>$r[titolo] ($r[tag]) backlink $r[backlink]";
			$contatore++;
		}
	}
	
	$qr = $db->query("SELECT * FROM testi JOIN pagine ON testi.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
        $questapagina = "$r[titolo] ($r[tag]) testo $r[ordine]";
        cercatag($tagdacercare, $r['testo']);
	}
	//
	$qr = $db->query("SELECT * FROM quantevolte JOIN pagine ON quantevolte.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
        $questapagina = "$r[titolo] ($r[tag]) testo quante volte $r[qvordine]";
        cercatag($tagdacercare, $r['testo']);
        $questapagina = "$r[titolo] ($r[tag]) classifica quante volte $r[qvordine]";
        cercatag($tagdacercare, $r['classifica']);
	}
	//
	$qr = $db->query("SELECT * FROM tabelle");
	while ($r = $qr->fetch_array()) {
        $questapagina = "tabella $r[ttag] riga $r[tordine] colonna 1";
        cercatag($tagdacercare, $r['prima']);
        $questapagina = "tabella $r[ttag] riga $r[tordine] colonna 2";
        cercatag($tagdacercare, $r['seconda']);
        $questapagina = "tabella $r[ttag] riga $r[tordine] colonna 3";
        cercatag($tagdacercare, $r['terza']);
	}

	$qr = $db->query("SELECT * FROM guest JOIN pagine ON guest.idpagina=pagine.idpagina WHERE guest.personaggio <>''");
	while ($r = $qr->fetch_array()) {
        $questapagina = "$r[titolo] ($r[tag]) guest $r[personaggio]";
        cercatag($tagdacercare, $r['personaggio']);
	}

	$qr = $db->query("SELECT descrizione FROM episodicampi");
	while ($r = $qr->fetch_array()) {
        $questapagina = "episodicampi $r[descrizione] ";
        cercatag($tagdacercare, $r['descrizione']);
	}

	$qr = $db->query("SELECT * FROM episodivalori JOIN pagine ON episodivalori.idpagina=pagine.idpagina");
	while ($r = $qr->fetch_array()) {
        $questapagina = "$r[titolo] ($r[tag]) episodiovalore $r[valore]";
        cercatag($tagdacercare, $r['valore']);
	}

	$qr = $db->query("SELECT tag,tagprima,tagdopo FROM pagine");
	while ($r = $qr->fetch_array()) {
		if ($r['tagprima']==$tagdacercare){
                echo "\n<br>$r[tag] tagprima $r[tagprima]";
                $contatore++;
        }
		if ($r['tagdopo']==$tagdacercare){
                echo "\n<br>$r[tag] tagdopo $r[tagdopo]";
                $contatore++;
        }
    }
    echo "<br>&nbsp;<br><b>$contatore</b> occorrenze<br>&nbsp;<br>";
}


echo "\n<form name='cambia' method='post' action='cambialink.php' target='main'>";
echo "\n<input type='hidden' name='azione' value='cambia'>";
echo "\n<table border='0'>";
echo "\n<tr><td>Cerca il tag:</td><td><input type='text' size='50' maxlength='100' name='cerca'></td></tr>";
echo "\n<tr><td>E sostituiscilo con:</td><td><input type='text' size='50' maxlength='100' name='cambia'></td></tr>";
echo "\n</table>";

echo "</table><p><input type='submit' value='E mo son cazzi'></p></form>";

echo "\n<form name='cerca' method='post' action='cambialink.php' target='main'>";
echo "\n<input type='hidden' name='azione' value='cerca'>";
echo "\n<table border='0'>";
echo "\n<tr><td>Cerca il tag:</td><td><input type='text' size='50' maxlength='100' name='cerca'></td></tr>";
echo "\n</table>";
echo "</table><p><input type='submit' value='Elenca il tag'></p></form>";

echo "</body></html>";

### END OF FILE ###