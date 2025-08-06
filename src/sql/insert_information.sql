USE project_db;

INSERT INTO
    author (
        first_name,
        last_name,
        username,
        email,
        password,
        orcid,
        afiliation
    )
VALUES (
        'María',
        'González',
        'mgonzalez',
        'maria.g@example.com',
        '1234',
        '0000-0001-1111-1111',
        'PUCE'
    ),
    (
        'Juan',
        'Pérez',
        'jperez',
        'juan.p@example.com',
        'abcd',
        '0000-0002-2222-2222',
        'ESPOL'
    ),
    (
        'Ana',
        'Martínez',
        'amartinez',
        'ana.m@example.com',
        'pass123',
        '0000-0003-3333-3333',
        'USFQ'
    ),
    (
        'Luis',
        'Torres',
        'ltorres',
        'luis.t@example.com',
        'secure',
        '0000-0004-4444-4444',
        'UDLA'
    ),
    (
        'Camila',
        'Ramos',
        'cramos',
        'camila.r@example.com',
        'qwerty',
        '0000-0005-5555-5555',
        'UTE'
    );

-- 🔸 Tabla: publication (3 libros, 2 artículos)
INSERT INTO
    publication (
        titulo,
        description,
        publication_date,
        author_id,
        type
    )
VALUES (
        'Introducción a la Programación',
        'Libro sobre programación básica',
        '2022-01-15',
        1,
        'book'
    ), -- ID 1
    (
        'Redes Computacionales',
        'Libro sobre redes de computadoras',
        '2023-03-10',
        2,
        'book'
    ), -- ID 2
    (
        'Bases de Datos Relacionales',
        'Libro sobre SQL y modelos de datos',
        '2024-05-05',
        3,
        'book'
    ), -- ID 3
    (
        'IA en la Medicina',
        'Artículo sobre inteligencia artificial aplicada a la salud',
        '2023-10-01',
        4,
        'article'
    ), -- ID 4
    (
        'Seguridad Informática',
        'Artículo sobre ciberseguridad en entornos web',
        '2022-11-20',
        5,
        'article'
    );
-- ID 5

-- 🔸 Tabla: book (coinciden con publications de tipo 'book')
INSERT INTO
    book (
        publication_id,
        isbn,
        genre,
        edition
    )
VALUES (
        1,
        '978-3-16-148410-0',
        'Tecnología',
        1
    ),
    (
        2,
        '978-0-13-110362-7',
        'Redes',
        2
    ),
    (
        3,
        '978-1-59327-599-0',
        'Bases de datos',
        1
    );

-- 🔸 Tabla: article (coinciden con publications de tipo 'article')
INSERT INTO
    article (
        publication_id,
        DOI,
        abstract,
        keywords,
        indexation,
        magazine,
        area
    )
VALUES (
        4,
        '10.1000/xyz123',
        'Estudio sobre IA aplicada a diagnósticos médicos.',
        'IA, medicina',
        'Scopus',
        'Revista Médica',
        'Salud'
    ),
    (
        5,
        '10.1000/abc456',
        'Investigación en técnicas de seguridad web.',
        'seguridad, web',
        'Latindex',
        'Revista de Ciberseguridad',
        'Informática'
    );