-- Crea la base si todavía no existe y selecciona la base usada por la aplicación.
CREATE DATABASE IF NOT EXISTS horario;
USE horario;

-- Borra solo las tablas definidas en este archivo para poder reiniciarlas.
-- Se eliminan primero las que dependen de otras por claves foráneas.
DROP TABLE IF EXISTS Horario;
DROP TABLE IF EXISTS Asignatura_Profesor;
DROP TABLE IF EXISTS Asignatura;
DROP TABLE IF EXISTS Hora;
DROP TABLE IF EXISTS Profesor;
DROP TABLE IF EXISTS Clase;

-- 1. Tabla Clase
CREATE TABLE Clase (
    idClase VARCHAR(8),
    nombre VARCHAR(50) NOT NULL,

    PRIMARY KEY (idClase)
);

-- 2. Tabla Profesor
CREATE TABLE Profesor (
    idProfesor TINYINT UNSIGNED AUTO_INCREMENT,
    nombreCompleto VARCHAR(50) NOT NULL,

    PRIMARY KEY (idProfesor)
);

-- 3. Tabla Hora
CREATE TABLE Hora (
    idHora TINYINT UNSIGNED AUTO_INCREMENT,
    hora TIME NOT NULL,

    PRIMARY KEY (idHora),
    UNIQUE (hora)
);

-- 4. Tabla Asignatura
CREATE TABLE Asignatura (
    idAsignatura TINYINT UNSIGNED AUTO_INCREMENT,
    nombre VARCHAR(30) NOT NULL,
    color VARCHAR(7) NOT NULL UNIQUE,
    idClase VARCHAR(8) NOT NULL,

    PRIMARY KEY (idAsignatura),

    CONSTRAINT fk_asignatura_clase
        FOREIGN KEY (idClase)
        REFERENCES Clase(idClase)
);

-- 5. Tabla intermedia Asignatura_Profesor
CREATE TABLE Asignatura_Profesor (
    idAsignatura TINYINT UNSIGNED,
    idProfesor TINYINT UNSIGNED,

    PRIMARY KEY (idAsignatura, idProfesor),

    FOREIGN KEY (idAsignatura)
        REFERENCES Asignatura(idAsignatura),

    FOREIGN KEY (idProfesor)
        REFERENCES Profesor(idProfesor)
);

-- 6. Tabla Horario
CREATE TABLE Horario (
    idHorario INT UNSIGNED AUTO_INCREMENT,
    dia VARCHAR(2) NOT NULL,
    idHora TINYINT UNSIGNED NOT NULL,
    idClase VARCHAR(8) NOT NULL,
    idAsignatura TINYINT UNSIGNED NOT NULL,
    idProfesor TINYINT UNSIGNED NOT NULL,

    PRIMARY KEY (idHorario),

    UNIQUE (dia, idHora, idProfesor),

    UNIQUE (dia, idHora, idClase),

    FOREIGN KEY (idHora)
        REFERENCES Hora(idHora),

    FOREIGN KEY (idClase)
        REFERENCES Clase(idClase),

    FOREIGN KEY (idAsignatura)
        REFERENCES Asignatura(idAsignatura),

    FOREIGN KEY (idProfesor)
        REFERENCES Profesor(idProfesor)
);



--INSERCION



-- 1. Tabla Clase
INSERT INTO Clase (idClase, nombre) 
    VALUES
        ('1DAW', '1 Desarrollo de aplicaciones web'),
        ('2DAW', '2 Desarrollo de aplicaciones web'),
        ('1BACHC', '1 Bachillerato cientifico');




-- 2. Tabla Hora
INSERT INTO Hora (hora)
    VALUES
        ('08:15'),
        ('09:10'),
        ('10:05'),
        ('11:00'),
        ('11:30'),
        ('12:25'),
        ('13:20'),
        ('14:15'),
        ('15:00');
        
