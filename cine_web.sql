-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-07-2025 a las 04:51:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cine_web`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `creado`) VALUES
(1, 'Javier', 'admin@email.com', '12345', '2025-06-24 23:53:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cines`
--

CREATE TABLE `cines` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `horario_atencion` varchar(100) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cines`
--

INSERT INTO `cines` (`id`, `nombre`, `direccion`, `imagen`, `ciudad`, `telefono`, `horario_atencion`, `creado`) VALUES
(6, 'Cinepoint Megaplaza', ' Calle Alfredo Mendiola 3698 Km', 'https://cinemarkla.modyocdn.com/uploads/2c24782d-be88-4819-94b8-cac59c37d0fe/original/LAMBRAMANI.png', 'Lima', '999555000', '12:00 PM - 12:00 AM', '2025-06-28 20:51:05'),
(7, 'Cinepoint Mallplaza Comas', ' MallPlaza, Las Almendras 126, Comas', 'https://cinemarkla.modyocdn.com/uploads/4fc682ed-a0cc-445a-b635-ed5205c4c10d/original/comas.png', 'Lima', '999000222', '10:00 AM - 10:00 PM', '2025-07-01 03:26:09'),
(8, 'Cinepoint Open Plaza Huanuco', ' Jr. 2 de Mayo 125, La Quinta, Huánuco', 'https://cinemarkla.modyocdn.com/uploads/cd9cc9d3-875f-461b-9fb2-940d107c74c2/original/cinemark.jpg', 'Huanuco', '958788008', '10:00 AM - 10:00 PM', '2025-07-01 03:27:22'),
(9, 'Cinepoint Gamarra', ' Avenida Aviación 950 La Victoria', 'https://cinemarkla.modyocdn.com/uploads/ad3b5c8d-7fa6-4c7b-b71c-1f5cb5b1a585/original/trujillo-1.png', 'Lima', '999666333', '12:00 PM - 12:00 AM', '2025-07-04 17:48:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peliculas`
--

