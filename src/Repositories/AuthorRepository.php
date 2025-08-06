<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use App\Config\Database;
use App\Entities\Author;
use PDO;

class AuthorRepository implements RepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM author");
        $list = [];
        while ($row = $stmt->fetch()) {
            $list[] = $this->hydrate($row);
        }
        return $list;
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Author) {
            throw new \InvalidArgumentException("Author expected");
        }

        $sql = "INSERT INTO author 
                (first_name, last_name, username, email, password, orcid, afiliation) 
                VALUES (:first_name, :last_name, :username, :email, :password, :orcid, :afiliation)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':first_name' => $entity->getFirstName(),
            ':last_name' => $entity->getLastName(),
            ':username' => $entity->getUsername(),
            ':email' => $entity->getEmail(),
            ':password' => $entity->getPassword(),
            ':orcid' => $entity->getOrcid(),
            ':afiliation' => $entity->getAfiliation()
        ]);
    }

    public function update(object $entity): bool{
        if (!$entity instanceof Author) {
            throw new \InvalidArgumentException("Author expected");
        }

        $sql = "UPDATE author SET 
                first_name = :first_name, 
                last_name = :last_name, 
                username = :username, 
                email = :email, 
                password = :password, 
                orcid = :orcid, 
                affiliation = :afiliation 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $entity->getId(),
            ':first_name' => $entity->getFirstName(),
            ':last_name' => $entity->getLastName(),
            ':username' => $entity->getUsername(),
            ':email' => $entity->getEmail(),
            ':password' => $entity->getPassword(),
            ':orcid' => $entity->getOrcid(),
            ':afiliation' => $entity->getAfiliation()
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM author WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    //Convierte filas de la base de datos a objetos Author
    private function hydrate(array $row): Author
    {
        $author = new Author(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            'temporal',
            $row['orcid'],
            $row['afiliation']
        );

        //REEMPLAZAR HASH SIN REGENERAR
        $ref = new \ReflectionClass($author);
        $property = $ref->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($author, $row['password']);
        return $author;
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM author WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

};