-- 3. Tabla Profesor
INSERT INTO Profesor (nombreCompleto) 
    VALUES
        ('Isabel'),
        ('Ernesto'),
        ('Alberto'),
        ('Luis'),
        ('Santi');


-- 4. Tabla Asignatura
INSERT INTO Asignatura (nombre, color, idClase)
    VALUES
        ('Servidor', '#E57373', '2DAW'),
        ('Cliente', '#64B5F6', '2DAW'),
        ('Interfaz', '#81C784', '2DAW'),
        ('PIMOD', '#FFD54F', '2DAW'),
        ('DEAPW', '#BA68C8', '2DAW'),
        ('SASP', '#4DB6AC', '2DAW'),
        ('IPE2', '#FF8A65', '2DAW'),
        ('OPT1', '#7986CB', '2DAW'),
        ('OPT2A', '#A1887F', '2DAW'),
        ('OPT2I', '#90A4AE', '2DAW');



-- 5. Tabla intermedia Asignatura_Profesor
INSERT INTO Asignatura_Profesor (idAsignatura, idProfesor) 
    VALUES
        (1, 1),
        (2, 3),
        (3, 1),
        (4, 1),
        (4, 2),
        (5, 2),
        (6, 5),
        (7, 4),
        (8, 4),
        (9, 1),
        (10,3);

-- 6. Asignatura de Bachillerato y profesores que la imparten.
INSERT INTO Asignatura (nombre, color, idClase)
    VALUES ('Asignatura 1', '#CE93D8', '1BACHC');

INSERT INTO Asignatura_Profesor (idAsignatura, idProfesor)
    VALUES
        (11, 1),
        (11, 2);

-- 7. Horario completo de Bachillerato.
-- Los idHora corresponden al orden de inserción de la tabla Hora:
-- 1=08:15, 2=09:10, 3=10:05, 5=11:30, 6=12:25, 7=13:20 y 8=14:15.
-- El recreo de las 11:00 no se inserta y las 14:15 solo se usan el lunes.
-- Los idProfesor corresponden al orden de inserción: 1=Isabel y 2=Ernesto.
INSERT INTO Horario (dia, idHora, idClase, idAsignatura, idProfesor)
    VALUES
        ('LU', 1, '1BACHC', 11, 2),
        ('MA', 1, '1BACHC', 11, 1),
        ('MI', 1, '1BACHC', 11, 1),
        ('JU', 1, '1BACHC', 11, 1),
        ('VI', 1, '1BACHC', 11, 1),

        ('LU', 2, '1BACHC', 11, 1),
        ('MA', 2, '1BACHC', 11, 2),
        ('MI', 2, '1BACHC', 11, 1),
        ('JU', 2, '1BACHC', 11, 2),
        ('VI', 2, '1BACHC', 11, 2),

        ('LU', 3, '1BACHC', 11, 2),
        ('MA', 3, '1BACHC', 11, 2),
        ('MI', 3, '1BACHC', 11, 1),
        ('JU', 3, '1BACHC', 11, 1),
        ('VI', 3, '1BACHC', 11, 1),

        ('LU', 5, '1BACHC', 11, 1),
        ('MA', 5, '1BACHC', 11, 2),
        ('MI', 5, '1BACHC', 11, 2),
        ('JU', 5, '1BACHC', 11, 1),
        ('VI', 5, '1BACHC', 11, 1),

        ('LU', 6, '1BACHC', 11, 1),
        ('MA', 6, '1BACHC', 11, 1),
        ('MI', 6, '1BACHC', 11, 1),
        ('JU', 6, '1BACHC', 11, 1),
        ('VI', 6, '1BACHC', 11, 2),

        ('LU', 7, '1BACHC', 11, 1),
        ('MA', 7, '1BACHC', 11, 1),
        ('MI', 7, '1BACHC', 11, 2),
        ('JU', 7, '1BACHC', 11, 1),
        ('VI', 7, '1BACHC', 11, 1),

        ('LU', 8, '1BACHC', 11, 1);

