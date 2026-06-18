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
// Cargar escuelas y torneos para JS (evitar AJAX inicial)
$escData = [];
$torneosData = [];
$conn = new mysqli('localhost', 'root', '', 'taekdb');
$conn->set_charset('utf8');
$r = $conn->query("SELECT id, nombre, siglas FROM escuelas WHERE estado = 1 ORDER BY nombre");
if ($r) { while ($row = $r->fetch_assoc()) { $escData[] = $row; } }
$r2 = $conn->query("SELECT *, CASE WHEN fecha < CURDATE() THEN 0 ELSE COALESCE(activo, 1) END as estado_efectivo, (SELECT COUNT(*) FROM torneoparticipante WHERE idTorneo = t.idTorneo) as total_participantes, (SELECT COUNT(*) FROM torneojueces WHERE idTorneo = t.idTorneo) as total_jueces FROM torneos t ORDER BY fecha DESC");
if ($r2) { while ($row = $r2->fetch_assoc()) { $torneosData[] = $row; } }
$conn->close();

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
        .admin-header { background: linear-gradient(135deg, #2e7d32, #4caf50); border-bottom: none; box-shadow:0 2px 12px rgba(0,0,0,0.12); }
        .admin-header h1 { color: #fff; text-shadow: none; font-size:18px; font-weight:600; letter-spacing:0.5px; }
        .admin-header .ui-btn { color: #fff !important; background: rgba(255,255,255,0.12) !important; border: 1px solid rgba(255,255,255,0.2) !important; border-radius: 22px !important; margin:6px 4px !important; padding:6px 14px !important; font-size:12px !important; font-weight:500 !important; transition:all 0.15s ease !important; box-shadow:none !important; }
        .admin-header .ui-btn:hover { background: rgba(255,255,255,0.25) !important; border-color: rgba(255,255,255,0.4) !important; }
        .admin-header .ui-btn.ui-icon-bars { padding-left:36px !important; }
        .admin-header .ui-btn.ui-icon-gear { padding-right:36px !important; }
        .admin-header .ui-btn.ui-icon-arrow-l { padding-left:32px !important; }
        .dashboard-section-title { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; padding:10px 4px 6px; margin:20px 0 6px; }
        .dashboard-section-title.activos { color:#2e7d32; border-bottom:2px solid #4caf50; }
        .dashboard-section-title.inactivos { color:#757575; border-bottom:2px solid #bdbdbd; }
        .torneo-card { display:flex; align-items:center; padding:16px 18px; margin:8px 0; border-radius:14px; background:#fff; border:1px solid #e8e8e8; box-shadow:0 2px 8px rgba(0,0,0,0.04); transition:all 0.2s ease; position:relative; overflow:hidden; }
        .torneo-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.08); transform:translateY(-1px); }
        .torneo-card.activo { border-left:5px solid #4caf50; }
        .torneo-card.activo::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #4caf50, #81c784); opacity:0.5; }
        .torneo-card.inactivo { border-left:5px solid #bdbdbd; opacity:0.75; }
        .torneo-card.inactivo::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #bdbdbd, #e0e0e0); opacity:0.4; }
        .torneo-card.pendiente { border-left:5px solid #ffb300; opacity:0.85; }
        .torneo-card.pendiente::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #ffb300, #ffd54f); opacity:0.4; }
        .torneo-card.pendiente .torneo-icon { background:linear-gradient(135deg, #fff8e1, #ffecb3); }
        .torneo-card.finalizado { border-left:5px solid #90a4ae; opacity:0.6; }
        .torneo-card.finalizado::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, #90a4ae, #cfd8dc); opacity:0.4; }
        .torneo-card.finalizado .torneo-icon { background:linear-gradient(135deg, #eceff1, #cfd8dc); }
        .torneo-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-right:14px; font-size:18px; flex-shrink:0; }
        .torneo-card.activo .torneo-icon { background:linear-gradient(135deg, #e8f5e9, #c8e6c9); }
        .torneo-card.inactivo .torneo-icon { background:linear-gradient(135deg, #f5f5f5, #eeeeee); }
        .torneo-info { flex:1; min-width:0; }
        .torneo-info strong { font-size:15px; color:#222; display:block; margin-bottom:2px; }
        .torneo-detalle { font-size:12px; color:#999; display:flex; align-items:center; gap:4px; }
        .torneo-detalle span { display:inline-flex; align-items:center; gap:2px; }
        .torneo-status { margin:0 12px; }
        .torneo-accion { flex-shrink:0; }
        .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
        .badge.activo { background:linear-gradient(135deg, #e8f5e9, #c8e6c9); color:#2e7d32; box-shadow:0 1px 3px rgba(76,175,80,0.15); }
        .badge.inactivo { background:linear-gradient(135deg, #f5f5f5, #e0e0e0); color:#757575; }
        .badge.pendiente { background:linear-gradient(135deg, #fff8e1, #ffecb3); color:#f57f17; box-shadow:0 1px 3px rgba(255,152,0,0.15); }
        .badge.finalizado { background:linear-gradient(135deg, #eceff1, #cfd8dc); color:#546e7a; }
        .btn-toggle { display:inline-block; padding:7px 16px; border-radius:8px; font-size:12px; text-decoration:none; font-weight:600; border:none; cursor:pointer; transition:all 0.15s ease; }
        .btn-toggle:hover { transform:scale(1.04); }
        .torneo-card.activo .btn-toggle { background:#ffebee; color:#c62828; border:1px solid #ffcdd2; }
        .torneo-card.activo .btn-toggle:hover { background:#ffcdd2; }
        .torneo-card.inactivo .btn-toggle { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
        .torneo-card.inactivo .btn-toggle:hover { background:#c8e6c9; }
        .torneo-card.finalizado .btn-toggle { background:#eceff1; color:#546e7a; border:1px solid #cfd8dc; }
        .torneo-card.finalizado .btn-toggle:hover { background:#cfd8dc; }
        .lock-icon { display:inline-block; padding:7px 12px; font-size:18px; opacity:0.5; cursor:default; }
        .torneo-counts { font-size:11px; color:#aaa; margin-left:8px; }
        .torneo-detalle span.torneo-counts::before { content:'|'; margin-right:8px; color:#ddd; }
        .dashboard-empty { text-align:center; padding:60px 20px; }
        .dashboard-empty-icon { font-size:48px; margin-bottom:12px; opacity:0.3; }
        .dashboard-empty-text { font-size:15px; color:#999; }
        .admin-footer { background: rgba(255,255,255,0.92); backdrop-filter:blur(8px); border-top: 1px solid rgba(0,0,0,0.06); box-shadow:0 -2px 12px rgba(0,0,0,0.04); padding:4px 0; }
        .admin-footer .ui-btn { background: linear-gradient(135deg, #2e7d32, #4caf50) !important; color: #fff !important; border: none !important; border-radius: 20px !important; margin:4px 6px !important; padding:8px 18px 8px 38px !important; font-size:12px !important; font-weight:600 !important; letter-spacing:0.3px !important; transition:all 0.15s ease !important; box-shadow:0 2px 6px rgba(76,175,80,0.2) !important; }
        .admin-footer .ui-btn:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(76,175,80,0.3) !important; }
        .admin-footer .ui-btn:active { transform:translateY(0); }
        .admin-footer .ui-btn .ui-btn-text { display:inline-block; margin-left:4px; }
        .admin-footer .ui-btn .ui-icon { width:16px !important; height:16px !important; margin-top:-8px !important; background-size:12px !important; left:14px !important; }
        .admin-panel { background: #fff; border:none !important; }
        .admin-panel .ui-header { background: linear-gradient(135deg, #2e7d32, #4caf50); border:none; min-height:52px; }
        .admin-panel .ui-header h1 { color:#fff; font-size:16px; font-weight:600; letter-spacing:0.5px; text-shadow:none; }
        .admin-panel .ui-listview { margin:8px 12px !important; border-radius:12px !important; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.04); }
        .admin-panel .ui-listview .ui-li-divider { background:linear-gradient(135deg, #f5f5f5, #e8f5e9); color:#2e7d32; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:10px 16px !important; border:none; border-top:1px solid #e8e8e8; }
        .admin-panel .ui-listview .ui-li-divider:first-child { border-top:none; }
        .admin-panel .ui-listview li { border:none !important; margin:0 !important; }
        .admin-panel .ui-listview li a { padding:12px 16px !important; font-size:14px; color:#444; font-weight:500; border:none !important; border-bottom:1px solid #f0f0f0 !important; margin:0 !important; transition:background 0.12s ease; }
        .admin-panel .ui-listview li a:hover { background:#f5f5f5; }
        .admin-panel .ui-listview li:last-child a { border-bottom:none !important; }
        .admin-panel .ui-listview .ui-btn-inner { border:none !important; }
        .admin-panel .ui-header h1 { color: #fff; }
        .elegant-form { max-width:420px; margin:16px auto; background:#fff; border-radius:18px; padding:24px 22px; box-shadow:0 4px 20px rgba(0,0,0,0.06); border:1px solid #f0f0f0; }
        .elegant-form .ui-fieldcontain { margin:6px 0 16px 0 !important; border:none !important; padding:0 !important; }
        .elegant-form .ui-fieldcontain .ui-input-text { margin:0 !important; }
        .elegant-form .ui-fieldcontain input, .elegant-form .ui-fieldcontain select { border-radius:10px !important; border:1.5px solid #e0e0e0 !important; padding:12px 14px !important; font-size:14px !important; background:#fafafa !important; transition:all 0.15s ease !important; box-shadow:none !important; }
        .elegant-form .ui-fieldcontain input:focus, .elegant-form .ui-fieldcontain select:focus { border-color:#4caf50 !important; background:#fff !important; box-shadow:0 0 0 3px rgba(76,175,80,0.1) !important; }
        .elegant-form .ui-fieldcontain label { font-size:12px !important; font-weight:600 !important; color:#555 !important; text-transform:uppercase !important; letter-spacing:0.8px !important; margin-bottom:6px !important; display:block !important; }
        .elegant-form .ui-btn.ui-btn-submit { background:linear-gradient(135deg,#2e7d32,#4caf50) !important; color:#fff !important; border:none !important; border-radius:12px !important; padding:12px !important; font-size:15px !important; font-weight:600 !important; letter-spacing:0.5px !important; box-shadow:0 4px 12px rgba(76,175,80,0.25) !important; transition:all 0.15s ease !important; margin-top:8px !important; }
        .elegant-form .ui-btn.ui-btn-submit:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(76,175,80,0.3) !important; }
        .elegant-form .ui-btn.ui-btn-cancel { background:#f5f5f5 !important; color:#666 !important; border:1px solid #e0e0e0 !important; border-radius:12px !important; padding:12px !important; font-size:14px !important; font-weight:500 !important; transition:all 0.15s ease !important; }
        .elegant-form .ui-btn.ui-btn-cancel:hover { background:#eee !important; }
        .form-header-icon { text-align:center; font-size:36px; margin-bottom:4px; }
        .form-header-title { text-align:center; font-size:18px; font-weight:700; color:#2e7d32; margin-bottom:20px; }
        .collapsible-participante .ui-collapsible-heading-toggle { padding:14px 16px 14px 40px !important; font-size:14px !important; }
        .collapsible-participante .ui-collapsible-content { padding:12px 16px !important; color:#333 !important; }
        .collapsible-participante .ui-collapsible-content label { color:#555 !important; }
        .collapsible-participante .ui-collapsible-content input,
        .collapsible-participante .ui-collapsible-content select,
        .collapsible-participante .ui-collapsible-content textarea { color:#333 !important; background:#fff !important; }
        .collapsible-participante .ui-collapsible-content input[type="number"] { width:100% !important; }
        .collapsible-participante .ui-collapsible-content .ui-select .ui-btn { color:#333 !important; background:#fff !important; }
        .btn-group { display:flex; justify-content:center; gap:10px; margin-top:14px; }
        .btn-group .button_mod.ui-btn { background:#4caf50 !important; border-color:#388e3c !important; color:#fff !important; text-shadow:none !important; }
        .btn-group .button_mod.ui-btn:hover { background:#388e3c !important; }
        .btn-group .button_del.ui-btn { background:#e53935 !important; border-color:#c62828 !important; color:#fff !important; text-shadow:none !important; }
        .btn-group .button_del.ui-btn:hover { background:#c62828 !important; }
        .btn-group .ui-btn .ui-icon { background-color:rgba(255,255,255,0.3) !important; }
    </style>
    <script>
        $(document).ready(function() {
            var escuelas = <?= json_encode($escData) ?>;
            var torneosData = <?= json_encode($torneosData) ?>;

            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
            }

            function escuelaOpts(sel) {
                var h = '<option value="">-- Sin escuela --</option>';
                escuelas.forEach(function(e) { h += '<option value="' + e.id + '"' + (sel == e.id ? ' selected' : '') + '>' + e.nombre + (e.siglas ? ' (' + e.siglas + ')' : '') + '</option>'; });
                return h;
            }

            function cargarParticipantes() {
                $.ajax({
                    type: 'GET',
                    url: 'getParticipantesTodos.php',
                    success: function(data) {
                        try {
                            var participantes = typeof data === 'string' ? JSON.parse(data) : data;
                            var participanteList = $('#listParticipantes');
                            participanteList.empty();

                            if (Array.isArray(participantes)) {
                                var belts = ['','\u26AA Blanco','\u26AA\uD83D\uDFE1 Blanco/Amarillo','\uD83D\uDFE1 Amarillo','\uD83D\uDFE1\uD83D\uDFE2 Amarillo/Verde','\uD83D\uDFE2 Verde','\uD83D\uDFE2\uD83D\uDD35 Verde/Azul','\uD83D\uDD35 Azul','\uD83D\uDD35\uD83D\uDD34 Azul/Rojo','\uD83D\uDD34 Rojo','\uD83D\uDD34\u26AB Rojo/Negro','\u26AB Negro 1er Dan','\u26AB Negro 2do Dan','\u26AB Negro 3er Dan+'];
                                var cats = ['','Infantil','Juvenil','Adulto','Master'];
                                function sel(id, name, val, opts) {
                                    var h = '<select id="' + name + id + '" name="' + name + '">';
                                    opts.forEach(function(o) { h += '<option value="' + o + '"' + (val === o ? ' selected' : '') + '>' + (o || '--') + '</option>'; });
                                    h += '</select>';
                                    return h;
                                }
                                participantes.forEach(function(p) {
                                    p.categoria = p.categoria || '';
                                    p.cinturon = p.cinturon || '';
                                    var escHeader = p.escuela_nombre ? ' <span style="font-size:11px;color:#999;font-weight:400;">[' + p.escuela_nombre + ']</span>' : '';
                                    var edadLabel = p.edad ? ' <span style="font-size:11px;color:#999;">\uD83C\uDF82' + p.edad + ' a\u00f1os</span>' : '';
                                    participanteList.append(
                                        $('<div data-role="collapsible" class="collapsible-participante" id="participante' + p.id + '">' +
                                            '<h3>' + p.nombre + ' ' + p.apellido + escHeader + edadLabel + '</h3>' +
                                            '<form>' +
                                                '<div data-role="fieldcontain"><label for="id' + p.id + '">📋 ID</label><input type="text" id="id' + p.id + '" value="' + p.id + '" disabled></div>' +
                                                '<div data-role="fieldcontain"><label for="nombre' + p.id + '">👤 Nombre</label><input type="text" id="nombre' + p.id + '" value="' + p.nombre + '"></div>' +
                                                '<div data-role="fieldcontain"><label for="apellido' + p.id + '">👥 Apellido</label><input type="text" id="apellido' + p.id + '" value="' + p.apellido + '"></div>' +
                                                '<div data-role="fieldcontain"><label for="telefono' + p.id + '">📱 Tel\u00e9fono</label><input type="text" id="telefono' + p.id + '" value="' + p.telefono + '"></div>' +
                                                '<div data-role="fieldcontain"><label for="ciudad' + p.id + '">📍 Ciudad</label><input type="text" id="ciudad' + p.id + '" value="' + p.ciudad + '"></div>' +
                                                '<div data-role="fieldcontain"><label for="edad' + p.id + '">🎂 Edad</label><input type="number" id="edad' + p.id + '" value="' + (p.edad || '') + '" min="1" max="120"></div>' +
                                                '<div data-role="fieldcontain"><label for="categoria' + p.id + '">🏆 Categor\u00eda</label>' + sel(p.id, 'categoria', p.categoria, cats) + '</div>' +
                                                '<div data-role="fieldcontain"><label for="cinturon' + p.id + '">🥋 Cintur\u00f3n</label>' + sel(p.id, 'cinturon', p.cinturon, belts) + '</div>' +
                                                '<div data-role="fieldcontain"><label for="escuela' + p.id + '">🏫 Escuela</label><select id="escuela' + p.id + '">' + escuelaOpts(p.id_escuela) + '</select></div>' +
                                                '<div class="btn-group"><a href="#" data-role="button" data-inline="true" data-mini="true" data-icon="edit" data-iconpos="right" class="button_mod" data-id="' + p.id + '">Modificar</a>' +
                                                '<a href="#aviso_borrar" data-role="button" data-inline="true" data-mini="true" data-icon="delete" data-iconpos="right" class="button_del" data-rel="dialog" data-transition="flip" data-id="' + p.id + '">Eliminar</a></div>' +
                                            '</form>' +
                                        '</div>')
                                    );
                                });
                                participanteList.collapsibleset('refresh');
                                setTimeout(function() {
                                    $('#listParticipantes .button_mod').button();
                                    $('#listParticipantes .button_del').button();
                                }, 100);
                                $('input[type="text"]').textinput();
                                $('#listParticipantes select').selectmenu();
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
            cargarParticipantes();

            $(document).on('click', '.button_mod', function() {
                var id = $(this).data('id');
                modificarParticipante(id, $('#nombre' + id).val(), $('#apellido' + id).val(), $('#telefono' + id).val(), $('#ciudad' + id).val(), $('#edad' + id).val(), $('#categoria' + id).val(), $('#cinturon' + id).val(), $('#escuela' + id).val());
            });

            function modificarParticipante(id, nombre, apellido, telefono, ciudad, edad, categoria, cinturon, id_escuela) {
                $.ajax({
                    type: 'POST', url: 'modificarParticipante.php',
                    data: { id: id, nombre: nombre, apellido: apellido, telefono: telefono, ciudad: ciudad, edad: edad, categoria: categoria, cinturon: cinturon, id_escuela: id_escuela },
                    success: function(response) { console.log('Participante modificado:', response); cargarParticipantes(); },
                    error: function(xhr, status, error) { console.error('Error al modificar el participante:', error); }
                });
            }

            $(document).on('click', '.button_del', function() {
                $('#btn_confirmar_eliminar').data('id', $(this).data('id'));
            });

            $('#btn_confirmar_eliminar').on('click', function() {
                $.ajax({
                    type: 'POST', url: 'eliminarParticipante.php',
                    data: { id: $(this).data('id') },
                    success: function(response) { console.log('Participante eliminado:', response); cargarParticipantes(); $.mobile.changePage('#participantes', { transition: 'pop' }); },
                    error: function(xhr, status, error) { console.error('Error al eliminar el participante:', error); }
                });
            });

            $('#form_agregar_participante').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST', url: 'agregarParticipante.php',
                    data: { nombre: $('#nombre_f').val(), apellido: $('#apellido_f').val(), telefono: $('#telefono_f').val(), ciudad: $('#ciudad_f').val(), edad: $('#edad_f').val(), categoria: $('#categoria_f').val(), cinturon: $('#cinturon_f').val(), id_escuela: $('#escuela_f').val() },
                    success: function(response) {
                        console.log('Participante agregado:', response);
                        $('#nombre_f').val(''); $('#apellido_f').val(''); $('#telefono_f').val(''); $('#ciudad_f').val(''); $('#edad_f').val(''); $('#categoria_f').val(''); $('#cinturon_f').val(''); $('#escuela_f').val('');
                        cargarParticipantes();
                        $.mobile.changePage('#participantes', { transition: 'pop' });
                    },
                    error: function(xhr, status, error) { console.error('Error al agregar el participante:', error); }
                });
            });

            function procesarTorneos(torneos) {
                try {
                    if (!torneos || torneos.error) {
                        $('#dashboardLoading').html('Error: ' + (torneos && torneos.error || 'Respuesta invalida'));
                        return;
                    }
                    var html = '';
                    var activos = [], inactivos = [];
                    var today = new Date(); today.setHours(0,0,0,0);

                    if (Array.isArray(torneos) && torneos.length > 0) {
                        function esPasada(f) { var p = f.split('-'); return new Date(p[0], p[1]-1, p[2]) < today; }
                        torneos.forEach(function(t) {
                            t.activo = t.activo !== undefined ? parseInt(t.activo) : 1;
                            t.estado_efectivo = t.estado_efectivo !== undefined ? parseInt(t.estado_efectivo) : 1;
                            t.total_participantes = parseInt(t.total_participantes) || 0;
                            t.total_jueces = parseInt(t.total_jueces) || 0;
                            t.puede_alternar = t.total_participantes > 0 && t.total_jueces > 0;
                            t.pasado = esPasada(t.fecha);
                            (t.estado_efectivo === 1 && !t.pasado ? activos : inactivos).push(t);
                        });

                        function renderCard(t, badgeClass, badgeText, icono) {
                            var requires = t.total_participantes + ' participante' + (t.total_participantes !== 1 ? 's' : '') + ' \u00b7 ' + t.total_jueces + ' juez' + (t.total_jueces !== 1 ? 'es' : '');
                            var accion = '';
                            if (t.pasado) {
                                accion = '<span class="lock-icon" title="Torneo finalizado">\uD83D\uDD12</span>';
                            } else if (t.estado_efectivo === 1) {
                                accion = '<a href="#" class="btn-toggle" data-id="' + t.idTorneo + '">Desactivar</a>';
                            } else if (t.puede_alternar) {
                                accion = '<a href="#" class="btn-toggle" data-id="' + t.idTorneo + '">Activar</a>';
                            } else {
                                accion = '<span class="lock-icon" title="Requiere participantes y jueces">\uD83D\uDD12</span>';
                            }
                            html += '<div class="torneo-card ' + badgeClass + '"><div class="torneo-icon">' + icono + '</div><div class="torneo-info"><strong>' + escapeHtml(t.nombre) + '</strong><div class="torneo-detalle"><span>\uD83D\uDCC5 ' + t.fecha + '</span><span>\uD83D\uDCCD ' + escapeHtml(t.ciudad) + '</span><span class="torneo-counts">' + requires + '</span></div></div><div class="torneo-status"><span class="badge ' + badgeClass + '">' + badgeText + '</span></div><div class="torneo-accion">' + accion + '</div></div>';
                        }

                        if (activos.length > 0) {
                            html += '<div class="dashboard-section-title activos">\u2696 Torneos Activos</div>';
                            activos.forEach(function(t) { renderCard(t, 'activo', 'Activo', '\uD83C\uDFC6'); });
                        }
                        if (inactivos.length > 0) {
                            html += '<div class="dashboard-section-title inactivos">\u23F3 Torneos Inactivos</div>';
                            inactivos.forEach(function(t) { renderCard(t, t.pasado ? 'finalizado' : 'inactivo', t.pasado ? 'Finalizado' : 'Inactivo', t.pasado ? '\uD83C\uDFC1' : '\uD83D\uDD34'); });
                        }
                    } else {
                        html = '<div class="dashboard-empty"><div class="dashboard-empty-icon">\uD83C\uDFC6</div><div class="dashboard-empty-text">No hay torneos registrados. Crea uno desde el men&uacute;.</div></div>';
                    }
                    $('#dashboardContent').html(html);
                    $('#dashboardLoading').hide();
                } catch (e) {
                    $('#dashboardLoading').html('Error al cargar torneos: ' + e.message);
                }
            }

            function cargarDashboard() {
                var el = $('#dashboardLoading').show();
                el.html('Cargando torneos...');
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'getTorneos.php', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState !== 4) return;
                    if (xhr.status === 200) {
                        try {
                            var torneos = JSON.parse(xhr.responseText);
                            procesarTorneos(torneos);
                        } catch(e) {
                            el.html('Error parseando: ' + e.message + '<br><pre>' + escapeHtml(xhr.responseText) + '</pre>');
                        }
                    } else {
                        el.html('Error HTTP ' + xhr.status + ': ' + xhr.statusText + '<br><pre>' + escapeHtml(xhr.responseText) + '</pre>');
                    }
                };
                xhr.send();
            }



            // Populate escuela selects
            function poblarEscuelas() {
                var opts = '<option value="">-- Sin escuela --</option>';
                escuelas.forEach(function(e) { opts += '<option value="' + e.id + '">' + e.nombre + (e.siglas ? ' (' + e.siglas + ')' : '') + '</option>'; });
                $('#escuela_f').html(opts);
                if ($.fn.selectmenu) $('#escuela_f').selectmenu('refresh');
            }
            poblarEscuelas();

            // No llamar cargarDashboard() aqui; PHP ya renderizó los torneos en #dashboardContent
            $(document).on('pageshow', '#mainPage', cargarDashboard);

            $(document).on('click', '.btn-toggle', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST', url: 'toggleTorneo.php',
                    data: { id: $(this).data('id') },
                    dataType: 'json',
                    success: function(r) {
                        if (r.success) { cargarDashboard(); }
                        else { alert('Error: ' + (r.message || 'No se pudo cambiar el estado.')); }
                    },
                    error: function(xhr) {
                        alert('Error HTTP ' + xhr.status + ': ' + xhr.statusText + '\n\n' + xhr.responseText);
                    }
                });
            });

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
                                    var fechaFormateada = formatDate(torneo.fecha);
                                    var activo = torneo.activo !== undefined ? parseInt(torneo.activo) : 1;
                                    var activoLabel = activo === 1 ? 'Activo' : 'Inactivo';

                                    torneoList.append(
                                        $('<div data-role="collapsible" data-theme="a" data-content-theme="a" data-role="content" id="torneo' + torneo.idTorneo + '">' +
                                            '<h3>' + torneo.nombre + ' - ' + fechaFormateada + ' <span style="color:' + (activo === 1 ? '#4caf50' : '#999') + ';font-size:12px;">[' + activoLabel + ']</span></h3>' +
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
                                                '<div data-role="fieldcontain">' +
                                                    '<label for="activoT' + torneo.idTorneo + '">Estado:</label>' +
                                                    '<select id="activoT' + torneo.idTorneo + '">' +
                                                        '<option value="1" ' + (activo === 1 ? 'selected' : '') + '>Activo</option>' +
                                                        '<option value="0" ' + (activo === 0 ? 'selected' : '') + '>Inactivo</option>' +
                                                    '</select>' +
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
                var nombre = $('#nombreT' + idTorneo).val();
                var fecha = $('#fecha' + idTorneo).val();
                var ciudad = $('#ciudadT' + idTorneo).val();
                var activo = $('#activoT' + idTorneo).val();

                modificarTorneo(idTorneo, nombre, fecha, ciudad, activo);
            });

            // Función para modificar el torneo
            function modificarTorneo(id, nombre, fecha, ciudad, activo) {
				$.ajax({
					type: 'POST',
					url: 'modificarTorneo.php',
					data: {
						id: id,
						nombre: nombre,
						fecha: fecha,
						ciudad: ciudad,
						activo: activo
					},
					success: function(response) {
						console.log('Torneo modificado:', response);
						if (response.success) {
							alert(response.message);
							cargarTorneos();
							cargarDashboard();
						} else {
							alert(response.message);
						}
					},
					error: function(xhr, status, error) {
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
                        cargarTorneos();
                        if (typeof cargarDashboard === 'function') cargarDashboard();
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
                        
                        $('#nombreT').val('');
                        $('#fecha').val('');
                        $('#ciudadT').val();

                        cargarTorneos();
                        if (typeof cargarDashboard === 'function') cargarDashboard();

                        $.mobile.changePage('#torneos', { transition: 'pop' });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al agregar el torneo:', error);
                    }
                });
            }
        });

        // ---- Escuelas (admin) ----

        function cargarEscuelas() {
            $('#escuelasLoading').show();
            $.ajax({
                type: 'GET',
                url: 'getEscuelasAdmin.php',
                dataType: 'text',
                success: function(data) {
                    try {
                        var escuelas = JSON.parse(data);
                        var html = '';
                        if (escuelas.error) {
                            html = '<div style="padding:20px;color:#c62828;">Error: ' + escuelas.error + '</div>';
                        } else if (Array.isArray(escuelas) && escuelas.length > 0) {
                            escuelas.forEach(function(e) {
                                var est = parseInt(e.estado);
                                var badgeClass = est ? 'activo' : 'pendiente';
                                var badgeText = est ? 'Activa' : 'Pendiente';
                                var icono = est ? '\uD83C\uDFEB' : '\uD83C\uDFE2';
                                var accion = '';
                                if (est) {
                                    accion = '<a href="#" class="btn-toggle-escuela" data-id="' + e.id + '" style="display:inline-block;padding:7px 16px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600;background:#ffebee;color:#c62828;border:1px solid #ffcdd2;">Desactivar</a>';
                                } else {
                                    accion = '<a href="#" class="btn-toggle-escuela" data-id="' + e.id + '" style="display:inline-block;padding:7px 16px;border-radius:8px;font-size:12px;text-decoration:none;font-weight:600;background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;">Activar</a>';
                                }
                                html += '<div class="torneo-card ' + badgeClass + '"><div class="torneo-icon" style="font-size:22px;">' + icono + '</div><div class="torneo-info"><strong>' + escapeHtml(e.nombre) + '</strong><div class="torneo-detalle" style="flex-wrap:wrap;gap:8px;"><span>\uD83D\uDCCD ' + escapeHtml(e.ciudad || '') + '</span><span>\uD83D\uDC68\u200D\uD83C\uDFEB ' + escapeHtml(e.instructor_nombre || '') + ' (' + escapeHtml(e.instructor_grado || '') + ')</span><span>\uD83D\uDCDE ' + escapeHtml(e.telefono || '') + '</span><span>\u2709 ' + escapeHtml(e.correo || '') + '</span><span class="torneo-counts">' + (parseInt(e.total_participantes) || 0) + ' participantes</span></div></div><div class="torneo-status"><span class="badge ' + badgeClass + '">' + badgeText + '</span></div><div class="torneo-accion">' + accion + '</div></div>';
                            });
                        } else {
                            html = '<div class="dashboard-empty"><div class="dashboard-empty-icon">\uD83C\uDFEB</div><div class="dashboard-empty-text">No hay escuelas registradas.</div></div>';
                        }
                        $('#escuelasContent').html(html);
                        $('#escuelasLoading').hide();
                    } catch (e) {
                        $('#escuelasLoading').html('Error parseando: ' + e.message + '<br><pre>' + escapeHtml(data) + '</pre>');
                    }
                },
                error: function(xhr) {
                    $('#escuelasLoading').html('Error de conexión. ' + xhr.status + ': ' + xhr.statusText);
                }
            });
        }

        $(document).on('click', '.btn-toggle-escuela', function(e) {
            e.preventDefault();
            var btn = $(this);
            $.ajax({
                type: 'POST',
                url: 'toggleEscuela.php',
                data: { id: btn.data('id') },
                dataType: 'json',
                success: function(r) {
                    if (r.success) { cargarEscuelas(); }
                    else { alert('Error: ' + (r.message || 'No se pudo cambiar el estado.')); }
                },
                error: function(xhr) {
                    alert('Error HTTP ' + xhr.status + ': ' + xhr.statusText + '\n\n' + xhr.responseText);
                }
            });
        });

        $(document).on('pageshow', '#escuelas', function() {
            cargarEscuelas();
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
			<div data-role="header"><h1>📋 Menú</h1><a href="#" data-rel="close" class="ui-btn ui-btn-right ui-icon-delete ui-btn-icon-notext" style="color:#fff;background:transparent !important;border:none !important;">Cerrar</a></div>
			<div>
				<ul data-role="listview" data-inset="true" data-shadow="false">
					<li data-role="list-divider">👥 Participantes</li>
					<li><a href="#participantes" data-rel="close">📋 Lista Participantes</a></li>
					<li data-role="list-divider">⚖️ Jueces</li>
					<li><a href="#Jueces" data-rel="close">📋 Lista Jueces</a></li>
					<li data-role="list-divider">🏫 Escuelas</li>
					<li><a href="#escuelas" data-rel="close">📋 Lista Escuelas</a></li>
					<li data-role="list-divider">🏆 Torneos</li>
					<li><a href="#torneos" data-rel="close">➕ Crear Torneo</a></li>
					<li><a href="#asignarParticipantes" data-rel="close">👤 Asignar Participantes</a></li>
					<li><a href="asignarJueces.php" data-rel="close">⚖️ Asignar Jueces</a></li>
				</ul>
			</div>
		</div>
		<div data-role="panel" id="rightPanel" data-position="right" data-display="overlay" data-dismissible="true" class="admin-panel">
			<div data-role="header"><h1>⚙️ Opciones</h1><a href="#" data-rel="close" class="ui-btn ui-btn-right ui-icon-delete ui-btn-icon-notext" style="color:#fff;background:transparent !important;border:none !important;">Cerrar</a></div>
			<div>
				<ul data-role="listview" data-inset="true">
					<li><a href="#contacto" data-rel="close">📞 Contacto</a></li>
					<li><a href="logout.php" data-ajax="false">🚪 Salir</a></li>
				</ul>
			</div>
		</div>
		<div role="main" class="ui-content">
			<div id="dashboard">
				<div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; padding:16px 18px; background:linear-gradient(135deg, #e8f5e9, #f1f8e9); border-radius:14px; border:1px solid #c8e6c9;">
					<div style="font-size:28px;">🏟️</div>
					<div><h2 style="color:#1b5e20; margin:0; font-size:18px; font-weight:700;">Panel de Torneos</h2>
					<p style="color:#558b2f; margin:2px 0 0; font-size:12px;">Gestiona el estado de tus torneos</p></div>
				</div>
				<div id="dashboardLoading" style="display:none;"></div>
				<div id="dashboardContent"><?php
				if (!empty($torneosData)):
					$today = new DateTime();
					$activos = []; $inactivos = [];
					foreach ($torneosData as $t):
						$fecha = new DateTime($t['fecha']);
						$pasado = $fecha < $today;
						$estado_efectivo = ($t['estado_efectivo'] ?? 1) && !$pasado ? 1 : 0;
						$puede = (intval($t['total_participantes'] ?? 0) > 0 && intval($t['total_jueces'] ?? 0) > 0);
						if ($estado_efectivo) { $activos[] = [$t, $pasado, $puede]; }
						else { $inactivos[] = [$t, $pasado, $puede]; }
					endforeach;
					function renderTorneos($items, $badgeClass, $badgeText, $icono, $title) {
						if (empty($items)) return;
						echo '<div class="dashboard-section-title ' . $badgeClass . '">' . $title . '</div>';
						foreach ($items as [$t, $pasado, $puede]):
							$bc = $pasado ? 'finalizado' : $badgeClass;
							$bt = $pasado ? 'Finalizado' : $badgeText;
							$ic = $pasado ? '🏁' : $icono;
							$req = intval($t['total_participantes'] ?? 0) . ' participante' . (intval($t['total_participantes'] ?? 0) !== 1 ? 's' : '') . ' · ' . intval($t['total_jueces'] ?? 0) . ' juez' . (intval($t['total_jueces'] ?? 0) !== 1 ? 'es' : '');
							$lock = ($pasado || ($bc === 'inactivo' && !$puede)) ? '🔒' : '';
							echo '<div class="torneo-card ' . $bc . '"><div class="torneo-icon">' . $ic . '</div><div class="torneo-info"><strong>' . htmlspecialchars($t['nombre']) . '</strong><div class="torneo-detalle"><span>📅 ' . $t['fecha'] . '</span><span>📍 ' . htmlspecialchars($t['ciudad']) . '</span><span class="torneo-counts">' . $req . '</span></div></div><div class="torneo-status"><span class="badge ' . $bc . '">' . $bt . '</span></div><div class="torneo-accion">' . $lock . '</div></div>';
						endforeach;
					}
					renderTorneos($activos, 'activo', 'Activo', '🏆', '⚖️ Torneos Activos');
					renderTorneos($inactivos, 'inactivo', 'Inactivo', '🔴', '⏳ Torneos Inactivos');
				else: ?>
				<div class="dashboard-empty"><div class="dashboard-empty-icon">🏆</div><div class="dashboard-empty-text">No hay torneos registrados. Crea uno desde el menú.</div></div>
				<?php endif; ?>
				</div>
			</div>
		</div>
		<div data-role="footer" data-position="fixed" class="admin-footer">
			<div data-role="controlgroup" data-type="horizontal" style="text-align:center;">
				<a href="logout.php" data-role="button" data-icon="power" data-ajax="false">Salir</a>
				<a href="#contacto" data-role="button" data-icon="mail" data-rel="dialog" data-transition="pop">Contacto</a>
			</div>
		</div>
	</div>
		
    <!-- CRUD Participantes -->
    <section data-role="page" id="participantes">
		<header data-role="header" class="admin-header">
			<a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left">Volver</a>
            <h1>Participantes</h1>
        </header>

        <div data-role="content">
            <div align="right" data-type="horizontal" data-role="controlgroup" data-mini='true'>
                <a href="#nuevoParticipante" data-role="button" data-icon="plus" data-iconpos="right" 
                data-rel="dialog" data-transition="pop" id="">Nuevo</a>
            </div>
            
            <div data-role="collapsibleset" id="listParticipantes" >
            </div>
        </div>
    </section>

    <!-- CRUD Escuelas -->
    <section data-role="page" id="escuelas">
        <header data-role="header" class="admin-header">
            <a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left">Volver</a>
            <h1>🏫 Escuelas</h1>
        </header>
        <div data-role="content">
            <div id="escuelasLoading" style="text-align:center; padding:40px; color:#999; font-size:14px;">Cargando escuelas...</div>
            <div id="escuelasContent"></div>
        </div>
        <center>
            <div data-role="footer" data-position="fixed" class="admin-footer">
                <div data-role="controlgroup" data-type="horizontal" data-position="center">
                    <a href="logout.php" data-role="button" data-icon="power" data-ajax="false">Salir</a>
                    <a href="#contacto" data-role="button" data-icon="mail" data-rel="dialog" data-transition="pop">Contacto</a>
                </div>
            </div>
        </center>
    </section>

    <div data-role="page" id="nuevoParticipante" data-position="center" data-display="overlay" >
        <div data-role="header" class="admin-header">
            <h1>Nuevo Participante</h1>
        </div>
        <div data-role="content" style="padding:16px;">
            <div class="elegant-form">
                <div class="form-header-icon">🏫</div>
                <div class="form-header-title">Registrar Participante</div>
                <form id="form_agregar_participante">
                    <div data-role="fieldcontain">
                        <label for="nombre_f">👤 Nombre</label>
                        <input type="text" id="nombre_f" placeholder="Nombres" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="apellido_f">👥 Apellido</label>
                        <input type="text" id="apellido_f" placeholder="Apellidos" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="telefono_f">📱 Teléfono</label>
                        <input type="text" id="telefono_f" placeholder="Número de teléfono" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="ciudad_f">📍 Ciudad</label>
                        <input type="text" id="ciudad_f" placeholder="Ciudad de origen" required>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="edad_f">🎂 Edad</label>
                        <input type="number" id="edad_f" placeholder="Edad" min="1" max="120">
                    </div>
                    <div data-role="fieldcontain">
                        <label for="categoria_f">🏆 Categoría</label>
                        <select id="categoria_f">
                            <option value="">-- Seleccionar --</option>
                            <option value="Infantil">Infantil</option>
                            <option value="Juvenil">Juvenil</option>
                            <option value="Adulto">Adulto</option>
                            <option value="Master">Master</option>
                        </select>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="cinturon_f">🥋 Cinturón</label>
                        <select id="cinturon_f">
                            <option value="">-- Seleccionar --</option>
                            <option value="Blanco">⚪ Blanco</option>
                            <option value="Blanco/Amarillo">⚪🟡 Blanco/Amarillo</option>
                            <option value="Amarillo">🟡 Amarillo</option>
                            <option value="Amarillo/Verde">🟡🟢 Amarillo/Verde</option>
                            <option value="Verde">🟢 Verde</option>
                            <option value="Verde/Azul">🟢🔵 Verde/Azul</option>
                            <option value="Azul">🔵 Azul</option>
                            <option value="Azul/Rojo">🔵🔴 Azul/Rojo</option>
                            <option value="Rojo">🔴 Rojo</option>
                            <option value="Rojo/Negro">🔴⚫ Rojo/Negro</option>
                            <option value="Negro 1er Dan">⚫ Negro 1er Dan</option>
                            <option value="Negro 2do Dan">⚫ Negro 2do Dan</option>
                            <option value="Negro 3er Dan+">⚫ Negro 3er Dan+</option>
                        </select>
                    </div>
                    <div data-role="fieldcontain">
                        <label for="escuela_f">🏫 Escuela</label>
                        <select id="escuela_f"><option value="">-- Sin escuela --</option></select>
                    </div>
                    <button type="submit" data-role="button" class="ui-btn-submit">✔ Guardar Participante</button>
                    <a href="#participantes" data-role="button" class="ui-btn-cancel" data-rel="back">✖ Cancelar</a>
                </form>
            </div>
        </div>
    </div>
	
	<!-- CRUD Torneos -->
    <section data-role="page" id="torneos">
        <header data-role="header" class="admin-header">
		<a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left">Volver</a>
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
                    <a href="logout.php" data-role="button" data-icon="power" data-ajax="false">Salir</a>
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
		<a href="#mainPage" data-role="button" data-icon="arrow-l" data-iconpos="left">Volver</a>
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