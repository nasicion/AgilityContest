<?php
/*
PrintInscripciones.php

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
/**
 * genera un pdf ordenado por club, categoria y nombre con una pagina por cada jornada
*/

require_once(__DIR__."/../fpdf.php");
require_once(__DIR__."/../../tools.php");
require_once(__DIR__."/../../logging.php");
require_once(__DIR__.'/../../database/classes/DBObject.php');
require_once(__DIR__.'/../../database/classes/Clubes.php');
require_once(__DIR__.'/../../database/classes/Pruebas.php');
require_once(__DIR__.'/../../database/classes/Jornadas.php');
require_once(__DIR__.'/../../database/classes/Inscripciones.php');
require_once(__DIR__."/../print_common.php");

class PrintCatalogo extends PrintCommon {
	protected $inscritos;
	protected $jornadas;
	
	protected $width = array( 41,17,14,21,29,6,6,6,6,6,6,6,6); // anchos predefinidos de las celdas
	protected $cellHeader = array( 'J1','J2','J3','J4','J5','J6','J7','J8');
	
	/**
	 * Constructor
	 * @param {integer} $prueba Prueba ID
	 * @param {array} $inscritos Lista de inscritos en formato jquery array[count,rows[]]
	 * @throws Exception
	*/
	function __construct($prueba,$inscritos,$jornadas) {
		parent::__construct('Portrait',"print_catalogo",$prueba,0);
		if ( ($prueba==0) || ($inscritos===null) ) {
			$this->errormsg="printInscritosByPrueba: either prueba or inscription data are invalid";
			throw new Exception($this->errormsg);
		}
		/* make sure that catalog is group'd by club  */
		$this->inscritos=$inscritos['rows'];
        usort($this->inscritos, function($a, $b) {
        	if ( strcmp($a['NombreClub'],$b['NombreClub']) == 0) return ($a['Dorsal']>$b['Dorsal'])?1:-1;
            return strcmp($a['NombreClub'],$b['NombreClub']);
        });
		$this->jornadas=$jornadas['rows'];
        $this->set_FileName("Catalogo_inscripciones.pdf");
	}
	
	// Cabecera de página
	function Header() {
		$this->myLogger->enter();
		$this->print_commonHeader(_('Contest catalog'));
		$this->Ln(5);
		$this->myLogger->leave();
	}
	
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}

	function printClub($id) {
        $y=$this->GetY();
		// retrieve club data
		$cmgr=new Clubes('printCatalogo',$this->prueba->RSCE);
		$club=$cmgr->selectByID($id);
		$fedName=$this->federation->get('Name');

        // evaluate logo
		$icon=getIconPath($fedName,"agilitycontest.png");
        if ( $club['Logo']==="") {
			$this->myLogger->error("inscritosByPrueba::printClub() club:$id {$club['Nombre']} no logo declared");
			$icon = getIconPath($fedName, $this->federation->get('Logo')); // default is federation logo
		} else {
			$icon = $icon = getIconPath($fedName, $club['Logo']);
		}
		$this->myLogger->trace("ID:".$id." Club: ".$club['Nombre']);

		$this->ac_SetFillColor($this->config->getEnv('pdf_hdrbg1')); // azul
		$this->ac_SetTextColor($this->config->getEnv('pdf_hdrfg1')); // blanco
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor')); // line color
		$this->SetLineWidth(.3); // ancho de linea
		
		// pintamos logo
		$this->SetXY(10,$y);
		$this->Cell(22,22,'','LTB',0,'C',false);
		$this->Image($icon,12,2+$y,18,18);

		// pintamos info del club
		$this->SetFont($this->getFontName(),'B',9);
		$this->SetXY(32,$y);
		$this->Cell( 50, 5, $club['Direccion1'],	'LT', 0, 'L', true); // pintamos direccion1
		$this->SetXY(32,5+$y);
		$this->Cell( 50, 5, $club['Direccion2'],	'L', 0, 'L',	true);	// pintamos direccion2
		$this->SetXY(32,10+$y);
		$prov=$club['Provincia'];
		if ($prov==="-- Sin asignar --") $prov="";
		$this->Cell( 50, 5,$prov ,	'L', 0, 'L',	true);	// pintamos provincia
		$this->SetFont($this->getFontName(),'IB',24);
		$this->SetXY(82,$y);
		$this->Cell( 110, 15, $club['Nombre'],	'T', 0, 'R',	true);	// pintamos Nombre
		$this->Cell( 10, 15, '',	'TR', 0, 'R',	true);	// caja vacia de relleno
		
		// pintamos cabeceras de la tabla		
		$this->ac_SetFillColor($this->config->getEnv('pdf_hdrbg2')); // gris
		$this->ac_SetTextColor($this->config->getEnv('pdf_hdrfg2')); // negro
		$this->SetFont($this->getFontName(),'B',9);
		$this->SetXY(32,15+$y);
		$this->Cell( $this->width[0], 7, _('Name'),'LTB', 0, 'C',true);
		$this->Cell( $this->width[1], 7, _('Breed'),'LTB', 0, 'C',true);
        if ($this->width[2]!=0) // skip license on international contests
		    $this->Cell( $this->width[2], 7, _('License'),'LTB', 0, 'C',true);
        if (intval($this->config->getEnv("pdf_grades"))!=0) {
            $this->Cell( $this->width[3], 7, _('Cat').'/'._('Grade'),'LTB', 0, 'C',true);
        } else {
            $this->Cell( $this->width[3], 7, _('Category'),'LTB', 0, 'C',true);
        }
		$this->Cell( $this->width[4], 7, _('Handler'),'LTBR', 0, 'C',true);
		// print names of each declared journeys
		for($i=5;$i<count($this->width);$i++) {
			// en la cabecera texto siempre centrado
			if ($this->width[$i]==0) continue;
			$this->Cell($this->width[$i],7,$this->cellHeader[$i-5],1,0,'C',true);
		}
		$this->Ln();
	}
	
	function printParticipante($count,$row) {
		// $this->myLogger->trace("Position: ".$pos." Dorsal: ".$row['Dorsal']);
        $this->ac_row($count,10); // set proper row background
		$this->SetTextColor(0,0,0); // negro
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor')); // line color
        $this->SetLineWidth(.3); // ancho de linea

        $this->SetX(17);
		// REMINDER: $this->cell( width, height, data, borders, where, align, fill)
		$this->SetFont($this->getFontName(),'B',12); //
		$this->Cell( 15, 7, $row['Dorsal'],	'TLB', 0, 'C',	true);
		$this->SetFont($this->getFontName(),'BI',9); // bold 9px italic
        $name= $row['Nombre'];
        if (!is_null($row['NombreLargo']) && $row['NombreLargo']!=="") $name = $name . " - " .$row['NombreLargo'];
		$this->Cell( $this->width[0], 7, $name,	'LB', 0, 'L',	true);
		$this->SetFont($this->getFontName(),'',8); // bold 8px
		$this->Cell( $this->width[1], 7, $row['Raza'],		'LB', 0, 'C',	true);
        if ($this->federation->get('WideLicense')) $this->SetFont($this->getFontName(),'',6); // bold 6px
        if ($this->width[2]!=0) // skip license on international contests
            $this->Cell( $this->width[2], 7, $row['Licencia'],	'LB', 0, 'C',	true);
        $this->SetFont($this->getFontName(),'',8); // bold 8px
		$grad="";
		if (intval($this->config->getEnv("pdf_grades"))!=0) { // if config requires print grade
			$grad=" - {$this->federation->getGradeShort($row['Grado'])}";
			if ($grad==" - -") $grad="";
		}
		$cat=$this->federation->getCategoryShort($row['Categoria']);
		$this->Cell( $this->width[3], 7, $cat.$grad,	'LB', 0, 'C',	true);
		$this->SetFont($this->getFontName(),'B',9); // bold 9px
		$this->Cell( $this->width[4], 7, $row['NombreGuia'],'LBR', 0, 'R',	true);
		
		$this->SetFont($this->getFontName(),'',8); // bold 8px
		
		// print inscrption data on each declared journeys
		for($i=5;$i<count($this->width);$i++) {
			// en la cabecera texto siempre centrado
			if ($this->width[$i]==0) continue;
			$j=$i-4;
			$this->Cell($this->width[$i],7,($row["J$j"]==0)?"":"X",'LBR',0,'C',true);
		}
		$this->Ln(7);
	}
	
	function composeTable() {
		$this->myLogger->enter();
		
		// check for show journeys in configuration options
		$skip=intval($this->config->getEnv('pdf_journeys')); 
		foreach($this->jornadas as $row => $jornada) {
			// contamos las jornadas sin asignar
			if (($skip==0) || ($jornada['Nombre']==='-- Sin asignar --')) {
				$this->cellHeader[$row]='';
				$this->width[0]+=3;$this->width[1]+=1;$this->width[4]+=2;
				$this->width[5+$row]=0;
			} else {
				$this->cellHeader[$row]=$jornada['Nombre'];
			}
		}
        // si la prueba es de caza ajustamos para que quepa la licencia
        if ($this->federation->get('WideLicense')) {
            $this->width[0] -= 7;  $this->width[1] -= 7; $this->width[2] +=14;
        }
        // si la prueba lo requiere quitamos licencia y sumamos el espacio al nombre
        if ($this->useLongNames) {
            $this->width[0]+=$this->width[2];
            $this->width[2]=0;
        }
		$this->AddPage(); // start page
		$club=0;
        $count=0;
		foreach($this->inscritos as $row) {
            $pos = $this->GetY();
            if (($club == $row['Club'])) {
                // no hay cambio de club
                if ($pos > 270) {
                    $this->AddPage();
                    $this->printClub($club);
                    $count = 0;
                }
            } else {
                $club = $row['Club'];
                // cambio de club
                $this->ln(7); // extra newline
                if ($pos > 250) $this->AddPage();
                $this->printClub($club);
                $count = 0;
            }
            $this->printParticipante($count, $row);
            $count++;
        }
		$this->myLogger->leave();		
	}
}

