 <!-- 
dlg_jornadas.inc

Copyright 2013-2015 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

This program is free software; you can redistribute it and/or modify it under the terms 
of the GNU General Public License as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; 
if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 -->

<?php
require_once(__DIR__."/../../server/auth/Config.php");
require_once(__DIR__."/../../server/tools.php");
$config =Config::getInstance();
?>

<!-- FORMULARIO DE DECLARACION Y MODIFICACION DE JORNADAS -->
<div id="jornadas-dialog" style="width:375px;padding:25px">
		<div class="ftitle"><?php _e('Informaci&oacute;n de la Jornada');?><br /></div>
        <form id="jornadas-form" method="get" novalidate>
            <div class="fitem">
                <label for="jornadas-Nombre" style="width:250px">Nombre / Comentarios:</label>
                <input id="jornadas-Nombre" name="Nombre" type="text" class="easyui-validatebox" style="width:325px"/>
            </div>
        	<div class="fitem">
				<!-- ID de la prueba asociada al formulario -->
				<input id="jornadas-Prueba" type="hidden" name="Prueba"/>
				<!-- ID de jornada; usado para updates y deletes -->
				<input id="jornadas-ID" type="hidden" name="ID"/> 
				<!-- Operacion a realizar con el formulario -->
				<input id="jornadas-Operation" type="hidden" name="Operation"/>
        	</div>
            <div class="fitem">
                <label for="jornadas-Fecha">Fecha</label>
                <select id="jornadas-Fecha" name="Fecha" class="easyui-datebox" style="width:250px;text-align:right"></select>
            </div>
            <div class="fitem">
                <label for="jornadas-Hora">Hora</label>
                <input id="jornadas-Hora" name="Hora" class="easyui-timespinner" style="width:250px;text-align:right"/>
            </div>
            <div class="fitem">
            	<label style="width:250px">Rondas de que consta la jornada:</label>
            </div>
            
            <div class="fitem">
            	<input type="hidden" id="jornadas-PreAgility" name="PreAgility" value="1">
            	<input type="hidden" id="jornadas-PreAgility2" name="PreAgility2" value="2">
                <label for="jornadas-PreAgilityChk" style="width:150px" >Pre-Agility</label>
                <input id="jornadas-PreAgilityChk" name="PreAgilityChk" class="easyui-checkbox" 
                	type="checkbox" checked="checked" value="1"  onClick="checkPrueba('#jornadas-PreAgilityChk',0x0003)"/>
                <select id="jornadas-MangasPreAgility" name="MangasPreAgility" style="width:130px">
                <option value="1"> Manga única </option>
                <option value="2"> Dos Mangas</option>
                </select>
            </div>
            
            <div class="fitem">
                <label for="jornadas-Grado1" style="width:150px" >Grado 1</label>
                <input id="jornadas-Grado1" name="Grado1" class="easyui-checkbox" 
                type="checkbox" checked="checked" value="1" onClick="checkPrueba('#jornadas-Grado1',0x0004)"/>
            </div>
            
            <div class="fitem">
                <label for="jornadas-Grado2" style="width:150px" >Grado 2</label>
                <input id="jornadas-Grado2" name="Grado2" class="easyui-checkbox" 
                	type="checkbox" checked="checked" value="1" onClick="checkPrueba('#jornadas-Grado2',0x0008)"/>
            </div>
            
            <div class="fitem">
                <label for="jornadas-Grado3" style="width:150px" >Grado 3</label>
                <input id="jornadas-Grado3" name="Grado3" class="easyui-checkbox" 
                	type="checkbox" checked="checked" value="1" onClick="checkPrueba('#jornadas-Grado3',0x0010)"/>
            </div>
            
            <div class="fitem">
                <label for="jornadas-Open" style="width:150px" >Prueba Abierta (Sin grados)</label>
                <input id="jornadas-Open" name="Open" class="easyui-checkbox" 
                	type="checkbox" value="1"  onClick="checkPrueba('#jornadas-Open',0x0020)"/>
            </div>
    
             <div class="fitem">
            	<input type="hidden" id="jornadas-Equipos3" name="Equipos3" value="1">
            	<input type="hidden" id="jornadas-Equipos4" name="Equipos4" value="2">
                <label for="jornadas-EquiposChk" style="width:150px" >Equipos</label>
                <input id="jornadas-EquiposChk" name="EquiposChk" class="easyui-checkbox" 
                	type="checkbox" checked="checked" value="1"  onClick="checkPrueba('#jornadas-EquiposChk',0x00C0)"/>
                <select id="jornadas-MangasEquipos" name="MangasEquipos" style="width:130px">
                <option value="1">3 mejores de 4</option>
                <option value="2">Cuatro conjunta</option>
                </select>
            </div>
            
            <div class="fitem">
                <label for="jornadas-KO" style="width:150px" >Prueba K.O.</label>
                <input id="jornadas-KO" name="KO" class="easyui-checkbox" 
                	type="checkbox" value="1" onClick="checkPrueba('#jornadas-KO',0x0100);"/>
            </div>
            
            <div class="fitem">
                <label for="jornadas-Especial" style="width:150px" >Manga especial</label>
                <input id="jornadas-Especial" name="Especial" class="easyui-checkbox" 
                	type="checkbox" value="1"  onClick="checkPrueba('#jornadas-Especial',0x0200)"/>
                <input id="jornadas-Observaciones" name="Observaciones" type="text" value="(Especificar)" class="easyui-validatebox" style="width:125px"/>
            </div>
        </form>
            
