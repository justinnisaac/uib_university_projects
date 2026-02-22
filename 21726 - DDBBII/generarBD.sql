CREATE DATABASE BD2XAMPPions;
USE BD2XAMPPions;

-- SECCIÓN DE CREACIÓN DE TABLAS
CREATE TABLE pais (
    id_pais INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(64) NOT NULL
);

CREATE TABLE comunidad_autonoma (
    id_comunidad INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(64) NOT NULL,
    id_pais INT NOT NULL,
    CONSTRAINT fk_comunidad_pais FOREIGN KEY (id_pais) REFERENCES pais(id_pais)
);

CREATE TABLE provincia (
    id_provincia INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(64) NOT NULL,
    id_comunidad INT NOT NULL,
    CONSTRAINT fk_provincia_comunidad FOREIGN KEY (id_comunidad) REFERENCES comunidad_autonoma(id_comunidad)
);

CREATE TABLE municipio (
    id_municipio INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(64) NOT NULL,
    id_provincia INT NOT NULL,
    CONSTRAINT fk_municipio_provincia FOREIGN KEY (id_provincia) REFERENCES provincia(id_provincia)
);

CREATE TABLE ayuntamiento (
    id_ayuntamiento INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    email_ayuntamiento Varchar(100) NOT NULL,
    telefono_ayuntamiento Varchar(15) NOT NULL,
    direccion Varchar(255) NOT NULL,
    id_municipio INT NOT NULL,
    CONSTRAINT fk_ayuntamiento_municipio FOREIGN KEY (id_municipio) REFERENCES municipio(id_municipio)
);

CREATE TABLE colonia (
    id_colonia INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre_colonia Varchar(100) NOT NULL,
    coordenadas_GPS Varchar(100) NOT NULL,
    descripción_ubicación Varchar(255),
    comentarios Varchar(255),
    id_ayuntamiento INT NOT NULL,
    CONSTRAINT fk_colonia_ayuntamiento FOREIGN KEY (id_ayuntamiento) REFERENCES ayuntamiento(id_ayuntamiento)
);

CREATE TABLE estado_gato (
    id_estado INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    estado VARCHAR(20) NOT NULL
);

CREATE TABLE gato (
    id_gato INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    num_chip Varchar(20) NULL,
    url_foto Varchar(255) NULL,
    descripcion_aspecto Varchar(255) NULL,
    id_estado INT NOT NULL,
    CONSTRAINT fk_gato_estado FOREIGN KEY (id_estado) REFERENCES estado_gato(id_estado)
);

CREATE TABLE historial_colonia (
    id_historial INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    id_gato INT NOT NULL,
    id_colonia INT NOT NULL,
    fecha_ingreso Date NOT NULL,
    fecha_salida Date NULL,
    CONSTRAINT fk_historial_gato FOREIGN KEY (id_gato) REFERENCES gato(id_gato),
    CONSTRAINT fk_historial_colonia FOREIGN KEY (id_colonia) REFERENCES colonia(id_colonia)
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre_usuario Varchar(50) NOT NULL UNIQUE,
    contrasena_hash Varchar(255) NOT NULL,
    nombre Varchar(100) NOT NULL,
    apellidos Varchar(150) NOT NULL,
    telefono Varchar(15) NULL,
    email Varchar(100) NULL
);

