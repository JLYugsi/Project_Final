<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;
use App\Repositories\AuthorRepository;
use App\Entities\Article;
use App\Entities\Author;

class ArticleController
{
    private ArticleRepository $articleRepository;
    private AuthorRepository $authorRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
        $this->authorRepository = new AuthorRepository();
    }

    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $article = $this->articleRepository->findById((int)$_GET['id']);
                echo json_encode($article ? $this->articleToArray($article) : ['error' => 'Article not found']);
            } else {
                $list = array_map(
                    [$this, 'articleToArray'],
                    $this->articleRepository->findAll()
                );
                echo json_encode($list);
            }
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            $author = $this->authorRepository->findById((int)$payload['author_id'] ?? 0);
            if (!$author) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid Author']);
                return;
            }

            $article = new Article(
                $payload['publication_id'],
                $payload['titulo'],
                $payload['description'],
                new \DateTime($payload['publication_date'] ?? 'now'),
                $author,
                $payload['DOI'],
                $payload['abstract'],
                $payload['keywords'],
                $payload['indexation'],
                $payload['magazine'],
                $payload['area']
            );

            echo json_encode(['success' => $this->articleRepository->create($article)]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->articleRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Article not found']);
                return;
            }

            if (isset($payload['author_id'])) {
                $author = $this->authorRepository->findById((int)$payload['author_id']);
                if ($author) $existing->setAuthor($author);
            }

            if (isset($payload['titulo'])) $existing->setTitulo($payload['titulo']);
            if (isset($payload['description'])) $existing->setDescription($payload['description']);
            if (isset($payload['publication_date'])) $existing->setPublicationDate(new \DateTime($payload['publication_date']));
            if (isset($payload['DOI'])) $existing->setDoi($payload['DOI']);
            if (isset($payload['abstract'])) $existing->setAbstract($payload['abstract']);
            if (isset($payload['keywords'])) $existing->setKeywords($payload['keywords']);
            if (isset($payload['indexation'])) $existing->setIndexation($payload['indexation']);
            if (isset($payload['magazine'])) $existing->setMagazine($payload['magazine']);
            if (isset($payload['area'])) $existing->setArea($payload['area']);

            echo json_encode(['success' => $this->articleRepository->update($existing)]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->articleRepository->delete((int)($payload['id'] ?? 0))]);
            return;
        }

        http_response_code(405);
        echo json_encode(['error' => 'METHOD NOT ALLOWED']);
    }

    public function articleToArray(Article $article): array
    {
        return [
            'id' => $article->getId(),
            'title' => $article->getTitulo(),
            'description' => $article->getDescription(),
            'publicationDate' => $article->getPublicationDate()->format('Y-m-d'),
            'author' => [
                'id' => $article->getAuthor()->getId(),
                'firstName' => $article->getAuthor()->getFirstName(),
                'lastName' => $article->getAuthor()->getLastName(),
            ],
            'DOI' => $article->getDoi(),
            'abstract' => $article->getAbstract(),
            'keywords' => $article->getKeywords(),
            'indexation' => $article->getIndexation(),
            'magazine' => $article->getMagazine(),
            'area' => $article->getArea(),
        ];
    }
}