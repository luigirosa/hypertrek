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
 * Menu principale sistema autore
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require; aggiunta chiusura html
 * 20100109: cambio destinazione per input tag
 * 20100131: aggiunta gestione nuova struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

include('global.php');

intestazione();

$tag = '';
$idpagina = '';

if (isset($_POST['tag'])){
	$tag = $_POST['tag'];
	$r = $db->query("SELECT idpagina FROM pagine WHERE tag='$tag'")->fetch_array();
	$idpagina = $r['idpagina'];
}

if ('' == $tag or '' == $idpagina) $idpagina = 0;

//pagine
echo "\n<form action='menu.php' name='fpagine'>";
echo "\n<p><b>Pagine:<br><select name='pagine'>";
echo "<option value='tab_pagine.php?idpagina=0'>Nuova</option>";
$q = $db->query("SELECT titolo,idpagina FROM pagine ORDER BY titolo");
while ($r = $q->fetch_array()) {
	echo "<option value='menublank.php?idpagina=$r[idpagina]'>$r[titolo]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.pagine.options[this.form.pagine.selectedIndex].value"></p></form>';

//pagina tramite il tag
echo "\n<form action='menublank.php' name='ftpagine' method='post' target='main'>";
echo "\n<p><b>Tag:<br>";
echo "<input type='text' size='4' maxlength='4' name='pre'> <input type='text' size='20' name='tag'>";
echo '<input type="submit" alt="Go!" value="Go!"></p></form>';

// timeline st
echo "\n<form action='menu.php' name='ftimelinest'>";
echo "\n<p><b>Timeline ST:<br><select name='timelinest'>";
echo "<option value='tab_timelinest.php?idtimeline=0'>Nuovo</option>";
$q = $db->query("SELECT DISTINCT anno FROM timelinest ORDER BY anno");
while ($r = $q->fetch_array()) {
	echo "<option value='tab_timelinest.php?anno=$r[anno]'>$r[anno]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.timelinest.options[this.form.timelinest.selectedIndex].value"></p></form>';

// timeline real
echo "\n<form action='menu.php' name='feventitrek'>";
echo "\n<p><b>Timeline reale:<br><select name='eventitrek'>";
echo "<option value='tab_eventitrek.php?idtimeline=0'>Nuovo</option>";
$q = $db->query("SELECT DISTINCT anno FROM eventitrek ORDER BY anno");
while ($r = $q->fetch_array()) {
	echo "<option value='tab_eventitrek.php?anno=$r[anno]'>$r[anno]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.eventitrek.options[this.form.eventitrek.selectedIndex].value"></p></form>';

//immagini
echo "\n<form action='menu.php' name='fimmagini'>";
echo "\n<p><b>Immagini:<br><select name='immagini'>";
echo "<option value='tab_immagini.php?idimmagine=0'>Nuova</option>";
$q = $db->query("SELECT idimmagine,file FROM immagini ORDER BY file");
while ($r = $q->fetch_array()) {
	echo "<option value='tab_immagini.php?idimmagine=$r[idimmagine]'>$r[file]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.immagini.options[this.form.immagini.selectedIndex].value"></p></form>';

//tabelle
echo "\n<form action='menu.php' name='fpersonaggi'>";
echo "\n<p><b>Tabelle:<br><select name='tabelle'>";
echo "<option value='tab_tabelle.php?ttag=0'>Nuova</option>";
$q = $db->query("SELECT DISTINCT ttag FROM tabelle ORDER BY ttag");
while ($r = $q->fetch_array()) {
	echo "<option value='tab_tabelle.php?ttag=$r[ttag]'>$r[ttag]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.tabelle.options[this.form.tabelle.selectedIndex].value"></p></form>';

//sezioni
echo "\n<form action='menu.php' name='fmenu'>";
echo "\n<p><b>Menu:<br><select name='menu'>";
echo "<option value='tab_menu.php?idmenu=0'>Nuova</option>";
$q = $db->query("SELECT idmenu,sigla FROM menu ORDER BY sigla");
while ($r = $q->fetch_array()) {
	echo "<option value='tab_menu.php?idmenu=$r[idmenu]'>$r[sigla]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.menu.options[this.form.menu.selectedIndex].value"></p></form>';

//sezioni
echo "\n<form action='menu.php' name='fsezioni'>";
echo "\n<p><b>Sezioni:<br><select name='sezioni'>";
echo "<option value='tab_sezioni.php?idsezione=0'>Nuova</option>";
$q = $db->query("SELECT idsezione,nome FROM sezioni ORDER BY nome");
while ($r = $q->fetch_array()) {
	echo "<option value='tab_sezioni.php?idsezione=$r[idsezione]'>$r[nome]</option>";
}
echo "</select>";
echo '<input type="BUTTON" alt="Go!" value="Go!" onclick="parent.main.location=this.form.sezioni.options[this.form.sezioni.selectedIndex].value"></p></form>';


echo "\n<p><a href='tab_immaginimobili.php' target='main'>Immagini mobili</a><br>";
echo "\n<a href='notizie.php' target='main'>No news is good news</a><br>";
echo "\n<a href='tab_capitolitipi.php' target='main'>Tipi dei capitoli</a><br>";
echo "\n<a href='tab_episodicampi.php' target='main'>Campi degli episodi</a><br>";
echo "\n<a href='ridirezioni.php' target='main'>Ridirezioni</a><br>";
echo "\n<a href='tab_skin.php' target='main'>Skin</a><br>";
echo "\n<a href='cambialink.php' target='main'>Sostituzione link</a><br>";
echo "\n<a href='cerca.php' target='main'>Cerca testo</a><br>";
echo "\n<a href='forzaaggiornamento.php' target='main'>Forza aggiornamento <i>last</i></a><br>";
echo "</p>\n<p><font color='#c00000'>$my_desc</font></p>";

echo "</body></html>";


### END OF FILE ###