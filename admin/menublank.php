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
 * Menu con pagina bianca
 * 
 * @author Luigi Rosa <lists@luigirosa.com>
 * 
 * 20100109: prima versione
 * 
 */
 
// serve per definire il punto d'ingresso corretto 
define('HYPERTREK', true);

require('global.php');

$idpagina = 0;

// dal menu di sinistra?
if (isset($_POST['tag'])) {
	$idpagina = 0;
	if (isset($_POST['pre'])) $tag = trim($_POST['pre']);
	$tag = strtolower($tag . trim($_POST['tag']));
	$q = $db->query("SELECT idpagina FROM pagine WHERE tag='$tag'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		$idpagina = $r['idpagina'];
	}
}

if (isset($_GET['idpagina'])) {
	$idpagina = $_GET['idpagina'];
}


if (0 == $idpagina) {
	header("Location: tab_pagine.php?tag=$tag");
	die();
}

intestazione();
mostramenu($idpagina);
	
### END OF FILE ###