<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\GogProductsRepository;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Table('gog_products', indexes: [
    new ORM\Index(name: 'fk_currency_idx', columns: [ 'id_currency' ])
])]
#[ORM\Entity(repositoryClass: GogProductsRepository::class)]
class GogProducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column('id', 'integer')]
    #[Assert\NotNull]
    #[Assert\Unique]
    private int $id;

    #[ORM\Column('title', 'string', 255, unique: true)]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    #[Assert\Unique]
    private string $title;

    #[ORM\Column('price', 'integer')]
    #[Assert\PositiveOrZero]
    #[Assert\NotNull]
    private int $price = 0;

    #[ORM\Column('created', 'datetime', options: [
        'default' => 'CURRENT_TIMESTAMP'
    ])]
    #[Assert\DateTime]
    #[Assert\NotNull]
    private DateTime $created;

    #[ORM\Column('updated', 'datetime', nullable: true)]
    #[Assert\DateTime]
    private ?DateTime $updated;

    #[ORM\ManyToOne('GogCurrency', cascade: [ 'persist' ])]
    #[ORM\JoinColumn(name: 'id_currency', referencedColumnName: 'id', onDelete: 'restrict')]
    private GogCurrency $currency;

    public function __construct()
    {
        $this->created = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

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

    public function getCurrency(): GogCurrency
    {
        return $this->currency;
    }

    public function setCurrency(GogCurrency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

}
