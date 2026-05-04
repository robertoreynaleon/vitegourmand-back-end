<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Service d'envoi d'e-mails transactionnels.
 * Utilise Symfony Mailer avec des templates Twig (dossier templates/emails/).
 * Toutes les méthodes sont void et lancent une exception si l'envoi échoue
 * (les contrôleurs doivent gérer l'exception avec un try/catch).
 */
class MailService
{
    /** Adresse expéditrice commune à tous les e-mails. */
    private const FROM_EMAIL = 'robertoreynaleon@gmail.com';

    /** Nom affiché comme expéditeur dans la boîte mail du destinataire. */
    private const FROM_NAME  = 'Vite & Gourmand';

    public function __construct(private readonly MailerInterface $mailer) {}

    /**
     * Envoie l'e-mail de bienvenue après la création d'un compte client.
     */
    public function sendWelcome(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Bienvenue chez Vite & Gourmand !')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    /**
     * Notifie l'utilisateur que son mot de passe a été modifié.
     */
    public function sendPasswordChanged(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Votre mot de passe a été modifié')
            ->htmlTemplate('emails/password_changed.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    /**
     * Notifie l'utilisateur que ses informations de profil ont été mises à jour.
     */
    public function sendProfileUpdated(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Vos informations ont été mises à jour')
            ->htmlTemplate('emails/profile_updated.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    /**
     * Envoie la confirmation de commande au client après création.
     */
    public function sendOrderConfirmation(User $user, object $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Confirmation de votre commande #' . $order->getId())
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context(['user' => $user, 'order' => $order]);

        $this->mailer->send($email);
    }

    /**
     * Notifie le client que sa commande a été modifiée (après édition par l'utilisateur).
     */
    public function sendOrderModified(User $user, object $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Votre commande #' . $order->getId() . ' a été modifiée')
            ->htmlTemplate('emails/order_modified.html.twig')
            ->context(['user' => $user, 'order' => $order]);

        $this->mailer->send($email);
    }

    /**
     * Notifie le client que sa commande a été annulée.
     * @param int    $orderId   Identifiant de la commande annulée
     * @param string $orderDate Date de la commande formatée pour affichage
     */
    public function sendOrderCancelled(User $user, int $orderId, string $orderDate): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Votre commande #' . $orderId . ' a été annulée')
            ->htmlTemplate('emails/order_cancelled.html.twig')
            ->context(['user' => $user, 'orderId' => $orderId, 'orderDate' => $orderDate]);

        $this->mailer->send($email);
    }

    /**
     * Informe le client d'une mise à jour de statut de sa commande par le staff.
     * @param string $staffMessage  Message explicatif du staff (affiché dans l'e-mail)
     * @param bool   $equipmentLoan Indique si du matériel est prêté (affiché dans l'e-mail)
     */
    public function sendOrderStaffUpdate(User $user, object $order, string $staffMessage, bool $equipmentLoan): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Mise à jour de votre commande #' . $order->getId())
            ->htmlTemplate('emails/order_staff_update.html.twig')
            ->context([
                'user'          => $user,
                'order'         => $order,
                'staffMessage'  => $staffMessage,
                'equipmentLoan' => $equipmentLoan,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie l'e-mail de bienvenue à un nouvel employé créé par l'administrateur.
     * Note : le mot de passe n'est pas inclus dans l'e-mail (l'employé doit le demander à l'admin).
     */
    public function sendStaffWelcome(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Bienvenue dans l\'équipe Vite & Gourmand !')
            ->htmlTemplate('emails/staff_welcome.html.twig')
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    /**
     * Notifie le client que sa commande a été refusée par le staff avec le motif indiqué.
     */
    public function sendOrderRefused(User $user, object $order, string $staffMessage): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Votre commande #' . $order->getId() . ' a été refusée')
            ->htmlTemplate('emails/order_refused.html.twig')
            ->context([
                'user'         => $user,
                'order'        => $order,
                'staffMessage' => $staffMessage,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie la réponse du staff au visiteur qui a soumis un message de contact.
     * @param string $clientEmail   Adresse e-mail du visiteur
     * @param string $subject       Sujet original du message de contact
     * @param string $staffResponse Texte de la réponse rédigée par le staff
     */
    public function sendContactReply(string $clientEmail, string $subject, string $staffResponse): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to($clientEmail)
            ->subject('Réponse à votre message : ' . $subject)
            ->htmlTemplate('emails/contact_reply.html.twig')
            ->context([
                'subject'       => $subject,
                'staffResponse' => $staffResponse,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie le lien de réinitialisation de mot de passe à l'utilisateur.
     * Le lien pointe vers la page frontend (/auth/reset-password?token=...).
     *
     * @param User   $user  Utilisateur qui a demandé la réinitialisation
     * @param string $token Token hexadécimal de 64 caractères à inclure dans l'URL
     */
    public function sendPasswordReset(User $user, string $token): void
    {
        // Lien renvoyant vers la page React de réinitialisation (frontend Vercel)
        $resetLink = 'https://vitegourmand-frontend.vercel.app/auth/reset-password?token=' . urlencode($token);

        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'user'      => $user,
                'resetLink' => $resetLink,
            ]);

        $this->mailer->send($email);
    }
}
