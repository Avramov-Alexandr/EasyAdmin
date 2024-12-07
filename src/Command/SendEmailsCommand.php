<?php

namespace App\Command;

use App\Entity\Domain;
use App\Entity\Email;
use App\Entity\Message;
use App\Entity\History;
use App\Service\MailerDomainService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Shapecode\Bundle\CronBundle\Attribute\AsCronJob;

#[AsCommand(
    name: 'send:emails',
    description: 'Send emails from the message queue'
)]
#[AsCronJob('*/10 * * * *')]
class SendEmailsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private MailerDomainService $mailerDomainService;
    private LoggerInterface $logger;

    protected function configure(): void
    {
        $this->addArgument('limit', InputArgument::OPTIONAL, 'Limit', 10);
    }

    public function __construct(EntityManagerInterface $entityManager,MailerDomainService $mailerDomainService, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailerDomainService = $mailerDomainService;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = $input->getArgument('limit');

        $repository = $this->entityManager->getRepository(Message::class);
        $emailRepository = $this->entityManager->getRepository(Email::class);


        $message = $repository->findOneBy(['active' => true, 'sent' => false]);

        if (!$message) {
            $output->writeln('<info>No active message to send.</info>');
            $this->logger->info('No active message to send.');
            return Command::INVALID;
        }


        /** @var Domain $config */
        $domain = $message->getDomain();
        if (!$domain) {
            $output->writeln('<info>No associated domain found for the message.</info>');
            return Command::INVALID;
        }


        $mailerDsn = $this->mailerDomainService->getMailerDsn($domain);
        $output->writeln(sprintf('<info>Using DSN: %s</info>', $mailerDsn));

        $validEmails = $emailRepository->createQueryBuilder('e')
            ->where('e.emailVerifyResult = :status')
            ->setParameter('status', 'Valid')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if ($validEmailsCount = count($validEmails)) {
            $progressBar = new ProgressBar($output, $validEmailsCount);
            $progressBar->start();



            $successful = 0;
            $failed = 0;
            $errors = [];

            /** @var Email $recipient */
            foreach ($validEmails as $recipient) {
                try {


                    $transport = new EsmtpTransport($domain->getSmtpHost(), $domain->getSmtpPort());
                    $transport->setUsername($domain->getFromEmail());
                    $transport->setPassword($domain->getSmtpPass());


                    $mailer = new Mailer($transport);


                    $email = (new MimeEmail())
                        ->from(new Address($domain->getFromEmail(), $domain->getFromName()))
                        ->to($recipient->getEmail())
                        ->subject($message->getSubject())
                        ->html($message->getBody())
                        ->text($message->getBody());

                    foreach ($message->getAttachments() as $attachment) {
                        $filePath = __DIR__ . '/../../public/uploads/attachments/' . $attachment->getFile(); // Путь к файлу
                        $fileName = $attachment->getFileFile(); // Имя файла

                        if (file_exists($filePath)) {
                            $email->attachFromPath($filePath, $fileName);
                        } else {
                            $this->logger->error(sprintf('Attachment file not found: %s', $filePath));
                        }
                    }


                    $mailer->send($email);

                    $successful++;

                    if (!$this->entityManager->contains($message)) {
                        $this->entityManager->persist($message);
                        $this->entityManager->flush();
                    }

                    $history = new History();
                    $history
                        ->setClientId('DefaultClient')
                        //->setMessage($message)
                        ->setDomain($domain)
                        ->setDate(new \DateTime())
                        ->setEmail($recipient->getEmail());

                    $message->addHistory($history);

                    $this->entityManager->persist($history);

                    $this->entityManager->flush();

                } catch (\Exception $e) {
                    $errors[] = sprintf('<error>Failed to send email to %s: %s</error>', $recipient->getEmail(), $e->getMessage());
                    $failed++;
                } catch (TransportExceptionInterface $e) {
                }

                $progressBar->advance();
            }

            if(!empty($errors))
            {
                foreach ($errors as $error) {
                    $output->writeln($error);
                }
            }

            $progressBar->finish();
        }else{
            $output->writeln('');
            $output->writeln('<comment>No valid email addresses found for this message.</comment>');
            $this->logger->warning('No valid email addresses found for the message.');
            return Command::INVALID;
        }


        if ($failed === 0) {
            $message->setSent(true);
            $message->setSentAt(new \DateTime());
        }


        $this->entityManager->flush();

        if ($failed === 0) {
            $output->writeln('');
            $output->writeln('<info>All emails sent successfully. Message marked as sent.</info>');
            $this->logger->info(sprintf('Message ID %d marked as sent.', $message->getId())); // func was deleted
        } else {
            $output->writeln('');
            $output->writeln(sprintf('<error>%d emails failed to send.</error>', $failed));
            $this->logger->warning(sprintf('%d emails failed to send for Message ID %d.', $failed, $message->getId())); // func was deleted
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Summary: %d successful, %d failed.</info>', $successful, $failed));

        return Command::SUCCESS;
    }
}
