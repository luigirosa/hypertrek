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
 * Cerca testo
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20090103: require_once -> require
 * 20100109: cambio della struttura del menu
 * 
 */

// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');
intestazione();

// devo cercare un testo
if ($_POST['cerca'] != '') {
	$cerca = trim($_POST['cerca']);
	//
	echo "\n<p><b>Testi</b><br>";
	$qr = mysqli_query($mylink, "SELECT testo,ordine,tag,titolo FROM testi JOIN pagine ON testi.idpagina=pagine.idpagina WHERE testo LIKE '%$cerca%'");
	echo "\n<table>";
	echo "\n<tr><td><b>Episodio</b></td><td><b>Tag</b></td><td><b>Ordine</b></td><td><b>Testo</b></td></tr>";
	while ($r = mysqli_fetch_array($qr)) {
		echo "\n<tr>";
		echo "<td>$r[titolo]</td>";
		echo "<td>$r[tag]</td>";
		echo "<td>$r[ordine]</td>";
		echo "<td>$r[testo]</td>";
		echo "</tr>";
	}
	echo "\n<table></p>";
	//
	echo "\n<p><b>Capitoli</b><br>";
	$qr = mysqli_query($mylink, "SELECT testo,ordine,tag,titolo FROM capitoli JOIN pagine ON capitoli.idpagina=pagine.idpagina WHERE testo LIKE '%$cerca%'");
	echo "\n<table>";
	echo "\n<tr><td><b>Episodio</b></td><td><b>Tag</b></td><td><b>Ordine</b></td><td><b>Testo</b></td></tr>";
	while ($r = mysqli_fetch_array($qr)) {
		echo "\n<tr>";
		echo "<td>$r[titolo]</td>";
		echo "<td>$r[tag]</td>";
		echo "<td>$r[ordine]</td>";
		echo "<td>$r[testo]</td>";
		echo "</tr>";
	}
	echo "\n<table></p>";
	//
	echo "\n<p><b>Timeline</b><br>";
	$qr = mysqli_query($mylink, "SELECT anno,tordine,evento,commento FROM timelinest WHERE evento LIKE '%$cerca%' or commento LIKE '%$cerca%'");
	echo "\n<table>";
	echo "\n<tr><td><b>Anno</b></td><td><b>Ordine</b></td><td><b>Evento</b></td><td><b>Commento</b></td></tr>";
	while ($r = mysqli_fetch_array($qr)) {
		echo "\n<tr>";
		echo "<td>$r[anno]</td>";
		echo "<td>$r[tordine]</td>";
		echo "<td>$r[evento]</td>";
		echo "<td>$r[commento]</td>";
		echo "</tr>";
	}
	echo "\n<table></p>";
	//
	echo "\n<p><b>Quante volte</b><br>";
	$qr = mysqli_query($mylink, "SELECT testo,qvordine,tag,titolo FROM quantevolte JOIN pagine ON qunatevolte.idpagina=pagine.idpagina WHERE testo LIKE '%$cerca%'");
	echo "\n<table>";
	echo "\n<tr><td><b>Episodio</b></td><td><b>Tag</b></td><td><b>Ordine</b></td><td><b>Testo</b></td></tr>";
	while ($r = mysqli_fetch_array($qr)) {
		echo "\n<tr>";
		echo "<td>$r[titolo]</td>";
		echo "<td>$r[tag]</td>";
		echo "<td>$r[qvordine]</td>";
		echo "<td>$r[testo]</td>";
		echo "</tr>";
	}
	echo "\n<table></p>";
}

echo "\n<p><form name='cerca' method='post' action='cerca.php' target='main'>";
echo "\n<table border='0'>";
echo "\n<tr><td>Cerca il testo:</td><td><input type='text' size='50' maxlength='100' name='cerca'></td></tr>";
echo "\n</table>";

echo "<p><input type='submit' value='Trova'></p></form>";

echo "</body></html>";

### END OF FILE ###