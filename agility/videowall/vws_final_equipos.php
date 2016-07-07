<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once(__DIR__."/../server/tools.php");
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/auth/AuthManager.php");
$config =Config::getInstance();
$am = new AuthManager("Videowall::combinada");
if ( ! $am->allowed(ENABLE_VIDEOWALL)) { include_once("unregistered.php"); return 0;}

?>
<!--
vws_final_equipos.php

Copyright  2013-2016 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 -->
<!--
<div id="vws-panel" style="padding:5px;">
-->
    <div id="vws_header>">
        <form id="vws_hdr_form">
        <?php if ($config->getEnv("vws_uselogo")!=0) {
            // logotipo alargado del evento
            echo '<input type="hidden" id="vws_hdr_logoprueba" name="LogoPrueba" value="/agility/images/agilityawc2016.png"/>';
            echo '<img src="/agility/images/agilityawc2016.png" id="vws_hdr_logo" alt="Logo"/>';
            echo '<input type="hidden"      id="vws_hdr_prueba"     name="Prueba" value="Prueba"/>';
            echo '<input type="hidden"      id="vws_hdr_jornada"     name="Jornada" value="Jornada"/>';
        } else {
            // logotipo del organizador. prueba y jornada en texto
            echo '<input type="hidden" id="vws_hdr_logoprueba" name="LogoPrueba" value="/agility/images/logos/agilitycontest.png"/>';
            echo '<img src="/agility/images/logos/agilitycontest.png" id="vws_hdr_logo" alt="Logo"/>';
            // nombre de la prueba y jornada
            echo '<input type="text"      id="vws_hdr_prueba"     name="Prueba" value="Prueba Equipos"/>';
            echo '<input type="text"      id="vws_hdr_jornada"     name="Jornada" value="Jornada Equipos"/>';
        }
        ?>
            <input type="text"      id="vws_hdr_manga"     name="Manga" value="Manga"/>
            <span style="text-align:center" id="vws_hdr_calltoring"><?php _e('Call to ring');?> </span>
            <span style="text-align:center" id="vws_hdr_teaminfo"><?php _e("Competitor's data");?> </span>
            <span style="text-align:center" id="vws_hdr_lastround"><?php _e('Round');?> </span>
            <span style="text-align:center" id="vws_hdr_finalscores"><?php _e('Final');?> </span>
            <input type="text"      id="vws_hdr_trs"     name="TRS" value="Dist/TRS"/>
        </form>
    </div>
    
    <div id="vws_llamada">
<?php for($n=0;$n<5;$n++) {
    echo '<form id="vws_call_'.$n.'">';
    echo '<input type="text" id="vws_call_Orden_'.$n.'" name="Orden" value="Orden '.$n.'"/>';
    echo '<input type="hidden" id="vws_call_LogoTeam_'.$n.'"      name="LogoTeam" value="Logo '.$n.'"/>';
    echo '<img src="/agility/images/logos/agilitycontest.png" id="vws_call_Logo_'.$n.'" name="Logo" alt="Logo '.$n.'"/>';
    echo '<input type="hidden"    id="vws_call_Equipo_'.$n.'"      name="Equipo" value="Equipo '.$n.'"/>';
    echo '<input type="hidden"    id="vws_call_Categoria_'.$n.'"  name="Categoria" value="Cat '.$n.'"/>';
    echo '<input type="hidden"    id="vws_call_Grado_'.$n.'"      name="Grado" value="Grad '.$n.'"/>';
    echo '<input type="text"      id="vws_call_NombreEquipo_'.$n.'" name="NombreEquipo" value="Equipo '.$n.'"/>';
    echo '</form>';
} ?>
    </div>
    
    <div id="vws_results">
