<?php
//php -S localhost:8080 -t public
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\repositories\AuthorRepository;
use App\repositories\BookRepository;
use App\repositories\ArticleRepository;
use App\entities\Author;
use App\entities\Book;
use App\entities\Article; 

echo "<h1>Probando Repositorios</h1>";
echo "<hr>";
echo "<h2>1. Probando AuthorRepository::findAll()</h2>";
try {
    $authorRepo = new AuthorRepository();
    $authors = $authorRepo->findAll();

    if (empty($authors)) {
        echo "<p>No se encontraron autores en la base de datos.</p>";
    } else {
        echo "<h3>Autores encontrados:</h3>";
        echo "<ul>";
        foreach ($authors as $author) {
            // Asegúrate de que Author::getId(), getFirstName(), etc. sean públicos en Author.php
            if ($author instanceof Author) {
                echo "<li>ID: " . $author->getId() . " | Nombre: " . $author->getFirstName() . " " . $author->getLastName() . " | Email: " . $author->getEmail() . "</li>";
            } else {
                echo "<li>Error: Objeto no es una instancia de Author.</li>";
            }
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al probar AuthorRepository: " . $e->getMessage() . "</p>";
}
echo "<hr>";


// --- Prueba de BookRepository ---
echo "<h2>2. Probando BookRepository::findAll()</h2>";
try {
    $bookRepo = new BookRepository();
    $books = $bookRepo->findAll();

    if (empty($books)) {
        echo "<p>No se encontraron libros en la base de datos.</p>";
    } else {
        echo "<h3>Libros encontrados:</h3>";
        echo "<ul>";
        foreach ($books as $book) {
            if ($book instanceof Book) {
                $authorName = "Desconocido";
                if ($book->getAuthor() instanceof Author) {
                    $authorName = $book->getAuthor()->getFirstName() . " " . $book->getAuthor()->getLastName();
                }
                echo "<li>ID: " . $book->getId() . " | Título: " . $book->getTitulo() . " | ISBN: " . $book->getIsbn() . " | Autor: " . $authorName . "</li>";
            } else {
                echo "<li>Error: Objeto no es una instancia de Book.</li>";
            }
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al probar BookRepository: " . $e->getMessage() . "</p>";
}
echo "<hr>";


// --- Prueba de ArticleRepository ---
echo "<h2>3. Probando ArticleRepository::findAll()</h2>";
try {
    $articleRepo = new ArticleRepository();
    $articles = $articleRepo->findAll();

    if (empty($articles)) {
        echo "<p>No se encontraron artículos en la base de datos.</p>";
    } else {
        echo "<h3>Artículos encontrados:</h3>";
        echo "<ul>";
        foreach ($articles as $article) {
            if ($article instanceof Article) {
                $authorName = "Desconocido";
                if ($article->getAuthor() instanceof Author) {
                    $authorName = $article->getAuthor()->getFirstName() . " " . $article->getAuthor()->getLastName();
                }
                echo "<li>ID: " . $article->getId() . " | Título: " . $article->getTitulo() . " | DOI: " . $article->getDOI() . " | Autor: " . $authorName . "</li>";
            } else {
                echo "<li>Error: Objeto no es una instancia de Article.</li>";
            }
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error al probar ArticleRepository: " . $e->getMessage() . "</p>";
}
echo "<hr>";

echo "<h2>Fin de las pruebas.</h2>";