<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité PasswordResetToken.
 * Fournit des méthodes de recherche et de nettoyage des tokens.
 *
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    /**
     * Recherche un token valide (non expiré et non utilisé) par sa valeur.
     *
     * @param string $token Valeur hexadécimale du token
     * @return PasswordResetToken|null Le token correspondant, ou null s'il est invalide
     */
    public function findValidToken(string $token): ?PasswordResetToken
    {
        return $this->createQueryBuilder('t')
            ->where('t.token = :token')
            ->andWhere('t.expiresAt > :now')
            ->andWhere('t.usedAt IS NULL')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Supprime tous les tokens expirés ou déjà utilisés.
     * Cette méthode est appelée par la commande Symfony de purge.
     *
     * @return int Nombre de tokens supprimés
     */
    public function deleteExpiredAndUsed(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :now OR t.usedAt IS NOT NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
