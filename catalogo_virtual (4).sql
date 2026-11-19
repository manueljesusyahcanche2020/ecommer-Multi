-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 18-11-2026 a las 15:51:49
-- Versión del servidor: 5.7.40
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `catalogo_virtual`
--

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `actualizar_direccion_cliente`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_direccion_cliente` (IN `p_direccion_id` BIGINT, IN `p_user_id` BIGINT, IN `p_nombre_recibe` VARCHAR(150), IN `p_telefono` VARCHAR(20), IN `p_calle` VARCHAR(191), IN `p_numero` VARCHAR(50), IN `p_cruzamientos` VARCHAR(191), IN `p_colonia` VARCHAR(150), IN `p_ciudad` VARCHAR(150), IN `p_estado` VARCHAR(150), IN `p_codigo_postal` VARCHAR(20), IN `p_referencia` TEXT, IN `p_latitud` DECIMAL(10,7), IN `p_longitud` DECIMAL(10,7), IN `p_principal` TINYINT)   BEGIN
    DECLARE v_existe INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_direccion_id IS NULL OR p_direccion_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dirección inválida.';
    END IF;

    IF p_user_id IS NULL OR p_user_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Usuario inválido.';
    END IF;

    IF p_nombre_recibe IS NULL OR TRIM(p_nombre_recibe) = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nombre de quien recibe es obligatorio.';
    END IF;

    IF p_calle IS NULL OR TRIM(p_calle) = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La calle es obligatoria.';
    END IF;

    START TRANSACTION;

    SELECT COUNT(*)
    INTO v_existe
    FROM direcciones
    WHERE id = p_direccion_id
      AND user_id = p_user_id;

    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dirección no encontrada.';
    END IF;

    IF p_principal = 1 THEN
        UPDATE direcciones
        SET principal = 0,
            updated_at = NOW()
        WHERE user_id = p_user_id
          AND id != p_direccion_id;
    END IF;

    UPDATE direcciones
    SET nombre_recibe = p_nombre_recibe,
        telefono = p_telefono,
        calle = p_calle,
        numero = p_numero,
        cruzamientos = p_cruzamientos,
        colonia = p_colonia,
        ciudad = p_ciudad,
        estado = p_estado,
        codigo_postal = p_codigo_postal,
        referencia = p_referencia,
        latitud = p_latitud,
        longitud = p_longitud,
        principal = p_principal,
        updated_at = NOW()
    WHERE id = p_direccion_id
      AND user_id = p_user_id;

    COMMIT;

    SELECT 'Dirección actualizada correctamente.' AS mensaje;
END$$

DROP PROCEDURE IF EXISTS `actualizar_estado_local`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_estado_local` (IN `p_local_id` BIGINT)   BEGIN
    DECLARE v_dia_hoy VARCHAR(20);
    DECLARE v_dia_ayer VARCHAR(20);
    DECLARE v_hora_actual TIME;

    SET v_hora_actual = CURTIME();

    SET v_dia_hoy = CASE DAYOFWEEK(CURDATE())
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END;

    SET v_dia_ayer = CASE DAYOFWEEK(DATE_SUB(CURDATE(), INTERVAL 1 DAY))
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END;

    UPDATE locales l
    LEFT JOIN local_horarios h ON h.local_id = l.id AND h.dia = v_dia_hoy
    LEFT JOIN local_horarios hy ON hy.local_id = l.id AND hy.dia = v_dia_ayer
    SET l.estado_local = CASE
            WHEN h.id IS NOT NULL AND h.cerrado = 0 AND h.hora_apertura IS NOT NULL AND h.hora_cierre IS NOT NULL AND h.hora_apertura <= h.hora_cierre AND v_hora_actual BETWEEN h.hora_apertura AND h.hora_cierre THEN 'abierto'
            WHEN h.id IS NOT NULL AND h.cerrado = 0 AND h.hora_apertura IS NOT NULL AND h.hora_cierre IS NOT NULL AND h.hora_apertura > h.hora_cierre AND (v_hora_actual >= h.hora_apertura OR v_hora_actual <= h.hora_cierre) THEN 'abierto'
            WHEN hy.id IS NOT NULL AND hy.cerrado = 0 AND hy.hora_apertura IS NOT NULL AND hy.hora_cierre IS NOT NULL AND hy.hora_apertura > hy.hora_cierre AND v_hora_actual <= hy.hora_cierre THEN 'abierto'
            ELSE 'cerrado'
        END,
        l.updated_at = NOW()
    WHERE l.id = p_local_id
      AND l.activo = 1
      AND l.estado_local <> 'pausado';

    SELECT 'Estado del local actualizado correctamente.' AS mensaje;
END$$