<?php for($n=0;$n<7;$n++) {
    echo '<form id="vws_results_'.$n.'">';
    echo '<input type="hidden" id="vws_results_LogoTeam_'.$n.'"      name="LogoTeam" value="Logo '.$n.'"/>';
    echo '<img alt="Logo '.$n.'"  id="vws_results_Logo_'.$n.'"       name="Logo" src="/agility/images/logos/agilitycontest.png"  />';
    echo '<input type="hidden"    id="vws_results_Categorias_'.$n.'"  name="Categorias" value="Cat '.$n.'"/>';
    echo '<input type="hidden"    id="vws_results_Equipo_'.$n.'"     name="ID" value="Equipo '.$n.'"/>';
    echo '<input type="text"    id="vws_results_NombreEquipo_'.$n.'" name="Nombre" value="Equipo '.$n.'"/>';
    echo '<!-- data on round 1 -->';
    echo '<input type="text"      id="vws_results_T1_'.$n.'"         name="T1" value="Time1 '.$n.'"/>';
    echo '<input type="text"      id="vws_results_P1_'.$n.'"         name="P1" value="Pen1 '.$n.'"/>';
    echo '<input type="text"      id="vws_results_Puesto1_'.$n.'"    name="Puesto1" value="Pos '.$n.'"/>';
    echo '<!-- data on round 2 -->';
    echo '<input type="hidden"    id="vws_results_T2_'.$n.'"         name="T2" value="Time2 '.$n.'"/>';
    echo '<input type="hidden"    id="vws_results_P2_'.$n.'"         name="P2" value="Pen2 '.$n.'"/>';
    echo '<input type="hidden"    id="vws_results_Puesto2_'.$n.'"    name="P2" value="Pos '.$n.'"/>';
    echo '<!-- Final data -->';
    echo '<input type="text"      id="vws_results_Tiempo_'.$n.'"       name="Tiempo" value="Tiempo '.$n.'"/>';
    echo '<input type="text"      id="vws_results_Penalizacion_'.$n.'" name="Penalizacion" value="Penal '.$n.'"/>';
    echo '<input type="text"      id="vws_results_Puesto_'.$n.'"       name="Puesto" value="Pos '.$n.'"/>';
    echo '</form>';

}?>
    </div>
    
    <div id="vws_equipo_en_pista">
<?php
for($n=0;$n<4;$n++) {
    echo '<form id= "vws_current_'.$n.'">';
    if ($n==0) {
        echo '<input type="text" id= "vws_current_Orden_'.$n.'" name="Orden" value="Orden '.$n.'"/>';
        echo '<img src="/agility/images/logos/getLogo.php?Federation=1&Logo=ES.png" id= "vws_current_Logo_'.$n.'" name="Logo" alt="Logo"/>';
        echo '<input type="hidden"    id= "vws_current_NombreEquipo_'.$n.'" name="NombreEquipo" value="Equipo '.$n.'"/>';
    } else if ($n==1) {

        echo '<input type="text"      id= "vws_current_NombreEquipo_'.$n.'" name="NombreEquipo" value="Equipo '.$n.'"/>';
    } else {
        echo '<input type="hidden"    id= "vws_current_NombreEquipo_'.$n.'" name="NombreEquipo" value="Equipo '.$n.'"/>';
    }
    echo '<input type="hidden"    id= "vws_current_Logo_'.$n.'"   name="LogoClub" value="Logo '.$n.'"/>';
    echo '<input type="text"      id= "vws_current_Dorsal_'.$n.'"     name="Dorsal" value="Dorsal '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Perro_'.$n.'"      name="Perro" value="Perro '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Categoria_'.$n.'"  name="Categoria" value="Cat '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Grado_'.$n.'"      name="Grado" value="Grad '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_CatGrad_'.$n.'"    name="CatGrad" value="Grad '.$n.'"/>';
    echo '<input type="text"      id= "vws_current_Nombre_'.$n.'"     name="Nombre" value="Nombre '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Celo_'.$n.'"       name="Celo" value="Celo '.$n.'"/>';
    echo '<input type="text"      id= "vws_current_NombreGuia_'.$n.'" name="NombreGuia" value="Guia '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_NombreClub_'.$n.'" name="NombreClub" value="Club '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_F_'.$n.'"          name="Faltas" value="Flt '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_T_'.$n.'"          name="Tocados" value="Toc '.$n.'"/>';
    echo '<input type="text"      id= "vws_current_FaltasTocados_'.$n.'" name="FaltasTocados" value="F/T '.$n.'">';
    echo '<input type="text"      id= "vws_current_Rehuses_'.$n.'"    name="Rehuses" value="R '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Tintermedio_'.$n.'" name="TIntermedio" value="Tint '.$n.'"/>';
    echo '<input type="text"      id= "vws_current_Tiempo_'.$n.'"     name="Tiempo" value="Time '.$n.'"/>';
    echo '<input type="text"      id= "vws_current_Puesto_'.$n.'"     name="Puesto" value="P '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Eliminado_'.$n.'"  name="Eliminado" value="Elim '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_NoPresentado_'.$n.'" name="NoPresentado" value="NPr '.$n.'"/>';
    echo '<input type="hidden"    id= "vws_current_Pendiente_'.$n.'"  name="Pendiente" value="Pend '.$n.'"/>';
    echo '</form>';
}
?>
    </div>
    
    <div id="vws_sponsors">
        <?php include_once(__DIR__."/../videowall/vws_footer.php");?>
    </div>
    
    <div id="vws_before">
