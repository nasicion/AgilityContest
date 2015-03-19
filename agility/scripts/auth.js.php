/*
auth.js

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
<?php
require_once(__DIR__."/../server/auth/Config.php");
require_once(__DIR__."/../server/tools.php");
$config =Config::getInstance()
?>
/*
* Client-side uthentication related functions
*/

/**
 * Abre el frame de login o logout dependiendo de si se ha iniciado o no la sesion
 */
function showLoginWindow() {
	if (typeof(authInfo.SessionKey)==undefined || (authInfo.SessionKey==null) ) {
		$('#login-window').remove();
		loadContents('/agility/client/frm_login.php','<?php _e('Iniciar sesi&oacute;n');?>');
	} else {
		$('#logout-window').remove();
		loadContents('/agility/client/frm_logout.php','<?php _e('Finalizar sesi&oacute;n');?>');
	}
}

function showMyAdminWindow() {
	$('#myAdmin-window').remove();
	loadContents('/agility/client/frm_myAdmin.php','<?php _e('Acceso directo a la Base de Datos');?>');
}

function acceptLogin() {
	var user= $('#login-Username').val();
	var pass=$('#login-Password').val();
	if (!user || !user.length) {
		$.messager.alert('<?php _e('Datos Inv&aacute;lidos');?>','<?php _e('No ha indicado ning&uacute;n usuario');?>',"error");
		return;
	};
	$.ajax({
		type: 'POST',
  		url: 'https://'+window.location.hostname+'/agility/server/database/userFunctions.php',
   		dataType: 'jsonp',
   		data: {
   			Operation: 'login',
   			Username: user,
   			Password: pass,
   		},
   		contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
   		success: function(data) {
       		if (data.errorMsg) { // error
       			$.messager.alert("Error",data.errorMsg,"error");
       			initAuthInfo();
       		} else {// success:
       			var str="AgilityContest version: "+ac_config.version_name+"-"+ac_config.version_date+"<br />";
				str =str+"<?php _e('Copia registrada por');?>: "+data.User+"<br />";
				str =str+"<?php _e('Para el club');?>: "+data.Club+"<br /><br />";
				str =str+"<?php _e('Usuario');?> "+data.Login+": <?php _e('Sesi&oacute;n iniciada correctamente')?>";
				$.messager.alert("Login",str,"info").window({width:400,height:175});
           		$('#login_menu-text').html("<?php _e('Finalizar sesi&oacute;n');?>"+": <br />"+data.Login);
           		initAuthInfo(data);
       		} 
       	},
   		error: function() { alert("error");	},
	});
	$('#login-window').window('close');
}

function acceptLogout() {
	var user=authInfo.Login;
	$.ajax({
		type: 'POST',
   		url: '/agility/server/database/userFunctions.php',
   		dataType: 'json',
   		data: {
   			Operation: 'logout',
   			Username: user,
   			Password: "",
   		},
   		contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
   		success: function(data) {
       		if (data.errorMsg) { // error
       			$.messager.alert("Error",data.errorMsg,"error");
       		} else {// success: 
       			$.messager.alert("<?php _e('Usuario');?>"+" "+user,'<?php _e('Sesi&oacute;n finalizada correctamente')?>',"info");
           		$('#login_menu-text').html("<?php _e('Iniciar sesi&oacute;n');?>");
           		initAuthInfo();
       		} 
       	},
   		error: function() { alert("error");	},
	});
	$('#logout-window').window('close');	
}

function acceptMyAdmin() {
	var user= $('#myAdmin-Username').val();
	var pass=$('#myAdmin-Password').val();
	if (!user || !user.length) {
		$.messager.alert('<?php _e('Datos Inv&aacute;lidos');?>','<?php _e('No ha indicado ning&uacute;n usuario');?>',"error");
		return;
	};
	$.ajax({
		type: 'POST',
  		url: 'https://'+window.location.hostname+'/agility/server/database/userFunctions.php',
   		dataType: 'jsonp',
   		data: {
   			Operation: 'pwcheck',
   			Username: user,
   			Password: pass,
   		},
   		contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
   		success: function(data) {
       		if (data.errorMsg) { // error
       			$.messager.alert("Error",data.errorMsg,"error");
       		} else {// success: 
       			if (parseInt(data.Perms)<=1) window.open("/phpmyadmin","phpMyAdmin");
       			else $.messager.alert("Error",'<?php _e('El usuario no tiene permisos de administrador');?>',"error");
       		} 
       	},
   		error: function() { alert("error");	},
	});
	$('#myAdmin-window').window('close');
}

function cancelLogin() {
	$('#login-Usuario').val('');
	$('#login-Password').val('');
	// close window
	$('#login-window').window('close');
}

function cancelLogout() {
	// close window
	$('#logout-window').window('close');
}

function cancelMyAdmin() {
	// close window
	$('#myAdmin-window').window('close');
}