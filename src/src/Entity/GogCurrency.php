<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\GogCurrencyRepository;
use DateTime;

#[ORM\Table('gog_currency')]
#[ORM\Entity(repositoryClass: GogCurrencyRepository::class)]
class GogCurrency
{

    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column('id', 'integer')]
    #[Assert\NotNull]
    #[Assert\Unique]
    private int $id;

    #[ORM\Column('short_name', 'string', 3, unique: true)]
    #[Assert\Length(exactly: 3)]
    #[Assert\NotNull]
    #[Assert\Unique]
    #[Assert\Currency]
    private string $shortName;

    #[ORM\Column('full_name', 'string', 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    private string $fullName;

    #[ORM\Column('symbol', 'string', 20, unique: true)]
    #[Assert\Length(min: 1, max: 20)]
    #[Assert\NotNull]
    #[Assert\Unique]
    private string $symbol;

    #[ORM\Column('created', 'datetime', options: [
        'default' => 'CURRENT_TIMESTAMP'
    ])]
    #[Assert\NotNull]
    #[Assert\DateTime]
    private DateTime $created;

    #[ORM\Column('updated', 'datetime', nullable: true)]
    #[Assert\DateTime]
    private ?DateTime $updated;

    public function __construct()
    {
        $this->created = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }


}