CREATE TABLE privilegios (
    id_privilegios INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    privilegiosVoluntario BOOLEAN NOT NULL DEFAULT FALSE,
    privilegiosResponsable BOOLEAN NOT NULL DEFAULT FALSE,
    privilegiosVeterinario BOOLEAN NOT NULL DEFAULT FALSE,
    privilegiosAyuntamiento BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE puede_hacer (
    id_usuario INT NOT NULL,
    id_privilegios INT NOT NULL,
    CONSTRAINT pk_puede_hacer PRIMARY KEY (id_usuario, id_privilegios),
    CONSTRAINT fk_puede_hacer_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_puede_hacer_privilegios FOREIGN KEY (id_privilegios) REFERENCES privilegios(id_privilegios)
);

CREATE TABLE borsin_voluntarios (
    id_borsin INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    descripcion Varchar(255) NOT NULL,
    id_ayuntamiento INT NOT NULL,
    CONSTRAINT fk_borsin_ayuntamiento FOREIGN KEY (id_ayuntamiento) REFERENCES ayuntamiento(id_ayuntamiento)
);

CREATE TABLE grupo_control_felino (
    id_grupo INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    id_ayuntamiento INT NOT NULL,
    CONSTRAINT fk_grupo_ayuntamiento FOREIGN KEY (id_ayuntamiento) REFERENCES ayuntamiento(id_ayuntamiento)
);

CREATE TABLE voluntario (
    id_voluntario INT NOT NULL PRIMARY KEY,
    id_grupo INT,
    id_borsin INT NOT NULL,
    CONSTRAINT fk_voluntario_borsin FOREIGN KEY (id_borsin) REFERENCES borsin_voluntarios(id_borsin),
    CONSTRAINT fk_voluntario_usuario FOREIGN KEY (id_voluntario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_voluntario_grupo FOREIGN KEY (id_grupo) REFERENCES grupo_control_felino(id_grupo)
);

CREATE TABLE tarea (
    id_tarea INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    descripcion Varchar(255) NOT NULL,
    completada BOOLEAN NOT NULL DEFAULT FALSE,
    id_voluntario INT NOT NULL,
    id_colonia INT NOT NULL,
    CONSTRAINT fk_tarea_colonia FOREIGN KEY (id_colonia) REFERENCES colonia(id_colonia),
    CONSTRAINT fk_tarea_voluntario FOREIGN KEY (id_voluntario) REFERENCES voluntario(id_voluntario)
);

CREATE TABLE personal_administrativo (
    id_personal_administrativo INT NOT NULL PRIMARY KEY,
    especialidad Varchar(100) NOT NULL,
    id_ayuntamiento INT NOT NULL,
    CONSTRAINT fk_personal_administrativo_usuario FOREIGN KEY (id_personal_administrativo) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_trabajador_ayuntamiento FOREIGN KEY (id_ayuntamiento) REFERENCES ayuntamiento(id_ayuntamiento)
);

CREATE TABLE centro_veterinario (
    id_centro INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    direccion Varchar(255) NOT NULL,
    telefono Varchar(15) NOT NULL,
    email Varchar(100) NOT NULL,
    id_municipio INT NOT NULL,
    CONSTRAINT fk_centro_municipio FOREIGN KEY (id_municipio) REFERENCES municipio(id_municipio)
);

CREATE TABLE veterinario (
    id_veterinario INT NOT NULL PRIMARY KEY,
    especialidad Varchar(100) NOT NULL,
    id_centro INT NOT NULL,
    CONSTRAINT fk_veterinario_usuario FOREIGN KEY (id_veterinario) REFERENCES usuario(id_usuario),
    CONSTRAINT fk_veterinario_centro FOREIGN KEY (id_centro) REFERENCES centro_veterinario(id_centro)
);

CREATE TABLE tipo_campana (
    id_tipo_campana INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    tipo Varchar(100) NOT NULL
);

CREATE TABLE campana (
    id_campana INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    id_tipo_campana INT NOT NULL,
    fechaInicio Date NOT NULL,
    fechaFin Date NULL,
    tipoVacunacion Varchar(100) NULL,
    id_centro_veterinario INT NOT NULL,
    id_colonia INT NOT NULL,
    id_responsable INT NOT NULL,
    CONSTRAINT fk_campana_tipo FOREIGN KEY (id_tipo_campana) REFERENCES tipo_campana(id_tipo_campana),
    CONSTRAINT fk_campana_responsable FOREIGN KEY (id_responsable) REFERENCES voluntario(id_voluntario),
    CONSTRAINT fk_campana_colonia FOREIGN KEY (id_colonia) REFERENCES colonia(id_colonia),
    CONSTRAINT fk_campana_centro FOREIGN KEY (id_centro_veterinario) REFERENCES centro_veterinario(id_centro)
);

CREATE TABLE visita (
    id_visita INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fecha_visita Date NOT NULL,
    comentarios Varchar(255),
    id_responsable INT NOT NULL,
    id_colonia INT NOT NULL,
    CONSTRAINT fk_visita_responsable FOREIGN KEY (id_responsable) REFERENCES voluntario(id_voluntario),
    CONSTRAINT fk_visita_colonia FOREIGN KEY (id_colonia) REFERENCES colonia(id_colonia)
);

-- salud, comportamiento, otro
CREATE TABLE incidencia_tipo (
    id_tipo_incidencia INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    tipo_incidencia Varchar(50) NOT NULL
);

CREATE TABLE incidencia (
    id_tipo_incidencia INT NOT NULL,
    descripcion Varchar(255) NOT NULL,
    id_visita INT NOT NULL,
    id_gato INT NOT NULL,
    CONSTRAINT pk_incidencia PRIMARY KEY (id_visita, id_gato),
    CONSTRAINT fk_incidencia_visita FOREIGN KEY (id_visita) REFERENCES visita(id_visita),
    CONSTRAINT fk_incidencia_gato FOREIGN KEY (id_gato) REFERENCES gato(id_gato),
    CONSTRAINT fk_incidencia_tipo FOREIGN KEY (id_tipo_incidencia) REFERENCES incidencia_tipo(id_tipo_incidencia)
);

CREATE TABLE cementerio (
    id_cementerio INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nombre Varchar(100) NOT NULL,
    direccion Varchar(255) NOT NULL,
    id_municipio INT NOT NULL,
    CONSTRAINT fk_cementerio_municipio FOREIGN KEY (id_municipio) REFERENCES municipio(id_municipio)
);

CREATE TABLE solicitud_retirada (
    id_solicitud INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fecha_solicitud Date NOT NULL,
    comentarios Varchar(255) NOT NULL,
    aprobada BOOLEAN NOT NULL DEFAULT FALSE,
    id_gato INT NOT NULL,
    id_responsable INT NOT NULL,
    CONSTRAINT fk_solicitud_gato FOREIGN KEY (id_gato) REFERENCES gato(id_gato),
    CONSTRAINT fk_solicitud_voluntario FOREIGN KEY (id_responsable) REFERENCES voluntario(id_voluntario)
);

CREATE TABLE retirada (
    id_retirada INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fecha_retirada Date NOT NULL,
    comentarios_autopsia Varchar(255) NULL,
    id_veterinario INT NOT NULL,
    id_cementerio INT NOT NULL,
    id_solicitud INT NOT NULL,
    CONSTRAINT fk_retirada_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitud_retirada(id_solicitud),
    CONSTRAINT fk_retirada_cementerio FOREIGN KEY (id_cementerio) REFERENCES cementerio(id_cementerio),
    CONSTRAINT fk_retirada_veterinario FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario)
);

CREATE TABLE avistamiento (
    id_avistamiento INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    id_colonia INT NOT NULL,
    id_gato INT NOT NULL,
    fecha DATE NOT NULL,
    comentarios Varchar(255) NOT NULL,
    CONSTRAINT fk_avistamiento_colonia FOREIGN KEY (id_colonia) REFERENCES colonia(id_colonia),
    CONSTRAINT fk_avistamiento_gato FOREIGN KEY (id_gato) REFERENCES gato(id_gato)
);

CREATE TABLE intervencion_veterinaria (
    id_intervencion INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fecha Date NOT NULL,
    comentario Varchar(255) NOT NULL,
    id_gato INT NOT NULL,
    id_campana INT NOT NULL,
    CONSTRAINT fk_intervencion_gato FOREIGN KEY (id_gato) REFERENCES gato(id_gato),
    CONSTRAINT fk_intervencion_campaña FOREIGN KEY (id_campana) REFERENCES campana(id_campana)
);

CREATE TABLE veterinario_accion (
    id_veterinario INT NOT NULL,
    id_intervencion INT NOT NULL,
    CONSTRAINT pk_veterinario_accion PRIMARY KEY (id_veterinario, id_intervencion),
    CONSTRAINT fk_veterinario_accion_veterinario FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario),
    CONSTRAINT fk_veterinario_accion_intervencion FOREIGN KEY (id_intervencion) REFERENCES intervencion_veterinaria(id_intervencion)
);

