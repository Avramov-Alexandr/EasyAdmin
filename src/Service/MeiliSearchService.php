<?php

namespace App\Service;

use Meilisearch\Client; // Исправленный регистр

class MeiliSearchService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client('http://test.wip:7700', 'mySecretMasterKey');
    }

    // Добавление документов в индекс
    public function addDocuments(string $indexName, array $documents): void
    {
        $this->client->index($indexName)->addDocuments($documents);
    }

    // Поиск в индексе
    public function search(string $indexName, string $query): array
    {
        $searchResult = $this->client->index($indexName)->search($query);
        return $searchResult->getHits();
    }

    // Удаление индекса
    public function deleteIndex(string $indexName): void
    {
        $this->client->deleteIndex($indexName);
    }

    public function configureIndex(string $indexName, array $searchableFields): void
    {
        $this->client->index($indexName)->updateSettings([
            'searchableAttributes' => $searchableFields
        ]);
    }
    public function addAllDocumentsForHistory(array $data): void
    {
        $documents = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'messageId' => $item->getMessage() ? $item->getMessage()->getId() : null,
                'domain' => $item->getDomain() ? $item->getDomain()->getName() : null,
                'date' => $item->getDate() ? $item->getDate()->format('Y-m-d H:i:s') : null,
                'email' => $item->getEmail(),
            ];
        }, $data);

        $this->client->index('History')->addDocuments($documents);
    }
    public function addAllDocumentsForEmail(array $data): void
    {
        $documents = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'email' => $item->getEmail(),
                'emailVerifyResult' => $item->getEmailVerifyResult(),
            ];
        }, $data);

        $this->client->index('Email')->addDocuments($documents);
    }

    public function addAllDocumentsForUser(array $data): void
    {
        $documents = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'username' => $item->getUsername(),
                'roles' => implode(',', $item->getRoles()),
            ];
        }, $data);

        $this->client->index('User')->addDocuments($documents);
    }
    public function addAllDocumentsForMessage(array $data): void
    {
        $documents = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'subject' => $item->getSubject(),
                'body' => $item->getBody(),
                'domain' => $item->getDomain()?->getName(),
            ];
        }, $data);

        $this->client->index('Message')->addDocuments($documents);
    }

    public function addAllDocumentsForD(array $domains): void
    {
        $formattedData = array_map(fn($domain) => $domain->toArray(), $domains);
        $this->client->index('Domain')->addDocuments($formattedData);
    }

    public function addAllDocumentsForDomain(array $data): void
    {
        $documents = array_map(function ($item) {
            return [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'smtpHost' => $item->getSmtpHost(),
                'smtpPort' => $item->getSmtpPort(),
                'smtpUser' => $item->getSmtpUser(),
                'fromEmail' => $item->getFromEmail(),
                'fromName' => $item->getFromName(),
                'fromHost' => $item->getFromHost(),
            ];
        }, $data);

        $this->client->index('Domain')->addDocuments($documents);
    }
}
