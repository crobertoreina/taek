-- Migración: Agregar columnas faltantes a tabla jueces
ALTER TABLE `jueces`
  ADD COLUMN IF NOT EXISTS `user` varchar(100) NOT NULL AFTER `ciudad`,
  ADD COLUMN IF NOT EXISTS `pass` varchar(255) NOT NULL AFTER `user`,
  ADD COLUMN IF NOT EXISTS `level` int(11) NOT NULL DEFAULT 2 AFTER `pass`;

-- Tabla de evaluación de puntajes
CREATE TABLE IF NOT EXISTS `evaluaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_torneo` int(11) NOT NULL,
  `id_participante` int(11) NOT NULL,
  `id_juez` int(11) NOT NULL,
  `categoria` varchar(50) NOT NULL DEFAULT 'poomsae',
  `puntos` decimal(3,1) NOT NULL DEFAULT 0.0,
  `fecha_evaluacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `id_torneo` (`id_torneo`),
  KEY `id_participante` (`id_participante`),
  KEY `id_juez` (`id_juez`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de asignación jueces a torneos
CREATE TABLE IF NOT EXISTS `torneojueces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idTorneo` int(11) NOT NULL,
  `idJuez` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idTorneo` (`idTorneo`),
  KEY `idJuez` (`idJuez`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para control de flujo de evaluación
CREATE TABLE IF NOT EXISTS `control_evaluacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_torneo` int(11) NOT NULL,
  `id_participante_actual` int(11) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `estado` varchar(20) NOT NULL DEFAULT 'evaluando',
  PRIMARY KEY (`id`),
  KEY `id_torneo` (`id_torneo`),
  KEY `id_participante_actual` (`id_participante_actual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de categorías de poomsae
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Kyorugi', 'Combate'),
(2, 'Poomsae', 'Formas'),
(3, 'Freestyle Poomsae', 'Formas Libres');

-- Tabla de participantes por categoría en torneo
CREATE TABLE IF NOT EXISTS `participante_categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_torneo` int(11) NOT NULL,
  `id_participante` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;