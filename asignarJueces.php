<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignar Jueces</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="themes/takwondoTheme.min.css" />
    <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    <link rel="stylesheet" href="lib/jquery.mobile.structure-1.4.5.min.css" />
    <script src="lib/jquery-1.11.1.min.js"></script>
    <script src="lib/jquery.mobile-1.4.5.min.js"></script>
    <style>
        body { background: #f5f5dc; }
        .ui-page { background: #f5f5dc; }
        .header-bar { background: linear-gradient(135deg, #4caf50, #388e3c); border-bottom: 3px solid #ffeb3b; }
        .header-bar h1 { color: #fff; font-size: 18px; }
        .list-container { margin: 10px; }
        .list-container h3 { color: #4caf50; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; margin: 16px 0 8px; }
        #torneoSelect { width: 100%; padding: 14px; border-radius: 10px; background: #fff; color: #333; border: 1px solid #ddd; font-size: 16px; }
        .ui-listview .ui-li-static { background: #fff; color: #333; border-color: #e0e0e0; }
        .ui-listview .ui-li-divider { background: #4caf50; color: #fff; }
        .ui-listview li .ui-btn { background: #fff; color: #333; border-color: #e0e0e0; }
        .ui-listview li .ui-btn:hover { background: #f5f5dc; }
        label { color: #666; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; }
    </style>
    <script>
    $(document).ready(function() {
        cargarTorneos();
        cargarJueces();

        function cargarTorneos() {
            $.ajax({ type: 'GET', url: 'getTorneos.php',
                success: function(data) {
                    try {
                        var torneos = JSON.parse(data);
                        var s = $('#torneoSelect'); s.empty();
                        s.append('<option value="">Selecciona un Torneo</option>');
                        if (Array.isArray(torneos)) {
                            torneos.forEach(function(t) {
                                s.append('<option value="' + t.idTorneo + '">' + t.nombre + ' - ' + t.ciudad + '</option>');
                            });
                        }
                    } catch(e) { console.error(e); }
                }
            });
        }

        function cargarJueces() {
            $.ajax({ type: 'GET', url: 'getJueces.php',
                success: function(data) {
                    try {
                        var jueces = JSON.parse(data);
                        var l = $('#listJueces'); l.empty();
                        if (Array.isArray(jueces)) {
                            jueces.forEach(function(j) {
                                l.append('<li data-icon="plus"><a href="#">' + j.nombre + ' ' + j.apellido + '</a></li>');
                                l.find('li:last').attr('data-juez-id', j.id);
                            });
                            l.listview('refresh');
                        }
                    } catch(e) { console.error(e); }
                }
            });
        }

        function cargarJuecesTorneo(torneoId) {
            $.ajax({ type: 'GET', url: 'getJuecesTorneo.php', data: { id_torneo: torneoId },
                success: function(data) {
                    try {
                        var jueces = data;
                        var l = $('#selectedJueces'); l.empty();
                        if (Array.isArray(jueces)) {
                            jueces.forEach(function(j) {
                                l.append('<li data-icon="minus"><a href="#">' + j.nombre + ' ' + j.apellido + '</a></li>');
                                l.find('li:last').attr('data-juez-id', j.id);
                            });
                            l.listview('refresh');
                        }
                    } catch(e) { console.error(e); }
                }
            });
        }

        $('#torneoSelect').on('change', function() {
            var id = $(this).val();
            if (id > 0) { cargarJuecesTorneo(id); }
        });

        $('#listJueces').on('click', 'li', function() {
            var juezId = $(this).attr('data-juez-id');
            var juezText = $(this).text();
            var torneoId = $('#torneoSelect').val();
            if (!torneoId || torneoId <= 0) { alert('Selecciona un torneo'); return; }

            $('#selectedJueces').append(
                $('<li data-icon="minus"><a href="#">' + juezText + '</a></li>').attr('data-juez-id', juezId)
            );
            $(this).remove();
            $('#listJueces').listview('refresh');
            $('#selectedJueces').listview('refresh');

            $.ajax({ type: 'POST', url: 'asignarJuezTorneo.php', data: { id_torneo: torneoId, id_juez: juezId } });
        });

        $('#selectedJueces').on('click', 'li', function() {
            var juezId = $(this).attr('data-juez-id');
            var juezText = $(this).text();
            var torneoId = $('#torneoSelect').val();
            if (!torneoId || torneoId <= 0) { alert('Selecciona un torneo'); return; }

            $('#listJueces').append(
                $('<li data-icon="plus"><a href="#">' + juezText + '</a></li>').attr('data-juez-id', juezId)
            );
            $(this).remove();
            $('#listJueces').listview('refresh');
            $('#selectedJueces').listview('refresh');

            $.ajax({ type: 'POST', url: 'quitarJuezTorneo.php', data: { id_torneo: torneoId, id_juez: juezId } });
        });
    });
    </script>
</head>
<body>
    <div data-role="page" id="mainPage">
        <div data-role="header" class="header-bar">
            <a href="index.php" class="ui-btn ui-btn-inline ui-icon-arrow-l ui-btn-icon-left" style="color:#fff;">Volver</a>
            <h1>Asignar Jueces</h1>
        </div>
        <div role="main" class="ui-content" style="padding:0;">
            <div class="list-container">
                <label for="torneoSelect">Torneo</label>
                <select name="torneoSelect" id="torneoSelect"></select>

                <h3>Jueces Disponibles</h3>
                <ul id="listJueces" data-role="listview" data-filter="true" data-inset="true"></ul>

                <h3>Jueces Asignados</h3>
                <ul id="selectedJueces" data-role="listview" data-filter="true" data-inset="true"></ul>
            </div>
        </div>
    </div>
</body>
</html>