CREATE TABLE participacion (
    id_veterinario INT NOT NULL,
    id_campana INT NOT NULL,
    CONSTRAINT pk_participacion PRIMARY KEY (id_veterinario, id_campana),
    CONSTRAINT fk_participacion_veterinario FOREIGN KEY (id_veterinario) REFERENCES veterinario(id_veterinario),
    CONSTRAINT fk_participacion_campana FOREIGN KEY (id_campana) REFERENCES campana(id_campana)
);

-- SECCIÓN DE INSERTS
INSERT INTO pais (nombre) VALUES
('España');

INSERT INTO comunidad_autonoma (nombre, id_pais) VALUES
('Islas Baleares', (SELECT id_pais FROM pais WHERE nombre='España'));

INSERT INTO provincia (nombre, id_comunidad) VALUES
('Baleares', (SELECT id_comunidad FROM comunidad_autonoma WHERE nombre='Islas Baleares'));

INSERT INTO municipio (nombre, id_provincia) VALUES
('Palma', (SELECT id_provincia FROM provincia WHERE nombre='Baleares')),
('Petra', (SELECT id_provincia FROM provincia WHERE nombre='Baleares')),
('Santanyí', (SELECT id_provincia FROM provincia WHERE nombre='Baleares'));

INSERT INTO ayuntamiento (nombre, email_ayuntamiento, telefono_ayuntamiento, direccion, id_municipio) VALUES
('Ajuntament de Palma', 'contacte@palma.cat', '971225900', 'Calle del ayuntamiento 1', (SELECT id_municipio FROM municipio WHERE nombre='Palma')),
('Ajuntament de Petra', 'contacte@petra.cat', '971561033', 'Calle del ayuntamiento 2', (SELECT id_municipio FROM municipio WHERE nombre='Petra')),
('Ajuntament de Santanyi', 'contacte@santanyi.cat', '971653002', 'Calle del ayuntamiento 3', (SELECT id_municipio FROM municipio WHERE nombre='Santanyí'));

