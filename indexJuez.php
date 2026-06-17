<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = require 'conexion.php';

if ($_SESSION['user_level'] !== 2) {
    header("Location: index.php");
    exit();
}

$juez_id = intval($_SESSION['juez_id']);
$juez_nombre = '';

$q = "SELECT nombre, apellido FROM jueces WHERE id = ?";
$s = $conn->prepare($q);
$s->bind_param("i", $juez_id);
$s->execute();
$r = $s->get_result();
if ($row = $r->fetch_assoc()) {
    $juez_nombre = $row['nombre'] . ' ' . $row['apellido'];
}
$s->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>Juez - Evaluación Poomsae</title>
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5dc; color: #333; }
        .ui-page { background: #f5f5dc; }

        .header-score { background: linear-gradient(135deg, #4caf50, #388e3c); border-bottom: 3px solid #ffeb3b; padding: 10px 16px; text-align: center; }
        .header-score h1 { font-size: 14px; color: #fff; margin: 0; font-weight: 400; letter-spacing: 2px; text-transform: uppercase; }
        .header-score .juez-name { color: #ffeb3b; font-size: 13px; margin-top: 2px; }

        .participant-card { background: #fff; border-radius: 16px; margin: 16px; padding: 20px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
        .participant-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #999; margin-bottom: 8px; }
        .participant-card .name { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 4px; }
        .participant-card .categoria-badge { display: inline-block; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .cat-poomsae { background: #4caf50; color: #fff; }
        .cat-kyorugi { background: #ff5722; color: #fff; }
        .cat-freestyle { background: #2196f3; color: #fff; }

        .score-display { text-align: center; padding: 10px 0; margin: 8px 16px; background: #fff; border-radius: 16px; border: 2px solid #4caf50; }
        .score-display .score-value { font-size: 96px; font-weight: 800; color: #388e3c; line-height: 1; }
        .score-display .score-label { font-size: 11px; text-transform: uppercase; letter-spacing: 3px; color: #999; margin-top: 4px; }

        .btn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 0 16px; margin: 8px 0; }
        .btn-score { padding: 20px; border: none; border-radius: 12px; font-size: 22px; font-weight: 700; cursor: pointer; transition: all 0.2s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-score:active { transform: scale(0.95); }
        .btn-plus1 { background: #43a047; color: #fff; }
        .btn-plus3 { background: #2e7d32; color: #fff; }
        .btn-minus1 { background: #ffb300; color: #fff; }
        .btn-minus3 { background: #ff8f00; color: #fff; }
        .btn-score:disabled { opacity: 0.3; }

        .btn-submit { display: block; width: calc(100% - 32px); margin: 12px 16px; padding: 18px; border: none; border-radius: 12px; font-size: 18px; font-weight: 700; cursor: pointer; transition: all 0.2s; text-transform: uppercase; letter-spacing: 2px; }
        .btn-submit:active { transform: scale(0.97); }
        .btn-guardar { background: linear-gradient(135deg, #4caf50, #388e3c); color: #fff; }
        .btn-guardar:disabled { background: #ccc; color: #999; }
        .btn-guardar.locked { background: #2e7d32; color: #fff; }
        .btn-sig { background: linear-gradient(135deg, #ffeb3b, #fdd835); color: #333; margin-top: 4px; }
        .btn-sig:disabled { background: #ccc; color: #999; }

        .status-bar { background: #fff; display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; font-size: 12px; color: #666; border-top: 1px solid #e0e0e0; }
        .status-bar .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .dot-green { background: #28a745; }
        .dot-red { background: #dc3545; }
        .dot-yellow { background: #ffc107; }

        .torneo-selector { margin: 8px 16px; }
        .torneo-selector select { width: 100%; padding: 14px; border-radius: 12px; background: #fff; color: #333; border: 1px solid #ddd; font-size: 16px; }
        .torneo-selector label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #999; margin-bottom: 6px; display: block; }

        .jueces-status { margin: 8px 16px; padding: 12px 16px; background: #fff; border-radius: 10px; font-size: 13px; color: #666; border: 1px solid #e0e0e0; }
        .jueces-status span { color: #4caf50; font-weight: 600; }

        .hidden { display: none !important; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .loading { text-align: center; padding: 40px; color: #999; }
        .loading .spinner { width: 40px; height: 40px; border: 3px solid #e0e0e0; border-top: 3px solid #4caf50; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .no-participants { text-align: center; padding: 40px 20px; color: #666; }
        .no-participants h3 { color: #999; margin-bottom: 8px; }
    </style>
</head>
<body>
<div data-role="page" id="judgePage">
    <div data-role="header" data-theme="b" class="header-score">
        <h1>Evaluación Poomsae</h1>
        <div class="juez-name">Juez: <?php echo htmlspecialchars($juez_nombre); ?></div>
    </div>

    <div role="main" class="ui-content" style="padding:0;">
        <div id="loadingView" class="loading">
            <div class="spinner"></div>
            <p>Cargando...</p>
        </div>

        <div id="torneoSelection" class="hidden fade-in">
            <div class="torneo-selector">
                <label>Selecciona un Torneo</label>
                <select id="torneoSelect">
                    <option value="">Cargando torneos...</option>
                </select>
            </div>
            <div id="sinTorneosMsg" class="no-participants hidden">
                <h3>No tienes torneos asignados</h3>
                <p>Contacta al administrador para asignarte a un torneo.</p>
            </div>
        </div>

        <div id="evaluationView" class="hidden fade-in">
            <div id="emptyParticipants" class="no-participants hidden">
                <h3>No hay participantes</h3>
                <p>Este torneo no tiene participantes asignados aún.</p>
            </div>

            <div id="scoringArea">
                <div class="participant-card">
                    <div class="label">Participante Actual</div>
                    <div class="name" id="participantName">---</div>
                    <div>
                        <span class="categoria-badge cat-poomsae" id="categoriaBadge">Poomsae</span>
                    </div>
                </div>

                <div class="score-display">
                    <div class="score-value" id="scoreDisplay">4.0</div>
                    <div class="score-label">Puntaje</div>
                </div>

                <div class="btn-grid">
                    <button class="btn-score btn-plus1" id="btnPlus01">+0.1</button>
                    <button class="btn-score btn-plus3" id="btnPlus03">+0.3</button>
                    <button class="btn-score btn-minus1" id="btnMinus01">-0.1</button>
                    <button class="btn-score btn-minus3" id="btnMinus03">-0.3</button>
                </div>

                <button class="btn-submit btn-guardar" id="btnGuardar">Guardar Puntaje</button>

                <div class="jueces-status" id="juecesStatus">
                    Jueces: <span id="juecesCount">0/0</span> han votado
                </div>

                <button class="btn-submit btn-sig" id="btnSiguiente" disabled>Siguiente Participante</button>
            </div>
        </div>

        <div class="status-bar">
            <div><span class="dot dot-yellow" id="statusDot"></span> <span id="statusText">Esperando torneo</span></div>
            <div><a href="logout.php" style="color:#dc3545; text-decoration:none; font-weight:600;">Salir</a></div>
        </div>
    </div>
</div>

<script>
let puntos = 4.0;
let idTorneo = 0;
let idParticipanteActual = 0;
let yaVoto = false;
let checkingInterval = null;
let categoriaActual = 'poomsae';

$(document).ready(function() {
    cargarTorneos();

    $('#torneoSelect').on('change', function() {
        idTorneo = parseInt($(this).val());
        if (idTorneo > 0) {
            iniciarEvaluacion();
        } else {
            mostrarSeleccion();
        }
    });

    $('#btnPlus01').on('click', function() { if (!yaVoto) ajustarPuntos(0.1); });
    $('#btnPlus03').on('click', function() { if (!yaVoto) ajustarPuntos(0.3); });
    $('#btnMinus01').on('click', function() { if (!yaVoto) ajustarPuntos(-0.1); });
    $('#btnMinus03').on('click', function() { if (!yaVoto) ajustarPuntos(-0.3); });

    $('#btnGuardar').on('click', guardarEvaluacion);
    $('#btnSiguiente').on('click', avanzarParticipante);

    function ajustarPuntos(valor) {
        puntos = Math.max(0.0, Math.min(4.0, puntos + valor));
        $('#scoreDisplay').text(puntos.toFixed(1));
    }

    function cargarTorneos() {
        $.ajax({
            url: 'getTorneosJuez.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var select = $('#torneoSelect');
                select.empty();
                select.append('<option value="">Selecciona un torneo</option>');
                if (data && data.length > 0) {
                    $.each(data, function(i, t) {
                        select.append('<option value="' + t.idTorneo + '">' + t.nombre + '</option>');
                    });
                    $('#sinTorneosMsg').addClass('hidden');
                } else {
                    $('#sinTorneosMsg').removeClass('hidden');
                }
                $('#loadingView').addClass('hidden');
                $('#torneoSelection').removeClass('hidden');
            },
            error: function() {
                $('#torneoSelect').empty().append('<option value="">Error al cargar</option>');
                $('#loadingView').addClass('hidden');
                $('#torneoSelection').removeClass('hidden');
            }
        });
    }

    function iniciarEvaluacion() {
        $('#loadingView').removeClass('hidden');
        $('#evaluationView').addClass('hidden');
        limpiarIntervalo();

        $.ajax({
            url: 'getCurrentParticipant.php',
            type: 'GET',
            data: { id_torneo: idTorneo },
            dataType: 'json',
            success: function(data) {
                $('#loadingView').addClass('hidden');
                if (data && data.id_participante_actual) {
                    idParticipanteActual = data.id_participante_actual;
                    $('#participantName').text(data.nombre + ' ' + data.apellido);
                    $('#emptyParticipants').addClass('hidden');
                    $('#scoringArea').removeClass('hidden');
                    resetearPuntaje();
                    verificarEstadoVotacion();
                    checkingInterval = setInterval(verificarEstadoVotacion, 2000);
                    $('#statusText').text('Evaluando a ' + data.nombre);
                    $('#statusDot').removeClass('dot-green dot-red').addClass('dot-yellow');
                } else if (data && data.error) {
                    mostrarSinParticipantes();
                } else {
                    mostrarSinParticipantes();
                }
                $('#evaluationView').removeClass('hidden');
            },
            error: function() {
                $('#loadingView').addClass('hidden');
                mostrarSinParticipantes();
                $('#evaluationView').removeClass('hidden');
            }
        });
    }

    function mostrarSinParticipantes() {
        $('#emptyParticipants').removeClass('hidden');
        $('#scoringArea').addClass('hidden');
        $('#statusText').text('Sin participantes');
        $('#statusDot').removeClass('dot-yellow dot-green').addClass('dot-red');
    }

    function resetearPuntaje() {
        puntos = 4.0;
        yaVoto = false;
        $('#scoreDisplay').text('4.0');
        $('#btnGuardar').text('Guardar Puntaje').removeClass('locked').prop('disabled', false);
        $('.btn-score').prop('disabled', false);
        $('#btnSiguiente').prop('disabled', true);
    }

    function guardarEvaluacion() {
        if (yaVoto) { alert('Ya has votado por este participante.'); return; }
        if (idParticipanteActual <= 0 || idTorneo <= 0) { alert('Error: datos inválidos'); return; }

        $('#btnGuardar').prop('disabled', true).text('Guardando...');

        $.ajax({
            url: 'evaluar.php',
            type: 'POST',
            data: {
                id_participante: idParticipanteActual,
                id_torneo: idTorneo,
                puntos: puntos,
                categoria: categoriaActual
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    yaVoto = true;
                    $('#btnGuardar').text('Votación Registrada').addClass('locked');
                    $('.btn-score').prop('disabled', true);
                    $('#statusText').text('Votación registrada, esperando otros jueces');
                    verificarEstadoVotacion();
                } else {
                    alert('Error: ' + (resp.message || 'No se pudo guardar'));
                    $('#btnGuardar').prop('disabled', false).text('Guardar Puntaje');
                }
            },
            error: function() {
                alert('Error de conexión al guardar');
                $('#btnGuardar').prop('disabled', false).text('Guardar Puntaje');
            }
        });
    }

    function verificarEstadoVotacion() {
        if (idTorneo <= 0 || idParticipanteActual <= 0) return;

        $.ajax({
            url: 'checkVotacion.php',
            type: 'GET',
            data: { id_torneo: idTorneo, id_participante: idParticipanteActual },
            dataType: 'json',
            success: function(data) {
                if (data) {
                    var total = data.total_jueces || 0;
                    var votaron = data.total_evaluaciones || 0;
                    $('#juecesCount').text(votaron + '/' + total);

                    if (data.ya_voto && !yaVoto) {
                        yaVoto = true;
                        $('#btnGuardar').text('Votación Registrada').addClass('locked');
                        $('.btn-score').prop('disabled', true);
                    }

                    if (data.todos_votaron) {
                        $('#btnSiguiente').prop('disabled', false);
                        $('#statusText').text('Todos votaron - Listo para siguiente');
                        $('#statusDot').removeClass('dot-yellow dot-red').addClass('dot-green');
                        limpiarIntervalo();
                    } else {
                        $('#btnSiguiente').prop('disabled', true);
                    }
                }
            }
        });
    }

    function avanzarParticipante() {
        if (idTorneo <= 0) return;
        $('#btnSiguiente').prop('disabled', true).text('Avanzando...');

        $.ajax({
            url: 'avanzarParticipante.php',
            type: 'POST',
            data: { id_torneo: idTorneo },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    idParticipanteActual = data.id_participante;
                    resetearPuntaje();
                    iniciarEvaluacion();
                } else {
                    alert(data.message || 'No hay más participantes');
                    $('#btnSiguiente').text('Siguiente Participante');
                }
            },
            error: function() {
                alert('Error al avanzar');
                $('#btnSiguiente').text('Siguiente Participante');
            }
        });
    }

    function limpiarIntervalo() {
        if (checkingInterval) {
            clearInterval(checkingInterval);
            checkingInterval = null;
        }
    }

    function mostrarSeleccion() {
        $('#evaluationView').addClass('hidden');
        $('#torneoSelection').removeClass('hidden');
        limpiarIntervalo();
        $('#statusText').text('Selecciona un torneo');
        $('#statusDot').removeClass('dot-green dot-red').addClass('dot-yellow');
    }
});
</script>
</body>
</html>
