<?php

namespace App\Entity;

use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un token de réinitialisation de mot de passe.
 * Un token est généré à la demande de l'utilisateur, valable 1 heure,
 * et marqué comme utilisé dès qu'il a servi une fois.
 */
#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
#[ORM\Table(name: 'password_reset_tokens')]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Utilisateur à qui appartient ce token. */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /** Valeur du token (64 caractères hexadécimaux, unique en base). */
    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    /** Date et heure d'expiration du token (1 heure après sa création). */
    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    /** Date et heure d'utilisation du token (null si pas encore utilisé). */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    /** Date et heure de création du token. */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTimeImmutable $usedAt): static
    {
        $this->usedAt = $usedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Retourne vrai si le token a expiré (date d'expiration dépassée).
     */
    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    /**
     * Retourne vrai si le token a déjà été utilisé.
     */
    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }
}
