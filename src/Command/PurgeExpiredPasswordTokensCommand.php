<?php

namespace App\Command;

use App\Repository\PasswordResetTokenRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande Symfony de purge des tokens de réinitialisation de mot de passe.
 * Supprime de la base de données tous les tokens expirés ou déjà utilisés.
 *
 * Usage : php bin/console app:purge-password-reset-tokens
 *
 * Planification recommandée (cron) : tous les jours à 3h du matin.
 * Exemple crontab : 0 3 * * * /usr/bin/php /var/www/html/bin/console app:purge-password-reset-tokens
 */
#[AsCommand(
    name: 'app:purge-password-reset-tokens',
    description: 'Supprime les tokens de réinitialisation de mot de passe expirés ou utilisés.',
)]
class PurgeExpiredPasswordTokensCommand extends Command
{
    public function __construct(
        private readonly PasswordResetTokenRepository $tokenRepository
    ) {
        parent::__construct();
    }

    /**
     * Exécute la purge et affiche le nombre de tokens supprimés.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->tokenRepository->deleteExpiredAndUsed();

        if ($count === 0) {
            $io->success('Aucun token à purger.');
        } else {
            $io->success(sprintf('%d token(s) de réinitialisation supprimé(s).', $count));
        }

        return Command::SUCCESS;
    }
}