DROP PROCEDURE IF EXISTS `actualizar_estado_locales`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_estado_locales` ()   BEGIN
    DECLARE v_dia_hoy VARCHAR(20);
    DECLARE v_dia_ayer VARCHAR(20);
    DECLARE v_hora_actual TIME;

    SET v_hora_actual = CURTIME();

    SET v_dia_hoy = CASE DAYOFWEEK(CURDATE())
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END;

    SET v_dia_ayer = CASE DAYOFWEEK(DATE_SUB(CURDATE(), INTERVAL 1 DAY))
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END;

    UPDATE locales l
    LEFT JOIN local_horarios h 
        ON h.local_id = l.id 
        AND h.dia = v_dia_hoy

    LEFT JOIN local_horarios hy 
        ON hy.local_id = l.id 
        AND hy.dia = v_dia_ayer

    SET 
        l.estado_local = CASE

            -- Horario normal, ejemplo: 08:00 a 20:00
            WHEN h.id IS NOT NULL
                 AND h.cerrado = 0
                 AND h.hora_apertura IS NOT NULL
                 AND h.hora_cierre IS NOT NULL
                 AND h.hora_apertura <= h.hora_cierre
                 AND v_hora_actual BETWEEN h.hora_apertura AND h.hora_cierre
            THEN 'abierto'

            -- Horario que pasa de medianoche, ejemplo: 20:00 a 02:00
            WHEN h.id IS NOT NULL
                 AND h.cerrado = 0
                 AND h.hora_apertura IS NOT NULL
                 AND h.hora_cierre IS NOT NULL
                 AND h.hora_apertura > h.hora_cierre
                 AND (
                    v_hora_actual >= h.hora_apertura
                    OR v_hora_actual <= h.hora_cierre
                 )
            THEN 'abierto'

            -- Caso de madrugada por horario del día anterior
            WHEN hy.id IS NOT NULL
                 AND hy.cerrado = 0
                 AND hy.hora_apertura IS NOT NULL
                 AND hy.hora_cierre IS NOT NULL
                 AND hy.hora_apertura > hy.hora_cierre
                 AND v_hora_actual <= hy.hora_cierre
            THEN 'abierto'

            ELSE 'cerrado'
        END,

        l.updated_at = NOW()

    WHERE l.activo = 1
      AND l.estado_local <> 'pausado';
END$$

DROP PROCEDURE IF EXISTS `actualizar_estado_locales_vendedor`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_estado_locales_vendedor` (IN `p_vendedor_id` BIGINT)   BEGIN
    DECLARE v_dia_hoy VARCHAR(20);
    DECLARE v_dia_ayer VARCHAR(20);
    DECLARE v_hora_actual TIME;

    SET v_hora_actual = CURTIME();

    SET v_dia_hoy = CASE DAYOFWEEK(CURDATE())
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END;

    SET v_dia_ayer = CASE DAYOFWEEK(DATE_SUB(CURDATE(), INTERVAL 1 DAY))
        WHEN 1 THEN 'domingo'
        WHEN 2 THEN 'lunes'
        WHEN 3 THEN 'martes'
        WHEN 4 THEN 'miercoles'
        WHEN 5 THEN 'jueves'
        WHEN 6 THEN 'viernes'
        WHEN 7 THEN 'sabado'
    END;

    UPDATE locales l
    LEFT JOIN local_horarios h 
        ON h.local_id = l.id 
        AND h.dia = v_dia_hoy

    LEFT JOIN local_horarios hy 
        ON hy.local_id = l.id 
        AND hy.dia = v_dia_ayer

    SET 
        l.estado_local = CASE

            -- Horario normal: 08:00 a 20:00
            WHEN h.id IS NOT NULL
                 AND h.cerrado = 0
                 AND h.hora_apertura IS NOT NULL
                 AND h.hora_cierre IS NOT NULL
                 AND h.hora_apertura <= h.hora_cierre
                 AND v_hora_actual BETWEEN h.hora_apertura AND h.hora_cierre
            THEN 'abierto'

            -- Horario que pasa medianoche: 20:00 a 02:00
            WHEN h.id IS NOT NULL
                 AND h.cerrado = 0
                 AND h.hora_apertura IS NOT NULL
                 AND h.hora_cierre IS NOT NULL
                 AND h.hora_apertura > h.hora_cierre
                 AND (
                    v_hora_actual >= h.hora_apertura
                    OR v_hora_actual <= h.hora_cierre
                 )
            THEN 'abierto'

            -- Madrugada del horario de ayer
            WHEN hy.id IS NOT NULL
                 AND hy.cerrado = 0
                 AND hy.hora_apertura IS NOT NULL
                 AND hy.hora_cierre IS NOT NULL
                 AND hy.hora_apertura > hy.hora_cierre
                 AND v_hora_actual <= hy.hora_cierre
            THEN 'abierto'

            ELSE 'cerrado'
        END,

        l.updated_at = NOW()

    WHERE l.activo = 1
      AND l.vendedor_id = p_vendedor_id
      AND l.estado_local <> 'pausado';
END$$

DROP PROCEDURE IF EXISTS `cancelar_pedido_cliente`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `cancelar_pedido_cliente` (IN `p_pedido_id` BIGINT, IN `p_cliente_id` BIGINT)   BEGIN
    DECLARE v_estado VARCHAR(50);
    DECLARE v_existe INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT COUNT(*)
    INTO v_existe
    FROM pedidos
    WHERE id = p_pedido_id
      AND cliente_id = p_cliente_id;

    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pedido no encontrado.';
    END IF;

    SELECT estado_pedido
    INTO v_estado
    FROM pedidos
    WHERE id = p_pedido_id
      AND cliente_id = p_cliente_id
    FOR UPDATE;

    IF v_estado NOT IN ('pendiente', 'aceptado') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este pedido ya no se puede cancelar.';
    END IF;

    UPDATE producto_stocks ps
    INNER JOIN pedido_detalles pd
        ON pd.producto_id = ps.producto_id
       AND (
            ps.variante_id = pd.variante_id
            OR (ps.variante_id IS NULL AND pd.variante_id IS NULL)
       )
    SET ps.stock = ps.stock + pd.cantidad
    WHERE pd.pedido_id = p_pedido_id
      AND ps.maneja_stock = 1;

    UPDATE pedidos
    SET estado_pedido = 'cancelado',
        fecha_cancelado = NOW(),
        updated_at = NOW()
    WHERE id = p_pedido_id
      AND cliente_id = p_cliente_id;

    COMMIT;

    SELECT 'Pedido cancelado correctamente. El stock fue devuelto.' AS mensaje;
END$$

DROP PROCEDURE IF EXISTS `crear_direccion_cliente`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_direccion_cliente` (IN `p_user_id` BIGINT, IN `p_nombre_recibe` VARCHAR(150), IN `p_telefono` VARCHAR(20), IN `p_calle` VARCHAR(191), IN `p_numero` VARCHAR(50), IN `p_cruzamientos` VARCHAR(191), IN `p_colonia` VARCHAR(150), IN `p_ciudad` VARCHAR(150), IN `p_estado` VARCHAR(150), IN `p_codigo_postal` VARCHAR(20), IN `p_referencia` TEXT, IN `p_latitud` DECIMAL(10,7), IN `p_longitud` DECIMAL(10,7), IN `p_principal` TINYINT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_user_id IS NULL OR p_user_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Usuario inválido.';
    END IF;

    IF p_nombre_recibe IS NULL OR TRIM(p_nombre_recibe) = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El nombre de quien recibe es obligatorio.';
    END IF;

    IF p_calle IS NULL OR TRIM(p_calle) = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La calle es obligatoria.';
    END IF;

    START TRANSACTION;

    IF p_principal = 1 THEN
        UPDATE direcciones
        SET principal = 0,
            updated_at = NOW()
        WHERE user_id = p_user_id;
    END IF;

    INSERT INTO direcciones (
        user_id,
        nombre_recibe,
        telefono,
        calle,
        numero,
        cruzamientos,
        colonia,
        ciudad,
        estado,
        codigo_postal,
        referencia,
        latitud,
        longitud,
        principal,
        created_at,
        updated_at
    ) VALUES (
        p_user_id,
        p_nombre_recibe,
        p_telefono,
        p_calle,
        p_numero,
        p_cruzamientos,
        p_colonia,
        p_ciudad,
        p_estado,
        p_codigo_postal,
        p_referencia,
        p_latitud,
        p_longitud,
        p_principal,
        NOW(),
        NOW()
    );

    COMMIT;

    SELECT 'Dirección guardada correctamente.' AS mensaje;
END$$

DROP PROCEDURE IF EXISTS `eliminar_direccion_cliente`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_direccion_cliente` (IN `p_direccion_id` BIGINT, IN `p_user_id` BIGINT)   BEGIN
    DECLARE v_existe INT DEFAULT 0;
    DECLARE v_usada INT DEFAULT 0;

    IF p_direccion_id IS NULL OR p_direccion_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dirección inválida.';
    END IF;

    IF p_user_id IS NULL OR p_user_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Usuario inválido.';
    END IF;

    SELECT COUNT(*)
    INTO v_existe
    FROM direcciones
    WHERE id = p_direccion_id
      AND user_id = p_user_id;

    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dirección no encontrada.';
    END IF;

    SELECT COUNT(*)
    INTO v_usada
    FROM pedidos
    WHERE direccion_id = p_direccion_id;

    IF v_usada > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No puedes eliminar esta dirección porque ya está ligada a un pedido.';
    END IF;

    DELETE FROM direcciones
    WHERE id = p_direccion_id
      AND user_id = p_user_id;

    SELECT 'Dirección eliminada correctamente.' AS mensaje;