INSERT INTO privilegios (privilegiosVoluntario, privilegiosResponsable, privilegiosVeterinario, privilegiosAyuntamiento) VALUES 
(1, 0, 0, 0), -- Voluntario
(0, 1, 0, 0), -- Responsable
(0, 0, 1, 0), -- Veterinario
(0, 0, 0, 1); -- Ayuntamiento

-- Nota: En un entorno real contrasena_hash debería ser un hash, aquí usamos texto por simplicidad
INSERT INTO usuario (nombre_usuario, contrasena_hash, nombre, apellidos, telefono, email) VALUES
('adminPalma', '1234', 'Joan', 'Garcia', '600111222', 'joan@palma.cat'),
('adminPetra', '1234', 'Maria', 'Oliver', '600333444', 'maria@petra.cat'),
('adminSantanyi', '1234', 'Pep', 'Bonet', '600555666', 'pep@santanyi.cat'),

('volPalma1', '1234', 'Laura', 'Martínez', '600111111', 'laura@mail.com'),
('volPetra1', '1234', 'Jordi', 'Serra', '600222222', 'jordi@mail.com'),
('volSantanyi1', '1234', 'Aina', 'Riera', '600333333', 'aina@mail.com'),
('volPalma2', '1234', 'Marta', 'López', '600444555', 'marta@mail.com'),
('volPetra2', '1234', 'Xisco', 'Vidal', '600555666', 'xisco@mail.com'),
('volSantanyi2', '1234', 'Clara', 'Soler', '600666777', 'clara@mail.com'),

