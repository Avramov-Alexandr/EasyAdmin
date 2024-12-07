<?php
namespace App\Service;

use App\Entity\Domain;
use App\Entity\Message;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class EmailSenderService
{
    private MailerInterface $mailer;
    private TransportInterface $transport;

    public function __construct(MailerInterface $mailer, TransportInterface $transport)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
    }


    public function sendEmail(Domain $domain, Message $message, string $recipient): void
    {
        // Генерация DSN
        $dsn = (new MailerDomainService())->getMailerDsn($domain);

        // Установка DSN для транспорта
        $this->transport->fromString($dsn);

        // Формирование письма
        $email = (new Email())
            ->from($domain->getFromEmail())
            ->to($recipient)
            ->subject($message->getSubject())
            ->html($message->getBody());

        // Отправка письма
        $this->mailer->send($email);
    }
}
