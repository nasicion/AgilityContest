<?php 
/*
 Config.php

 Copyright 2013-2015 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

 This program is free software; you can redistribute it and/or modify it under the terms
 of the GNU General Public License as published by the Free Software Foundation;
 either version 2 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along with this program;
 if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/** default values **/
define('AC_CONFIG_FILE',__DIR__."/config.ini");

/** base de datos **/
define('AC_DATABASE_NAME','agility');
define('AC_DATABASE_HOST','localhost');
define('AC_DATABASE_USER','agility_operator');
define('AC_DATABASE_PASS','operator@cachorrera');

/** entorno grafico **/
define('AC_EASYUI_THEME','metro');
define('AC_EASYUI_BGCOLOR','#0000ff');
define('AC_EASYUI_HDRCOLOR','#00ff00');
define('AC_EASYUI_OPCOLOR','#c0c0c0');
define('AC_EASYUI_ROWCOLOR1','#ffffff');
define('AC_EASYUI_ROWCOLOR2','#c0c0c0');

/** personalizacion del videowall **/
define('AC_VW_POLLTIME',5);
define('AC_VW_HDRFG1','#0000ff');
define('AC_VW_HDRBG1','#808080');
define('AC_VW_HDRFG2','#0000ff');
define('AC_VW_HDRBG2','#808080');
define('AC_VW_ROWCOLOR1','#ffffff');
define('AC_VW_ROWCOLOR2','#e0ebff');

/** generacion de PDF's **/
define('AC_PDF_HDRFG1','#0000ff');
define('AC_PDF_HDRBG1','#808080');
define('AC_PDF_HDRFG2','#0000ff');
define('AC_PDF_HDRBG2','#808080');
define('AC_PDF_ROWCOLOR1','#ffffff');
define('AC_PDF_ROWCOLOR2','#e0ebff');
define('AC_PDF_LINECOLOR','#808080');

/** Internacionalizacion. Idiomas **/
define ('AC_LANG','es');