('respPalma1', '1234', 'Carme', 'Fuster', '600444444', 'carme@mail.com'),
('respPetra1', '1234', 'Bernat', 'Mir', '600555555', 'bernat@mail.com'),
('respSantanyi1', '1234', 'Tomeu', 'Font', '600666666', 'tomeu@mail.com'),

('vetPalma1', '1234', 'Pau', 'Sastre', '600777777', 'pau@vet.com'),
('vetPalma2', '1234', 'Eva', 'Cabrera', '600777778', 'eva@vet.com'),
('vetPetra1', '1234', 'Neus', 'Moll', '600888888', 'neus@vet.com'),
('vetPetra2', '1234', 'Pere', 'Juan', '600111111', 'pere@vet.com'),
('vetSantanyi1', '1234', 'Biel', 'Riera', '600999999', 'biel@vet.com'),
('vetSantanyi2', '1234', 'Lola', 'Vives', '600222222', 'lola@vet.com');

INSERT INTO personal_administrativo (id_personal_administrativo, especialidad, id_ayuntamiento) VALUES
((SELECT id_usuario FROM usuario WHERE nombre_usuario='adminPalma'), 'Administrativo', 1),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='adminPetra'), 'Tècnic Medi Ambient', 2),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='adminSantanyi'), 'Regidor', 3);

INSERT INTO puede_hacer (id_usuario, id_privilegios) VALUES
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPalma1'), 1),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPetra1'), 1),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volSantanyi1'), 1),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPalma2'), 1),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPetra2'), 1),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volSantanyi2'), 1),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='respPalma1'), 2),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1'), 2),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='respSantanyi1'), 2),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPalma1'), 3),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPalma2'), 3),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPetra1'), 3),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPetra2'), 3), 
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetSantanyi1'), 3),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetSantanyi2'), 3),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='adminPalma'), 4),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='adminPetra'), 4),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='adminSantanyi'), 4);

INSERT INTO colonia (nombre_colonia, coordenadas_GPS, descripción_ubicación, comentarios, id_ayuntamiento) VALUES
('Colonia 1', '1,1', 'Uno', '', 1),
('Colonia 2', '2,2', 'Dos', '', 2),
('Colonia 3', '3,3', 'Tres', '', 3),
('Colonia 4', '4,4', 'Cuatro', '', 1);

INSERT INTO estado_gato (estado) VALUES
('Saludable'),
('Enfermo'),
('Herido'),
('Difunto');

INSERT INTO gato (nombre, num_chip, url_foto, descripcion_aspecto, id_estado) VALUES
('Miu', '1234567890', 'http://example.com/gato1.jpg', 'Gato atigrado con ojos verdes', 1),
('Mia', '0987654321', 'http://example.com/gato2.jpg', 'Gato negro con manchas blancas', 1),
('Lucas', NULL, 'http://example.com/gato3.jpg', 'Gato gris con cola larga', 1),
('Luna', '1122334455', 'http://example.com/gato4.jpg', 'Gato blanco con ojos azules', 1),
('Miau', NULL, NULL, 'Gato sin chip ni foto', 1),
('Milo', '2233445566', NULL, 'Gato sin foto', 1),
('Leo', '4488559977', 'http://example.com/gato5.jpg', 'Gato recién nacido', 1),
('Garfield', '2200223399', 'http://example.com/gato6.jpg', 'Gato icónico', 1),
('Loki', '2200334499', 'http://example.com/gato7.jpg', 'Gato blanco y negro travieso', 1),
('Zeus', '3300445599', 'http://example.com/gato8.jpg', 'Gato majestuoso', 1),
('Batman', '2200445599', NULL, 'Gato negro y misterioso', 1),
('Doraemon', NULL, NULL, 'Gato de color azul cósmico', 1),
('Gordi', NULL, 'http://example.com/gato9.jpg', 'Gato gordito', 1),
('Gato con botas', NULL, NULL, 'Gato con botas', 1),
('Tom', '2211003322', NULL, 'Gato grisáceo que siempre caza ratones', 1),
('Silvestre', NULL, NULL, 'Gato blanco y negro que le gustan los canarios', 1),
('Cleopatra', NULL, NULL, 'Gata egipcia elegante', 1);

