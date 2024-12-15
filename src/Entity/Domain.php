<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\DomainRepository;

#[ORM\Entity]
#[ORM\Table(name: 'domain')]
class Domain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 16)]
    private ?string $clientId = null;

    #[ORM\Column(length: 255)]
    private ?string $smtpHost = null;

    #[ORM\Column(length: 255)]
    private ?string $smtpUser = null;

    #[ORM\Column(length: 255)]
    private ?string $smtpPass = null;

    #[ORM\Column]
    private ?int $smtpPort = null;

    #[ORM\Column]
    private ?bool $useAuth = null;

    #[ORM\Column(length: 255)]
    private ?string $fromEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $fromName = null;

    #[ORM\Column(length: 255)]
    private ?string $fromHost = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'domain', targetEntity: Message::class, cascade: ['persist', 'remove'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    // Геттеры и сеттеры
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function getSmtpHost(): ?string
    {
        return $this->smtpHost;
    }

    public function setSmtpHost(string $smtpHost): self
    {
        $this->smtpHost = $smtpHost;
        return $this;
    }

    public function getSmtpUser(): ?string
    {
        return $this->smtpUser;
    }

    public function setSmtpUser(string $smtpUser): self
    {
        $this->smtpUser = $smtpUser;
        return $this;
    }

    public function getSmtpPass(): ?string
    {
        return $this->smtpPass;
    }

    public function setSmtpPass(string $smtpPass): self
    {
        $this->smtpPass = $smtpPass;
        return $this;
    }

    public function getSmtpPort(): ?int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(int $smtpPort): self
    {
        $this->smtpPort = $smtpPort;
        return $this;
    }

    public function isUseAuth(): ?bool
    {
        return $this->useAuth;
    }

    public function setUseAuth(bool $useAuth): self
    {
        $this->useAuth = $useAuth;
        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    public function getFromHost(): ?string
    {
        return $this->fromHost;
    }

    public function setFromHost(string $fromHost): self
    {
        $this->fromHost = $fromHost;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setDomain($this);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getDomain() === $this) {
                $message->setDomain(null);
            }
        }
        return $this;
    }
    public function __toString(): string
    {
        return $this->name ?? 'Without name';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'clientId' => $this->getClientId(),
            'smtpHost' => $this->getSmtpHost(),
            'smtpUser' => $this->getSmtpUser(),
            'smtpPass' => $this->getSmtpPass(),
            'smtpPort' => $this->getSmtpPort(),
            'useAuth' => $this->isUseAuth(),
            'fromEmail' => $this->getFromEmail(),
            'fromName' => $this->getFromName(),
            'fromHost' => $this->getFromHost(),
            'createdAt' => $this->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}