Class Config {
	
	var $config=array();
	
	function __construct() {

		/** cargamos los valores por defecto **/
		// database
		$this->config['database_name'] =	AC_DATABASE_NAME;
		$this->config['database_host'] =	AC_DATABASE_HOST;
		$this->config['database_user'] =	AC_DATABASE_USER;
		$this->config['database_pass'] = 	AC_DATABASE_PASS;
		// entorno grafico
		$this->config['easyui_theme'] = 	AC_EASYUI_THEME;
		$this->config['easyui_bgcolor'] =	AC_EASYUI_BGCOLOR;
		$this->config['easyui_hdrcolor'] =	AC_EASYUI_HDRCOLOR;
		$this->config['easyui_opcolor'] =	AC_EASYUI_OPCOLOR;
		$this->config['easyui_rowcolor1'] =	AC_EASYUI_ROWCOLOR1;
		$this->config['easyui_rowcolor2'] =	AC_EASYUI_ROWCOLOR2;
		// configuracion del videowall
		$this->config['vw_polltime'] =	AC_VW_POLLTIME;
		$this->config['vw_hdrfg1'] =	AC_VW_HDRFG1;
		$this->config['vw_hdrbg1'] =	AC_VW_HDRBG1;
		$this->config['vw_hdrfg2'] =	AC_VW_HDRFG2;
		$this->config['vw_hdrbg2'] =	AC_VW_HDRBG2;
		$this->config['vw_rowcolor1'] =	AC_VW_ROWCOLOR1;
		$this->config['vw_rowcolor2'] =	AC_VW_ROWCOLOR2;
		// generacion de PDF's
		$this->config['pdf_hdrfg1'] =	AC_PDF_HDRFG1;
		$this->config['pdf_hdrbg1'] =	AC_PDF_HDRBG1;
		$this->config['pdf_hdrfg2'] =	AC_PDF_HDRFG2;
		$this->config['pdf_hdrbg2'] =	AC_PDF_HDRBG2;
		$this->config['pdf_rowcolor1'] =	AC_PDF_ROWCOLOR1;
		$this->config['pdf_rowcolor2'] =	AC_PDF_ROWCOLOR2;
		$this->config['pdf_linecolor'] =	AC_PDF_LINECOLOR;
		// Internacionalizacion. Idiomas
		$this->config['lang'] =	AC_LANG;
		
		// ahora intentamos leer el fichero de configuracion
		$res=parse_ini_file(AC_CONFIG_FILE,false); // false: don't parse subsections
		if ($res===FALSE) {
			$this->config['configured'] =false; // mark initialization code to be executed
			return;
		}
		// cargamos los valores definidos en el fichero de configuracion
		foreach($this->config as $key => $val) {
			if ( array_key_exists($key,$res)) $this->config[$key]=$res[$key];
		}
	}
	
	public function getEnv($key) {
		if (array_key_exists($key,$this->config)===FALSE) return null;
		return $this->config[$key];
	}
	
	public function setEnv($key,$value) {
		if (array_key_exists($key,$this->config)===FALSE) return;
		$this->config[$key]=$value;
	}
	
	/* from PHP documentation on parse_ini_file(); */
	private function write_ini_file($assoc_arr, $path, $has_sections=FALSE) {
		$content = "";
		if ($has_sections) {
			foreach ($assoc_arr as $key=>$elem) {
				$content .= "[".$key."]\n";
				foreach ($elem as $key2=>$elem2) {
					if(is_array($elem2)) {
						for($i=0;$i<count($elem2);$i++)	{
							$content .= $key2."[] = \"".$elem2[$i]."\"\n";
						}
					} else if($elem2=="") {
						$content .= $key2." = \n";
					} else {
						$content .= $key2." = \"".$elem2."\"\n";
					}
				}
			}
		} else {
			foreach ($assoc_arr as $key=>$elem) {
				if(is_array($elem))	{
					for($i=0;$i<count($elem);$i++) {
						$content .= $key."[] = \"".$elem[$i]."\"\n";
					}
				} else if($elem=="") {
					$content .= $key." = \n";
				} else {
					$content .= $key." = \"".$elem."\"\n";
				}
			}
		}
		if (!$handle = fopen($path, 'w')) {
			return false;
		}
		$success = fwrite($handle, $content);
    	fclose($handle);
		return $success;
	}
	
	public function loadConfig() {
		return $this->config;
	}
	
	public function saveConfig() {
		$data=array();
		// entorno grafico
		$data=testAndSet($data,'easyui_theme','s',AC_EASYUI_THEME);
		$data=testAndSet($data,'easyui_bgcolor','s',AC_EASYUI_BGCOLOR);
		$data=testAndSet($data,'easyui_hdrcolor','s',AC_EASYUI_HDRCOLOR);
		$data=testAndSet($data,'easyui_opcolor','s',AC_EASYUI_OPCOLOR);
		$data=testAndSet($data,'easyui_rowcolor1','s',AC_EASYUI_ROWCOLOR1);
		$data=testAndSet($data,'easyui_rowcolor2','s',AC_EASYUI_ROWCOLOR2);
		// configuracion del videowall
		$data=testAndSet($data,'vw_polltime','i',AC_VW_POLLTIME);
		$data=testAndSet($data,'vw_hdrfg1','s',AC_VW_HDRFG1);
		$data=testAndSet($data,'vw_hdrbg1','s',AC_VW_HDRBG1);
		$data=testAndSet($data,'vw_hdrfg2','s',AC_VW_HDRFG2);
		$data=testAndSet($data,'vw_hdrbg2','s',AC_VW_HDRBG2);
		$data=testAndSet($data,'vw_rowcolor1','s',AC_VW_ROWCOLOR1);
		$data=testAndSet($data,'vw_rowcolor2','s',AC_VW_ROWCOLOR2);
		// generacion de PDF's
		$data=testAndSet($data,'pdf_hdrfg1','s',AC_PDF_HDRFG1);
		$data=testAndSet($data,'pdf_hdrbg1','s',AC_PDF_HDRBG1);
		$data=testAndSet($data,'pdf_hdrfg2','s',AC_PDF_HDRFG2);
		$data=testAndSet($data,'pdf_hdrbg2','s',AC_PDF_HDRBG2);
		$data=testAndSet($data,'pdf_rowcolor1','s',AC_PDF_ROWCOLOR1);
		$data=testAndSet($data,'pdf_rowcolor2','s',AC_PDF_ROWCOLOR2);
		$data=testAndSet($data,'pdf_linecolor','s',AC_PDF_LINECOLOR);
		// Internacionalizacion. Idiomas
		$data=testAndSet($data,'lang','s',AC_LANG);
		// finally write file:
		$res=array_merge($this->config,$data);
		$result=$this->write_ini_file($res,AC_CONFIG_FILE);
		if ($result===FALSE) return "Error al generar el fichero de configuracion";
		return "";
	}
}


?>