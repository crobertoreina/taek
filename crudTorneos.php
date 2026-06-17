<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluación</title>
    <link rel="stylesheet" href="themes/takwondoTheme.min.css" />
    <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile.structure-1.4.5.min.css" />
    <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Función para formatear la fecha (en formato YYYY-MM-DD)
            function formatDate(date) {
                var d = new Date(date);
                if (isNaN(d)) return '';  // Si la fecha es inválida, devuelve una cadena vacía
                var year = d.getFullYear();
                var month = ("0" + (d.getMonth() + 1)).slice(-2);  // Mes en formato de 2 dígitos
                var day = ("0" + d.getDate()).slice(-2);  // Día en formato de 2 dígitos
                return year + "-" + month + "-" + day;  // Devuelve la fecha en formato YYYY-MM-DD
            }

            // Función para cargar los torneos desde el servidor
            function cargarTorneos() {
                $.ajax({
                    type: 'GET',
                    url: 'getTorneos.php', // Archivo PHP que devuelve los torneos
                    success: function(data) {
                        try {
                            var torneos = typeof data === 'string' ? JSON.parse(data) : data;
                            var torneoList = $('#listTorneos');
                            var mensajeNoTorneos = $('#mensajeNoTorneos');
                            torneoList.empty(); // Limpiar la lista de torneos antes de agregar nuevos

                            if (Array.isArray(torneos) && torneos.length > 0) {
                                mensajeNoTorneos.hide(); // Ocultar el mensaje cuando hay torneos

                                torneos.forEach(function(torneo) {
                                    var fechaFormateada = formatDate(torneo.fecha); // Aseguramos que la fecha esté en formato correcto
                                    
                                    // Crear un collapsible para cada torneo
                                    torneoList.append(
                                        $('<div data-role="collapsible" data-theme="a" data-content-theme="a" data-role="content" id="torneo' + torneo.idTorneo + '">' +
                                            '<h3>' + torneo.nombre + ' - ' + fechaFormateada + '</h3>' +
                                            '<form>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="idTorneo' + torneo.idTorneo + '">ID:</label>' +
                                                    '<input type="text" id="idTorneo' + torneo.idTorneo + '" value="' + torneo.idTorneo + '" disabled>' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="nombre' + torneo.idTorneo + '">Nombre:</label>' +
                                                    '<input type="text" id="nombre' + torneo.idTorneo + '" value="' + torneo.nombre + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="fecha' + torneo.idTorneo + '">Fecha:</label>' +
                                                    '<input type="date" id="fecha' + torneo.idTorneo + '" value="' + fechaFormateada + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="ciudad' + torneo.idTorneo + '">Ciudad:</label>' +
                                                    '<input type="text" id="ciudad' + torneo.idTorneo + '" value="' + torneo.ciudad + '" >' +
                                                '</div>' +
                                                '<center><a href="#" data-role="button" data-inline="true" data-theme="a" class="button_mod" data-id="' + torneo.idTorneo + '">Modificar</a>' +
                                                '<a href="#aviso_borrar" data-role="button" data-inline="true" data-position="center" data-theme="a" class="button_del" data-id="' + torneo.idTorneo + '">Eliminar</a></center>' +
                                            '</form>' +
                                        '</div>')  
                                    );
                                });

                                // Refrescar el collapsible para aplicar el estilo correctamente
                                torneoList.collapsibleset('refresh');
                                

                                // Refrescar solo los botones de Modificar y Eliminar
                                setTimeout(function() {
                                    $('#listTorneos .button_mod').button();  // Refresca los botones Modificar
                                    $('#listTorneos .button_del').button();  // Refresca los botones Eliminar
                                }, 100);

                                $('input[type="text"]').textinput();  // Refresca los campos de texto
                            } else {
                                // Si no hay torneos, mostrar el mensaje
                                mensajeNoTorneos.show();
                            }
                        } catch (e) {
                            console.error("Error al procesar torneos:", e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud de torneos:', error);
                    }
                });
            }

            // Cargar los torneos al cargar la página
            cargarTorneos();

            // Evento para manejar el clic en el botón Modificar
            $(document).on('click', '.button_mod', function() {
                var idTorneo = $(this).data('id');
                // Capturamos los nuevos valores que el usuario ha editado
                var nombre = $('#nombre' + idTorneo).val();
                var fecha = $('#fecha' + idTorneo).val();
                var ciudad = $('#ciudad' + idTorneo).val();

                // Llamar a la función para modificar el torneo
                modificarTorneo(idTorneo, nombre, fecha, ciudad);
            });

            // Función para modificar el torneo
            function modificarTorneo(id, nombre, fecha, ciudad) {
				$.ajax({
					type: 'POST',
					url: 'modificarTorneo.php',  // Archivo PHP que procesa la modificación
					data: {
						id: id,
						nombre: nombre,
						fecha: fecha,
						ciudad: ciudad
					},
					success: function(response) {
						console.log('Torneo modificado:', response); // Esto ya es un objeto, no es necesario JSON.parse
						if (response.success) {
							alert(response.message); // Si la modificación fue exitosa
							cargarTorneos(); // Recargar la lista de torneos
						} else {
							alert(response.message); // Si hubo un error
						}
					},
					error: function(xhr, status, error) {
						console.error('Error al modificar el torneo:', error);
					}
				});
			}


            // Evento para manejar el clic en el botón Eliminar
            $(document).on('click', '.button_del', function() {
                var idTorneo = $(this).data('id');
                // Mostrar el diálogo de confirmación para eliminar el torneo
                console.log('Eliminar torneo con ID:', idTorneo);
                $('#btn_confirmar_eliminar').data('id', idTorneo);
            });

            // Confirmar eliminación
            $('#btn_confirmar_eliminar').on('click', function() {
                var idTorneo = $(this).data('id');
                
                // Realizar la solicitud AJAX para eliminar el torneo
                $.ajax({
                    type: 'POST',
                    url: 'eliminarTorneo.php', // Archivo PHP para eliminar el torneo
                    data: { idTorneo: idTorneo },
                    success: function(response) {
                        console.log('Torneo eliminado:', response);
                        // Recargar la lista de torneos
                        cargarTorneos();

                        // Regresar a la página de la lista de torneos
                        $.mobile.changePage('#torneos', { transition: 'pop' });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al eliminar el torneo:', error);
                    }
                });
            });

            // Función para agregar un nuevo torneo
            $('#form_agregar_torneo').on('submit', function(e) {
                e.preventDefault();
                
                var nombre = $('#nombre').val();
                var fecha = $('#fecha').val();
                var ciudad = $('#ciudad').val();

                // Llamar a la función para agregar el torneo
                agregarTorneo(nombre, fecha, ciudad);
            });

            // Función para agregar el torneo
            function agregarTorneo(nombre, fecha, ciudad) {
                $.ajax({
                    type: 'POST',
                    url: 'agregarTorneo.php',  // Archivo PHP que procesa la adición del torneo
                    data: {
                        nombre: nombre,
                        fecha: fecha,
                        ciudad: ciudad
                    },
                    success: function(response) {
                        console.log('Torneo agregado:', response);
                        
                        // Limpiar los campos del formulario
                        $('#nombre').val('');
                        $('#fecha').val('');
                        $('#ciudad').val('');

                         // Recargar la lista de torneos
                        cargarTorneos();
                        
                        // Cerrar el formulario de agregar torneo
                        $.mobile.changePage('#torneos', { transition: 'pop' });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al agregar el torneo:', error);
                    }
                });
            }
        });
    </script>
