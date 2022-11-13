<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\GogCartRepository;
use DateTime;

#[ORM\Table('gog_cart')]
#[ORM\Entity(repositoryClass: GogCartRepository::class)]
class GogCart
{

    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column('id', 'integer')]
    #[Assert\NotNull]
    #[Assert\Unique]
    private int $id;

    #[ORM\Column('id_session', 'string', 255, unique: true)]
    #[Assert\NotNull]
    #[Assert\Unique]
    #[Assert\Length(min: 1, max: 255)]
    private string $idSession;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdSession(): string
    {
        return $this->idSession;
    }

    public function setIdSession(string $idSession): self
    {
        $this->idSession = $idSession;

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
