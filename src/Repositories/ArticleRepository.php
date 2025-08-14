<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Author;
use App\Entities\Article;
use App\Config\Database;
use App\Interfaces\RepositoryInterface;
use PDO;
use PDOException;
use ReflectionClass;

class ArticleRepository implements RepositoryInterface
{
    private PDO $db;
    private AuthorRepository $authorRepo;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->authorRepo = new AuthorRepository();
    }

    public function findAll(): array
    {
        try {
            // Llama al procedimiento almacenado sp_article_list
            $stmt = $this->db->query("CALL sp_article_list();");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtener como array asociativo
            $stmt->closeCursor(); // Uso correcto de closeCursor()

            $out = [];
            foreach ($rows as $r) {
                $out[] = $this->hydrate($r);
            }
            return $out;
        } catch (PDOException $e) {
            error_log("Error al obtener todos los artículos: " . $e->getMessage());
            return []; // Retorna un array vacío en caso de error
        }
    }

    public function findById(int $id): ?object
    {
        try {
            // Llama al procedimiento almacenado sp_find_article
            $stmt = $this->db->prepare("CALL sp_find_article(:id);");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $row ? $this->hydrate($row) : null;
        } catch (PDOException $e) {
            error_log("Error al encontrar artículo por ID: " . $e->getMessage());
            return null;
        }
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Article) {
            throw new \InvalidArgumentException('Article expected');
        }

        try {
            // Llama al procedimiento almacenado sp_create_article
            $stmt = $this->db->prepare("CALL sp_create_article(
                :titulo,
                :description,
                :publication_date,
                :author_id,
                :DOI,
                :abstract,
                :keywords,
                :indexation,
                :magazine,
                :area
            );");

            // Asegúrate de que el autor del artículo tenga un ID válido
            $authorId = $entity->getAuthor() ? $entity->getAuthor()->getId() : null;
            if (is_null($authorId)) {
                throw new \InvalidArgumentException('El autor del artículo debe tener un ID para ser creado.');
            }

            $stmt->bindValue(':titulo', $entity->getTitulo());
            $stmt->bindValue(':description', $entity->getDescription());
            $stmt->bindValue(':publication_date', $entity->getPublicationDate()->format('Y-m-d'));
            $stmt->bindValue(':author_id', $authorId);
            $stmt->bindValue(':DOI', $entity->getDOI());
            $stmt->bindValue(':abstract', $entity->getAbstract());
            $stmt->bindValue(':keywords', $entity->getKeywords());
            $stmt->bindValue(':indexation', $entity->getIndexation());
            $stmt->bindValue(':magazine', $entity->getMagazine());
            $stmt->bindValue(':area', $entity->getArea());

            $stmt->execute();

            // sp_create_article retorna el new_article_id, lo recuperamos
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($result && isset($result['new_pub_id'])) {
                // Actualiza el ID de la entidad Article con el ID generado por la base de datos
                $entity->setId((int)$result['new_pub_id']);
                return true;
            }
            return false;

        } catch (PDOException $e) {
            error_log("Error al crear artículo: " . $e->getMessage());
            return false;
        }
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Article) {
            throw new \InvalidArgumentException('Article expected');
        }
        if (is_null($entity->getId())) {
            throw new \InvalidArgumentException('El ID del artículo debe estar establecido para actualizar.');
        }

        try {
            // Llama al procedimiento almacenado sp_update_article
            $stmt = $this->db->prepare("CALL sp_update_article(
                :publication_id,
                :titulo,
                :description,
                :publication_date,
                :author_id,
                :DOI,
                :abstract,
                :keywords,
                :indexation,
                :magazine,
                :area
            );");

            $authorId = $entity->getAuthor() ? $entity->getAuthor()->getId() : null;
            if (is_null($authorId)) {
                throw new \InvalidArgumentException('El autor del artículo debe tener un ID para actualizar.');
            }

            $stmt->bindValue(':publication_id', $entity->getId());
            $stmt->bindValue(':titulo', $entity->getTitulo());
            $stmt->bindValue(':description', $entity->getDescription());
            $stmt->bindValue(':publication_date', $entity->getPublicationDate()->format('Y-m-d'));
            $stmt->bindValue(':author_id', $authorId);
            $stmt->bindValue(':DOI', $entity->getDOI());
            $stmt->bindValue(':abstract', $entity->getAbstract());
            $stmt->bindValue(':keywords', $entity->getKeywords());
            $stmt->bindValue(':indexation', $entity->getIndexation());
            $stmt->bindValue(':magazine', $entity->getMagazine());

            $stmt->execute();

            // sp_update_article retorna 1 si es exitoso
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $result && isset($result['OK']) && (int)$result['OK'] === 1;

        } catch (PDOException $e) {
            error_log("Error al actualizar artículo: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            // Llama al procedimiento almacenado sp_delete_article
            $stmt = $this->db->prepare("CALL sp_delete_article(:id);");
            $stmt->execute([':id' => $id]);

            // sp_delete_article retorna 1 si es exitoso
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $result && isset($result['OK']) && (int)$result['OK'] === 1;

        } catch (PDOException $e) {
            error_log("Error al eliminar artículo: " . $e->getMessage());
            return false;
        }
    }

    public function hydrate(array $row): Article
{
    // Verificación de si los índices existen en el array con valores predeterminados
    $author = new Author(
        (int) ($row['id'] ?? 0),
        $row['first_name'] ?? '',
        $row['last_name'] ?? '',
        $row['username'] ?? '',
        $row['email'] ?? '',
        'temporal', // El password no se trae desde la BD para seguridad, pero se podría
        $row['orcid'] ?? '',
        $row['afiliation'] ?? ''
    );

    // Reemplazar hash sin regenerar solo si existe password
    if (isset($row['password'])) {
        $ref = new ReflectionClass($author);
        $property = $ref->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($author, $row['password']);
    }

    // Creación de la entidad Article
    return new Article(
        (int) ($row['publication_id'] ?? 0),
        $row['titulo'] ?? '',
        $row['description'] ?? '',
        new \DateTime($row['publication_date'] ?? 'now'),
        $author,
        $row['DOI'] ?? '',
        $row['abstract'] ?? '',
        $row['keywords'] ?? '',
        $row['indexation'] ?? '',
        $row['magazine'] ?? '',
        $row['area'] ?? ''
    );
}
}    