</head>
<body>
    <!-- CRUD Torneos -->
    <section data-role="page" id="torneos">
        <header data-role="header">
		<a href="index.php" data-role="button" data-icon="arrow-l" data-iconpos="left" data-rel="back">Volver</a>
            <h1>Torneos</h1>
        </header>

        <div data-role="content">
            <!-- Botón para agregar un torneo -->
            <div align="right" data-type="horizontal" data-role="controlgroup" data-mini='true'>
                <a href="#nuevoTorneo" data-role="button" data-icon="plus" data-iconpos="right" 
                data-rel="dialog" data-transition="pop" id="">Nuevo</a>
            </div>
            
            <!-- Lista de torneos con marco -->
            <div id="listTorneosContainer">
                <div id="listTorneos" data-role="collapsibleset" data-theme="a" data-content-theme="a">
                    <!-- Los torneos se cargarán aquí dinámicamente -->
                </div>
                <div id="mensajeNoTorneos">
                    <p><center>No hay torneos disponibles.</center></p>
                </div>
            </div>
        </div>
        <center>
            <div data-role="footer" data-position="fixed">
                <div data-role="controlgroup" data-type="horizontal" data-position="center">
                    <a href="logout.php" data-role="button" data-icon="home">Salir</a>
                    <a href="#contacto" data-rel="popup" data-position-to="window" data-transition="pop" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-icon-info ui-btn-icon-left ui-btn-a">Contacto</a>
                </div>
            </div>
        </center>
    </section>

    <!-- Panel para agregar un nuevo torneo -->
    <div data-role="page" id="nuevoTorneo" data-position="center" data-display="overlay" data-theme="a">
        <div data-role="header">
            <h1>Agregar Torneo</h1>
        </div>
        <div data-role="content">
            <form id="form_agregar_torneo">
                <div data-role="fieldcontain">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="" placeholder="Nombre del torneo">
                </div>
                <div data-role="fieldcontain">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="">
                </div>
                <div data-role="fieldcontain">
                    <label for="ciudad">Ciudad:</label>
                    <input type="text" name="ciudad" id="ciudad" value="" placeholder="Ciudad del torneo">
                </div>
                <center><button type="submit" data-role="button" data-inline="true">Agregar Torneo</button></center>
            </form>
        </div>
    </div>

    <!-- Popup de confirmación de eliminación -->
    <div data-role="popup" id="aviso_borrar" data-overlay-theme="b" data-theme="a" data-dismissible="false">
        <div data-role="header" data-theme="a">
            <h1>Confirmación</h1>
        </div>
        <div role="main" class="ui-content">
            <h3>¿Seguro que deseas eliminar este torneo?</h3>
            <center>
                <a href="#" data-role="button" data-theme="b" data-rel="back">Cancelar</a>
                <a href="#" data-role="button" data-theme="b" id="btn_confirmar_eliminar">Eliminar</a>
            </center>
        </div>
    </div>

</body>
</html>
