/*
 inscripciones.js

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

<?php
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/tools.php");
$config =Config::getInstance();
?>

//***** gestion de inscripciones de una prueba	*****************************************************

/**
 *Abre dialogo de registro de inscripciones
 *@param {string} dg datagrid ID de donde se obtiene el id de la prueba
 *@param {string} def default value to insert into search field
 *@param {function} onAccept what to do when a new inscription is created
 */
function newInscripcion(dg,def,onAccept) {
	$('#new_inscripcion-dialog').dialog('open').dialog('setTitle','<?php _e('New inscriptions'); ?>');
	// let openEvent on dialog fire up form setup
	if (onAccept!==undefined)$('#new_inscripcion-okBtn').one('click',onAccept);
}

/**
 * On-the-fly change inscription on a journey
 *
 * Call server to perform a simple (un)inscription on a given journey for a dog
 * On success refresh affected datagrid row
 * @param idx inscription datagrid index
 * @param prueba Prueba ID
 * @param perro Dog ID
 * @param jindex Journey index (0..7)
 * @param obj changed checkbox
 */
function changeInscription(idx,prueba,perro,jindex,obj) {
    var ji=1+parseInt(jindex);
    $.messager.progress({height:75, text:'<?php _e("Updating inscription");?>'});
    $.ajax({
        type: 'GET',
        url: '../ajax/database/inscripcionFunctions.php',
        data: {
            Operation: (obj.checked)?"insertIntoJourney":"deleteFromJourney",
            Prueba: prueba,
            Perro: perro,
            Jornada: ji // notice index, no real Jornada ID
        },
        dataType: 'json',
        success: function (result) {
            if (result.errorMsg){
                $.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: result.errorMsg });
                obj.checked=!obj.checked; // revert change ( beware on recursive onChange events )
            } else {
                var j="J"+ji;
                // on save done refresh related datagrid index data
                $('#inscripciones-datagrid').datagrid('getRows')[idx][j]=obj.checked;
            }
        },
        complete: function () {
            $.messager.progress('close');
        }
    });
}

function editInscripcion() {
	if ($('#inscripciones-datagrid-search').is(":focus")) return; // on enter key in search input ignore
	// obtenemos datos de la inscripcion seleccionada
	var row= $('#inscripciones-datagrid').datagrid('getSelected');
    if (!row) {
    	$.messager.alert('<?php _e("No selection"); ?>','<?php _e("There is no inscription(s) selected"); ?>',"warning");
    	return; // no hay ninguna inscripcion seleccionada. retornar
    }
    row.Operation='update';
    $('#edit_inscripcion-form').form('load',row);
    $('#edit_inscripcion-dialog').dialog('open');
}

/**
 * Save Inscripcion being edited, as result of doneBtn.onClick()
 * On success refresh every related datagrids
 * if (done) close dialog, else reload
 */
function saveInscripcion(close) {
	// make sure that "Celo" field has correct value
	$('#edit_inscripcion-Celo').val( $('#edit_inscripcion-Celo2').is(':checked')?'1':'0');
    var frm = $('#edit_inscripcion-form');
    if (!frm.form('validate')) return;

    // disable button in ajax call to avoid recall twice
    $('#edit_inscripcion-okBtn').linkbutton('disable');
    $.ajax({
        type: 'GET',
        url: '../ajax/database/inscripcionFunctions.php',
        data: frm.serialize(),
        dataType: 'json',
        success: function (result) {
            if (result.errorMsg){ 
            	$.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: result.errorMsg });
            } else {
            	// on save done refresh related data/combo grids and close dialog
                $('#inscripciones-datagrid').datagrid('reload');
            	if (close)  $('#edit_inscripcion-dialog').dialog('close');
            }
        },
        error: function(XMLHttpRequest,textStatus,errorThrown) {
            $.messager.alert("Save Inscripcion","Error:"+XMLHttpRequest.status+" - "+XMLHttpRequest.responseText+" - "+textStatus+" - "+errorThrown,'error' );
        },
        complete: function(result) {
            $('#edit_inscripcion-okBtn').linkbutton('enable');
        }
    });
}

/**
 * Delete data related with an inscription in BBDD
 */