-- Gatos que no están saludables (pruebas)
INSERT INTO gato (nombre, num_chip, url_foto, descripcion_aspecto, id_estado) VALUES
('Mishu', NULL, NULL, 'Gato difunto (pruebas)', 4),
('Nina', NULL, NULL, 'Gato difunto 2 (pruebas)', 4),
('Simba', NULL, NULL, 'Gato enfermo (pruebas)', 2),
('Loki', NULL, NULL, 'Gato herido  (pruebas)', 3),
('Toby', NULL, NULL, 'Gato herido 2 (pruebas)', 3);

INSERT INTO historial_colonia(id_gato, id_colonia, fecha_ingreso, fecha_salida) VALUES
(1, 1, '2025-01-10', '2025-06-15'),
(1, 2, '2025-06-16', NULL),
(2, 1, '2023-09-01', '2025-02-10'),
(2, 3, '2025-02-11', NULL),
(3, 1, '2025-05-20', NULL),
(4, 4, '2025-06-01', NULL),
(5, 1, '2025-01-01', NULL),
(6, 2, '2025-01-01', NULL),
(7, 3, '2025-01-01', NULL),
(8, 4, '2025-01-01', NULL),
(9, 1, '2025-01-01', NULL),
(10, 2, '2025-01-01', NULL),
(11, 3, '2025-01-01', NULL),
(12, 4, '2025-01-01', NULL),
(13, 1, '2025-01-01', NULL),
(14, 2, '2025-01-01', NULL),
(15, 3, '2025-01-01', NULL),
(16, 4, '2025-01-01', NULL),
(17, 1, '2025-01-01', NULL),
(18, 2, '2024-12-01', NULL),
(19, 3, '2025-02-15', NULL),
(20, 4, '2025-03-10', NULL),
(21, 1, '2025-04-05', NULL),
(22, 2, '2025-05-20', NULL);

INSERT INTO borsin_voluntarios (descripcion, id_ayuntamiento) VALUES
('Bolsín Palma', 1),
('Bolsín Petra', 2),
('Bolsín Santanyí', 3);

INSERT INTO grupo_control_felino (nombre, id_ayuntamiento) VALUES
('Grupo de trabajo Palma 1', 1),
('Grupo de trabajo Petra 1', 2),
('Grupo de trabajo Santanyi 1', 3);

INSERT INTO voluntario (id_voluntario, id_grupo, id_borsin) VALUES
((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPalma1'),
 (SELECT id_grupo FROM grupo_control_felino WHERE nombre='Grupo de trabajo Palma 1'),
 (SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=1)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPetra1'),
 (SELECT id_grupo FROM grupo_control_felino WHERE nombre='Grupo de trabajo Petra 1'),
 (SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=2)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='volSantanyi1'),
 (SELECT id_grupo FROM grupo_control_felino WHERE nombre='Grupo de trabajo Santanyi 1'),
 (SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=3)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPalma2'),
NULL,
(SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=1)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='volPetra2'),
NULL,
(SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=2)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='volSantanyi2'),
NULL,
(SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=3)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='respPalma1'),
(SELECT id_grupo FROM grupo_control_felino WHERE nombre='Grupo de trabajo Palma 1'),
(SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=1)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1'),
 (SELECT id_grupo FROM grupo_control_felino WHERE nombre='Grupo de trabajo Petra 1'),
 (SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=2)),

((SELECT id_usuario FROM usuario WHERE nombre_usuario='respSantanyi1'),
 (SELECT id_grupo FROM grupo_control_felino WHERE nombre='Grupo de trabajo Santanyi 1'),
 (SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento=3));

INSERT INTO centro_veterinario (nombre, direccion, telefono, email, id_municipio) VALUES
('Vet Palma', 'C/ Major 12', '971111111', 'vetpalma@vet.com', (SELECT id_municipio FROM municipio WHERE nombre='Palma')),
('Vet Petra', 'C/ Centre 5', '971222222', 'vetpetra@vet.com', (SELECT id_municipio FROM municipio WHERE nombre='Petra')),
('Vet Santanyi', 'Av. Marina 20', '971333333', 'vetsantanyi@vet.com', (SELECT id_municipio FROM municipio WHERE nombre='Santanyí'));