class PrintEstadisticas extends PrintCommon {
	protected $inscritos;
	protected $jornadas;
	
	/**
	 * Constructor
	 * @param {integer} $prueba Prueba ID
	 * @param {array} $inscritos Lista de inscritos en formato jquery array[count,rows[]]
	 * @throws Exception
	 */
	function __construct($prueba,$inscritos,$jornadas) {
		parent::__construct('Portrait','print_Estadisticas',$prueba,0);
		if ( ($prueba==0) || ($inscritos===null) ) {
			$this->errormsg="printInscritosByPrueba: either prueba or inscription data are invalid";
			throw new Exception($this->errormsg);
		}
		$this->inscritos=$inscritos['rows'];
		$this->jornadas=$jornadas['rows'];
        $this->set_FileName("Estadisticas_inscripciones.pdf");
	}
	
	// Cabecera de página
	function Header() {
		$this->myLogger->enter();
		$this->print_commonHeader(_("Statistics"));
		$this->Ln(5);
		$this->myLogger->leave();
	}
	
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}
	
	function evalItem($jornada,&$data,$item) {
		// do not account when undefined catetory or grade
		if ($item['Categoria']==='-') return;
		if ($item['Grado']==='-') return;
        if ($item['Grado']==='P.B.') return; // perro en blanco no se toma en cuenta
        // if ($item['Grado']==='Jr') return; // PENDING: procesar perros Junior
        // if ($item['Grado']==='Sr') return; // PENDING: procesar perros Senior
		if ($item['Grado']==='Baja') return; // PENDING: no se deberia admitir la inscripcion
		if ($item['Grado']==='Ret.') return; // PENDING: no se deberia admitir la inscripcion
		$data[$jornada]['G']['C']++;
		$data[$jornada]['G'][$item['Categoria']]++;
		$data[$jornada][$item['Grado']]['C']++;
		$data[$jornada][$item['Grado']][$item['Categoria']]++;
	}
	
	function evalData() {
		$est=array();
		// datos globales
		$est['Prueba']=array();
		$est['Prueba']['G']=array();
		$est['Prueba']['G']['C']=0;$est['Prueba']['G']['L']=0;$est['Prueba']['G']['M']=0;$est['Prueba']['G']['S']=0;$est['Prueba']['G']['T']=0;
        $est['Prueba']['Jr']=array();
        $est['Prueba']['Jr']['C']=0;$est['Prueba']['Jr']['L']=0;$est['Prueba']['Jr']['M']=0;$est['Prueba']['Jr']['S']=0;$est['Prueba']['Jr']['T']=0;
        $est['Prueba']['Sr']=array();
        $est['Prueba']['Sr']['C']=0;$est['Prueba']['Sr']['L']=0;$est['Prueba']['Sr']['M']=0;$est['Prueba']['Sr']['S']=0;$est['Prueba']['Sr']['T']=0;
		$est['Prueba']['P.A.']=array();
		$est['Prueba']['P.A.']['C']=0;$est['Prueba']['P.A.']['L']=0;$est['Prueba']['P.A.']['M']=0;$est['Prueba']['P.A.']['S']=0;$est['Prueba']['P.A.']['T']=0;	
		$est['Prueba']['GI']=array();
		$est['Prueba']['GI']['C']=0;$est['Prueba']['GI']['L']=0;$est['Prueba']['GI']['M']=0;$est['Prueba']['GI']['S']=0;$est['Prueba']['GI']['T']=0;	
		$est['Prueba']['GII']=array();
		$est['Prueba']['GII']['C']=0;$est['Prueba']['GII']['L']=0;$est['Prueba']['GII']['M']=0;$est['Prueba']['GII']['S']=0;$est['Prueba']['GII']['T']=0;	
		$est['Prueba']['GIII']=array();
		$est['Prueba']['GIII']['C']=0;$est['Prueba']['GIII']['L']=0;$est['Prueba']['GIII']['M']=0;$est['Prueba']['GIII']['S']=0;$est['Prueba']['GIII']['T']=0;
		$est['Prueba']['-']=array();
        $est['Prueba']['-']['C']=0;$est['Prueba']['-']['L']=0;$est['Prueba']['-']['M']=0;$est['Prueba']['-']['S']=0;$est['Prueba']['-']['T']=0;

		foreach (array('1','2','3','4','5','6','7','8') as $j) { // creamos arrays para las ocho posibles jornadas
            // Jornada 1
            $est['J'.$j]=array();
            $est['J'.$j]['G']=array();
            $est['J'.$j]['G']['C']=0;$est['J'.$j]['G']['L']=0;$est['J'.$j]['G']['M']=0;$est['J'.$j]['G']['S']=0;$est['J'.$j]['G']['T']=0;
            $est['J'.$j]['Jr']=array();
            $est['J'.$j]['Jr']['C']=0;$est['J'.$j]['Jr']['L']=0;$est['J'.$j]['Jr']['M']=0;$est['J'.$j]['Jr']['S']=0;$est['J'.$j]['Jr']['T']=0;
            $est['J'.$j]['Sr']=array();
            $est['J'.$j]['Sr']['C']=0;$est['J'.$j]['Sr']['L']=0;$est['J'.$j]['Sr']['M']=0;$est['J'.$j]['Sr']['S']=0;$est['J'.$j]['Sr']['T']=0;
            $est['J'.$j]['P.A.']=array();
            $est['J'.$j]['P.A.']['C']=0;$est['J'.$j]['P.A.']['L']=0;$est['J'.$j]['P.A.']['M']=0;$est['J'.$j]['P.A.']['S']=0;$est['J'.$j]['P.A.']['T']=0;
            $est['J'.$j]['GI']=array();
            $est['J'.$j]['GI']['C']=0;$est['J'.$j]['GI']['L']=0;$est['J'.$j]['GI']['M']=0;$est['J'.$j]['GI']['S']=0;$est['J'.$j]['GI']['T']=0;
            $est['J'.$j]['GII']=array();
            $est['J'.$j]['GII']['C']=0;$est['J'.$j]['GII']['L']=0;$est['J'.$j]['GII']['M']=0;$est['J'.$j]['GII']['S']=0;$est['J'.$j]['GII']['T']=0;
            $est['J'.$j]['GIII']=array();
            $est['J'.$j]['GIII']['C']=0;$est['J'.$j]['GIII']['L']=0;$est['J'.$j]['GIII']['M']=0;$est['J'.$j]['GIII']['S']=0;$est['J'.$j]['GIII']['T']=0;
            $est['J'.$j]['-']=array();
            $est['J'.$j]['-']['C']=0;$est['J'.$j]['-']['L']=0;$est['J'.$j]['-']['M']=0;$est['J'.$j]['-']['S']=0;$est['J'.$j]['-']['T']=0;
        }

		foreach($this->inscritos as $item){
			$j=$item['Jornadas'];
			$this->evalItem('Prueba',$est,$item);
			if ($j&0x01) $this->evalItem('J1',$est,$item);
			if ($j&0x02) $this->evalItem('J2',$est,$item);
			if ($j&0x04) $this->evalItem('J3',$est,$item);
			if ($j&0x08) $this->evalItem('J4',$est,$item);
			if ($j&0x10) $this->evalItem('J5',$est,$item);
			if ($j&0x20) $this->evalItem('J6',$est,$item);
			if ($j&0x40) $this->evalItem('J7',$est,$item);
			if ($j&0x80) $this->evalItem('J8',$est,$item);
		}
		return $est;
	}
	
	function printTableHeader($data,$name,$text) {
		$this->SetX(10);		
		$this->ac_header(1,15);
		// $this->cell( width, height, data, borders, where, align, fill)
		$this->Cell(190,10,$text,'LRTB',0,'L',true);
		$this->Ln();	
	}

    // $this->cell( width, height, data, borders, where, align, fill)
	function printTableData($data,$name,$alturas) {
		$this->ac_header(2,9);
		$this->SetX(10);

		// Pintamos la cabecera de la tabla
        $this->SetFont($this->getFontName(),'B',9);
        $this->cell(35,6,'','LRB',0,'L',true);
        $this->cell(30,6,$this->federation->getCategory('L'),'TRB',0,'C',true);
        $this->cell(30,6,$this->federation->getCategory('M'),'TRB',0,'C',true);
        $this->cell(30,6,$this->federation->getCategory('S'),'TRB',0,'C',true);
        if ($alturas==4)
            $this->cell(30,6,$this->federation->getCategory('T'),'TRB',0,'C',true);
        $this->cell(30,6,'Total','TRB',0,'C',true);
        $this->Ln(6);

		// Pre Agility
        $this->ac_header(2,9); // pre-agility
        $this->cell(35,6,$this->federation->getGrade('P.A.'),'LRB',0,'L',true);
        $this->ac_row(0,9);
        $this->cell(30,6,$data[$name]['P.A.']['L'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['P.A.']['M'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['P.A.']['S'],'RB',0,'C',true);
        if ($alturas==4)
        	$this->cell(30,6,$data[$name]['P.A.']['T'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['P.A.']['C'],'RB',0,'C',true);
        $this->Ln(6);

        // grado 1
        $this->ac_header(2,9);
        $this->cell(35,6,$this->federation->getGrade('GI'),'LRB',0,'L',true);
        $this->ac_row(1,9);
        $this->cell(30,6,$data[$name]['GI']['L'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['GI']['M'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['GI']['S'],'RB',0,'C',true);
        if ($alturas==4)
        	$this->cell(30,6,$data[$name]['GI']['T'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['GI']['C'],'RB',0,'C',true);
        $this->Ln(6);

        // grado II
        $this->ac_header(2,9);
        $this->cell(35,6,$this->federation->getGrade('GII'),'LRB',0,'L',true);
        $this->ac_row(2,9);
        $this->cell(30,6,$data[$name]['GII']['L'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['GII']['M'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['GII']['S'],'RB',0,'C',true);
        if ($alturas==4)
        	$this->cell(30,6,$data[$name]['GII']['T'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['GII']['C'],'RB',0,'C',true);
        $this->Ln(6);


        if ($this->federation->hasGrade3()) {
            $this->ac_header(2,9); // grado III
            $this->cell(35,6,$this->federation->getGrade('GIII'),'LRB',0,'L',true);
            $this->ac_row(3,9);
            $this->cell(30,6,$data[$name]['GIII']['L'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['GIII']['M'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['GIII']['S'],'RB',0,'C',true);
            if ($alturas==4)
            	$this->cell(30,6,$data[$name]['GIII']['T'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['GIII']['C'],'RB',0,'C',true);
            $this->Ln(6);
		}

        // Junior
		if ($this->federation->hasJunior()) {
            $this->ac_header(2,9);
            $this->cell(35,6,$this->federation->getGrade('Jr'),'LRB',0,'L',true);
            $this->ac_row(0,9);
            $this->cell(30,6,$data[$name]['Jr']['L'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['Jr']['M'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['Jr']['S'],'RB',0,'C',true);
            if($alturas==4)
            	$this->cell(30,6,$data[$name]['Jr']['T'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['Jr']['C'],'RB',0,'C',true);
            $this->Ln(6);
        }

        // Senior
		if ($this->federation->hasSenior()) {
            $this->ac_header(2,9);
            $this->cell(35,6,$this->federation->getGrade('Sr'),'LRB',0,'L',true);
            $this->ac_row(0,9);
            $this->cell(30,6,$data[$name]['Sr']['L'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['Sr']['M'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['Sr']['S'],'RB',0,'C',true);
            if($alturas==4)
                $this->cell(30,6,$data[$name]['Sr']['T'],'RB',0,'C',true);
            $this->cell(30,6,$data[$name]['Sr']['C'],'RB',0,'C',true);
            $this->Ln(6);
		}

        // Total
        $this->ac_header(2,9);
        $this->cell(35,6,_('Total'),'LRB',0,'L',true);
        $this->ac_row(4,9);
        $this->cell(30,6,$data[$name]['G']['L'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['G']['M'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['G']['S'],'RB',0,'C',true);
        if ($alturas==4)
        	$this->cell(30,6,$data[$name]['G']['T'],'RB',0,'C',true);
        $this->cell(30,6,$data[$name]['G']['C'],'RB',0,'C',true);
        $this->Ln(8);
	}

	/**
	 * En mangas especiales ( ko,games, equipos, etc ) no hay categorias. solo globales
	 */
	function printTableDataSpecial($data,$name,$alturas,$flag) {
		$this->ac_header(2,9);
		$this->SetX(10);

		$this->SetFont($this->getFontName(),'B',9);
		// $this->cell( width, height, data, borders, where, align, fill)
		$this->cell(30,6,'','LRB',0,'L',true);
		$this->cell(30,6,$this->federation->getCategory('L'),'TRB',0,'C',true);
		$this->cell(30,6,$this->federation->getCategory('M'),'TRB',0,'C',true);
		$this->cell(30,6,$this->federation->getCategory('S'),'TRB',0,'C',true);
		if ($alturas==4)
			$this->cell(30,6,$this->federation->getCategory('T'),'TRB',0,'C',true);
		$this->cell(30,6,_('Total'),'TRB',0,'C',true);
		$this->Ln(6);

		$this->ac_header(0,9); // Special round==total
		$this->cell(30,6,$flag,'LRB',0,'L',true);
		$this->ac_row(0,9);
		$this->cell(30,6,$data[$name]['G']['L'],'RB',0,'C',true);
		$this->cell(30,6,$data[$name]['G']['M'],'RB',0,'C',true);
		$this->cell(30,6,$data[$name]['G']['S'],'RB',0,'C',true);
		if ($alturas==4)
			$this->cell(30,6,$data[$name]['G']['T'],'RB',0,'C',true);
		$this->cell(30,6,$data[$name]['G']['C'],'RB',0,'C',true);
		$this->Ln(8);
	}

	function composeTable() {
		$alturas=$this->federation->get('Heights');
		$est=$this->evalData();
		$this->AddPage();
		$count=0;
		$this->printTableHeader($est,'Prueba',_('Participation global data'));
		$this->printTableData($est,'Prueba',$alturas);
		$count+=2;
		foreach($this->jornadas as $jornada) {
			if ($jornada['Nombre']==='-- Sin asignar --') continue;
			$name="J{$jornada['Numero']}";
			$this->printTableHeader($est,$name,$jornada['Nombre']);
			// check for Open/Team/Ko
			$flag="";
			switch(intval($jornada['Equipos3'])) {
				case 1:$flag=_("3 Best"); break; // old style
				case 2:$flag=_("2 Best"); break;
				case 3:$flag=_("3 Best"); break;
				default: break;
			}
			switch(intval($jornada['Equipos4'])) {
				case 1:$flag=_("Team 4"); break; // old style
				case 2:$flag=_("Team 2"); break;
				case 3:$flag=_("Team 3"); break;
				case 4:$flag=_("Team 4"); break;
				default: break;
			}
			if ($jornada['Open']!=0) /* $flag=_("Individual"); */ $flag="Open";
            if ($jornada['KO']!=0) $flag=_("K.O.");
            if ($jornada['Games']!=0) {
            	$flag=_("Games");
                if ($jornada['Tipo_Competicion']==1) $flag=_("Pentathlon");
                if ($jornada['Tipo_Competicion']==2) $flag=_("Biathlon");
                if ($jornada['Tipo_Competicion']==3) $flag=_("Games");
            }
			if ($flag===""){
                $this->printTableData($est,$name,$alturas);
                $count+=2;
			} else {
                $this->printTableDataSpecial($est,$name,$alturas,$flag);
                $count++;
			}
			if ($count>8) {
            	$count=0;
            	$this->AddPage();
            }
		}
	}
}

class PrintInscritos extends PrintCommon {
	
	protected $inscritos;
	protected $jornadas;
	protected $header; // titulo de cabecera

	// geometria de las celdas
	protected $cellHeader;
    protected $pos =
			//  0         1     2       3      4       5       6         7    8      9        10   11  12  13  14  15  16  17
			// dorsal   name   license  Breed  cat     grade   handler  club  heat comments   J1  J2  J3  J4  J5  J6  J7  J8
        array(  7,       25,   16,     14,     10,     10,      31,     24,    9,       1,     6,  6,  6,  6,  6,  6,  6,  6 );
    protected $align=
        array(  'C',     'L',  'C',   'R',    'C',     'L',    'R',    'R',   'C',    'L',    'C','C','C','C','C','C','C','C');
	
	/**
	 * Constructor
	 * @param {integer} $prueba Prueba ID
     * @param {array} $inscritos Lista de inscritos en formato jquery array[count,rows[]]
     * @param {array} $jornadas Lista de jornadas de la prueba
	 * @param {bool} special: true to indicate a not-global listing
	 * @throws Exception
	 */
	function __construct($prueba,$inscritos,$jornadas,$header="") {
		parent::__construct('Portrait','print_inscritosByPrueba',$prueba,0);
		if ( ($prueba==0) || ($inscritos===null) ) {
			$this->errormsg="printInscritosByPrueba: either prueba or inscription data are invalid";
			throw new Exception($this->errormsg);
		}
		$this->inscritos=$inscritos['rows'];
		$this->jornadas=$jornadas['rows'];
        $this->cellHeader=
			//        0           1         2          3       4         5           6            7            8           9
            array(_('Dorsal'),_('Name'),_('Lic'),_('Breed'),_('Cat'),_('Grado'),_('Handler'),$this->strClub,_('Heat'),_('Comments'));
        // on request to dont print grades re-evaluate sizes and text
        $grad=intval($this->config->getEnv('pdf_grades'));
        if ($grad==0) { // remove grade column
            $this->cellHeader[4]=_('Category');
            $this->pos[4]+=$this->pos[5];
            $this->pos[5]=0;
        }
        // check for need to show long names by iterating every journeys
		$flag=0;
		foreach ($this->jornadas as $jornada) {
        	if ($jornada['Nombre']==='-- Sin asignar --') continue;
        	if (Competitions::getCompetition($this->prueba,json_decode(json_encode($jornada)) )->useLongNames() ) $flag++;
		}
        $this->useLongNames=($flag!=0);
		// set up field sizes according evaluated parameters
		if ($this->federation->isInternational()) {
			// on international contest, remove license
            $this->pos[1]+=$this->pos[2]; // remove license and add to name
            $this->pos[2]=0;
            $this->pos[3]+=$this->pos[8]; // remove heat and add to breed
            $this->pos[8]=0;
        }
        else if ($this->useLongNames) { // enlarge name, enshort license and comments
            $this->pos[1]+=15;
            $this->pos[2]-=6;
            $this->pos[9]-=9;
        }
        // set file name
		if ($header==="") {
            $this->set_FileName("Listado_Participantes.pdf");
            $this->header=_("Competitors list");
		} else {
            $this->set_FileName("Listado_Personalizado.pdf");
            $this->header=$header;
		}
	}
	
	// Cabecera de página
	function Header() {
		$this->myLogger->enter();
		$this->print_commonHeader($this->header);
		$this->Ln(5);
		$this->myLogger->leave();
	}
		
	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}
	
	function writeTableHeader() {
		$this->myLogger->enter();
		// Colores, ancho de línea y fuente en negrita de la cabecera de tabla
		$this->ac_SetFillColor($this->config->getEnv('pdf_hdrbg1')); // azul
		$this->ac_SetTextColor($this->config->getEnv('pdf_hdrfg1')); // blanco
		$this->ac_SetDrawColor("0x000000"); // line color
		$this->SetFont($this->getFontName(),'B',9); // bold 9px

		$this->Cell($this->pos[0],6,$this->cellHeader[0],1,0,$this->align[0],true); // dorsal
		if ($this->federation->isInternational()) // if intl insert country
			$this->Cell($this->pos[7],6,$this->cellHeader[7],1,0,$this->align[7],true);
		$this->Cell($this->pos[1],6,$this->cellHeader[1],1,0,$this->align[1],true); // name
		if ($this->pos[2]!=0) // skip license when required
			$this->Cell($this->pos[2],6,$this->cellHeader[2],1,0,$this->align[2],true); // license
		$this->Cell($this->pos[3],6,$this->cellHeader[3],1,0,$this->align[3],true); // breed
		$this->Cell($this->pos[4],6,$this->cellHeader[4],1,0,$this->align[4],true); // cat
		if ($this->pos[5]!=0) // skip grade when required
			$this->Cell($this->pos[5],6,$this->cellHeader[5],1,0,$this->align[5],true); // grade
		$this->Cell($this->pos[6],6,$this->cellHeader[6],1,0,$this->align[6],true); // handler
		if (! $this->federation->isInternational()) // if not intl here comes club
			$this->Cell($this->pos[7],6,$this->cellHeader[7],1,0,$this->align[7],true);
		if ($this->pos[8]!=0) // skip license when required
			$this->Cell($this->pos[8],6,$this->cellHeader[8],1,0,$this->align[8],true); // heat
		$this->Cell($this->pos[9],6,$this->cellHeader[9],1,0,$this->align[9],true); // comments
		for($i=10;$i<count($this->cellHeader);$i++) {
			// en la cabecera texto siempre centrado
			if ($this->pos[$i]==0) continue;
			$this->Cell($this->pos[$i],6,$this->cellHeader[$i],1,0,$this->align[$i],true);
		}
		// Restauración de colores y fuentes
		$this->ac_SetFillColor($this->config->getEnv('pdf_rowcolor2')); // azul merle
		$this->SetTextColor(0,0,0); // negro
		$this->SetFont(''); // remove bold
		$this->Ln();
		$this->myLogger->leave();
	}
	
	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor')); // line color
		$this->SetLineWidth(.3);
		
		// contamos las jornadas sin asignar	
		foreach($this->jornadas as $row => $jornada) {
			if ($jornada['Nombre']==='-- Sin asignar --') {
				$this->pos[9]+=6;
				$this->pos[10+$row]=0;
				continue;
			} else {
				$this->cellHeader[10+$row]=$jornada['Nombre'];
			}
		}
		if (!$this->useLongNames) {
            // si estamos en caza ajustamos para que quepa la licencia
            if ($this->federation->get('WideLicense')) {
				// dorsal         nombre          	licencia           categoria         grado             observaciones
                $this->pos[0]-=1; $this->pos[1]-=2; $this->pos[2]+=15; $this->pos[4]-=3; $this->pos[5]-=2; $this->pos[9]-=7;
            }
        }
		// Datos
		$fill = false;
		$rowcount=0;
		foreach($this->inscritos as $row) {
			// REMINDER: $this->cell( width, height, data, borders, where, align, fill)
			if( ($rowcount%46) == 0 ) { // assume 39 rows per page ( header + rowWidth = 5mmts )
				if ($rowcount>0) 
					$this->Cell(array_sum($this->pos),0,'','T'); // linea de cierre
				$this->AddPage();
				$this->writeTableHeader();
			} 
			// $this->Cell($this->pos[0],7,$row['IDPerro'],	'LR',0,$this->align[0],$fill);
			// $this->Cell($this->pos[0],7,$rowcount+1,		'LR',	0,		$this->align[0],$fill); // display order instead of idperro

			//     0           1         2          3       4         5           6            7            8           9
			// _('Dorsal'),_('Name'),_('Lic'),_('Breed'),_('Cat'),_('Grado'),_('Handler'),$this->strClub,_('Heat'),_('Comments'));
			$this->SetFont($this->getFontName(),'B',7); // bold 7px
			$this->Cell($this->pos[0],5,$row['Dorsal'],		'LR',	0,		$this->align[0],	$fill);
			if ($this->federation->isInternational()) { // on intl here comes country
				$this->SetFont($this->getFontName(),'',7); // bold 7px
				$this->Cell($this->pos[7],5,$row['NombreClub'],	'LR',	0,		$this->align[7],	$fill); // club
			}
			$this->SetFont($this->getFontName(),'B',8); // bold 8px
			if ($this->federation->isInternational()) { // skip license
                $n=$row['Nombre']." - ".$row['NombreLargo'];
                $this->Cell($this->pos[1],5,$n,		'LR',	0,		$this->align[1],	$fill);
            } else {
				$n=($this->useLongNames)? $row['Nombre']." - ".$row['NombreLargo']: $row['Nombre'];
                $this->Cell($this->pos[1],5,$n,		'LR',	0,		$this->align[1],	$fill);
                if ($this->federation->get('WideLicense')) $this->SetFont($this->getFontName(),'',7); // normal 7px
                else $this->SetFont($this->getFontName(),'',8); // normal 8px
                $this->Cell($this->pos[2],5,$row['Licencia'],	'LR',	0,		$this->align[2],	$fill);
            }
            $this->SetFont($this->getFontName(),'',7); // normal 7px
			$this->Cell($this->pos[3],5,$row['Raza'],		'LR',	0,		$this->align[3],	$fill); // breed
            $cat=$this->federation->getCategoryShort($row['Categoria']);
			if (intval($this->config->getEnv("pdf_grades"))!=0) { // properly handle cat/grad
                $grad=$this->federation->getGradeShort($row['Grado']);
				$this->Cell($this->pos[4],5,$cat,	'LR',	0,		$this->align[4],	$fill); // cat
				$this->Cell($this->pos[5],5,$grad,		'LR',	0,		$this->align[5],	$fill); // grad
			} else {
				$this->Cell($this->pos[4]+$this->pos[5],5,$cat,'LR',0,$this->align[4],$fill); // catgrad
			}
			$this->Cell($this->pos[6],5,$row['NombreGuia'],	'LR',	0,		$this->align[6],	$fill); // handler
			if (!$this->federation->isInternational()){ // if not intl here comes club
				$this->Cell($this->pos[7],5,$row['NombreClub'],	'LR',	0,		$this->align[7],	$fill); // club
			}
            if ($this->pos[8]!=0) // special handling of heat for intl contests
				$this->Cell($this->pos[8],5,($row['Celo']==0)?"":"X",'LR',0,	$this->align[8],	$fill); // heat
			$this->Cell($this->pos[9],5,$row['Observaciones'],'LR',	0,			$this->align[9],	$fill); // comments
			if ($this->pos[10]!=0)
				$this->Cell($this->pos[10],5,($row['J1']==0)?"":"X",'LR',0,		$this->align[10],	$fill);
			if ($this->pos[11]!=0)
				$this->Cell($this->pos[11],5,($row['J2']==0)?"":"X",'LR',0,		$this->align[11],	$fill);
			if ($this->pos[12]!=0)
				$this->Cell($this->pos[12],5,($row['J3']==0)?"":"X",'LR',0,		$this->align[12],	$fill);
			if ($this->pos[13]!=0)
				$this->Cell($this->pos[13],5,($row['J4']==0)?"":"X",'LR',0,		$this->align[13],	$fill);
			if ($this->pos[14]!=0)
				$this->Cell($this->pos[14],5,($row['J5']==0)?"":"X",'LR',0,		$this->align[14],	$fill);
			if ($this->pos[15]!=0)
				$this->Cell($this->pos[15],5,($row['J6']==0)?"":"X",'LR',0,		$this->align[15],	$fill);
			if ($this->pos[16]!=0)
				$this->Cell($this->pos[16],5,($row['J7']==0)?"":"X",'LR',0,		$this->align[16],	$fill);
			if ($this->pos[17]!=0)
				$this->Cell($this->pos[17],5,($row['J8']==0)?"":"X",'LR',0,		$this->align[17],	$fill);
			$this->Ln();
			$fill = ! $fill;
			$rowcount++;
		}
		// Línea de cierre
		$this->Cell(array_sum($this->pos),0,'','T');
		$this->myLogger->leave();
	}
}

class PrintInscritosByJornada extends PrintCommon {

	protected $inscritos; // inscritos en la prueba
	protected $jornadas; // datos de todas las jornadas de esta prueba
    protected $JName; // campo "JX" a buscar para ver si esta inscrito o no en la jornada

	// geometria de las celdas
	protected $cellHeader;
                            // Dorsal    name   license breed    handler Club   cat      grad    heat    comments
	protected $pos =	array(  7,       35,     20,     25,       38,    25,     8,       8,     9,       15 );
	protected $align=	array(  'R',     'C',    'C',    'R',     'R',    'R',    'C',    'C',    'C',      'L');

	/**
	 * Constructor
	 * @param {integer} $pruebaid Prueba ID
	 * @param {array} $inscritos Lista de inscritos en formato jquery array[count,rows[]]
	 * @param {array} $jornadas lista de jornadas de la prueba
	 * @param {integer} $jornadaid id de la prueba que buscamos
	 * @throws Exception
	 */
	function __construct($pruebaid,$inscritos,$jornadas,$jornadaid) {
		parent::__construct('Portrait','print_inscritosByJornada',$pruebaid,$jornadaid);
		if ( ($pruebaid==0) || ($inscritos===null) ) {
			$this->errormsg="printInscritosByPrueba: either prueba or inscription data are invalid";
			throw new Exception($this->errormsg);
		}
		usort($inscritos['rows'],function($a,$b){return ($a['Dorsal']>$b['Dorsal'])?1:-1;});
        $this->inscritos=$inscritos['rows'];
        $this->jornadas=$jornadas['rows'];
		$this->cellHeader=
			array(_('Dorsal'),_('Name'),_('Lic'),_('Breed'), _('Handler'),$this->strClub,_('Cat'),_('Grado'),_('Heat'),_('Comments'));
        $this->JName="";
        foreach ($jornadas['rows'] as $j) {
            if ($j['ID'] == $jornadaid) {
                $this->JName = "J{$j['Numero']}";
				// remove "Grade" from cell array if jornada is open/team/KO
				if( !Jornadas::hasGrades($j) || (intval($this->config->getEnv("pdf_grades"))==0) ) {
				    $this->cellHeader[6]=_('Category');
					$this->pos[6]+=$this->pos[7]; // increase category size
					$this->pos[7]=0;  // to fit grade
				}
				// remove "License" in international contests
				if($this->useLongNames) {
					$this->pos[1]+=$this->pos[2]; // increase name size
					$this->pos[2]=0;  // and remove license
				}
                break;
            }
        }
        if ($this->JName==="") {
            $this->errormsg="printInscritosByJornada: Invalid Jornada ID:$jornadaid for provided prueba";
            throw new Exception($this->errormsg);
        }
        // set file name
        $str=$this->jornada->Nombre;
        $res=normalize_filename($str);
        $this->set_FileName("Inscripciones_Jornada_{$res}.pdf");
	}

	// Cabecera de página
	function Header() {
		$this->print_commonHeader(_("Competitors list"));
	}

	// Pie de página
	function Footer() {
		$this->print_commonFooter();
	}

	function writeTableHeader() {
		$this->myLogger->enter();
        // pintamos "identificacion de la manga"
        $this->SetFont($this->getFontName(),'B',12); // bold 15
        $str  = _('Journey'). ": ". $this->jornada->Nombre . " - " . $this->jornada->Fecha;
        $this->Cell(90,9,$str,0,0,'L',false); // a un lado nombre y fecha de la jornada
        $this->Ln();

		// Colores, ancho de línea y fuente en negrita de la cabecera de tabla
		$this->ac_SetFillColor($this->config->getEnv('pdf_hdrbg1')); // azul
		$this->ac_SetTextColor($this->config->getEnv('pdf_hdrfg1')); // blanco
		$this->ac_SetDrawColor("0x000000"); // line color
		$this->SetFont($this->getFontName(),'B',9); // bold 9px
		for($i=0;$i<count($this->cellHeader);$i++) {
			// en la cabecera texto siempre centrado
			if ($this->pos[$i]==0) continue;
			$this->Cell($this->pos[$i],6,$this->cellHeader[$i],1,0,'C',true);
		}
		// Restauración de colores y fuentes
		$this->ac_SetFillColor($this->config->getEnv('pdf_rowcolor2')); // azul merle
		$this->SetTextColor(0,0,0); // negro
		$this->SetFont(''); // remove bold
		$this->Ln();
		$this->myLogger->leave();
	}

	// Tabla coloreada
	function composeTable() {
		$this->myLogger->enter();
		$this->ac_SetDrawColor($this->config->getEnv('pdf_linecolor')); // line color
		$this->SetLineWidth(.3);

		// si estamos en caza ajustamos para que quepa la licencia
		if ($this->federation->get('WideLicense')) {
			$this->pos[2]+=15; $this->pos[4]-=8; $this->pos[5]-=7;
		}
		// Datos
		$fill = false;
		$rowcount=0;
		foreach($this->inscritos as $row) {
            // si no esta inscrito en la jornada skip
            if ($row[$this->JName]==0) continue;

			// REMINDER: $this->cell( width, height, data, borders, where, align, fill)
			if( ($rowcount%46) == 0 ) { // assume 44 rows per page ( header + rowWidth = 5mmts )
				if ($rowcount>0)
					$this->Cell(array_sum($this->pos),0,'','T'); // linea de cierre
				$this->AddPage();
				$this->writeTableHeader();
			}
			// $this->Cell($this->pos[0],7,$row['IDPerro'],	'LR',0,$this->align[0],$fill);
			// $this->Cell($this->pos[0],7,$rowcount+1,		'LR',	0,		$this->align[0],$fill); // display order instead of idperro

            $this->SetFont($this->getFontName(),'',8); // normal 8px
			$this->Cell($this->pos[0],5,$row['Dorsal'],		'LR',	0,		$this->align[1],	$fill);
			$this->SetFont($this->getFontName(),'B',9); // bold 9px
			if ($this->useLongNames) {
				$n=$row['Nombre']." - ".$row['NombreLargo'];
				$this->Cell($this->pos[1],5,$n,		'LR',	0,		'L',	$fill);
			} else {
				$this->Cell($this->pos[1],5,$row['Nombre'],		'LR',	0,		$this->align[1],	$fill);
				if ($this->federation->get('WideLicense')) $this->SetFont($this->getFontName(),'',7); // normal 7px
				else $this->SetFont($this->getFontName(),'',8); // normal 8px
				$this->Cell($this->pos[2],5,$row['Licencia'],	'LR',	0,		$this->align[2],	$fill);
			}
			$this->SetFont($this->getFontName(),'',8); // normal 8px
            $this->Cell($this->pos[3],5,$row['Raza'],	    'LR',	0,		$this->align[3],	$fill);
            $this->Cell($this->pos[4],5,$row['NombreGuia'],	'LR',	0,		$this->align[4],	$fill);
			$this->Cell($this->pos[5],5,$row['NombreClub'],	'LR',	0,		$this->align[5],	$fill);
			if ($this->pos[7]==0) { // journey has no grades
				$cat=$this->federation->getCategory($row['Categoria']);
				$this->Cell($this->pos[6],5,$cat,	'LR',	0,		$this->align[6],	$fill);
			} else {
                $cat=$this->federation->getCategoryShort($row['Categoria']);
                $grad=$this->federation->getGradeShort($row['Grado']);
				$this->Cell($this->pos[6],5,$cat,	'LR',	0,		$this->align[6],	$fill);
				$this->Cell($this->pos[7],5,$grad,		'LR',	0,		$this->align[7],	$fill);
			}
			$this->Cell($this->pos[8],5,($row['Celo']==0)?"":"X",'LR',0,	$this->align[8],	$fill);
			$this->Cell($this->pos[9],5,$row['Observaciones'],'LR',	0,		$this->align[9],	$fill);
			$this->Ln();
			$fill = ! $fill;
			$rowcount++;
		}
		// Línea de cierre
		$this->Cell(array_sum($this->pos),0,'','T');
		$this->myLogger->leave();
	}
}

/**
 * Class PrintTarjetasDeVisita
 * Imprime en formato de tarjeta de visita las inscripciones indicadas
 * Por cada dorsal se imprimen dos tarjetas
 */
class PrintTarjetasDeVisita extends PrintCommon{
    protected $inscritos; // inscritos en la prueba
	protected $hasGrades=false;
    /**
     * Constructor
     * @param {integer} $pruebaid Prueba ID
     * @param {array} $inscritos Lista de inscritos en formato jquery array[count,rows[]]
     * @param {array} $jornadas lista de jornadas de la prueba
     * @param {integer} $jornadaid id de la prueba que buscamos
     * @throws Exception
     */
    function __construct($pruebaid,$inscritos,$jornadas) {
        parent::__construct('Portrait','print_tarjetaDeVisita',$pruebaid,0);
        if ( ($pruebaid==0) || ($inscritos===null) ) {
            $this->errormsg="printTarjetaDeVisita: either prueba or inscription data are invalid";
            throw new Exception($this->errormsg);
        }
        // ordenamos inscripciones por dorsal
        usort($inscritos['rows'],function($a,$b){return ($a['Dorsal']>$b['Dorsal'])?1:-1;});
        $this->inscritos=$inscritos['rows'];
        // miramos si alguna jornada tiene grado para ponerlo
		foreach ($jornadas['rows'] as $jornada) {
        	if ($jornada['Nombre']==='-- Sin asignar --') continue;
        	if (Jornadas::hasGrades((object)$jornada)) $this->hasGrades=true;
		}
		// ajustamos nombre del fichero
        $this->set_FileName("Dorsales_Identificativos.pdf");
    }

    private function printCard($x,$y,$item) {
        // REMINDER: $this->cell( width, height, data, borders, where, align, fill)
		// borde de la tarjeta
		$this->SetXY($x,$y);
        $this->Cell(85,55, '','TBLR',0,'C',false);
    	// nombre de la prueba
        $this->SetXY($x+1,$y+1);
		$this->ac_header(1,15);
		$this->Cell(83,9, $this->prueba->Nombre,'',0,'C',true);
		// Dorsal
        $this->SetXY($x+1,$y+10+1);
        $this->ac_header(2,65);
        $this->Cell(63,20, sprintf("%03d",$item['Dorsal']),'',0,'C',true);
		// Nombre del perro y Pedigree
		$n=$item['Nombre']." - ".$item['NombreLargo'];
        $this->SetXY($x,$y+30);
		$this->ac_row(1,13,'BI');
        $this->Cell(65,8, $n,'',0,'L',false);
		// Nombre del guia
        $this->SetXY($x,$y+38);
        $this->ac_row(1,12,'B');
        $this->Cell(65,8, $item['NombreGuia'],'',0,'R',false);
		// categoria/grado si se requiere
        $catstr=$this->federation->getCategory($item['Categoria']);
        $grstr="";
		if ($this->hasGrades) {
			$grstr= " - ".$this->federation->getGrade($item['Grado']);
		}
        $this->SetXY($x,$y+48);
        $this->Cell(40,5, $catstr.$grstr,'',0,'L',false);
		// club
        $this->SetXY($x+40,$y+48);
        $this->Cell(45,5, $item['NombreClub'],'',0,'R',false);

		// logotipo de la organizacion
        $this->SetXY($x+65+1,$y+10+1);
        $this->Image($this->icon,$this->GetX(),$this->GetY(),18);
		// logotipo del club/pais
        $logo=$this->getLogoName($item['Perro']);
        $this->SetXY($x+65+1,$y+30+1);
        $this->Image($logo,$this->GetX(),$this->GetY(),18);
	}

    function composeTable() {
    	$rowcount=0;
    	$this->AddPage();
    	foreach($this->inscritos as $item) {
    	    // when configured to do, skip printing of pre-agility dorsals
    	    if ( ($item['Grado']==="P.A.") && (intval($this->config->getEnv('pdf_skippa')) )===1) continue;
            if ( $rowcount>9) { $this->AddPage(); $rowcount=0; }
            $dx=(($rowcount%2)==0)?0:85+10;
            $this->printCard(10+$dx,10+55*intval($rowcount/2),$item);
            $rowcount++;
		}
	}
}

?>
