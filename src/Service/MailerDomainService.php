<?php
namespace App\Service;

use App\Entity\Domain;

class MailerDomainService
{

    public function getMailerDsn(Domain $domain): string
    {
        $protocol = $domain->isUseAuth() ? 'smtp' : 'smtp+plaintext';
        $user = urlencode($domain->getSmtpUser());
        $pass = urlencode($domain->getSmtpPass());
        $host = $domain->getSmtpHost();
        $port = $domain->getSmtpPort();

        return sprintf('%s://%s:%s@%s:%d', $protocol, $user, $pass, $host, $port);
    }
}