END$$

DROP PROCEDURE IF EXISTS `estadisticas_productos_mas_vendidos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `estadisticas_productos_mas_vendidos` (IN `p_vendedor_id` BIGINT, IN `p_limite` INT)   BEGIN
    DECLARE v_limite INT;

    IF p_vendedor_id IS NULL OR p_vendedor_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Vendedor inválido.';
    END IF;

    -- Se acota el límite entre 1 y 50 dentro del propio procedimiento
    SET v_limite = LEAST(GREATEST(COALESCE(p_limite, 5), 1), 50);

    SELECT
        pd.producto_id,
        pd.nombre_producto,
        SUM(pd.cantidad) AS total_vendido,
        SUM(pd.subtotal) AS total_recaudado
    FROM pedido_detalles pd
    INNER JOIN pedidos p ON p.id = pd.pedido_id
    INNER JOIN locales l ON l.id = p.local_id
    WHERE l.vendedor_id = p_vendedor_id
      AND p.estado_pedido = 'entregado'
    GROUP BY pd.producto_id, pd.nombre_producto
    ORDER BY total_vendido DESC
    LIMIT v_limite;
END$$

DROP PROCEDURE IF EXISTS `estadisticas_resumen_vendedor`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `estadisticas_resumen_vendedor` (IN `p_vendedor_id` BIGINT, IN `p_fecha_inicio` DATE, IN `p_fecha_fin` DATE)   BEGIN
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;

    IF p_vendedor_id IS NULL OR p_vendedor_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Vendedor inválido.';
    END IF;

    SET v_fecha_inicio = COALESCE(p_fecha_inicio, DATE_SUB(CURDATE(), INTERVAL 30 DAY));
    SET v_fecha_fin = COALESCE(p_fecha_fin, CURDATE());

    IF v_fecha_inicio > v_fecha_fin THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de inicio no puede ser mayor que la fecha fin.';
    END IF;

    SELECT
        COUNT(*) AS total_pedidos,
        COALESCE(SUM(CASE WHEN p.estado_pedido = 'entregado' THEN p.total ELSE 0 END), 0) AS total_ingresos,
        COALESCE(SUM(CASE WHEN p.estado_pedido = 'entregado' THEN 1 ELSE 0 END), 0) AS pedidos_entregados,
        COALESCE(SUM(CASE WHEN p.estado_pedido = 'cancelado' THEN 1 ELSE 0 END), 0) AS pedidos_cancelados
    FROM pedidos p
    INNER JOIN locales l ON l.id = p.local_id
    WHERE l.vendedor_id = p_vendedor_id
      AND p.created_at >= v_fecha_inicio
      AND p.created_at < DATE_ADD(v_fecha_fin, INTERVAL 1 DAY);
END$$

DROP PROCEDURE IF EXISTS `estadisticas_ventas_por_periodo`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `estadisticas_ventas_por_periodo` (IN `p_vendedor_id` BIGINT, IN `p_dias` INT)   BEGIN
    DECLARE v_dias INT;

    IF p_vendedor_id IS NULL OR p_vendedor_id <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Vendedor inválido.';
    END IF;

    -- Se acota el rango entre 1 y 90 días dentro del propio procedimiento
    SET v_dias = LEAST(GREATEST(COALESCE(p_dias, 7), 1), 90);

    SELECT
        DATE(p.created_at) AS fecha,
        COALESCE(SUM(p.total), 0) AS total_ventas,
        COUNT(*) AS cantidad_pedidos
    FROM pedidos p
    INNER JOIN locales l ON l.id = p.local_id
    WHERE l.vendedor_id = p_vendedor_id
      AND p.estado_pedido = 'entregado'
      AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL (v_dias - 1) DAY)
    GROUP BY DATE(p.created_at)
    ORDER BY fecha ASC;
END$$

DROP PROCEDURE IF EXISTS `toggle_favorito`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `toggle_favorito` (IN `p_cliente_id` BIGINT, IN `p_producto_id` BIGINT)   BEGIN
    DECLARE v_existe INT DEFAULT 0;

    SELECT COUNT(*)
    INTO v_existe
    FROM favoritos
    WHERE cliente_id = p_cliente_id
      AND producto_id = p_producto_id;

    IF v_existe > 0 THEN
        DELETE FROM favoritos
        WHERE cliente_id = p_cliente_id
          AND producto_id = p_producto_id;

        SELECT 0 AS favorito, 'Producto quitado de favoritos.' AS mensaje;
    ELSE
        INSERT INTO favoritos (
            cliente_id,
            producto_id,
            created_at,
            updated_at
        ) VALUES (
            p_cliente_id,
            p_producto_id,
            NOW(),
            NOW()
        );

        SELECT 1 AS favorito, 'Producto agregado a favoritos.' AS mensaje;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carritos`
--

DROP TABLE IF EXISTS `carritos`;
CREATE TABLE IF NOT EXISTS `carritos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `carritos_cliente_id_foreign` (`cliente_id`),
  KEY `carritos_local_id_foreign` (`local_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carritos`
--

INSERT INTO `carritos` (`id`, `cliente_id`, `local_id`, `estado`, `total`, `created_at`, `updated_at`) VALUES
(3, 3, 8, 'comprado', '200.00', '2026-10-01 22:01:26', '2026-10-01 22:01:49'),
(4, 3, 8, 'comprado', '600.00', '2026-10-02 00:00:19', '2026-10-02 00:17:18'),
(5, 3, 8, 'comprado', '200.00', '2026-10-02 00:17:37', '2026-10-02 00:17:53'),
(6, 3, 8, 'comprado', '400.00', '2026-10-02 00:18:20', '2026-10-02 00:39:25'),
(7, 3, 8, 'activo', '600.00', '2026-10-19 19:44:55', '2026-10-20 04:59:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_items`
--

