<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Injecte les headers de sécurité HTTP sur toutes les réponses de l'API.
 * Protège contre les principales attaques web :
 *   - X-Frame-Options          : clickjacking (chargement dans une iframe malveillante)
 *   - X-Content-Type-Options   : MIME-sniffing (interprétation incorrecte du type de contenu)
 *   - Referrer-Policy          : fuite du token de réinitialisation dans le header Referer
 *   - Content-Security-Policy  : atténuation des attaques XSS (scripts, styles, fonts, API)
 *   - Permissions-Policy       : désactive les API navigateur non utilisées (caméra, micro, géoloc)
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    /**
     * Ajoute les headers de sécurité à chaque réponse HTTP.
     * N'écrase pas les headers déjà définis par Symfony (CORS, Cache-Control, etc.).
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        // Ne traiter que la réponse principale (pas les sous-requêtes internes)
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        // Empêche le chargement de l'app dans une iframe (protection clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Empêche le navigateur de deviner le type MIME (protection MIME-sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Limite les informations envoyées dans le header Referer
        // Protège notamment le token ?token=... du lien de réinitialisation de mot de passe
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Désactive les API navigateur non utilisées par l'application
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content-Security-Policy : définit les sources autorisées pour chaque type de ressource.
        // - default-src 'self'      : toutes les ressources par défaut depuis la même origine
        // - script-src 'self'       : scripts uniquement depuis la même origine (pas de scripts inline)
        // - style-src 'self' ...    : styles depuis la même origine + Google Fonts (polices de l'app)
        // - font-src 'self' ...     : polices depuis la même origine + Google Fonts CDN
        // - img-src 'self' data:    : images locales + data URIs (thumbs, avatars base64)
        // - connect-src 'self' ...  : appels fetch/XHR vers la même origine + backend local Symfony
        // - frame-ancestors 'none'  : renforce X-Frame-Options pour les navigateurs modernes
        // - form-action 'self'      : soumissions de formulaires uniquement vers la même origine
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "connect-src 'self' https://app-9cc45e16-e981-413e-bfd3-5455bf559b1f.cleverapps.io http://localhost:8000 http://localhost:3000",
            "frame-ancestors 'none'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // Garantie CORS : si NelmioCorsBundle a manqué l'origine (bug array_filter),
        // on injecte le header manuellement pour les origines connues.
        $origin = $event->getRequest()->headers->get('Origin');
        $allowedOrigins = [
            'https://vitegourmand-frontend.vercel.app',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
        ];
        if ($origin !== null && in_array($origin, $allowedOrigins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->setVary('Origin', false);
        }
    }
}