function deleteInscripcion() {
	var row = $('#inscripciones-datagrid').datagrid('getSelected');    
	if (!row) {
		$.messager.alert('<?php _e("No selection"); ?>','<?php _e("There is no inscription(s) selected"); ?>',"warning");
    	return; // no hay ninguna inscripcion seleccionada. retornar
    }
	$.messager.confirm(
	        'Confirm',
			"<p><b><?php _e('Notice'); ?>:</b></p>" +
			"<p><?php _e('If you delete this inscription'); ?> ("+row.Nombre+")<br/>" +
			"<?php _e('<b>youll loose</b> every related results and data on this contest'); ?><br />" +
			"<?php _e('afecting journeys not marked as <em>closed</em>'); ?><br/>" +
			"<?php _e('Really want to delete selected inscription'); ?>?</p>",
			function(r){
				if (r){
					$.get(
						// URL
						'../ajax/database/inscripcionFunctions.php',
						// arguments
						{ 
							Operation:'delete',
							ID:row.ID, // id de la inscripcion
							Perro:row.Perro, // id del perro
							Prueba:row.Prueba // id de la prueba
						},
						// on Success function
						function(result){
							if (result.success) {
								$('#inscripciones-datagrid').datagrid('unselectAll').datagrid('reload',{ // load the inscripciones table
									where: $('#inscripciones-search').val()
								});
							} else {
								$.messager.show({ width:300, height:200, title:'Error', msg:result.errorMsg });
							}
						},
						// expected datatype format for response
						'json'
					);
				} // if (r)
		}).window('resize',{width:475});
}

/**
 * Ask for commit new inscripcion to server
 * @param {string} dg datagrid to retrieve selections from
 */
function insertInscripcion(dg) {
	function handleInscription(rows,index,size) {
		if (index>=size){
            // recursive call finished, clean, close and refresh
            $('#new_inscripcion-okBtn').linkbutton('enable');
            pwindow.window('close');
            $(dg).datagrid('clearSelections');
            reloadWithSearch('#new_inscripcion-datagrid','noinscritos');
            reloadWithSearch('#inscripciones-datagrid','inscritos');
			return;
		}
		$('#new_inscripcion-progresslabel').text('<?php _e("Enrolling"); ?>'+": "+rows[index].Nombre);
		$('#new_inscripcion-progressbar').progressbar('setValue', (100.0*(index+1)/size).toFixed(2));
		$.ajax({
			cache: false,
			timeout: 20000, // 20 segundos
			type:'GET',
			url:"../ajax/database/inscripcionFunctions.php",
			dataType:'json',
			data: {
				Prueba: workingData.prueba,
				Operation: 'insert',
				Perro: rows[index].ID,
				Jornadas: $('#new_inscripcion-Jornadas').val(),
				Celo: $('#new_inscripcion-Celo').val(),
				Pagado: $('#new_inscripcion-Pagado').val()
			},
			success: function(result) {
                handleInscription(rows,index+1,size);
            },
            error: function(XMLHttpRequest,textStatus,errorThrown) {
                $.messager.alert("Save Club","Error:"+XMLHttpRequest.status+" - "+XMLHttpRequest.responseText+" - "+textStatus+" - "+errorThrown,'error' );
                $('#new_inscripcion-okBtn').linkbutton('enable'); // enable button and do not continue inscription chain
            }
		});
	}

	var pwindow=$('#new_inscripcion-progresswindow');
	var selectedRows= $(dg).datagrid('getSelections');
	var size=selectedRows.length;
	if(size==0) {
		$.messager.alert('<?php _e("No selection"); ?>','<?php _e("There is no marked dog to be inscribed"); ?>',"warning");
    	return; // no hay ninguna inscripcion seleccionada. retornar
	}
	if (ac_authInfo.Perms>2) {
    	$.messager.alert('<?php _e("No permission"); ?>','<?php _e("Current user has not enought permissions to handle inscriptions"); ?>',"error");
    	return; // no tiene permiso para realizar inscripciones. retornar
	}
	pwindow.window('open');

    // disable button in ajax call to avoid recall twice
    $('#new_inscripcion-okBtn').linkbutton('disable');
	handleInscription(selectedRows,0,size);
}

/**
 * Reajusta los dorsales de los perros inscritos ordenandolos por club,categoria,grado,nombre
 * @param idprueba ID de la prueba
 */
