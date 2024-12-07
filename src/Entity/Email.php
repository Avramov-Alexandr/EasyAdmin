<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'email')]
class Email
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $domainId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $customersId = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $couponsId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ordersId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $packageId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $emailVerifyResult = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getDomainId(): ?int
    {
        return $this->domainId;
    }

    public function setDomainId(?int $domainId): self
    {
        $this->domainId = $domainId;
        return $this;
    }

    public function getCustomersId(): ?int
    {
        return $this->customersId;
    }

    public function setCustomersId(?int $customersId): self
    {
        $this->customersId = $customersId;
        return $this;
    }

    public function getCouponsId(): ?string
    {
        return $this->couponsId;
    }

    public function setCouponsId(?string $couponsId): self
    {
        $this->couponsId = $couponsId;
        return $this;
    }

    public function getOrdersId(): ?int
    {
        return $this->ordersId;
    }

    public function setOrdersId(?int $ordersId): self
    {
        $this->ordersId = $ordersId;
        return $this;
    }

    public function getPackageId(): ?int
    {
        return $this->packageId;
    }

    public function setPackageId(?int $packageId): self
    {
        $this->packageId = $packageId;
        return $this;
    }

    public function getEmailVerifyResult(): ?string
    {
        return $this->emailVerifyResult;
    }

    public function setEmailVerifyResult(?string $emailVerifyResult): static
    {
        $this->emailVerifyResult = $emailVerifyResult;

        return $this;
    }
}


