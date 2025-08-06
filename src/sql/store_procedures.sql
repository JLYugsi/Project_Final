USE project_db;

DELIMITER $$;

-- Procedimiento para listar todos los libros
CREATE OR REPLACE PROCEDURE sp_book_list()
BEGIN
    SELECT 
        b.isbn,
        b.genre,
        b.edition,
        b.publication_id,
        p.id,
        p.title,
        p.publication_date,
        p.type,
        p.author_id,
        a.id,
        a.first_name,
        a.last_name
    FROM book b
        JOIN publication p ON b.publication_id = p.id
        JOIN author a ON p.author_id = a.id
    ORDER BY p.publication_date DESC;
END$$

-- Procedimiento para encontrar un libro por su ID de publicación
CREATE OR REPLACE PROCEDURE sp_find_book(IN p_id INT)
BEGIN
    SELECT 
        b.isbn,
        b.genre,
        b.edition,
        b.publication_id,
        p.id,
        p.title,
        p.publication_date,
        p.type,
        p.author_id,
        a.id,
        a.first_name,
        a.last_name
    FROM book b
        JOIN publication p ON b.publication_id = p.id
        JOIN author a ON p.author_id = a.id
    WHERE b.publication_id = p_id
    ORDER BY p.publication_date DESC; 
END$$

-- Procedimiento para crear un nuevo libro
CREATE OR REPLACE PROCEDURE sp_create_book(
    IN p_title              VARCHAR(255),
    IN p_description        TEXT,
    IN p_publication_date   DATE,
    IN p_author_id          INT,
    IN p_isbn               VARCHAR(20),
    IN p_genre              VARCHAR(20),
    IN p_edition            INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;
    INSERT INTO publication (title, description, publication_date, author_id, type)
    VALUES (p_title, p_description, p_publication_date, p_author_id, 'book');
    
    SET @new_pub_id := LAST_INSERT_ID();
    
    INSERT INTO book (publication_id, isbn, genre, edition)
    VALUES (@new_pub_id, p_isbn, p_genre, p_edition);
    
    COMMIT;
    SELECT @new_pub_id AS pub_id;
END$$

-- Procedimiento para actualizar un libro
CREATE OR REPLACE PROCEDURE sp_update_book(
    IN p_publication_id     INT,
    IN p_title              VARCHAR(255),
    IN p_description        TEXT,
    IN p_publication_date   DATE,
    IN p_author_id          INT,
    IN p_isbn               VARCHAR(20),
    IN p_genre              VARCHAR(20),
    IN p_edition            INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;
    UPDATE publication
    SET title = p_title,
        description = p_description,
        publication_date = p_publication_date,
        author_id = p_author_id
    WHERE id = p_publication_id;

    UPDATE book
    SET isbn = p_isbn,
        genre = p_genre,
        edition = p_edition
    WHERE publication_id = p_publication_id;
    COMMIT;
END$$

-- Procedimiento para eliminar un libro
CREATE OR REPLACE PROCEDURE sp_delete_book(IN p_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;
    DELETE FROM book WHERE publication_id = p_id;
    DELETE FROM publication WHERE id = p_id;
    COMMIT;
    SELECT 1 AS ok;
END$$

DELIMITER ;