function reorderInscripciones(idprueba) {
    $.messager.confirm(
        '<?php _e("Reorder dorsals"); ?>',
        '<?php _e("Current dorsal numbers will be lost<br />Continue"); ?>?',
        function (r) {
            if (!r) return false;
            $.messager.progress({title:'<?php _e("Sort"); ?>',text:'<?php _e("Re-ordering Dorsals");?>'});
            $.ajax({
                cache: false,
                timeout: 60000, // 60 segundos
                type:'GET',
                url:"../ajax/database/inscripcionFunctions.php",
                dataType:'json',
                data: {
                    Prueba: idprueba,
                    Operation: 'reorder'
                },
                success: function(data) {
                    if(data.errorMsg) {
                        $.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: data.errorMsg });
                    } else {
                        $('#inscripciones-datagrid').datagrid('reload');
                    }
                    $.messager.progress('close');
                },
                error:function(jqXHR, textStatus, errorThrown) {
                    // console.log(textStatus, errorThrown);
                    $.messager.progress('close');
                }
            });
        }
    );
}


function clearJourneyInscriptions(current){
    var row=$('#inscripciones-jornadas').datagrid('getData')['rows'][current];
    if (row.Nombre==='-- Sin asignar --') {
        $.messager.alert('<?php _e("Undeclared"); ?>','<?php _e("Selected journey to clear is empty"); ?>',"warning");
        return false; // no hay ninguna jornada seleccionada para clonar
    }
    $.messager.progress({title:'<?php _e("Clear inscriptions"); ?>',text:'<?php _e("Clearing inscriptions in journey");?>'+"'"+row.Nombre+"'" });
    $.ajax({
        cache: false,
        timeout: 60000, // 60 segundos
        type:'GET',
        url:"../ajax/database/inscripcionFunctions.php",
        dataType:'json',
        data: {
            Prueba: row.Prueba,
            Operation: 'clearinscripciones',
            Jornada: row.ID
        },
        success: function(data) {
            if(data.errorMsg) {
                $.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: data.errorMsg });
            } else {
                $('#inscripciones-datagrid').datagrid('reload');
            }
            $.messager.progress('close');
        },
        error:function(jqXHR, textStatus, errorThrown) {
            // console.log(textStatus, errorThrown);
            $.messager.progress('close');
        }
    });
    return false;
}

function inscribeAllIntoJourney(current){
    var row=$('#inscripciones-jornadas').datagrid('getData')['rows'][current];
    if (row.Nombre==='-- Sin asignar --') {
        $.messager.alert('<?php _e("Undeclared"); ?>','<?php _e("Must declare this journey first"); ?>',"warning");
        return false; // no hay ninguna jornada seleccionada para clonar
    }
    $.messager.progress({title:'<?php _e("Inscribe all"); ?>',text:'<?php _e("Inscribe all dogs into journey");?>'+"'"+row.Nombre+"'" });
    $.ajax({
        cache: false,
        timeout: 60000, // 60 segundos
        type:'GET',
        url:"../ajax/database/inscripcionFunctions.php",
        dataType:'json',
        data: {
            Prueba: row.Prueba,
            Operation: 'populateinscripciones',
            Jornada: row.ID
        },
        success: function(data) {
            if(data.errorMsg) {
                $.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: data.errorMsg });
            } else {
                $('#inscripciones-datagrid').datagrid('reload');
            }
            $.messager.progress('close');
        },
        error:function(jqXHR, textStatus, errorThrown) {
            // console.log(textStatus, errorThrown);
            $.messager.progress('close');
        }
    });
    return false;
}