<?php for($n=0;$n<2;$n++) {
    echo '<form id="vws_before_'.$n.'">';

    echo '<input type="text"      id="vws_before_Orden_'.$n.'"      name="Orden" value="Orden '.$n.'"/>';
    echo '<input type="hidden"    id="vws_before_LogoTeam_'.$n.'"    name="LogoTeam" value="Logo '.$n.'"/>';
    echo '<img alt="Logo '.$n.'"  id="vws_before_Logo_'.$n.'"       name="Logo" src="/agility/images/logos/agilitycontest.png"  />';
    echo '<input type="hidden"    id="vws_before_Categorias_'.$n.'"  name="Categorias" value="Cat '.$n.'"/>';
    echo '<input type="hidden"    id="vws_before_Equipo_'.$n.'"     name="ID" value="Equipo '.$n.'"/>';
    echo '<input type="text"      id="vws_before_NombreEquipo_'.$n.'" name="Nombre" value="Equipo '.$n.'"/>';
    echo '<!-- data on round 1 -->';
    echo '<input type="text"      id="vws_before_T1_'.$n.'"         name="T1" value="Time1 '.$n.'"/>';
    echo '<input type="text"      id="vws_before_P1_'.$n.'"         name="P1" value="Pen1 '.$n.'"/>';
    echo '<input type="text"      id="vws_before_Puesto1_'.$n.'"    name="Puesto1" value="Pos '.$n.'"/>';
    echo '<!-- data on round 2 (hidden in simplified videowall ) -->';
    echo '<input type="hidden"    id="vws_before_T2_'.$n.'"         name="T2" value="Time2 '.$n.'"/>';
    echo '<input type="hidden"    id="vws_before_P2_'.$n.'"         name="P2" value="Pen2 '.$n.'"/>';
    echo '<input type="hidden"    id="vws_before_Puesto2_'.$n.'"    name="P2" value="Pos '.$n.'"/>';
    echo '<!-- Final data -->';
    echo '<input type="text"      id="vws_before_Tiempo_'.$n.'"       name="Tiempo" value="Tiempo '.$n.'"/>';
    echo '<input type="text"      id="vws_before_Penalizacion_'.$n.'" name="Penalizacion" value="Penal '.$n.'"/>';
    echo '<input type="text"      id="vws_before_Puesto_'.$n.'"       name="Puesto" value="Pos '.$n.'"/>';
    echo '</form>';
} ?>
    </div>
<!--
</div>
-->
<script type="text/javascript" charset="utf-8">
    
    var layout= {'rows':142,'cols':247};
    
    // cabeceras
<?php
    if ($config->getEnv("vws_uselogo")!=0) { // logotipo del evento
        echo 'doLayout(layout,"#vws_hdr_logo",1,0,88,27);';
        echo 'doLayout(layout,"#vws_hdr_manga",101,0,122,9);';
    } else { // logotipo del organizador, prueba y jornada en texto
        echo 'doLayout(layout,"#vws_hdr_logo",1,0,27,27);';
        echo 'doLayout(layout,"#vws_hdr_prueba",28,0,82,9);';
        echo 'doLayout(layout,"#vws_hdr_jornada",28,9,61,9);';
        echo 'doLayout(layout,"#vws_hdr_manga",110,0,112,9);';
    }