CREATE TABLE `peliculas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `genero` varchar(100) DEFAULT NULL,
  `duracion` varchar(50) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `trailer` varchar(255) DEFAULT NULL,
  `carrusel` varchar(255) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `peliculas`
--

INSERT INTO `peliculas` (`id`, `titulo`, `descripcion`, `imagen`, `genero`, `duracion`, `precio`, `stock`, `trailer`, `carrusel`, `creado`) VALUES
(1, 'Destino final', 'Atragantada por una violenta pesadilla recurrente, la estudiante universitaria Stefanie regresa a casa para encontrar a la única persona que podría romper el ciclo y salvar a su familia de la horrible muerte que inevitablemente les espera a todos.', 'https://image.tmdb.org/t/p/original/frNkbclQpexf3aUzZrnixF3t5Hw.jpg', 'Terror', '1h 40min', 20.00, 18, 'https://www.youtube.com/embed/8FudANSsWNQ', 'https://i.ibb.co/cSgcfcMk/portada2.png', '2025-06-24 14:36:38'),
(4, 'Karate Kid: Leyendas', 'Tras mudarse a Nueva York con su madre, el prodigio del kung fu Li Fong lucha por superar el pasado mientras intenta integrarse con sus nuevos compañeros de clase. Cuando un nuevo amigo necesita su ayuda, Li participa en una competición de karate, pero sus habilidades por sí solas no son suficientes. Con la ayuda del Sr. Han y Daniel LaRusso, él Pronto aprende a fusionar dos estilos en uno para el enfrentamiento definitivo de artes marciales.', 'https://image.tmdb.org/t/p/original/efNhiZPk71FTYJ30dBkWMfc939D.jpg', 'Acción', '1h 34min', 75.00, 13, 'https://www.youtube.com/embed/LhRXf-yEQqA', 'https://i.ibb.co/tTGkydgv/portada1.jpg', '2025-06-24 22:55:47'),
(5, 'M3GAN 2.0', 'Cuando la tecnología subyacente de M3GAN es robada y mal utilizada por un poderoso contratista de defensa para crear un arma de grado militar conocida como Amelia, Gemma se da cuenta de que la única opción es resucitar a M3GAN y mejorarla para hacerla más rápida, más fuerte y más letal.', 'https://image.tmdb.org/t/p/original/lHChxm7sv3gWR2qz5PwjdxcXQf7.jpg', 'Terror', '2h 10min', 40.00, 38, 'https://www.youtube.com/embed/JFVhW0hreiw', NULL, '2025-06-25 03:04:37'),
(6, 'Jurassic World: Renace', 'Cinco años después de Dominion, la ecología del planeta ha demostrado ser en gran medida inhóspita para los dinosaurios. Las criaturas más colosales de esa biosfera tienen la clave para un fármaco que salvará vidas.', 'https://image.tmdb.org/t/p/original/k8ZmT0TpNGdUUUU4sWJyI8NL4uX.jpg', 'Acción', '2h 14min', 75.00, 2, 'https://www.youtube.com/embed/DzMbe-SKwxU', 'https://i.ibb.co/M5jV3jC1/1750363275.jpg', '2025-06-28 02:57:11'),
(8, 'Superman', 'En sus primeros años, el joven reportero de Metrópolis y superhéroe se embarca en un viaje para reconciliar su herencia kryptoniana con su educación humana como Clark Kent.', 'https://image.tmdb.org/t/p/original/waa6v1VKBbOGzWku7OpwmQw2uEf.jpg', 'Aventura', '2h 10min', 60.00, 14, 'https://www.youtube.com/embed/0X_kBulSMjQ', 'https://i.ibb.co/bMCGLSfW/1750363872.png', '2025-06-30 23:03:01'),
(9, 'Elio', 'Elio, un niño fanático del espacio con una imaginación activa, se encuentra en una desventura cósmica en la que debe formar nuevos vínculos con excéntricas formas de vida alienígenas, navegar por una crisis de proporciones intergalácticas y, de alguna manera, descubrir quién está realmente destinado a ser.', 'https://image.tmdb.org/t/p/original/3se2wFVp9HITFMIjPOHmnyaMXjx.jpg', 'Infantil', '1h 39min', 18.00, 31, 'https://www.youtube.com/embed/h-yLUnDvvqg', '', '2025-07-04 18:34:52'),
(10, 'Los 4 Fantásticos', 'Con un estruendoso trasfondo en un mundo retrofuturista inspirado en los años 60, la Primera Familia de Marvel enfrenta su desafío más intimidante hasta ahora. Obligados a equilibrar sus roles como héroes con la fuerza de su vínculo familiar, deben defender la Tierra de un voraz dios espacial llamado Galactus y su enigmático heraldo, Silver Surfer.', 'https://image.tmdb.org/t/p/original/u6iFFGcOXk4d6C5pZes1qRgU8Nt.jpg', 'Aventura', '2h 10min', 28.00, 9, 'https://www.youtube.com/embed/f4yzurCjyPQ', 'https://i.ibb.co/fdq53vZ6/1752249198.jpg', '2025-07-04 18:36:46'),
(11, 'Pitufos', 'Cuando los malvados magos Razamel y Gargamel secuestran misteriosamente a Papá Pitufo, Pitufina lidera a los Pitufos en una misión al mundo real para salvarlo. Con la ayuda de nuevos amigos, los Pitufos deben descubrir qué define su destino para salvar el universo.', 'https://image.tmdb.org/t/p/original/hdIKbDnWOlZEzKvDBTz2tubOcDI.jpg', 'Infantil', '1h 32min', 30.00, 28, 'https://www.youtube.com/embed/zCXlxj49cz4', NULL, '2025-07-04 18:39:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `email`, `password`) VALUES
(1, 'lukas', 'castro', 'lukas@gmail.com', '12345'),
(2, 'kiko', 'perez', 'kiko@email.com', '12345'),
(3, 'chavo', 'cars', 'chavo@email.com', '12345'),
(4, 'coco', 'ramirez', 'coco@email.com', '12345'),
(5, 'alex', 'vega', 'alex@email.com', '12345');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `cines`
--
ALTER TABLE `cines`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `peliculas`
--
ALTER TABLE `peliculas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cines`
--
ALTER TABLE `cines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `peliculas`
--
ALTER TABLE `peliculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