function inscribeSelectedIntoJourney(current){
    function doInscribeSelectedIntoJourney(tojourney) {
        $.messager.progress({title:'<?php _e("Inscribe selection"); ?>',text:'<?php _e("Cloning inscriptions from selected journey into ");?>'+"'"+tojourney.Nombre+"'" });
        $.ajax({
            cache: false,
            timeout: 60000, // 60 segundos
            type:'GET',
            url:"../ajax/database/inscripcionFunctions.php",
            dataType:'json',
            data: {
                Prueba: row.Prueba,
                Operation: 'cloneinscripciones',
                From: row.ID,
                Jornada: tojourney.ID
            },
            success: function(data) {
                if(data.errorMsg) {
                    $.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: data.errorMsg });
                } else {
                    $('#inscripciones-datagrid').datagrid('reload');
                }
                $.messager.progress('close');
            },
            error:function(jqXHR, textStatus, errorThrown) {
                // console.log(textStatus, errorThrown);
                $.messager.progress('close');
            }
        });
    }

    var row=$('#inscripciones-jornadas').datagrid('getSelected');
    if (!row) {
        $.messager.alert('<?php _e("No selection"); ?>','<?php _e("There is no journey selected"); ?>',"warning");
        return false; // no hay ninguna jornada seleccionada para clonar
    }
    if(row.Nombre==='-- Sin asignar --') {
        $.messager.alert('<?php _e("Undeclared"); ?>','<?php _e("Selected journey to clone has no data"); ?>',"warning");
        return false; // no hay ninguna jornada seleccionada para clonar
    }
    var tojourney=$('#inscripciones-jornadas').datagrid('getData')['rows'][current];
    if (tojourney.Nombre==='-- Sin asignar --') {
        $.messager.confirm('<?php _e("Undeclared"); ?>','<?php _e("Selected journey to clone into is not defined. Create?"); ?>',function(r){
            if (!r) return false;
            // create new journey data from original
            var id=tojourney.ID;
            var journey=cloneObj(row);
            journey.ID=id;
            journey.Operation='update';
            journey.Nombre="Clone of "+row.Nombre;
            // update journey info
            $.ajax({
                type: 'GET',
                url: '../ajax/database/jornadaFunctions.php',
                data: journey,
                dataType: 'json',
                success: function (result) {
                    if (result.errorMsg){
                        $.messager.show({width:300, height:200, title:'Error',msg: result.errorMsg });
                        return false;
                    } else {
                        doInscribeSelectedIntoJourney(journey);
                        $('#inscripciones-jornadas').datagrid('reload');    // reload the prueba data
                    }
                }
            });
        });
        return false; // no hay ninguna jornada seleccionada para clonar, y el usuario aborta operacion
    }
    // arriving here means that destination journey exists and is defined. try to process
    doInscribeSelectedIntoJourney(tojourney);
    return false;
}

/**
 * cambia el dorsal
 * @param idprueba ID de la prueba
 */
function setDorsal() {
	var row = $('#inscripciones-datagrid').datagrid('getSelected');
	if (!row) {
		$.messager.alert('<?php _e("No selection"); ?>','<?php _e("There is no inscription(s) selected"); ?>',"warning");
		return; // no hay ninguna inscripcion seleccionada. retornar
	}
	var m=$.messager.prompt(
		'<?php _e("Set dorsal"); ?>',
		'<?php _e("Please type new dorsal<br />If already assigned, <br/>dorsals will be swapped"); ?>',
		function(r) {
			if (!r || isNaN(parseInt(r))) return;
			$.messager.progress({title:'<?php _e("Set dorsal"); ?>',text:'<?php _e("Setting new dorsal...");?>'});
			$.ajax({
				cache: false,
				timeout: 60000, // 60 segundos
				type:'GET',
				url:"../ajax/database/inscripcionFunctions.php",
				dataType:'json',
				data: {
					Prueba: row.Prueba,
					Perro: row.Perro,
					Dorsal: row.Dorsal,
					NewDorsal: parseInt(r),
					Operation: 'setdorsal'
				},
				success: function(data) {
					if(data.errorMsg) {
						$.messager.show({width:300, height:200, title:'<?php _e('Error'); ?>',msg: data.errorMsg });
					} else {
						$('#inscripciones-datagrid').datagrid('reload');
					}
					$.messager.progress('close');
				},
				error:function(jqXHR, textStatus, errorThrown) {
					// console.log(textStatus, errorThrown);
					$.messager.progress('close');
				}
			});
		}
	);
    m.find('.messager-input').bind('keypress', function(e) { // accept "Enter" as "OK" button
            if(e.keyCode==13) $('body div.messager-body>div.messager-button').children('a.l-btn:first-child').click();
        }
    );
}
/**
 * Comprueba si un participante se puede o no inscribir en una jornada
 * @param {object} jornada, datos de la jornada
 */
function canInscribe(jornada) {
	var result=true;
	if (jornada.Cerrada==1) result=false;
	if (jornada.Nombre === '-- Sin asignar --') result=false;
	return result;
}