?>
    doLayout(layout,"#vws_hdr_trs",222,0,24,9); // dist / trs

    doLayout(layout,"#vws_hdr_calltoring",1,27,87,10);
    doLayout(layout,"#vws_hdr_teaminfo",91,9,83,9);
    doLayout(layout,"#vws_hdr_lastround",174,9,36,9);
    doLayout(layout,"#vws_hdr_finalscores",210,9,36,9);


    // llamada a pista
    for (var n=0;n<5;n++) {
        doLayout(layout,"#vws_call_Orden_"+n,1,37+9*n,10,9);
        doLayout(layout,"#vws_call_Logo_"+n,11,37+9*n,15,9);
        doLayout(layout,"#vws_call_NombreEquipo_"+n,26,37+9*n,56,9);
    }
    
    // perros del equipo en pista
    for (n=0;n<4;n++) {
        if (n==0) { // orden, logo, dorsal
            doLayout(layout,"#vws_current_Orden_0",    1,     82+10*n,10,10);
            doLayout(layout,"#vws_current_Logo_0",     11,    82+10*n,15,10);
            doLayout(layout,"#vws_current_Dorsal_0",   40,    82+10*n,10,10);
        } else if (n==1) { // equipo,dorsal
            doLayout(layout,"#vws_current_NombreEquipo_1", 1, 82+10*n,39,10);
            doLayout(layout,"#vws_current_Dorsal_1",   40,    82+10*n,10,10);
        } else { // dorsal
            doLayout(layout,"#vws_current_Dorsal_"+n,  40,    82+10*n,10,10);
        }
        doLayout(layout,"#vws_current_Nombre_"+n,      50,    82+10*n,41,10);
        doLayout(layout,"#vws_current_NombreGuia_"+n,  91,    82+10*n,70,10);
        doLayout(layout,"#vws_current_FaltasTocados_"+n,156,  82+10*n,20,10);
        doLayout(layout,"#vws_current_Rehuses_"+n,     176,   82+10*n,20,10);
        doLayout(layout,"#vws_current_Tiempo_"+n,      196,   82+10*n,35,10);
        doLayout(layout,"#vws_current_Puesto_"+n,      231,   82+10*n,15,10);
    }

    // resultados
    for(n=0;n<7;n++) {
        doLayout(layout,"#vws_results_Logo_"+n,     91,     19+9*n,10,9);
        doLayout(layout,"#vws_results_NombreEquipo_"+n,101, 19+9*n,61,9);
        doLayout(layout,"#vws_results_T1_"+n,       162,    19+9*n,16,9);
        doLayout(layout,"#vws_results_P1_"+n,       178,    19+9*n,16,9);
        doLayout(layout,"#vws_results_Puesto1_"+n,  194,    19+9*n,10,9);
        doLayout(layout,"#vws_results_Tiempo_"+n,   204,    19+9*n,16,9);
        doLayout(layout,"#vws_results_Penalizacion_"+n,220, 19+9*n,16,9);
        doLayout(layout,"#vws_results_Puesto_"+n,   236,    19+9*n,10,9);
    }
    // ultimos resultados
    for(n=0;n<2;n++) {
        doLayout(layout,"#vws_before_Orden_"+n,    82,     122+9*n,9,9);
        doLayout(layout,"#vws_before_Logo_"+n,     91,     122+9*n,10,9);
        doLayout(layout,"#vws_before_NombreEquipo_"+n,101, 122+9*n,61,9);
        doLayout(layout,"#vws_before_T1_"+n,       162,    122+9*n,16,9);
        doLayout(layout,"#vws_before_P1_"+n,       178,    122+9*n,16,9);
        doLayout(layout,"#vws_before_Puesto1_"+n,  194,    122+9*n,10,9);
        doLayout(layout,"#vws_before_Tiempo_"+n,   204,    122+9*n,16,9);
        doLayout(layout,"#vws_before_Penalizacion_"+n,220, 122+9*n,16,9);
        doLayout(layout,"#vws_before_Puesto_"+n,   236,    122+9*n,10,9);
    }
    // sponsor
    doLayout(layout,"#vws_sponsors",   1,    122,79,18);
</script>