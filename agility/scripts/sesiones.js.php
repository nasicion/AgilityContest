/*
 sesiones.js.php

Copyright  2013-2017 by Juan Antonio Martinez ( juansgaviota at gmail dot com )

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

//***** gestion de sesiones	*********************************************************

/**
 * Open "New Session dialog"
 *@param {string} dg datagrid ID de donde se obtiene la sesion
 *@param {string} def default value to insert into Nombre 
 *@param {function} onAccept what to do when a new Session is created
 */
function newSession(dg,def,onAccept){
	$('#sesiones-dialog').dialog('open').dialog('setTitle','<?php _e('New session'); ?>'); // open dialog
	$('#sesiones-form').form('clear');// clear old data (if any)
	if (!strpos(def,"Buscar")) $('#sesiones-Nombre').val(def);// fill session Name
	$('#sesiones-Operation').val('insert');// set up operation
	$('#sesiones-Operador').val(1);// set default user id for new session
	$('#sesiones-Login').val('-- Sin asignar --');// set up default user name for new session
	$('#sesiones-Logout').linkbutton('disable'); // no sense to logout default user
	if (onAccept!==undefined) $('#sesiones-okBtn').one('click',onAccept);
}

/**
 * Open "Edit Session" dialog
 * @param {string} dg datagrid ID de donde se obtiene la sesion
 */
function editSession(dg){
	if ($('#sesiones-datagrid-search').is(":focus")) return; // on enter key in search input ignore
    var row = $(dg).datagrid('getSelected');
    if (!row) {
    	$.messager.alert('<?php _e("Edit Error"); ?>','<?php _e("There is no session selected"); ?>',"warning");
    	return; // no way to know which dog is selected
    }
    if (row.ID<=1) {
        $.messager.alert('<?php _e("Edit Error"); ?>','<?php _e("This entry cannot be modified"); ?>',"error");
        return; // cannot delete default session
    }
    // set up operation properly
    row.Operation='update';
    // open dialog
    $('#sesiones-dialog').dialog('open').dialog('setTitle','<?php _e('Modify session data'); ?>');
    // and fill form with row data
    $('#sesiones-form').form('load',row);
	$('#sesiones-Logout').linkbutton('enable'); // let us logout user from session
}

/**
 * Call json to Ask for commit new/edit session to server
 */
function saveSession(){
    var frm = $('#sesiones-form');
    if (!frm.form('validate')) return; // don't call inside ajax to avoid override beforeSend()
    $.ajax({
        type: 'GET',
        url: '/agility/server/database/sessionFunctions.php',
        data: frm.serialize(),
        dataType: 'json',
        success: function (result) {
            if (result.errorMsg){
                $.messager.show({ width:300, height:200, title: '<?php _e('Error'); ?>', msg: result.errorMsg });
            } else {
                $('#sesiones-dialog').dialog('close');        // close the dialog
                $('#sesiones-datagrid').datagrid('reload');    // reload the session data
            }
        }
    });
}

/**
 * Delete session data in bbdd
 * @param {string} dg datagrid ID de donde se obtiene el session
 */
function deleteSession(dg){
    var row = $(dg).datagrid('getSelected');
    if (!row) {
    	$.messager.alert('<?php _e("Delete error"); ?>','<?php _e("There is no session selected"); ?>',"warning");
    	return; // no way to know which session is selected
    }
    if (row.ID<=2) {
    	$.messager.alert('<?php _e("Delete error"); ?>','<?php _e("This entry cannot be deleted"); ?>',"error");
    	return; // cannot delete default session
    }
    $.messager.confirm('<?php _e('Confirm'); ?>','<?php _e('Delete session'); ?>'+':'+row.Nombre+'\n '+'<?php _e('Sure?'); ?>',function(r){
      	if (!r) return;
        $.get('/agility/server/database/sessionFunctions.php',{Operation:'delete',ID:row.ID},function(result){
            if (result.success){
                $(dg).datagrid('reload');    // reload the session data
            } else {
            	// show error message
                $.messager.show({width:300,height:200,title: 'Error',msg: result.errorMsg});
            }
        },'json');
    });
}

function session_logout() {
	$('#sesiones-Operador').val(1);// set default user id for new session
	$('#sesiones-Login').val('-- Sin asignar --');// set up default user name for new session
}

function resetSession(dg) {
    var row = $(dg).datagrid('getSelected');
    if (!row) {
    	$.messager.alert('<?php _e("Delete error"); ?>','<?php _e("There is no session selected"); ?>',"warning");
    	return; // no way to know which session is selected
    }
    $.messager.confirm('<?php _e('Confirm'); ?>','<?php _e('Delete event history on session'); ?>'+':'+row.Nombre+'\n '+'<?php _e('Sure?'); ?>',function(r){
      	if (!r) return;
        $.get('/agility/server/database/sessionFunctions.php',{Operation:'reset',ID:row.ID},function(result){
            if (result.success){
                $(dg).datagrid('reload');    // reload the session data
            } else {
            	// show error message
                $.messager.show({width:300,height:200,title:'<?php _e( 'Error'); ?>',msg: result.errorMsg});
            }
        },'json');
    });
}

function reloadRemoteClientList() {
    $('#remote-videowall-datagrid').datagrid('load');
    $('#remote-livestream-datagrid').datagrid('load');
    $('#remote-chronometer-datagrid').datagrid('load');
}

// retrieve ring name from val:session_id
function formatRingName(val,row,index) {
    var data=$('#sesiones-datagrid').datagrid('getData')['rows'];
    for (var n=0; n<data.length;n++) if (data[n]['ID']==val) return data[n]['Nombre'];
    return "<?php _e('Ring session id');?>: "+val;
}

// retrieve view mode for Videowall val:mode
function formatVideowallView(val,row,index) {
    switch (parseInt(val)) {
        case 0: return '<?php _e("Starting order"); ?>';
        case 1: return '<?php _e("Live Stream"); ?>';
        case 2: return '<?php _e("Partial scores"); ?>';
        case 3: return '<?php _e("Final scores"); ?>';
        case 4: return '<?php _e("Training session"); ?>';
    }
    // default: ( should not occurs ) return session id as string
    return "<?php _e('View mode');?>: "+val;
}

// retrieve View mode from livestream display val:mode
function formatLiveStreamView(val,row,index) {
    switch (parseInt(val)) {
        case 0: return "<?php _e('Starting order'); ?>";
        case 1: return "<?php _e('Training session'); ?>";
        case 2: return "<?php _e('Partial scores'); ?>";
        case 3: return "<?php _e('Partial Scores'); ?> (<?php _e('simplified'); ?>)";
        case 4: return "<?php _e('Final scores'); ?>";
        case 5: return "<?php _e('Advertising videos'); ?>";
        case 6: return "<?php _e('Training session'); ?> (<?php _e('simplified'); ?>)";
        case 7: return "<?php _e('Call to ring '); ?> / <?php _e('Partial scores'); ?>";
        case 8: return "<?php _e('Call to ring '); ?> / <?php _e('Final scores'); ?>";
        case 9: return "<?php _e('Final Scores'); ?> (<?php _e('simplified'); ?>)";
    }
    // default: ( should not occurs ) return session id as string
    return "<?php _e('View mode');?>: "+val;
}