function importExportInscripciones() {
	var cb='<input type="text" id="excel-selClub" class="easyui-combobox" name="excel-selClub" />';
	var options= {
		0: '<?php _e("Generate empty excel inscription template");?>',
		1: '<?php _e("Generate inscription template for club");?>: '+cb,
		2: '*<?php _e("Export current inscriptions to Excel file");?>',
		3: '<?php _e("Import inscriptions from Excel file");?>'
	};
	$.messager.radio(
		'<?php _e('Excel import/export'); ?>',
		'<?php _e('Choose from available operations'); ?>:<br/>&nbsp;<br/>',
		options,
		function(r) {
            var opt=parseInt(r);
		    var club= /* opt==1 */ $('#excel-selClub').combobox('getValue'); // on "--sin asignar--" means print inscriptions
            if (opt==0) club=-1;
            if (opt==2) club=0;
            if (opt!=3) { // export
                $.fileDownload(
                    '../ajax/excel/excelWriterFunctions.php',
                    {
                        httpMethod: 'GET',
                        data: {	'Operation':'Inscripciones','Prueba': workingData.prueba, 'Club': club },
                        preparingMessageHtml: '<?php _e("Creating Excel file. Please wait"); ?> ...',
                        failMessageHtml: '<?php _e("There was a problem generating your report, please try again"); ?>.'
                    }
                );
            } else { // import
                check_permissions(access_perms.ENABLE_IMPORT, function (res) {
                    if (res.errorMsg) {
                        $.messager.alert('License error','<?php _e("Current license has no Excel import function enabled"); ?>', "error");
                    } else {
                        $('#inscripciones-excel-dialog').dialog('open');
                    }
                    return false; // prevent default fireup of event trigger
                });
            }
		}
	).window('resize',{width:530});
	$('#excel-selClub').combobox({
		width:165,
		valueField:'ID',
		textField:'Nombre',
		mode:'remote',
		url:'../ajax/database/clubFunctions.php',
		queryParams: {
			Operation:	'enumerate',
			Combo:	1,
			Federation: workingData.federation
		}
	});
	return false; //this is critical to stop the click event which will trigger a normal file download!
}

/**
 * Imprime las inscripciones
 * @returns {Boolean} true on success, otherwise false
 */
function printInscripciones() {

    function do_print(r) {
        var where= $('#inscripciones-datagrid-search').val();
        if (where==="<?php _e('-- Search --');?>") where="";
        var dg=$('#inscripciones-datagrid');
        var order= dg.datagrid('options').sortOrder;
        var sort= dg.datagrid('options').sortName;
        if ( (sort==null) || (sort=="" )) { order=""; sort=""; }
        $.fileDownload(
            '../ajax/pdf/print_inscritosByPrueba.php',
            {
                httpMethod: 'GET',
                data: {
                    Prueba: workingData.prueba,
                    Jornada: jornada,
                    Mode: parseInt(r),
                    page: 0,
                    rows: 0,
                    where: where,
                    sort: sort,
                    order: order
                },
                preparingMessageHtml: '<?php _e("Printing inscriptions. Please wait"); ?> ...',
                failMessageHtml: '<?php _e("There was a problem generating your report, please try again"); ?>.'
            }
        );
        return false;
    }

	// en el caso de que haya alguna jornada seleccionada.
	// anyadir al menu la posibilidad de imprimir solo los inscritos en dicha jornada
	var options= {
	    0:'<?php _e('Simple listing'); ?>',
        1:'<?php _e('Catalog'); ?>',
        2:'<?php _e('Statistics'); ?>',
        4:'<?php _e('Current selection/order'); ?>',
        6:'<?php _e('Handlers with more than one dog'); ?>',
        5:'<?php _e('Competition ID Cards'); ?>'
	};
	// buscamos la jornada seleccionada
	var row=$('#inscripciones-jornadas').datagrid('getSelected');
    var jornada=0;
	// si hay jornada seleccionada la anyadimos a la lista
	if (row!==null && row.Nombre!=="-- Sin asignar --") {
		options[3]='<?php _e('Inscriptions for journey'); ?>: "'+row.Nombre+'"';
        jornada=row.ID;
	}
	$.messager.radio(
		'<?php _e('Select form'); ?>',
		'<?php _e('Select type of document to be generated'); ?>:',
		options,
		function(r){
			if (r) { setTimeout(do_print(r),0); }
			return false ;
		}
	).window('resize',{width:(jornada==0)?300:350});
	return false; //this is critical to stop the click event which will trigger a normal file download!
}
