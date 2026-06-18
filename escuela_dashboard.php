<?php
session_start();
if (!isset($_SESSION['escuela_id'])) {
    header("Location: login.php");
    exit();
}
$escuela_id = $_SESSION['escuela_id'];
$escuela_nombre = $_SESSION['escuela_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($escuela_nombre) ?> - Panel</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="themes/takwondoTheme.min.css" />
    <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    <link rel="stylesheet" href="lib/jquery.mobile.structure-1.4.5.min.css" />
    <script src="lib/jquery-1.11.1.min.js"></script>
    <script src="lib/jquery.mobile-1.4.5.min.js"></script>
    <style>
        body { background:#f5f5dc; }
        .ui-page { background:#f5f5dc; }
        .esc-header { background:linear-gradient(135deg,#2e7d32,#4caf50); border-bottom:none; }
        .esc-header h1 { color:#fff; text-shadow:none; font-size:17px; }
        .esc-header .ui-btn { color:#fff !important; background:rgba(255,255,255,0.12) !important; border-radius:20px !important; border:1px solid rgba(255,255,255,0.2) !important; }
        .esc-content { padding:12px; }
        .esc-card { background:#fff; border-radius:14px; padding:18px; margin-bottom:14px; box-shadow:0 2px 10px rgba(0,0,0,0.04); border:1px solid #f0f0f0; }
        .esc-card h3 { margin:0 0 6px; font-size:15px; color:#333; }
        .esc-card .meta { font-size:12px; color:#999; }
        .esc-card .meta span { margin-right:12px; }
        .esc-btn { display:inline-block; padding:8px 20px; border-radius:10px; font-size:13px; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all 0.15s; }
        .esc-btn-primary { background:linear-gradient(135deg,#2e7d32,#4caf50); color:#fff; box-shadow:0 2px 8px rgba(76,175,80,0.2); }
        .esc-btn-primary:hover { transform:translateY(-1px); }
        .esc-btn-danger { background:#ffebee; color:#c62828; border:1px solid #ffcdd2; }
        .esc-btn-danger:hover { background:#ffcdd2; }
        .esc-btn-sm { padding:5px 14px; font-size:12px; }
        .empty-state { text-align:center; padding:40px 20px; color:#999; }
        .empty-state .icon { font-size:48px; opacity:0.3; margin-bottom:10px; }
        .part-item { display:flex; align-items:center; padding:12px 0; border-bottom:1px solid #f5f5f5; }
        .part-item:last-child { border-bottom:none; }
        .part-info { flex:1; }
        .part-info strong { display:block; font-size:14px; color:#333; }
        .part-info .detalles { font-size:12px; color:#999; margin-top:2px; }
        .part-accion { margin-left:10px; }
        .elegant-form { max-width:420px; margin:0 auto; }
        .elegant-form .ui-fieldcontain { margin:6px 0 14px !important; }
        .elegant-form .ui-fieldcontain input,.elegant-form .ui-fieldcontain select { border-radius:10px !important; border:1.5px solid #e0e0e0 !important; padding:11px 13px !important; font-size:14px !important; background:#fafafa !important; }
        .elegant-form .ui-fieldcontain label { font-size:12px !important; font-weight:600 !important; color:#555 !important; text-transform:uppercase !important; letter-spacing:0.8px !important; display:block !important; margin-bottom:4px !important; }
    </style>
    <script>
        var ESCUELA_ID = <?= json_encode($escuela_id) ?>;
        $(document).ready(function() {
            cargarParticipantes();

            function cargarParticipantes() {
                $.ajax({
                    type:'GET', url:'getParticipantesEscuela.php?id='+ESCUELA_ID, dataType:'json',
                    success: function(data) {
                        var list = $('#lista-participantes');
                        list.empty();
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(p) {
                                p.categoria = p.categoria || '';
                                p.cinturon = p.cinturon || '';
                                var beltEmoji = '';
                                var b = p.cinturon;
                                if (b=='Blanco') beltEmoji='\u26AA';
                                else if (b=='Blanco/Amarillo') beltEmoji='\u26AA\uD83D\uDFE1';
                                else if (b=='Amarillo') beltEmoji='\uD83D\uDFE1';
                                else if (b=='Amarillo/Verde') beltEmoji='\uD83D\uDFE1\uD83D\uDFE2';
                                else if (b=='Verde') beltEmoji='\uD83D\uDFE2';
                                else if (b=='Verde/Azul') beltEmoji='\uD83D\uDFE2\uD83D\uDD35';
                                else if (b=='Azul') beltEmoji='\uD83D\uDD35';
                                else if (b=='Azul/Rojo') beltEmoji='\uD83D\uDD35\uD83D\uDD34';
                                else if (b=='Rojo') beltEmoji='\uD83D\uDD34';
                                else if (b=='Rojo/Negro') beltEmoji='\uD83D\uDD34\u26AB';
                                else if (b.indexOf('Negro')==0) beltEmoji='\u26AB';
                                var detalles = '';
                                if (p.edad) { detalles += '\uD83C\uDF82 ' + p.edad + ' años'; }
                                if (p.categoria) { if (detalles) detalles += ' \u00B7 '; detalles += '\uD83C\uDFC6 ' + p.categoria; }
                                if (p.cinturon) { if (detalles) detalles += ' \u00B7 '; detalles += beltEmoji + ' ' + p.cinturon; }
                                if (p.telefono) { if (detalles) detalles += ' \u00B7 '; detalles += '\uD83D\uDCF1 ' + p.telefono; }
                                list.append(
                                    '<div class="part-item">' +
                                    '<div class="part-info"><strong>' + p.nombre + ' ' + p.apellido + '</strong>' +
                                    '<div class="detalles">' + detalles + '</div></div>' +
                                    '<div class="part-accion"><a href="#" class="esc-btn esc-btn-danger esc-btn-sm btn-del" data-id="' + p.id + '">Eliminar</a></div>' +
                                    '</div>'
                                );
                            });
                        } else {
                            list.html('<div class="empty-state"><div class="icon">\uD83C\uDFC6</div><div>No hay participantes registrados. Agrega el primero.</div></div>');
                        }
                    }
                });
            }

            $(document).on('click', '.btn-del', function(e) {
                e.preventDefault();
                if (!confirm('\u00bfEliminar este participante?')) return;
                var id = $(this).data('id');
                $.ajax({
                    type:'POST', url:'eliminarParticipante.php',
                    data:{id:id},
                    success:function(){ cargarParticipantes(); },
                    error:function(){ alert('Error al eliminar'); }
                });
            });

            $('#form-agregar').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type:'POST', url:'agregarParticipante.php',
                    data:{
                        nombre: $('#nombre').val(),
                        apellido: $('#apellido').val(),
                        telefono: $('#telefono').val(),
                        ciudad: $('#ciudad').val(),
                        edad: $('#edad').val(),
                        categoria: $('#categoria').val(),
                        cinturon: $('#cinturon').val(),
                        id_escuela: ESCUELA_ID
                    },
                    success: function() {
                        $('#nombre').val(''); $('#apellido').val(''); $('#telefono').val('');
                        $('#ciudad').val(''); $('#edad').val(''); $('#categoria').val(''); $('#cinturon').val('');
                        cargarParticipantes();
                        $.mobile.changePage('#panel-participantes', { transition: 'pop' });
                    },
                    error: function() { alert('Error al agregar'); }
                });
            });
        });
    </script>
</head>
<body>
    <div data-role="page" id="panel-participantes">
        <div data-role="header" class="esc-header">
            <a href="logout.php" data-role="button" class="ui-btn-right" style="margin-top:8px;">Salir</a>
            <h1><?= htmlspecialchars($escuela_nombre) ?></h1>
        </div>
        <div data-role="content" class="esc-content">
            <div class="esc-card">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;">\uD83C\uDFC6 Mis Participantes</h3>
                    <a href="#nuevo-participante" data-role="button" data-icon="plus" class="esc-btn esc-btn-primary esc-btn-sm" style="text-decoration:none;">Agregar</a>
                </div>
                <div id="lista-participantes"></div>
            </div>
        </div>
    </div>

    <div data-role="page" id="nuevo-participante" data-position="center" data-display="overlay">
        <div data-role="header" class="esc-header">
            <h1>Nuevo Participante</h1>
        </div>
        <div data-role="content" style="padding:16px;">
            <div class="elegant-form">
                <form id="form-agregar">
                    <div data-role="fieldcontain">
                        <label for="nombre">\uD83D\uDC64 Nombre</label>
                        <input type="text" id="nombre" placeholder="Nombres" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="apellido">\uD83D\uDC65 Apellido</label>
                        <input type="text" id="apellido" placeholder="Apellidos" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="telefono">\uD83D\uDCF1 Tel\u00e9fono</label>
                        <input type="text" id="telefono" placeholder="Tel\u00e9fono" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="ciudad">\uD83D\uDCCD Ciudad</label>
                        <input type="text" id="ciudad" placeholder="Ciudad" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="edad">🎂 Edad</label>
                        <input type="number" id="edad" placeholder="Edad" min="1" max="120">
                    </div>
                    <div data-role="fieldcontain">
                        <label for="categoria">\uD83C\uDFC6 Categor\u00eda</label>
                        <select id="categoria">
                            <option value="">-- Seleccionar --</option>
                            <option value="Infantil">Infantil</option>
                            <option value="Juvenil">Juvenil</option>
                            <option value="Adulto">Adulto</option>
                            <option value="Master">Master</option>
                        </select>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="cinturon">\uD83C\uDFF5\uFE0F Cintur\u00f3n</label>
                        <select id="cinturon">
                            <option value="">-- Seleccionar --</option>
                            <option value="Blanco">\u26AA Blanco</option>
                            <option value="Blanco/Amarillo">\u26AA\uD83D\uDFE1 Blanco/Amarillo</option>
                            <option value="Amarillo">\uD83D\uDFE1 Amarillo</option>
                            <option value="Amarillo/Verde">\uD83D\uDFE1\uD83D\uDFE2 Amarillo/Verde</option>
                            <option value="Verde">\uD83D\uDFE2 Verde</option>
                            <option value="Verde/Azul">\uD83D\uDFE2\uD83D\uDD35 Verde/Azul</option>
                            <option value="Azul">\uD83D\uDD35 Azul</option>
                            <option value="Azul/Rojo">\uD83D\uDD35\uD83D\uDD34 Azul/Rojo</option>
                            <option value="Rojo">\uD83D\uDD34 Rojo</option>
                            <option value="Rojo/Negro">\uD83D\uDD34\u26AB Rojo/Negro</option>
                            <option value="Negro 1er Dan">\u26AB Negro 1er Dan</option>
                            <option value="Negro 2do Dan">\u26AB Negro 2do Dan</option>
                            <option value="Negro 3er Dan+">\u26AB Negro 3er Dan+</option>
                        </select>
                    </div>
                    <button type="submit" data-role="button" class="esc-btn esc-btn-primary" style="width:100%;text-align:center;padding:12px;font-size:15px;">\u2714 Guardar</button>
                    <a href="#panel-participantes" data-role="button" data-rel="back" style="text-align:center;padding:12px;border-radius:10px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;display:block;text-decoration:none;font-weight:500;margin-top:8px;">\u2716 Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