</div>
    
<!-- BOTONES DE ACEPTAR / CANCELAR DEL CUADRO DE DIALOGO -->
<div id="jornadas-dlg-buttons" style="padding:5px">
	<a id="jornadas-okBtn" href="#" class="easyui-linkbutton" 
		data-options="iconCls:'icon-ok'" onclick="saveJornada()">Guardar</a>
	<a id="jornadas-cancelBtn" href="#" class="easyui-linkbutton" 
		data-options="iconCls:'icon-cancel'" onclick="$('#jornadas-dialog').dialog('close')">Cancelar</a>
</div>
	
<script type="text/javascript">
    // datos del formulario de nuevo/edit jornada
    $('#jornadas-dialog').dialog({
        modal:true,
        closed:true,
        buttons:'#jornadas-dlg-buttons'
    });
    // - declaracion del formulario
    $('#jornadas-form').form({
        onLoadSuccess : function(data) {
            // fix date and checkboxes value into datebox in "onLoadSuccess" event declaration
            if (data.PreAgility==1) {
                $('#jornadas-PreAgilityChk').prop('checked',true);
                $('#jornadas-MangasPreAgility').prop('value',1);
            	$('#jornadas-MangasPreAgility').prop('disabled',false);
            } else if (data.PreAgility2==1) {
                $('#jornadas-PreAgilityChk').prop('checked',true);
            	$('#jornadas-MangasPreAgility').prop('disabled',false);
                $('#jornadas-MangasPreAgility').prop('value',2);
            } else {
                $('#jornadas-PreAgilityChk').prop('checked',false);
            	$('#jornadas-MangasPreAgility').prop('disabled','disabled');
            }
            $('#jornadas-PreAgility').prop('checked',(data.PreAgility == 1));
            $('#jornadas-Grado1').prop('checked',(data.Grado1 == 1));
            $('#jornadas-Grado2').prop('checked',(data.Grado2 == 1));
            if (workingData.datosPrueba.RSCE==0) {
            	$('#jornadas-Grado3').prop('disabled',false);
                $('#jornadas-Grado3').prop('checked',(data.Grado3 == 1));
            } else {
            	$('#jornadas-Grado3').prop('disabled','disabled');
                $('#jornadas-Grado3').prop('checked',false);
            }
            if (data.Equipos3==1) {
                $('#jornadas-EquiposChk').prop('checked',true);
                $('#jornadas-MangasEquipos').prop('value',64);
            	$('#jornadas-MangasEquipos').prop('disabled',false);
            } else if (data.Equipos4==1) {
                $('#jornadas-EquiposChk').prop('checked',true);
            	$('#jornadas-MangasEquipos').prop('disabled',false);
                $('#jornadas-MangasEquipos').prop('value',128);
            } else {
                $('#jornadas-EquiposChk').prop('checked',false);
            	$('#jornadas-MangasEquipos').prop('disabled','disabled');
            }
            $('#jornadas-Equipos3').prop('checked',(data.Equipos3 == 1));
            $('#jornadas-Equipos4').prop('checked',(data.Equipos4 == 1));
            $('#jornadas-Open').prop('checked',(data.Open == 1));
            $('#jornadas-KO').prop('checked',(data.KO == 1));
            $('#jornadas-Especial').prop('checked',(data.Especial == 1));
        	$('#jornadas-Observaciones').prop('disabled',(data.Especial==1)?false:'disabled');
            $('#jornadas-Cerrada').prop('checked',(data.Cerrada == 1));
        	$('#jornadas-Prueba').val(data.Prueba);
        	$('#jornadas-Operation').val('update');
        }
    });

    // fechas
    $('#jornadas-Fecha').datebox({ 
        required: true,
        formatter: function(date) {
            		var y = date.getFullYear();
            		var m = date.getMonth()+1;
            		var d = date.getDate();
            		return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        		},
        parser:  function (s) {
            		if (!s) return new Date();
            		var ss = (s.split('-'));
            		var y = parseInt(ss[0],10);
            		var m = parseInt(ss[1],10);
            		var d = parseInt(ss[2],10);
            		if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
            		    return new Date(y,m-1,d);
            		} else {
            		    return new Date();
            		}
        		}
    });
    $('#jornadas-Fecha').datebox('calendar').calendar({ firstDay:1 });
    $('#jornadas-Fecha').datebox('options').keyHandler.query = function(q) {
    	if (q.indexOf('-') >= 0) { q = replaceAll('-', '/',q);	}
    	$(this).datebox('setValue', q);
    };
    // hora
    $('#jornadas-Hora').timespinner({
        required: true
    });
	// Nombre/comentarios
    $('#jornadas-Nombre').validatebox({
        required: true,
        validType: 'length[1,255]'
    });

	addTooltip($('#jornadas-okBtn').linkbutton(),"Registrar la entrada en la Base de Datos");
	addTooltip($('#jornadas-cancelBtn').linkbutton(),"Cancelar la operaci&oacute;n. Cerrar ventana");
    
</script>