<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado (por ejemplo, si hay una variable de sesión llamada 'user_id')
if (!isset($_SESSION['user_id']) ) {
    // Si no hay sesión activa, redirigir al usuario a la página de inicio de sesión
    header("Location: login.php");
    exit();  // Asegura que no se ejecute más código después de redirigir
}
else if( $_SESSION['user_level'] === 1 )
{

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluación</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="themes/takwondoTheme.min.css" />
    <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    <link rel="stylesheet" href="lib/jquery.mobile.structure-1.4.5.min.css" />
    <script src="lib/jquery-1.11.1.min.js"></script>
    <script src="lib/jquery.mobile-1.4.5.min.js"></script>
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js');
    }
    </script>
    <style>
        .ui-collapsible-content { padding: 10px; }
        body { background: #f5f5dc; }
        .ui-page { background: #f5f5dc; }
        .admin-header { background: linear-gradient(135deg, #4caf50, #388e3c); border-bottom: 3px solid #ffeb3b; }
        .admin-header h1 { color: #fff; text-shadow: none; }
        .admin-header .ui-btn { color: #fff !important; background: rgba(255,255,255,0.15) !important; border: none !important; }
        .btn-cat { font-size: 32px !important; font-weight: 700 !important; letter-spacing: 2px; border-radius: 16px !important; margin: 8px 16px !important; height: 100px !important; text-transform: uppercase !important; color: #fff !important; }
        .btn-cat.kyorugi { background: linear-gradient(135deg, #ff5722, #e64a19) !important; }
        .btn-cat.poomsae { background: linear-gradient(135deg, #4caf50, #388e3c) !important; }
        .btn-cat.freestyle { background: linear-gradient(135deg, #2196f3, #1565c0) !important; }
        .admin-footer { background: #fff; border-top: 1px solid #e0e0e0; }
        .admin-footer .ui-btn { background: #4caf50 !important; color: #fff !important; border: none !important; }
        .admin-panel { background: #fff; }
        .admin-panel .ui-header { background: #4caf50; }
        .admin-panel .ui-header h1 { color: #fff; }
    </style>
    <script>
        $(document).ready(function() {
            // Función para cargar los participantes desde el servidor
            function cargarParticipantes() {
                $.ajax({
                    type: 'GET',
                    url: 'getParticipantesTodos.php', // Archivo PHP que devuelve los participantes
                    success: function(data) {
                        try {
                            var participantes = typeof data === 'string' ? JSON.parse(data) : data;
                            var participanteList = $('#listParticipantes');
                            participanteList.empty(); // Limpiar la lista de participantes antes de agregar nuevos

                            if (Array.isArray(participantes)) {
                                participantes.forEach(function(participante) {
                                    // Crear un collapsible para cada participante
                                    participanteList.append(
                                        $('<div data-role="collapsible" data-theme="a" data-content-theme="a" id="participante' + participante.id + '">' +
                                            '<h3>' + participante.nombre + ' ' + participante.apellido + '</h3>' +
                                            '<form>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="id' + participante.id + '">ID:</label>' +
                                                    '<input type="text" id="id' + participante.id + '" value="' + participante.id + '" disabled>' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="nombre' + participante.id + '">Nombre:</label>' +
                                                    '<input type="text" id="nombre' + participante.id + '" value="' + participante.nombre + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="apellido' + participante.id + '">Apellido:</label>' +
                                                    '<input type="text" id="apellido' + participante.id + '" value="' + participante.apellido + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="telefono' + participante.id + '">Telefono:</label>' +
                                                    '<input type="text" id="telefono' + participante.id + '" value="' + participante.telefono + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="ciudad' + participante.id + '">Ciudad:</label>' +
                                                    '<input type="text" id="ciudad' + participante.id + '" value="' + participante.ciudad + '" >' +
                                                '</div>' +
                                                '<center><a href="#" data-role="button" data-inline="true" data-theme="a" class="button_mod" data-id="' + participante.id + '">Modificar</a>' +
                                                '<a href="#aviso_borrar" data-role="button" data-inline="true" data-position="center" data-theme="a" class="button_del" data-rel="dialog" data-transition="flip" data-id="' + participante.id + '">Eliminar</a></center>' +
                                            '</form>' +
                                        '</div>')
                                    );
                                });

                                // Refrescar el collapsible para aplicar el estilo correctamente
                                participanteList.collapsibleset('refresh');
                                
                                // Refrescar solo los botones de Modificar y Eliminar
                                setTimeout(function() {
                                    $('#listParticipantes .button_mod').button();  // Refresca los botones Modificar
                                    $('#listParticipantes .button_del').button();  // Refresca los botones Eliminar
                                }, 100);

                                $('input[type="text"]').textinput();  // Refresca los campos de texto
                            }
                        } catch (e) {
                            console.error("Error al procesar participantes:", e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud de participantes:', error);
                    }
                });
            }

            // Cargar los participantes al cargar la página
            cargarParticipantes();

            // Evento para manejar el clic en el botón Modificar
            $(document).on('click', '.button_mod', function() {
                var idParticipante = $(this).data('id');
                // Capturamos los nuevos valores que el usuario ha editado
                var nombre = $('#nombre' + idParticipante).val();
                var apellido = $('#apellido' + idParticipante).val();
                var telefono = $('#telefono' + idParticipante).val();
                var ciudad = $('#ciudad' + idParticipante).val();

                // Llamar a la función para modificar el participante
                modificarParticipante(idParticipante, nombre, apellido, telefono, ciudad);
            });

            // Función para modificar el participante
            function modificarParticipante(id, nombre, apellido, telefono, ciudad) {
                $.ajax({
                    type: 'POST',
                    url: 'modificarParticipante.php',  // Archivo PHP que procesa la modificación
                    data: {
                        id: id,
                        nombre: nombre,
                        apellido: apellido,
                        telefono: telefono,
                        ciudad: ciudad
                    },
                    success: function(response) {
                        console.log('Participante modificado:', response);
                        // Recargar la lista de participantes
                        cargarParticipantes();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al modificar el participante:', error);
                    }
                });
            }

            // Evento para manejar el clic en el botón Eliminar
            $(document).on('click', '.button_del', function() {
                var idParticipante = $(this).data('id');
                // Mostrar el diálogo de confirmación para eliminar al participante
                console.log('Eliminar participante con ID:', idParticipante);
                $('#btn_confirmar_eliminar').data('id', idParticipante);
            });

            // Confirmar eliminación
			$('#btn_confirmar_eliminar').on('click', function() {
				var idParticipante = $(this).data('id');
				
				// Realizar la solicitud AJAX para eliminar el participante
				$.ajax({
					type: 'POST',
					url: 'eliminarParticipante.php', // Archivo PHP para eliminar al participante
					data: { id: idParticipante },
					success: function(response) {
						console.log('Participante eliminado:', response);
						// Recargar la lista de participantes
						cargarParticipantes();

						// Regresar a la página de la lista de participantes
						$.mobile.changePage('#participantes', { transition: 'pop' });
					},
					error: function(xhr, status, error) {
						console.error('Error al eliminar el participante:', error);
					}
				});
			});


            // Función para agregar un nuevo participante
            $('#form_agregar_participante').on('submit', function(e) {
                e.preventDefault();
                
                var nombre = $('#nombre').val();
                var apellido = $('#apellido').val();
                var telefono = $('#telefono').val();
                var ciudad = $('#ciudad').val();

                // Llamar a la función para agregar el participante
                agregarParticipante(nombre, apellido, telefono, ciudad);
            });

            // Función para agregar el participante
            function agregarParticipante(nombre, apellido, telefono, ciudad) {
                $.ajax({
                    type: 'POST',
                    url: 'agregarParticipante.php',  // Archivo PHP que procesa la adición del participante
                    data: {
                        nombre: nombre,
                        apellido: apellido,
                        telefono: telefono,
                        ciudad: ciudad
                    },
                    success: function(response) {
                        console.log('Participante agregado:', response);
						
						 // Limpiar los campos del formulario
						$('#nombre').val('');
						$('#apellido').val('');
						$('#telefono').val('');
						$('#ciudad').val('');

						 // Recargar la lista de participantes
                        cargarParticipantes();
						
                        // Cerrar el formulario de agregar participante
                        $.mobile.changePage('#participantes', { transition: 'pop' });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al agregar el participante:', error);
                    }
                });
            }
        });
    </script>
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
                                                    '<label for="nombreT' + torneo.idTorneo + '">Nombre:</label>' +
                                                    '<input type="text" id="nombreT' + torneo.idTorneo + '" value="' + torneo.nombre + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="fecha' + torneo.idTorneo + '">Fecha:</label>' +
                                                    '<input type="date" id="fecha' + torneo.idTorneo + '" value="' + fechaFormateada + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="ciudadT' + torneo.idTorneo + '">Ciudad:</label>' +
                                                    '<input type="text" id="ciudadT' + torneo.idTorneo + '" value="' + torneo.ciudad + '" >' +
                                                '</div>' +
                                                '<center><a href="#" data-role="button" data-inline="true" data-theme="a" class="button_modT" data-id="' + torneo.idTorneo + '">Modificar</a>' +
                                                '<a href="#aviso_borrarT" data-role="button" data-inline="true" data-position="center" data-theme="a" data-rel="dialog" data-transition="flip" class="button_delT" data-id="' + torneo.idTorneo + '">Eliminar</a></center>' +
                                            '</form>' +
                                        '</div>')  
                                    );
                                });

                                // Refrescar el collapsible para aplicar el estilo correctamente
                                torneoList.collapsibleset('refresh');
                                

                                // Refrescar solo los botones de Modificar y Eliminar
                                setTimeout(function() {
                                    $('#listTorneos .button_modT').button();  // Refresca los botones Modificar
                                    $('#listTorneos .button_delT').button();  // Refresca los botones Eliminar
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
            $(document).on('click', '.button_modT', function() {
                var idTorneo = $(this).data('id');
                // Capturamos los nuevos valores que el usuario ha editado
                var nombre = $('#nombreT' + idTorneo).val();
                var fecha = $('#fecha' + idTorneo).val();
                var ciudad = $('#ciudadT' + idTorneo).val();

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
						//console.error('Error al modificar el torneo:', error);
					}
				});
			}


            // Evento para manejar el clic en el botón Eliminar
            $(document).on('click', '.button_delT', function() {
                var idTorneo = $(this).data('id');
                // Mostrar el diálogo de confirmación para eliminar el torneo
                console.log('Eliminar torneo con ID:', idTorneo);
                $('#btn_confirmar_eliminarT').data('id', idTorneo);
            });

            // Confirmar eliminación
            $('#btn_confirmar_eliminarT').on('click', function() {
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
                
                var nombre = $('#nombreT').val();
                var fecha = $('#fecha').val();
                var ciudad = $('#ciudadT').val();

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
                        $('#nombreT').val('');
                        $('#fecha').val('');
                        $('#ciudadT').val('');

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
	<script>
        $(document).ready(function() {
            // Función para cargar los Jueces desde el servidor
            function cargarJueces() {
                $.ajax({
                    type: 'GET',
                    url: 'getJueces.php', // Archivo PHP que devuelve los Jueces
                    success: function(data) {
                        try {
                            var Jueces = typeof data === 'string' ? JSON.parse(data) : data;
                            var juezList = $('#listJueces');
                            juezList.empty(); // Limpiar la lista de Jueces antes de agregar nuevos

                            if (Array.isArray(Jueces)) {
                                Jueces.forEach(function(juez) {
                                    // Crear un collapsible para cada juez
                                    juezList.append(
										$('<div data-role="collapsible" data-theme="a" data-content-theme="a" id="juez' + juez.id + '">' +
											'<h3>' + juez.nombre + ' ' + juez.apellido + '</h3>' +
											'<form>' +
												'<div data-role="fieldcontain">' +
													'<label for="idJ' + juez.id + '">ID:</label>' +
													'<input type="text" id="idJ' + juez.id + '" value="' + juez.id + '" disabled>' +
												'</div>' +
												'<div data-role="fieldcontain">' +
													'<label for="nombreJ' + juez.id + '">Nombre:</label>' +
													'<input type="text" id="nombreJ' + juez.id + '" value="' + juez.nombre + '" >' +
												'</div>' +
												'<div data-role="fieldcontain">' +
													'<label for="apellidoJ' + juez.id + '">Apellido:</label>' +
													'<input type="text" id="apellidoJ' + juez.id + '" value="' + juez.apellido + '" >' +
												'</div>' +
												'<div data-role="fieldcontain">' +
													'<label for="telefonoJ' + juez.id + '">Telefono:</label>' +
													'<input type="text" id="telefonoJ' + juez.id + '" value="' + juez.telefono + '" >' +
												'</div>' +
												'<div data-role="fieldcontain">' +
													'<label for="ciudadJ' + juez.id + '">Ciudad:</label>' +
													'<input type="text" id="ciudadJ' + juez.id + '" value="' + juez.ciudad + '" >' +
												'</div>' +
												'<div data-role="fieldcontain">' +
													'<label for="user' + juez.id + '">Usuario:</label>' +
													'<input type="text" id="user' + juez.id + '" value="' + juez.user + '" >' +
												'</div>' +
												'<div data-role="fieldcontain">' +
													'<label for="pass' + juez.id + '">Contraseña:</label>' +
													'<input type="text" id="pass' + juez.id + '" value="' + juez.pass + '" >' +
												'</div>' +
												// Campo Nivel de Acceso
												'<div data-role="fieldcontain">' +
													'<label for="level' + juez.id + '">Nivel de Acceso:</label>' +
													'<select id="level' + juez.id + '">' +
														'<option value="2" ' + (juez.level == 2 ? 'selected' : '') + '>Usuario</option>' +  // Si level es 2, seleccionamos la opción 'Usuario'
														'<option value="1" ' + (juez.level == 1 ? 'selected' : '') + '>Administrador</option>' +  // Si level es 1, seleccionamos la opción 'Administrador'
													'</select>' +
												'</div>' +
												'<center><a href="#" data-role="button" data-inline="true" data-theme="a" class="button_modJ" data-id="' + juez.id + '">Modificar</a>' +
												'<a href="#aviso_borrarJ" data-role="button" data-inline="true" data-position="center" data-theme="a" class="button_delJ" data-rel="dialog" data-transition="flip" data-id="' + juez.id + '">Eliminar</a></center>' +
											'</form>' +
										'</div>')
									);

                                });
								//$('#level' + juez.id).val(nivelDeAcceso).selectmenu('refresh');
                                // Refrescar el collapsible para aplicar el estilo correctamente
                                juezList.collapsibleset('refresh');
                                
                                // Refrescar solo los botones de Modificar y Eliminar
                                setTimeout(function() {
                                    $('#listJueces .button_modJ').button();  // Refresca los botones Modificar
                                    $('#listJueces .button_delJ').button();  // Refresca los botones Eliminar
                                }, 100);

                                $('input[type="text"]').textinput();  // Refresca los campos de texto
								
								// Refrescar campos select
								$('select').selectmenu('refresh', true);
                            }
                        } catch (e) {
                            console.error("Error al procesar Jueces:", e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud de Jueces:', error);
                    }
                });
            }

            // Cargar los Jueces al cargar la página
            cargarJueces();

            // Evento para manejar el clic en el botón Modificar
            $(document).on('click', '.button_modJ', function() {
                var id = $(this).data('id');
                // Capturamos los nuevos valores que el usuario ha editado
                var nombre = $('#nombreJ' + id).val();
                var apellido = $('#apellidoJ' + id).val();
                var telefono = $('#telefonoJ' + id).val();
                var ciudad = $('#ciudadJ' + id).val();
				var user = $('#user' + id).val();
                var pass = $('#pass' + id).val();
				var level = $('#level' + id).val();

                // Llamar a la función para modificar el juez
                modificarjuez(id, nombre, apellido, telefono, ciudad, user, pass, level);
            });

            // Función para modificar el juez
            function modificarjuez(id, nombre, apellido, telefono, ciudad, user, pass, level) {
                $.ajax({
                    type: 'POST',
                    url: 'modificarjuez.php',  // Archivo PHP que procesa la modificación
                    data: {
                        id: id,
                        nombre: nombre,
                        apellido: apellido,
                        telefono: telefono,
                        ciudad: ciudad,
						user: user,
                        pass: pass,
                        level: level
                    },
                    success: function(response) {
                        console.log('juez modificado:', response);
						alert("Dato modificado");
                        // Recargar la lista de Jueces
                        cargarJueces();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al modificar el juez:', error);
                    }
                });
            }

            // Evento para manejar el clic en el botón Eliminar
            $(document).on('click', '.button_delJ', function() {
                var id = $(this).data('id');
                // Mostrar el diálogo de confirmación para eliminar al juez
                console.log('Eliminar juez con ID:', id);
                $('#btn_confirmar_eliminarJ').data('id', id);
            });

            // Confirmar eliminación
			$('#btn_confirmar_eliminarJ').on('click', function() {
				var id = $(this).data('id');
				
				// Realizar la solicitud AJAX para eliminar el juez
				$.ajax({
					type: 'POST',
					url: 'eliminarJuez.php', // Archivo PHP para eliminar al juez
					data: { id: id },
					success: function(response) {
						console.log('juez eliminado:', response);
						// Recargar la lista de Jueces
						cargarJueces();

						// Regresar a la página de la lista de Jueces
						$.mobile.changePage('#Jueces', { transition: 'pop' });
					},
					error: function(xhr, status, error) {
						console.error('Error al eliminar el juez:', error);
					}
				});
			});


            // Función para agregar un nuevo juez
            $('#form_agregar_juez').on('submit', function(e) {
                e.preventDefault();
                
                var nombre = $('#nombreJ').val();
                var apellido = $('#apellidoJ').val();
                var telefono = $('#telefonoJ').val();
                var ciudad = $('#ciudadJ').val();
				var user = $('#user').val();
                var pass = $('#pass').val();
				var level = $('#level').val();
                

                // Llamar a la función para agregar el juez
                agregarjuez(nombre, apellido, telefono, ciudad, user, pass, level);
            });

            // Función para agregar el juez
            function agregarjuez(nombre, apellido, telefono, ciudad, user, pass, level) {
                $.ajax({
                    type: 'POST',
                    url: 'agregarJuez.php',  // Archivo PHP que procesa la adición del juez
                    data: {
                        nombre: nombre,
                        apellido: apellido,
						telefono: telefono,
                        ciudad: ciudad,
						user: user,
						pass: pass,
                        level: level
                    },
                    success: function(response) {
                        console.log('juez agregado:', response);
						
						 // Limpiar los campos del formulario
						$('#nombreJ').val('');
						$('#apellidoJ').val('');
						$('#telefonoJ').val('');
						$('#ciudadJ').val('');
						$('#user').val('');
						$('#pass').val('');
						$('#level').val('');

						 // Recargar la lista de Jueces
                        cargarJueces();
						
                        // Cerrar el formulario de agregar juez
                        $.mobile.changePage('#Jueces', { transition: 'pop' });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al agregar el juez:', error);
                    }
                });
            }
        });
    </script>

</head>
<body data-theme="b">
	<div data-role="page" id="mainPage">
		<div data-role="header" class="admin-header">
			<a href="#menu" class="ui-btn ui-btn-inline ui-icon-bars ui-btn-icon-left" style="color:#fff;">Menú</a>
			<h1>태권도 Admin</h1>
			<a href="#rightPanel" class="ui-btn ui-btn-inline ui-icon-gear ui-btn-icon-right" style="color:#fff;">Opciones</a>
		</div>
		<div data-role="panel" id="menu" data-position="left" data-display="overlay" data-dismissible="true" class="admin-panel">
			<div data-role="header"><h1>Menú</h1></div>
			<div>
				<ul data-role="listview" data-inset="true" data-shadow="false">
					<li data-role="list-divider">Participantes</li>
					<li><a href="#participantes" data-rel="close">Lista Participantes</a></li>
					<li data-role="list-divider">Jueces</li>
					<li><a href="#Jueces" data-rel="close">Lista Jueces</a></li>
					<li data-role="list-divider">Torneos</li>
					<li><a href="#torneos" data-rel="close">Crear Torneo</a></li>
					<li><a href="#asignarParticipantes" data-rel="close">Asignar Participantes</a></li>
					<li><a href="asignarJueces.php" data-rel="close">Asignar Jueces</a></li>
				</ul>
			</div>
		</div>
		<div data-role="panel" id="rightPanel" data-position="right" data-display="overlay" data-dismissible="true" class="admin-panel">
			<div data-role="header"><h1>Opciones</h1></div>
			<div>
				<ul data-role="listview" data-inset="true">
					<li><a href="#contacto" data-rel="close">Contacto</a></li>
					<li><a href="logout.php">Salir</a></li>
				</ul>
			</div>
		</div>
		<div role="main" class="ui-content">
			<div style="padding: 8px 0;">
				<a href="#kyorugiPage" data-role="button" class="btn-cat kyorugi">Kyorugi</a>
				<a href="#poonsaePage" data-role="button" class="btn-cat poomsae">Poomsae</a>
				<a href="#freestylePage" data-role="button" class="btn-cat freestyle">Freestyle Poomsae</a>
			</div>
		</div>
		<div data-role="footer" data-position="fixed" class="admin-footer">
			<div data-role="controlgroup" data-type="horizontal" style="text-align:center;">
				<a href="logout.php" data-role="button" data-icon="power">Salir</a>
				<a href="#contacto" data-role="button" data-icon="mail" data-rel="dialog" data-transition="pop">Contacto</a>
			</div>
		</div>
	</div>
		
    <!-- CRUD Participantes -->
    <section data-role="page" id="participantes">
		<header data-role="header" class="admin-header">
			<a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left" style="color:#fff;">Volver</a>
            <h1>Participantes</h1>
        </header>

        <div data-role="content">
            <!-- Botón para agregar un participante -->
            <div align="right" data-type="horizontal" data-role="controlgroup" data-mini='true'>
                <a href="#nuevoParticipante" data-role="button" data-icon="plus" data-iconpos="right" 
                data-rel="dialog" data-transition="pop" id="">Nuevo</a>
            </div>
            
            <!-- Lista de participantes -->
            <div data-role="collapsibleset" id="listParticipantes" >
                <!-- Los participantes se cargarán aquí dinámicamente -->
            </div>
        </div>
    </section>

    <!-- Panel para agregar un nuevo participante -->
    <div data-role="page" id="nuevoParticipante" data-position="center" data-display="overlay" >
        <div data-role="header">
            <h1>Agregar Participante</h1>
        </div>
        <div data-role="content">
            <form id="form_agregar_participante">
                <div data-role="fieldcontain">
                    <label for="nombre">Nombre del Participante:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre del participante" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" placeholder="Apellido" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" placeholder="Teléfono" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="ciudad">Ciudad:</label>
                    <input type="text" id="ciudad" name="ciudad" placeholder="Ciudad" required>
                </div>

                <button type="submit" data-role="button">Agregar</button>
                <a href="#participantes" data-role="button" data-rel="back">Cancelar</a>
            </form>
        </div>
    </div>
	
	<!-- CRUD Torneos -->
    <section data-role="page" id="torneos">
        <header data-role="header" class="admin-header">
		<a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left" style="color:#fff;">Volver</a>
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
            <div data-role="footer" data-position="fixed" class="admin-footer">
                <div data-role="controlgroup" data-type="horizontal" data-position="center">
                    <a href="logout.php" data-role="button" data-icon="power">Salir</a>
					<a href="#contacto" data-role="button" data-icon="mail" data-rel="dialog" data-transition="pop">Contacto</a>
                </div>
            </div>
        </center>
    </section>
	
	<style>
	#listParticipantes .ui-collapsible-heading-toggle { background: #f5f5dc !important; color: #333 !important; border: 1px solid #e0e0e0 !important; }
    #listParticipantes .ui-collapsible-content { background: #fff !important; color: #333 !important; border: 1px solid #e0e0e0 !important; }
    #listTorneos .ui-collapsible-heading-toggle { background: #f5f5dc !important; color: #333 !important; border: 1px solid #e0e0e0 !important; }
    #listTorneos .ui-collapsible-content { background: #fff !important; color: #333 !important; border: 1px solid #e0e0e0 !important; }
    #listJueces .ui-collapsible-heading-toggle { background: #f5f5dc !important; color: #333 !important; border: 1px solid #e0e0e0 !important; }
    #listJueces .ui-collapsible-content { background: #fff !important; color: #333 !important; border: 1px solid #e0e0e0 !important; }
    input[type="text"], input[type="date"], select { background: #fff !important; color: #333 !important; border: 1px solid #ddd !important; }
    label { color: #666 !important; font-size: 12px !important; text-transform: uppercase !important; letter-spacing: 1px !important; }
	</style>

    <!-- Panel para agregar un nuevo torneo -->
    <div data-role="page" id="nuevoTorneo" data-position="center" data-display="overlay" data-theme="a">
        <div data-role="header">
            <h1>Agregar Torneo</h1>
        </div>
        <div data-role="content">
            <form id="form_agregar_torneo">
                <div data-role="fieldcontain">
                    <label for="nombreT">Nombre:</label>
                    <input type="text" name="nombreT" id="nombreT" value="" placeholder="Nombre del torneo">
                </div>
                <div data-role="fieldcontain">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="">
                </div>
                <div data-role="fieldcontain">
                    <label for="ciudadT">Ciudad:</label>
                    <input type="text" name="ciudadT" id="ciudadT" value="" placeholder="Ciudad del torneo">
                </div>
                <center><button type="submit" data-role="button" data-inline="true">Agregar Torneo</button></center>
            </form>
        </div>
    </div>
	
	<!-- CRUD Jueces -->
    <section data-role="page" id="Jueces">
        <header data-role="header" class="admin-header">
		<a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left" style="color:#fff;">Volver</a>
            <h1>Jueces</h1>
        </header>

        <div data-role="content">
            <!-- Botón para agregar un juez -->
            <div align="right" data-type="horizontal" data-role="controlgroup" data-mini='true'>
                <a href="#nuevojuez" data-role="button" data-icon="plus" data-iconpos="right" 
                data-rel="dialog" data-transition="pop" id="">Nuevo</a>
            </div>
            
            <!-- Lista de Jueces -->
            <div data-role="collapsibleset" id="listJueces" data-theme="a" data-content-theme="a">
                <!-- Los Jueces se cargarán aquí dinámicamente -->
            </div>
        </div>
    </section>

    <!-- Panel para agregar un nuevo juez -->
    <div data-role="page" id="nuevojuez" data-position="center" data-display="overlay" data-theme="a">
        <div data-role="header">
            <h1>Agregar juez</h1>
        </div>
        <div data-role="content">
            <form id="form_agregar_juez">
                <div data-role="fieldcontain">
                    <label for="nombreJ">Nombre del juez:</label>
                    <input type="text" id="nombreJ" name="nombreJ" placeholder="Nombre del juez" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="apellidoJ">Apellido:</label>
                    <input type="text" id="apellidoJ" name="apellidoJ" placeholder="Apellido" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefonoJ" name="telefonoJ" placeholder="Teléfono" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="ciudadJ">Ciudad:</label>
                    <input type="text" id="ciudadJ" name="ciudadJ" placeholder="Ciudad" required>
                </div>
				
				<div data-role="fieldcontain">
                    <label for="user">User:</label>
                    <input type="text" id="user" name="user" placeholder="Email" required>
                </div>

                <div data-role="fieldcontain">
                    <label for="pass">Password:</label>
                    <input type="text" id="pass" name="pass" placeholder="Contraseña" required>
                </div>
				
				<div data-role="fieldcontain">
					<label for="level">Nivel de Acceso:</label>
					<select id="level" name="level">
						<option value="1" >Administrador</option>
						<option value="2" >Usuario</option>
					</select>
				</div>

                <button type="submit" data-role="button">Agregar</button>
                <a href="#Jueces" data-role="button" data-rel="back">Cancelar</a>
            </form>
        </div>
    </div>

    <!-- Dialogo de confirmación para eliminar Participante-->
    <div data-role="page" id="aviso_borrar">
        <div data-role="header">
            <h1>Confirmar Eliminación</h1>
        </div>
        <div data-role="content">
            <p>¿Estás seguro de que deseas eliminar este participante?</p>
            <a href="#" data-role="button" id="btn_confirmar_eliminar">Eliminar</a>
            <a href="#participantes" data-role="button" data-rel="back">Cancelar</a>
        </div>
    </div>
	
	<!-- Dialogo de confirmación para eliminar Torneo-->
    <div data-role="page" id="aviso_borrarT">
        <div data-role="header">
            <h1>Confirmar Eliminación</h1>
        </div>
        <div data-role="content">
            <p>¿Estás seguro de que deseas eliminar este Torneo?</p>
            <a href="#" data-role="button" id="btn_confirmar_eliminarT">Eliminar</a>
            <a href="#torneos" data-role="button" data-rel="back">Cancelar</a>
        </div>
    </div>
	
	<!-- Dialogo de confirmación para eliminar Juez-->
    <div data-role="page" id="aviso_borrarJ">
        <div data-role="header">
            <h1>Confirmar Eliminación</h1>
        </div>
        <div data-role="content">
            <p>¿Estás seguro de que deseas eliminar este Juez?</p>
            <a href="#" data-role="button" id="btn_confirmar_eliminarJ">Eliminar</a>
            <a href="#Jueces" data-role="button" data-rel="back">Cancelar</a>
        </div>
    </div>
	
	<!-- Panel para Contacto -->
    <div data-role="page" id="contacto" data-position="center" data-display="overlay">
        <div data-role="header" class="admin-header">
            <h1>Contacto</h1>
        </div>
        <div data-role="content" style="text-align:center; padding:30px; background:#fff;">
            <img src="images/sonbae.jpg" width="150" height="150" style="border-radius:50%; margin-bottom:16px; border:3px solid #ffeb3b;" /><br>
            <p style="color:#666; margin-bottom:16px;">Contáctanos por WhatsApp</p>
            <a href="https://wa.me/50432114668/" data-role="button" data-icon="phone" style="background:#25D366; color:#fff;">WhatsApp</a>
            <a href="#" data-role="button" data-rel="back">Cerrar</a>
        </div>
    </div>

</body>
</html>
<?php

}
else
{
	header("Location: indexJuez.php");
    exit();  // Asegura que no se ejecute más código después de redirigir
}

?>