DROP TABLE IF EXISTS `carrito_items`;
CREATE TABLE IF NOT EXISTS `carrito_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `carrito_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `variante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `carrito_items_carrito_id_foreign` (`carrito_id`),
  KEY `carrito_items_producto_id_foreign` (`producto_id`),
  KEY `carrito_items_variante_id_foreign` (`variante_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito_items`
--

INSERT INTO `carrito_items` (`id`, `carrito_id`, `producto_id`, `variante_id`, `cantidad`, `precio_unitario`, `subtotal`, `notas`, `created_at`, `updated_at`) VALUES
(3, 3, 2, 3, 1, '200.00', '200.00', NULL, '2026-10-01 22:01:26', '2026-10-01 22:01:26'),
(4, 4, 2, 4, 2, '200.00', '400.00', NULL, '2026-10-02 00:00:19', '2026-10-02 00:16:27'),
(5, 4, 2, 3, 1, '200.00', '200.00', NULL, '2026-10-02 00:16:43', '2026-10-02 00:16:43'),
(6, 5, 1, 1, 1, '0.00', '0.00', NULL, '2026-10-02 00:17:37', '2026-10-02 00:17:37'),
(7, 5, 2, 3, 1, '200.00', '200.00', NULL, '2026-10-02 00:17:43', '2026-10-02 00:17:43'),
(9, 6, 2, 3, 2, '200.00', '400.00', NULL, '2026-10-02 00:19:16', '2026-10-02 00:39:07'),
(10, 7, 1, 1, 1, '200.00', '200.00', NULL, '2026-10-19 19:44:55', '2026-10-20 04:59:06'),
(11, 7, 2, 3, 1, '200.00', '200.00', NULL, '2026-10-19 19:45:27', '2026-10-19 19:46:36'),
(12, 7, 4, 6, 1, '200.00', '200.00', NULL, '2026-10-20 04:59:04', '2026-10-20 04:59:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `local_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `imagen` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categorias_local_id_index` (`local_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `local_id`, `nombre`, `descripcion`, `imagen`, `orden`, `activo`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Comida', 'Productos de comida del local', NULL, 1, 1, '2026-06-15 18:08:17', '2026-06-15 18:08:17'),
(2, NULL, 'Bebidas', 'Refrescos, aguas y bebidas', NULL, 2, 1, '2026-06-15 18:08:17', '2026-06-15 18:08:17'),
(3, NULL, 'Postres', 'Dulces, pasteles y postres', NULL, 3, 1, '2026-06-15 18:08:17', '2026-06-15 18:08:17'),
(4, NULL, 'Promociones', 'Ofertas especiales del local', NULL, 4, 1, '2026-06-15 18:08:17', '2026-06-15 18:08:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

DROP TABLE IF EXISTS `direcciones`;
CREATE TABLE IF NOT EXISTS `direcciones` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nombre_recibe` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calle` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cruzamientos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colonia` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_postal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia` text COLLATE utf8mb4_unicode_ci,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `direcciones_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id`, `user_id`, `nombre_recibe`, `telefono`, `calle`, `numero`, `cruzamientos`, `colonia`, `ciudad`, `estado`, `codigo_postal`, `referencia`, `latitud`, `longitud`, `principal`, `created_at`, `updated_at`) VALUES
(2, 3, 'casa de abuela', '9971345022', 'Calle 28', 'SN', '43 y 65', 'Peto', 'Peto', 'Yucatán', '97930', 'frente a una tienda', '20.1167262', '-88.9217765', 1, '2026-05-19 03:45:21', '2026-11-04 17:17:10'),
(3, 3, 'casa', '9971345022', 'Calle 29', '208', NULL, 'Centro', 'Peto', 'Yucatán', '97930', NULL, '20.1268609', '-88.9226273', 0, '2026-05-19 10:16:13', '2026-11-04 17:17:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorito_cliente_producto_unique` (`cliente_id`,`producto_id`),
  KEY `fk_favoritos_productos` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locales`
--

DROP TABLE IF EXISTS `locales`;
CREATE TABLE IF NOT EXISTS `locales` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendedor_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portada` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `ciudad` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `minimo_pedido` decimal(10,2) NOT NULL DEFAULT '0.00',
  `costo_envio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `permite_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `permite_recoger` tinyint(1) NOT NULL DEFAULT '1',
  `estado_local` enum('abierto','cerrado','pausado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cerrado',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locales_vendedor_id_index` (`vendedor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `locales`
--

INSERT INTO `locales` (`id`, `vendedor_id`, `nombre`, `descripcion`, `logo`, `portada`, `telefono`, `whatsapp`, `correo`, `direccion`, `ciudad`, `estado`, `latitud`, `longitud`, `minimo_pedido`, `costo_envio`, `permite_delivery`, `permite_recoger`, `estado_local`, `activo`, `created_at`, `updated_at`) VALUES
(1, 2, 'juan y cesar prueva de hora', 'ropa1', NULL, NULL, '9971345022', '9971345022', NULL, '42 x 41 y 43', 'peto', 'yucatan', NULL, NULL, '0.00', '12.00', 1, 1, 'abierto', 1, '2026-05-18 04:17:01', '2026-11-18 15:49:41'),
(2, 2, 'juan', 'ropa', 'locales/logos/U5HXzmnLRZaeS7pj7QMw0pmHVm32i17QySn2ujdJ.png', 'locales/portadas/hYrOfI6FFdQ2o2iLAvnsSqCSnd7fIFjqYRwO1jez.png', '9971345022', '9971345022', NULL, '42 x 41 y 43', 'peto', 'yucatan', NULL, NULL, '0.00', '11.00', 0, 1, 'abierto', 1, '2026-05-18 04:17:38', '2026-11-18 15:49:41'),
(3, 2, 'sam miguel', 'pero', 'locales/logos/nG934TnevN6sA4VXuJMyF7fjXNHbYkOMMlSd1pcQ.png', NULL, '9971345022', '9971345022', NULL, '42 x 41 y 52', 'Peto', 'Yucatán', NULL, NULL, '20.00', '5.00', 1, 1, 'abierto', 1, '2026-05-18 04:38:00', '2026-11-18 15:49:41'),
(5, 2, 'cesar peto', 'ropas', 'locales/logos/5MxtFKvBnIhkq98accbCinSueNyxlOMj57xGNJd8.png', 'locales/portadas/KiGVodxKKbsvO4qNRKGBq8RCQOqUQ96JQi5Rb19x.png', '9971345022', '9971345022', NULL, '42 x 41 y 43', 'Peto', 'Yucatán', NULL, NULL, '11.00', '10.99', 0, 1, 'abierto', 1, '2026-05-21 10:15:18', '2026-11-18 15:49:41'),
(6, 2, 'mama', NULL, NULL, NULL, NULL, NULL, NULL, 'peto yucatan', NULL, NULL, NULL, NULL, '0.00', '0.00', 0, 1, 'abierto', 1, '2026-09-23 22:44:36', '2026-11-18 15:49:41'),
(7, 2, 'escuela ut del map', NULL, NULL, NULL, NULL, '9971345022', NULL, '33VM+XR, 97930 Peto, Yuc., México', 'Peto', 'Yucatán', NULL, NULL, '0.00', '0.00', 0, 1, 'abierto', 1, '2026-09-23 23:38:51', '2026-11-18 15:49:41'),
(8, 2, 'ut del maya', 'papasfritas', NULL, NULL, '9971345022', NULL, NULL, '33VM+XR, 97930 Peto, Yuc., México', 'Peto', 'Yucatán', NULL, NULL, '0.00', '0.00', 1, 1, 'abierto', 1, '2026-09-23 23:43:29', '2026-11-18 15:49:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `local_horarios`
--

DROP TABLE IF EXISTS `local_horarios`;
CREATE TABLE IF NOT EXISTS `local_horarios` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `dia` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL,
  `cerrado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `local_dia_unique` (`local_id`,`dia`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `local_horarios`
--

INSERT INTO `local_horarios` (`id`, `local_id`, `dia`, `hora_apertura`, `hora_cierre`, `cerrado`, `created_at`, `updated_at`) VALUES
(1, 3, 'lunes', NULL, NULL, 1, '2026-05-18 04:38:00', '2026-05-18 04:48:04'),
(2, 3, 'martes', '08:00:00', '23:34:00', 0, '2026-05-18 04:38:00', '2026-05-20 11:07:40'),
(3, 3, 'miercoles', '08:00:00', '20:00:00', 0, '2026-05-18 04:38:00', '2026-05-20 11:07:40'),
(4, 3, 'jueves', '08:00:00', '20:00:00', 0, '2026-05-18 04:38:00', '2026-05-20 11:07:40'),
(5, 3, 'viernes', '08:00:00', '20:00:00', 0, '2026-05-18 04:38:00', '2026-05-20 11:07:40'),
(6, 3, 'sabado', '08:00:00', '20:00:00', 0, '2026-05-18 04:38:00', '2026-05-20 11:07:40'),
(7, 3, 'domingo', '08:00:00', '20:00:00', 0, '2026-05-18 04:38:00', '2026-05-20 11:07:40'),
(8, 2, 'lunes', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(9, 2, 'martes', '08:00:00', '23:34:00', 1, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(10, 2, 'miercoles', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(11, 2, 'jueves', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(12, 2, 'viernes', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(13, 2, 'sabado', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(14, 2, 'domingo', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:38', '2026-05-18 04:58:38'),
(15, 1, 'lunes', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(16, 1, 'martes', '08:00:00', '23:49:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(17, 1, 'miercoles', '01:00:00', '20:00:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(18, 1, 'jueves', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(19, 1, 'viernes', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(20, 1, 'sabado', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(21, 1, 'domingo', '08:00:00', '20:00:00', 0, '2026-05-18 04:58:53', '2026-10-20 04:35:13'),
(22, 5, 'lunes', '08:00:00', '20:00:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(23, 5, 'martes', '08:00:00', '20:00:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(24, 5, 'miercoles', '08:00:00', '23:55:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(25, 5, 'jueves', '08:00:00', '20:00:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(26, 5, 'viernes', '08:00:00', '20:00:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(27, 5, 'sabado', '08:00:00', '20:00:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(28, 5, 'domingo', '08:00:00', '20:00:00', 0, '2026-05-21 10:15:18', '2026-05-21 22:25:24'),
(29, 6, 'lunes', NULL, NULL, 1, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(30, 6, 'martes', '08:00:00', '20:00:00', 0, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(31, 6, 'miercoles', '08:00:00', '20:00:00', 0, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(32, 6, 'jueves', '08:00:00', '20:00:00', 0, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(33, 6, 'viernes', '08:00:00', '20:00:00', 0, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(34, 6, 'sabado', '08:00:00', '20:00:00', 0, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(35, 6, 'domingo', '08:00:00', '20:00:00', 0, '2026-09-23 22:44:36', '2026-10-20 04:35:52'),
(36, 7, 'lunes', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(37, 7, 'martes', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(38, 7, 'miercoles', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(39, 7, 'jueves', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(40, 7, 'viernes', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(41, 7, 'sabado', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(42, 7, 'domingo', '08:00:00', '20:00:00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(43, 8, 'lunes', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29'),
(44, 8, 'martes', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29'),
(45, 8, 'miercoles', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29'),
(46, 8, 'jueves', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29'),
(47, 8, 'viernes', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29'),
(48, 8, 'sabado', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29'),
(49, 8, 'domingo', '08:00:00', '20:00:00', 0, '2026-09-23 23:43:29', '2026-09-23 23:43:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `local_imagenes`
--

DROP TABLE IF EXISTS `local_imagenes`;
CREATE TABLE IF NOT EXISTS `local_imagenes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `imagen` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `local_imagenes_local_id_index` (`local_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `local_zonas_entrega`
--

DROP TABLE IF EXISTS `local_zonas_entrega`;
CREATE TABLE IF NOT EXISTS `local_zonas_entrega` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `ciudad` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `costo_envio` decimal(10,2) DEFAULT '0.00',
  `activo` tinyint(4) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_local_zonas_entrega_locales` (`local_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `local_zonas_entrega`
--

INSERT INTO `local_zonas_entrega` (`id`, `local_id`, `ciudad`, `estado`, `costo_envio`, `activo`, `created_at`, `updated_at`) VALUES
(1, 5, 'Peto', 'Yucatán', '10.99', 0, '2026-05-21 10:15:18', '2026-09-23 21:43:08'),
(3, 5, 'Tzucacab', 'Yucatán', '11.00', 0, '2026-05-21 11:36:34', '2026-09-23 21:43:08'),
(4, 2, 'Peto', 'Yucatán', '11.00', 0, '2026-05-21 12:56:07', '2026-09-23 21:43:41'),
(5, 7, 'Peto', 'Yucatán', '0.00', 0, '2026-09-23 23:38:51', '2026-09-23 23:38:51'),
(6, 8, 'Peto', 'Yucatán', '10.32', 1, '2026-09-23 23:43:29', '2026-10-02 00:03:47'),
(7, 3, 'Peto', 'Yucatán', '10.00', 1, '2026-11-18 21:48:12', '2026-11-18 21:48:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispositivo_id` int(11) DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `nivel` enum('INFO','WARNING','ERROR') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dispositivo_id` (`dispositivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruta` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT '0',
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_ruta_unique` (`ruta`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `menus`
--

INSERT INTO `menus` (`id`, `nombre`, `icono`, `ruta`, `orden`, `estado`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', 'fa-solid fa-house', 'vendedor.dashboardVer', 1, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(2, 'Usuarios', 'fa-solid fa-users', 'usuarios.index', 2, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(3, 'Roles', 'fa-solid fa-user-shield', 'roles.index', 3, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(4, 'Menús', 'fa-solid fa-list', 'menus.index', 4, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(5, 'Permisos', 'fa-solid fa-lock', 'permisos.index', 5, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(6, 'Mis Locales', 'fa-solid fa-store', 'vendedor.dashboard.locales', 6, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(7, 'Crear Local', 'fa-solid fa-plus', 'locales.create', 7, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(8, 'Categorías', 'fa-solid fa-tags', 'categorias.index', 8, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(9, 'Productos', 'fa-solid fa-box', 'vendedor.productos.todos', 9, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(10, 'Stock', 'fa-solid fa-boxes-stacked', 'vendedor.stock.index', 10, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(11, 'Pedidos Recibidos', 'fa-solid fa-receipt', 'vendedor.pedidos.index', 11, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(12, 'Catálogo', 'fa-solid fa-shop', 'catalogo.index', 12, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(13, 'Carrito', 'fa-solid fa-cart-shopping', 'carrito.index', 13, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(14, 'Mis Pedidos', 'fa-solid fa-bag-shopping', 'cliente.pedidos.ver', 14, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(15, 'Mis Direcciones', 'fa-solid fa-location-dot', 'direcciones.index', 15, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(16, 'Favoritos', 'fa-solid fa-heart', 'cliente.favoritos.index', 16, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(17, 'Reseñas', 'fa-solid fa-star', 'resenas.index', 17, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(18, 'inicio', 'fa-solid fa-house', 'cliente.dashboard', 2, 1, NULL, NULL),
(19, 'pedidos por enviar', 'fa-solid fa-truck-fast', 'vendedor.pedidos.enviar', 20, 1, NULL, NULL),
(20, 'Estadisticas', 'bi bi-activity', 'vendedor.estadisticas', 4, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leida` tinyint(4) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `leida` (`leida`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `user_id`, `titulo`, `mensaje`, `tipo`, `url`, `leida`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nuevo pedido recibido', 'Tienes un nuevo pedido de $200.00 en ut del maya', 'pedido', 'http://127.0.0.1:8000/vendedor/pedidos/1', 1, '2026-10-01 22:01:49', '2026-10-02 00:38:43'),
(2, 2, 'Nuevo pedido recibido', 'Tienes un nuevo pedido de $600.00 en ut del maya', 'pedido', 'http://127.0.0.1:8000/vendedor/pedidos/2', 1, '2026-10-02 00:17:18', '2026-10-02 00:38:41'),
(3, 2, 'Nuevo pedido recibido', 'Tienes un nuevo pedido de $210.32 en ut del maya', 'pedido', 'http://127.0.0.1:8000/vendedor/pedidos/3', 1, '2026-10-02 00:17:53', '2026-10-02 00:38:38'),
(4, 3, 'Tu pedido cambió de estado', 'Tu pedido PED-20261001181753-VTIT ahora está: Entregado. Nota del vendedor: pagado', 'estado', 'http://127.0.0.1:8000/cliente/pedidos/3', 1, '2026-10-02 00:24:08', '2026-11-07 01:56:05'),
(5, 3, 'Tu pedido cambió de estado', 'Tu pedido PED-20261001181718-CECT ahora está: Entregado. Nota del vendedor: pagado', 'estado', 'http://127.0.0.1:8000/cliente/pedidos/2', 1, '2026-10-02 00:24:35', '2026-10-02 00:39:33'),
(6, 3, 'Tu pedido cambió de estado', 'Tu pedido PED-20261001160149-U1N9 ahora está: Entregado. Nota del vendedor: pagado', 'estado', 'http://127.0.0.1:8000/cliente/pedidos/1', 1, '2026-10-02 00:24:54', '2026-10-20 12:42:51'),
(7, 2, 'Nuevo pedido recibido', 'Tienes un nuevo pedido de $410.32 en ut del maya', 'pedido', 'http://127.0.0.1:8000/vendedor/pedidos/4', 1, '2026-10-02 00:39:25', '2026-10-02 00:39:49'),
(8, 3, 'Tu pedido cambió de estado', 'Tu pedido PED-20261001183925-HDG7 ahora está: Aceptado.', 'estado', 'http://127.0.0.1:8000/cliente/pedidos/4', 1, '2026-10-02 00:40:25', '2026-11-04 23:18:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `direccion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tipo_entrega` enum('delivery','recoger') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'delivery',
  `metodo_pago` enum('efectivo','transferencia','tarjeta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `estado_pago` enum('pendiente','pagado','fallido','reembolsado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `estado_pedido` enum('pendiente','aceptado','preparando','en_camino','entregado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `costo_envio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notas_cliente` text COLLATE utf8mb4_unicode_ci,
  `notas_vendedor` text COLLATE utf8mb4_unicode_ci,
  `fecha_aceptado` timestamp NULL DEFAULT NULL,
  `fecha_entregado` timestamp NULL DEFAULT NULL,
  `fecha_cancelado` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedidos_codigo_unique` (`codigo`),
  KEY `pedidos_cliente_id_index` (`cliente_id`),
  KEY `pedidos_local_id_index` (`local_id`),
  KEY `pedidos_direccion_id_index` (`direccion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `codigo`, `cliente_id`, `local_id`, `direccion_id`, `tipo_entrega`, `metodo_pago`, `estado_pago`, `estado_pedido`, `subtotal`, `costo_envio`, `descuento`, `total`, `notas_cliente`, `notas_vendedor`, `fecha_aceptado`, `fecha_entregado`, `fecha_cancelado`, `created_at`, `updated_at`) VALUES
(1, 'PED-20261001160149-U1N9', 3, 8, NULL, 'recoger', 'transferencia', 'pendiente', 'entregado', '200.00', '0.00', '0.00', '200.00', NULL, 'pagado', NULL, '2026-10-02 00:24:54', NULL, '2026-10-01 22:01:49', '2026-10-02 00:24:54'),
(2, 'PED-20261001181718-CECT', 3, 8, NULL, 'recoger', 'efectivo', 'pendiente', 'entregado', '600.00', '0.00', '0.00', '600.00', NULL, 'pagado', NULL, '2026-10-02 00:24:35', NULL, '2026-10-02 00:17:18', '2026-10-02 00:24:35'),
(3, 'PED-20261001181753-VTIT', 3, 8, 3, 'delivery', 'efectivo', 'pendiente', 'entregado', '200.00', '10.32', '0.00', '210.32', NULL, 'pagado', NULL, '2026-10-02 00:24:08', NULL, '2026-10-02 00:17:53', '2026-10-02 00:24:08'),
(4, 'PED-20261001183925-HDG7', 3, 8, 3, 'delivery', 'transferencia', 'pendiente', 'aceptado', '400.00', '10.32', '0.00', '410.32', NULL, NULL, '2026-10-02 00:40:25', NULL, NULL, '2026-10-02 00:39:25', '2026-10-02 00:40:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

DROP TABLE IF EXISTS `pedido_detalles`;
CREATE TABLE IF NOT EXISTS `pedido_detalles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `variante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_producto` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_variante` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_detalles_pedido_id_index` (`pedido_id`),
  KEY `pedido_detalles_producto_id_index` (`producto_id`),
  KEY `pedido_detalles_variante_id_index` (`variante_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pedido_detalles`
--

INSERT INTO `pedido_detalles` (`id`, `pedido_id`, `producto_id`, `variante_id`, `nombre_producto`, `nombre_variante`, `cantidad`, `precio_unitario`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 3, 'hello', 'tamaño: 180 cm', 1, '200.00', '200.00', '2026-10-01 22:01:49', '2026-10-01 22:01:49'),
(2, 2, 2, 4, 'hello', 'hello: cintra 20', 2, '200.00', '400.00', '2026-10-02 00:17:18', '2026-10-02 00:17:18'),
(3, 2, 2, 3, 'hello', 'tamaño: 180 cm', 1, '200.00', '200.00', '2026-10-02 00:17:18', '2026-10-02 00:17:18'),
(4, 3, 1, 1, 'pizaa', NULL, 1, '0.00', '0.00', '2026-10-02 00:17:53', '2026-10-02 00:17:53'),
(5, 3, 2, 3, 'hello', 'tamaño: 180 cm', 1, '200.00', '200.00', '2026-10-02 00:17:53', '2026-10-02 00:17:53'),
(6, 4, 2, 3, 'hello', 'tamaño: 180 cm', 2, '200.00', '400.00', '2026-10-02 00:39:25', '2026-10-02 00:39:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `categoria_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `destacado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `productos_local_id_index` (`local_id`),
  KEY `productos_categoria_id_index` (`categoria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `local_id`, `categoria_id`, `nombre`, `descripcion`, `activo`, `destacado`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 'pizaa', 'hello', 1, 0, '2026-10-01 00:29:15', '2026-10-20 03:58:55'),
(2, 8, 1, 'hello', 'hello como estas prueva 1', 1, 0, '2026-10-01 00:42:43', '2026-10-01 02:51:09'),
(3, 8, 4, 'peluche gato tiburocin', 'esta bonito el peluche si tambien te gusto compra', 1, 0, '2026-10-20 04:43:25', '2026-10-20 04:43:25'),
(4, 8, 4, 'conejo tiburoncin', 'conejo con zanahoria', 1, 0, '2026-10-20 04:45:36', '2026-10-20 04:45:36');

--
-- Disparadores `productos`
--
DROP TRIGGER IF EXISTS `trg_no_activar_producto_local_pausado`;
DELIMITER $$
CREATE TRIGGER `trg_no_activar_producto_local_pausado` BEFORE UPDATE ON `productos` FOR EACH ROW BEGIN
    DECLARE v_estado_local VARCHAR(50);
    DECLARE v_activo_local TINYINT DEFAULT 0;

    IF NEW.activo = 1 THEN
        SELECT estado_local, activo
        INTO v_estado_local, v_activo_local
        FROM locales
        WHERE id = NEW.local_id
        LIMIT 1;

        IF v_estado_local = 'pausado' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No puedes activar este producto porque el local está pausado.';
        END IF;

        IF v_activo_local = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No puedes activar este producto porque el local está inactivo.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagenes`
--

DROP TABLE IF EXISTS `producto_imagenes`;
CREATE TABLE IF NOT EXISTS `producto_imagenes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `imagen` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `producto_imagenes_producto_id_index` (`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `producto_imagenes`
--

INSERT INTO `producto_imagenes` (`id`, `producto_id`, `imagen`, `principal`, `orden`, `created_at`, `updated_at`) VALUES
(1, 2, 'productos/i3zQkBxoEA3YfaHgQvMOS7YqfQfEsdRspW2ef8yQ.png', 1, 0, '2026-10-01 02:52:58', '2026-10-01 02:52:58'),
(2, 3, 'productos/Jpy8IlmeHI0m7H5E2lfrM4k1GjD5eXWfMVQk1lhj.jpg', 1, 0, '2026-10-20 04:43:25', '2026-10-20 04:43:25'),
(3, 4, 'productos/OB5oB1urU4wRT8rFQrH93Wgnfn3fERCkh3xA7mR0.jpg', 1, 0, '2026-10-20 04:45:36', '2026-10-20 04:45:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_stocks`
--

DROP TABLE IF EXISTS `producto_stocks`;
CREATE TABLE IF NOT EXISTS `producto_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `variante_id` bigint(20) UNSIGNED DEFAULT NULL,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `stock_minimo` int(11) NOT NULL DEFAULT '0',
  `maneja_stock` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_producto_variante_local_unique` (`producto_id`,`variante_id`,`local_id`),
  KEY `producto_stocks_variante_id_index` (`variante_id`),
  KEY `producto_stocks_local_id_index` (`local_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `producto_stocks`
--

INSERT INTO `producto_stocks` (`id`, `producto_id`, `variante_id`, `local_id`, `stock`, `stock_minimo`, `maneja_stock`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 8, 29, 10, 1, '2026-10-01 00:29:15', '2026-10-02 00:17:53'),
(3, 2, 3, 8, 5, 1, 1, '2026-10-01 02:49:56', '2026-10-02 00:39:25'),
(4, 2, 4, 8, 18, 3, 1, '2026-10-01 02:52:58', '2026-10-02 00:17:18'),
(5, 3, 5, 8, 100, 5, 1, '2026-10-20 04:43:25', '2026-10-20 04:43:25'),
(6, 4, 6, 8, 20, 6, 1, '2026-10-20 04:45:36', '2026-10-20 04:45:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_variantes`
--

DROP TABLE IF EXISTS `producto_variantes`;
CREATE TABLE IF NOT EXISTS `producto_variantes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_costo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `producto_variantes_producto_id_index` (`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `producto_variantes`
--

INSERT INTO `producto_variantes` (`id`, `producto_id`, `nombre`, `valor`, `precio_venta`, `precio_costo`, `precio_oferta`, `sku`, `stock`, `imagen`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Estándar', NULL, '200.00', '99.86', NULL, NULL, 29, NULL, 1, '2026-10-01 00:29:15', '2026-10-20 03:58:55'),
(3, 2, 'tamaño', '180 cm', '200.00', '140.00', '200.00', NULL, 5, NULL, 1, '2026-10-01 02:49:56', '2026-10-20 03:57:24'),
(4, 2, 'hello', 'cintra 20', '200.00', '180.00', NULL, NULL, 18, NULL, 1, '2026-10-01 02:52:58', '2026-10-20 03:56:01'),
(5, 3, 'Estándar', NULL, '200.00', '100.00', NULL, NULL, 100, NULL, 1, '2026-10-20 04:43:25', '2026-10-20 04:43:25'),
(6, 4, 'Estándar', NULL, '200.00', '100.00', NULL, NULL, 20, NULL, 1, '2026-10-20 04:45:36', '2026-10-20 04:45:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

DROP TABLE IF EXISTS `resenas`;
CREATE TABLE IF NOT EXISTS `resenas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `local_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pedido_id` bigint(20) UNSIGNED DEFAULT NULL,
  `calificacion` tinyint(3) UNSIGNED NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resenas_cliente_id_index` (`cliente_id`),
  KEY `resenas_local_id_index` (`local_id`),
  KEY `resenas_producto_id_index` (`producto_id`),
  KEY `resenas_pedido_id_index` (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Administrador general del sistema', '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(2, 'Vendedor', 'Usuario que administra sus locales y productos', '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(3, 'Cliente', 'Usuario que compra o consulta productos', '2026-05-17 17:08:03', '2026-05-17 17:08:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_menu_permissions`
--

DROP TABLE IF EXISTS `role_menu_permissions`;
CREATE TABLE IF NOT EXISTS `role_menu_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `permitido` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_menu_unique` (`role_id`,`menu_id`),
  KEY `fk_rmp_menu` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `role_menu_permissions`
--

INSERT INTO `role_menu_permissions` (`id`, `role_id`, `menu_id`, `permitido`, `created_at`, `updated_at`) VALUES
(1, 1, 13, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(2, 1, 12, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(3, 1, 8, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(4, 1, 14, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(5, 1, 1, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(6, 1, 15, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(7, 1, 16, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(8, 1, 7, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(9, 1, 6, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(10, 1, 4, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(11, 1, 5, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(12, 1, 9, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(13, 1, 17, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(14, 1, 3, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(15, 1, 10, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(16, 1, 2, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(17, 1, 11, 0, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(32, 2, 1, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(33, 2, 6, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(34, 2, 7, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(35, 2, 8, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(36, 2, 9, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(37, 2, 10, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(38, 2, 11, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(39, 3, 18, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(40, 3, 12, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(41, 3, 13, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(42, 3, 14, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(43, 3, 15, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(44, 3, 16, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(45, 3, 17, 1, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(46, 2, 19, 1, NULL, NULL),
(47, 2, 20, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Wh50CZqxV4ioELDunVIWQsgYiTIehvswicKg7km2', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNkZrcmxOVkFUb1k2bFNPNXRTTFY2RlB3Y0w0TEJ6VTVUa0NmakF0ZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGllbnRlL2NhdGFsb2dvIjtzOjU6InJvdXRlIjtzOjIyOiJjbGllbnRlLmNhdGFsb2dvLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9', 1795017097);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT '3',
  `imagen` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('activo','bloqueado','pendiente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_index` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `role_id`, `imagen`, `name`, `email`, `telefono`, `password`, `estado`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Admin', 'admin@gmail.com', '9999999999', '$2y$12$6sSPkSdvW7maHzYxLZpFDeZba.U5StUtMraLD1NuZSzbBA1lpdz6a', 'activo', NULL, NULL, '2026-05-17 17:08:03', '2026-05-17 17:08:03'),
(2, 2, NULL, 'manuel yah', 'hello11@gmail.com', '9971345022', '$2y$12$6sSPkSdvW7maHzYxLZpFDeZba.U5StUtMraLD1NuZSzbBA1lpdz6a', 'activo', NULL, 'qo5Iv91S7JNncVqPFKGDFeIouYE15kI8CGhRTVyobqh9y3fR2gUfD291uMHu', '2026-05-17 23:43:53', '2026-05-17 23:43:53'),
(3, 3, NULL, 'manuel yah canche', 'hello111@gmail.com', '9971345022', '$2y$12$g6T22JHHmbP9yImsPr0trOjgo2VyT7JxBkx9dkS1oP.sonNfx0rfO', 'activo', NULL, 'y4fyJV7EKTAoGw2es2MsWzk2bL4H6DP9H8YWXUGtlHguuxhvpljKIqoZ85cE', '2026-05-18 21:40:24', '2026-05-18 21:40:24'),
(4, 3, NULL, 'Joel', 'hellojoel@gmail.com', '9971116734', '$2y$12$Z7Etl2ElAKN6vL1v20fz2.vOU/hruWsD/pOx1KgYpHXHRGA3F7SmW', 'activo', NULL, NULL, '2026-06-27 00:34:38', '2026-06-27 00:34:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD CONSTRAINT `carritos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carritos_local_id_foreign` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD CONSTRAINT `carrito_items_carrito_id_foreign` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_items_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_items_variante_id_foreign` FOREIGN KEY (`variante_id`) REFERENCES `producto_variantes` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `fk_direcciones_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `fk_favoritos_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favoritos_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `locales`
--
ALTER TABLE `locales`
  ADD CONSTRAINT `fk_locales_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `local_horarios`
--
ALTER TABLE `local_horarios`
  ADD CONSTRAINT `fk_local_horarios_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `local_imagenes`
--
ALTER TABLE `local_imagenes`
  ADD CONSTRAINT `fk_local_imagenes_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `local_zonas_entrega`
--
ALTER TABLE `local_zonas_entrega`
  ADD CONSTRAINT `fk_local_zonas_entrega_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_direcciones` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `fk_pedido_detalles_pedidos` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_detalles_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_detalles_variantes` FOREIGN KEY (`variante_id`) REFERENCES `producto_variantes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_productos_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `fk_producto_imagenes_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_stocks`
--
ALTER TABLE `producto_stocks`
  ADD CONSTRAINT `fk_producto_stocks_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_stocks_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producto_stocks_variantes` FOREIGN KEY (`variante_id`) REFERENCES `producto_variantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_variantes`
--
ALTER TABLE `producto_variantes`
  ADD CONSTRAINT `fk_producto_variantes_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `fk_resenas_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resenas_locales` FOREIGN KEY (`local_id`) REFERENCES `locales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resenas_pedidos` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resenas_productos` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `role_menu_permissions`
--
ALTER TABLE `role_menu_permissions`
  ADD CONSTRAINT `fk_rmp_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rmp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