INSERT INTO veterinario (id_veterinario, especialidad, id_centro) VALUES
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPalma1'), 'Menescal', 
(SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Palma')),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPalma2'), 'Auxiliar',
(SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Palma')),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPetra1'), 'Urgencias', 
(SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Petra')),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetPetra2'), 'General', 
(SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Petra')),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetSantanyi1'), 'Cirugía', 
(SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Santanyi')),
((SELECT id_usuario FROM usuario WHERE nombre_usuario='vetSantanyi2'), 'Auxiliar',
(SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Santanyi'));

INSERT INTO tipo_campana (tipo) VALUES
('Esterilización'),
('Implantación de chips'),
('Vacunación');

INSERT INTO campana (nombre, id_tipo_campana, fechaInicio, fechaFin, tipoVacunacion, id_centro_veterinario, id_colonia, id_responsable) VALUES
('Campaña de esterilización en Palma', 1, '2023-05-01', NULL, NULL,
 (SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Palma'), 1,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPalma1'))),

('Campaña de esterilización en Petra', 1, '2023-04-10', NULL, NULL,
 (SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Petra'), 2,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1'))),

('Campaña de esterilización en Santanyí', 1, '2023-06-01', NULL, NULL,
 (SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Santanyi'), 3,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respSantanyi1'))),

 ('Campaña de esterilización en Petra 2', 1, '2023-04-20', NULL, NULL,
 (SELECT id_centro FROM centro_veterinario WHERE nombre ='Vet Petra'), 2,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1')));

INSERT INTO visita (fecha_visita, comentarios, id_responsable, id_colonia) VALUES
('2023-05-12', 'Revisión general', (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPalma1')), 1),
('2023-04-22', 'Control sanitario', (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1')), 2),
('2023-06-05', 'Verificación de gatos nuevos', (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respSantanyi1')), 3);

INSERT INTO incidencia_tipo (tipo_incidencia) VALUES
('Salud'),
('Comportamiento'),
('Otro');

INSERT INTO avistamiento (id_colonia, id_gato, fecha, comentarios) VALUES
(1, 4, '2023-06-01','Gato observado cerca del contenedor'),
(2, 3, '2023-05-20','Gato descansando bajo un árbol'),
(3, 2, '2023-02-11','Gato jugando con otros');

INSERT INTO cementerio (nombre, direccion, id_municipio) VALUES
('Son Reus', 'C/ Funeraria 1', (SELECT id_municipio FROM municipio WHERE nombre='Palma')),
('Cementerio Palma', 'C/ Funeraria 10', (SELECT id_municipio FROM municipio WHERE nombre='Palma')),
('Cementerio Petra', 'C/ Funeraria 2', (SELECT id_municipio FROM municipio WHERE nombre='Petra')),
('Cementerio Petra 2', 'C/ Funeraria 20', (SELECT id_municipio FROM municipio WHERE nombre='Petra')),
('Cementerio Santanyi', 'C/ Funeraria 3', (SELECT id_municipio FROM municipio WHERE nombre='Santanyí')),
('Cementerio Santanyi 2', 'C/ Funeraria 4', (SELECT id_municipio FROM municipio WHERE nombre='Santanyí'));

INSERT INTO solicitud_retirada (fecha_solicitud, comentarios, id_gato, id_responsable) VALUES
('2023-06-20', 'Solicitud de retirada por estado crítico', 5,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPalma1'))),
('2023-06-18', 'Solicitud de retirada por enfermedad grave', 6,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1'))),
('2023-06-15', 'Solicitud de retirada por accidente', 7,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respPetra1'))),
('2023-06-10', 'Solicitud de retirada por estado terminal', 8,
 (SELECT id_voluntario FROM voluntario WHERE id_voluntario=(SELECT id_usuario FROM usuario WHERE nombre_usuario='respSantanyi1')));