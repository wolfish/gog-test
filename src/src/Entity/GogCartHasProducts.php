<?php

namespace App\Entity;

use App\Repository\GogCartHasProductsRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table('gog_cart_has_products', indexes: [
    new ORM\Index(name: 'fk_product_idx', columns: [ 'id_product' ]),
    new ORM\Index(name: 'fk_cart_idx', columns: [ 'id_cart' ])
])]
#[ORM\UniqueConstraint(columns: [ 'id_cart', 'id_product' ])]
#[ORM\Entity(repositoryClass: GogCartHasProductsRepository::class)]
#[UniqueEntity([ 'id_cart', 'id_product' ])]
class GogCartHasProducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column('id', 'integer')]
    #[Assert\NotNull]
    #[Assert\Unique]
    #[Assert\Type('integer')]
    private int $id;

    #[ORM\ManyToOne('GogCart', cascade: [ 'persist' ])]
    #[ORM\JoinColumn(name: 'id_cart', referencedColumnName: 'id', onDelete: 'cascade')]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    private GogCart $cart;

    #[ORM\ManyToOne('GogProducts', cascade: [ 'persist' ])]
    #[ORM\JoinColumn(name: 'id_product', referencedColumnName: 'id', onDelete: 'cascade')]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    private GogProducts $product;

    #[ORM\Column('quantity', 'integer')]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    private int $quantity = 1;

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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getCart(): ?GogCart
    {
        return $this->cart;
    }

    public function setCart(?GogCart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getProduct(): ?GogProducts
    {
        return $this->product;
    }

    public function setProduct(?GogProducts $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
