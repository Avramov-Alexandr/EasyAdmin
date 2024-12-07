<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailVerificationService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function verifyEmail(string $email): string
    {
        $url = sprintf('https://api.elasticemail.com/v4/verifications/%s', $email);
        $apiKey = $_ENV['ELASTICEMAIL_API_KEY'];

        try {
            // Выполняем POST-запрос
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-ElasticEmail-ApiKey' => $apiKey,
                ],
                'json' => [
                    'emails' => [$email],
                ],
            ]);

            // Преобразуем ответ в массив
            $data = $response->toArray();

            return $data['Result'] ?? 'None';

        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $errorContent = $e->getResponse()->getContent(false);
            throw new \RuntimeException("ElasticEmail API Error: $errorContent");
        }
    }
}