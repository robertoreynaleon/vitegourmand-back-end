<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailService
{
    private const FROM_EMAIL = 'noreply@vitegourmand.fr';
    private const FROM_NAME  = 'Vite & Gourmand';

    public function __construct(private readonly MailerInterface $mailer) {}

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

    public function sendOrderStatusUpdate(User $user, object $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
            ->to(new Address($user->getEmail(), $user->getName() . ' ' . $user->getLastname()))
            ->subject('Mise à jour de votre commande #' . $order->getId())
            ->htmlTemplate('emails/order_status.html.twig')
            ->context(['user' => $user, 'order' => $order]);

        $this->mailer->send($email);
    }

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
}
