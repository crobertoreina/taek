<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado (por ejemplo, si hay una variable de sesión llamada 'user_id')
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión activa, redirigir al usuario a la página de inicio de sesión
    header("Location: login.html");
    exit();
}
?>

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
    <style>
        /* Estilo adicional (si se desea personalizar más) */
        .ui-collapsible-content {
            padding: 10px;
        }
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
                                                '<a href="#aviso_borrar" data-role="button" data-inline="true" data-position="center" data-theme="a" class="button_del" data-id="' + participante.id + '">Eliminar</a></center>' +
                                            '</form>' +
                                        '</div>')
                                    );
                                });

                                // Refrescar el collapsible para aplicar el estilo correctamente
                                participanteList.collapsibleset('refresh');
                                
                                // Refrescar los botones de Modificar y Eliminar
                                $('#listParticipantes .button_mod').button();
                                $('#listParticipantes .button_del').button();
                                
                                // Refrescar los campos de texto
                                $('input[type="text"]').textinput();
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
			
			});
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

</head>
<body>
    <!-- CRUD Participantes -->
    <section data-role="page" id="participantes">
        <header data-role="header">
			<a href="index.php" data-role="button" data-icon="arrow-l" data-iconpos="left" data-rel="back">Volver</a>
            <h1>Participantes</h1>
        </header>

        <div data-role="content">
            <!-- Botón para agregar un participante -->
            <div align="right" data-type="horizontal" data-role="controlgroup" data-mini='true'>
                <a href="#nuevoParticipante" data-role="button" data-icon="plus" data-iconpos="right" 
                data-rel="dialog" data-transition="pop" id="">Nuevo</a>
            </div>
            
            <!-- Lista de participantes -->
            <div data-role="collapsibleset" id="listParticipantes" data-theme="a" data-content-theme="a">
                <!-- Los participantes se cargarán aquí dinámicamente -->
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
	
	<!-- Dialogo de confirmación para eliminar -->
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

    <!-- Panel para agregar un nuevo participante -->
    <div data-role="page" id="nuevoParticipante" data-position="center" data-display="overlay" data-theme="a">
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

    

</body>
</html>
