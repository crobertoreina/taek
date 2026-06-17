<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado (por ejemplo, si hay una variable de sesión llamada 'user_id')
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión activa, redirigir al usuario a la página de inicio de sesión
    header("Location: login.php");
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
                                                    '<label for="id' + juez.id + '">ID:</label>' +
                                                    '<input type="text" id="id' + juez.id + '" value="' + juez.id + '" disabled>' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="nombre' + juez.id + '">Nombre:</label>' +
                                                    '<input type="text" id="nombre' + juez.id + '" value="' + juez.nombre + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="apellido' + juez.id + '">Apellido:</label>' +
                                                    '<input type="text" id="apellido' + juez.id + '" value="' + juez.apellido + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="telefono' + juez.id + '">Telefono:</label>' +
                                                    '<input type="text" id="telefono' + juez.id + '" value="' + juez.telefono + '" >' +
                                                '</div>' +
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="ciudad' + juez.id + '">Ciudad:</label>' +
                                                    '<input type="text" id="ciudad' + juez.id + '" value="' + juez.ciudad + '" >' +
                                                '</div>' +
                                                '<center><a href="#" data-role="button" data-inline="true" data-theme="a" class="button_mod" data-id="' + juez.id + '">Modificar</a>' +
                                                '<a href="#aviso_borrar" data-role="button" data-inline="true" data-position="center" data-theme="a" class="button_del" data-id="' + juez.id + '">Eliminar</a></center>' +
                                            '</form>' +
                                        '</div>')
                                    );
                                });

                                // Refrescar el collapsible para aplicar el estilo correctamente
                                juezList.collapsibleset('refresh');
                                
                                // Refrescar solo los botones de Modificar y Eliminar
                                setTimeout(function() {
                                    $('#listJueces .button_mod').button();  // Refresca los botones Modificar
                                    $('#listJueces .button_del').button();  // Refresca los botones Eliminar
                                }, 100);

                                $('input[type="text"]').textinput();  // Refresca los campos de texto
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
            $(document).on('click', '.button_mod', function() {
                var id = $(this).data('id');
                // Capturamos los nuevos valores que el usuario ha editado
                var nombre = $('#nombre' + id).val();
                var apellido = $('#apellido' + id).val();
                var telefono = $('#telefono' + id).val();
                var ciudad = $('#ciudad' + id).val();

                // Llamar a la función para modificar el juez
                modificarjuez(id, nombre, apellido, telefono, ciudad);
            });

            // Función para modificar el juez
            function modificarjuez(id, nombre, apellido, telefono, ciudad) {
                $.ajax({
                    type: 'POST',
                    url: 'modificarjuez.php',  // Archivo PHP que procesa la modificación
                    data: {
                        id: id,
                        nombre: nombre,
                        apellido: apellido,
                        telefono: telefono,
                        ciudad: ciudad
                    },
                    success: function(response) {
                        console.log('juez modificado:', response);
                        // Recargar la lista de Jueces
                        cargarJueces();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al modificar el juez:', error);
                    }
                });
            }

            // Evento para manejar el clic en el botón Eliminar
            $(document).on('click', '.button_del', function() {
                var id = $(this).data('id');
                // Mostrar el diálogo de confirmación para eliminar al juez
                console.log('Eliminar juez con ID:', id);
                $('#btn_confirmar_eliminar').data('id', id);
            });

            // Confirmar eliminación
			$('#btn_confirmar_eliminar').on('click', function() {
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
                
                var nombre = $('#nombre').val();
                var apellido = $('#apellido').val();
                var telefono = $('#telefono').val();
                var ciudad = $('#ciudad').val();

                // Llamar a la función para agregar el juez
                agregarjuez(nombre, apellido, telefono, ciudad);
            });

            // Función para agregar el juez
            function agregarjuez(nombre, apellido, telefono, ciudad) {
                $.ajax({
                    type: 'POST',
                    url: 'agregarJuez.php',  // Archivo PHP que procesa la adición del juez
                    data: {
                        nombre: nombre,
                        apellido: apellido,
                        telefono: telefono,
                        ciudad: ciudad
                    },
                    success: function(response) {
                        console.log('juez agregado:', response);
						
						 // Limpiar los campos del formulario
						$('#nombre').val('');
						$('#apellido').val('');
						$('#telefono').val('');
						$('#ciudad').val('');

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
<body>
    <!-- CRUD Jueces -->
    <section data-role="page" id="Jueces">
        <header data-role="header">
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
                    <label for="nombre">Nombre del juez:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre del juez" required>
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
                <a href="#Jueces" data-role="button" data-rel="back">Cancelar</a>
            </form>
        </div>
    </div>

    <!-- Dialogo de confirmación para eliminar -->
    <div data-role="page" id="aviso_borrar">
        <div data-role="header">
            <h1>Confirmar Eliminación</h1>
        </div>
        <div data-role="content">
            <p>¿Estás seguro de que deseas eliminar este juez?</p>
            <a href="#" data-role="button" id="btn_confirmar_eliminar">Eliminar</a>
            <a href="#Jueces" data-role="button" data-rel="back">Cancelar</a>
        </div>
    </div>

</body>
</html>
