
/**
 * Recalcula el formulario anyadiendo parametros de busqueda
 */
function doSearchPerro() {
	// reload data adding search criteria
    $('#perros-datagrid').datagrid('load',{
        where: $('#perros-search').val()
    });
}

/**
 * Open "New Dog dialog"
 */
function newDog(){
	$('#perros-dialog').dialog('open').dialog('setTitle','Nuevo perro');
	$('#perros-form').form('clear');
	$('#perros-Operation').val('insert');
}

/**
 * Open "Edit Dog" dialog
 */
function editDog(){
    var row = $('#perros-datagrid').datagrid('getSelected');
    if (row){
        $('#perros-dialog').dialog('open').dialog('setTitle','Modificar datos del perro');
        $('#perros-form').form('load',row);
    	$('#perros-Operation').val('update');
    }
}

/**
 * Ask for commit new/edit dog to server
 */
function saveDog(){
    $('#perros-form').form('submit',{
        url: 'database/dogFunctions.php',
        method: 'get',
        onSubmit: function(param){
            return $(this).form('validate');
        },
        success: function(result){
            var result = eval('('+result+')');
            if (result.errorMsg){
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#perros-dialog').dialog('close');        // close the dialog
                $('#perros-datagrid').datagrid('reload');    // reload the dog data
            }
        }
    });
}

/**
 * Delete dog data
 */
function destroyDog(){
    var row = $('#perros-datagrid').datagrid('getSelected');
    if (row){
        $.messager.confirm('Confirm','Borrar datos del perro. ¿Seguro?',function(r){
            if (r){
                $.get('database/dogFunctions.php',{Operation:'delete',Dorsal:row.Dorsal},function(result){
                    if (result.success){
                        $('#perros-datagrid').datagrid('reload');    // reload the dog data
                    } else {
                        $.messager.show({    // show error message
                            title: 'Error',
                            msg: result.errorMsg
                        });
                    }
                },'json');
            }
        });
    }
}