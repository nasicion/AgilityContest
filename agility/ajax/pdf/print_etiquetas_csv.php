<?php
/*
print_etiquetas_csv.php

Copyright  2013-2018 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


header('Set-Cookie: fileDownload=true; path=/');
// mandatory 'header' to be the first element to be echoed to stdout

/**
 * genera un CSV con los datos para las etiquetas
 */

require_once(__DIR__ . "/../../server/tools.php");
require_once(__DIR__ . "/../../server/logging.php");
require_once(__DIR__ . "/../../server/auth/Config.php");
require_once(__DIR__ . '/../../server/modules/Federations.php');
require_once(__DIR__ . '/../../server/modules/Competitions.php');
require_once(__DIR__ . '/../../server/database/classes/DBObject.php');
require_once(__DIR__ . '/../../server/pdf/classes/PrintEtiquetasCSV.php');


try {
	$result=null;
	$mangas=array();
	$prueba=http_request("Prueba","i",0);
	$jornada=http_request("Jornada","i",0);
	$rondas=http_request("Rondas","i","0"); // bitfield of 512:Esp 256:KO 128:Eq4 64:Eq3 32:Opn 16:G3 8:G2 4:G1 2:Pre2 1:Pre1
	$mangas[0]=http_request("Manga1","i",0); // single manga
	$mangas[1]=http_request("Manga2","i",0); // mangas a dos vueltas
	$mangas[2]=http_request("Manga3","i",0);
	$mangas[3]=http_request("Manga4","i",0); // 1,2:GII 3,4:GIII
	$mangas[4]=http_request("Manga5","i",0);
	$mangas[5]=http_request("Manga6","i",0);
	$mangas[6]=http_request("Manga7","i",0);
	$mangas[7]=http_request("Manga8","i",0);
	$mangas[8]=http_request("Manga9","i",0); // mangas 3..9 are used in KO rondas
	$mode=http_request("Mode","i","0"); // 0:Large 1:Medium 2:Small 3:Medium+Small 4:Large+Medium+Small

	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=printEtiquetas.csv");	
	
	// buscamos los recorridos asociados a la mangas
	$dbobj=new DBObject("print_etiquetas_csv");
	$mng=$dbobj->__getObject("mangas",$mangas[0]);
	$prb=$dbobj->__getObject("pruebas",$prueba);
	$c= Competitions::getClasificacionesInstance("print_etiquetas_csv",$jornada);
	$result=array();
	$heights=intval(Federations::getFederation( intval($prb->RSCE) )->get('Heights'));
	switch($mng->Recorrido) {
		case 0: // recorridos separados large medium small
			$r=$c->clasificacionFinal($rondas,$mangas,0);
			$result[0]=$r['rows'];
			$r=$c->clasificacionFinal($rondas,$mangas,1);
			$result[1]=$r['rows'];
			$r=$c->clasificacionFinal($rondas,$mangas,2);
			$result[2]=$r['rows'];
			if ($heights!=3) {
				$r=$c->clasificacionFinal($rondas,$mangas,5);
				$result[5]=$r['rows'];
			}
			break;
		case 1: // large / medium+small
			if ($heights==3) {
				$r=$c->clasificacionFinal($rondas,$mangas,0);
				$result[0]=$r['rows'];
				$r=$c->clasificacionFinal($rondas,$mangas,3);
				$result[3]=$r['rows'];
			} else {
				$r=$c->clasificacionFinal($rondas,$mangas,6);
				$result[6]=$r['rows'];
				$r=$c->clasificacionFinal($rondas,$mangas,7);
				$result[7]=$r['rows'];
			}
			break;
		case 2: // recorrido conjunto large+medium+small
			if ($heights==3) {
				$r=$c->clasificacionFinal($rondas,$mangas,4);
				$result[4]=$r['rows'];
			} else {
				$r=$c->clasificacionFinal($rondas,$mangas,8);
				$result[8]=$r['rows'];
			}
			break;
	}
	$first=true;
	foreach ($result as $res) {
		$csv =new PrintEtiquetasCSV($prueba,$jornada,$mangas,$res);
		echo $csv->composeTable($first);
		$first=false;
	}

} catch (Exception $e) {
	do_log($e->getMessage());
	die ($e->getMessage());
}

?>
