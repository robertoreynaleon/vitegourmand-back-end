<?php

namespace App\Enum;

enum OrderStatus: string
{
    case EnAttente                  = 'en attente';
    case Acceptee                   = 'acceptée';
    case EnPreparation              = 'en préparation';
    case EnCoursLivraison           = 'en cours de livraison';
    case Livree                     = 'livrée';
    case EnAttenteRetourMateriel    = 'en attente de retour de matériel';
    case Terminee                   = 'terminée';
    case Annulee                    = 'annulée';
}
