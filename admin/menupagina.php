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
 * Menu con i link alle altre voci da mettere in fondo alle pagine
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 *
 * 20160522 GitHub
 * 
 */

echo "\n<p>";
$q = $db->query("SELECT idpagina FROM testi WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_testi.php?idpagina=$idpagina'>Testi standard ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM capitoli WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_capitoli.php?idpagina=$idpagina'>Capitoli ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM episodivalori WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_episodivalori.php?idpagina=$idpagina'>Valori episodio, pianeta, personaggio... ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM guest WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_guest.php?idpagina=$idpagina'>Attori ospiti ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM immaginipagine WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_immaginipagine.php?idpagina=$idpagina'>Immagini ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM riferimenti WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_riferimenti.php?idpagina=$idpagina&edit=1'>Riferimenti ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM quantevolte WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_quantevolte.php?idpagina=$idpagina'>Quante Volte... ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM piepagina WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_piepagina.php?idpagina=$idpagina'>Pi&eacute; di pagina ($nr)</a><br>";

$q = $db->query("SELECT idpagina FROM analitico WHERE idpagina=$idpagina");
$nr = $q->num_rows;
echo "\n<a href='tab_analitico.php?idpagina=$idpagina'>Voci indice analitico ($nr)</a><br>";


echo "\n</p>";


### END OF FILE ###
