# Vite & Gourmand — Back-end

Vite & Gourmand est une application web de commande de menus traiteur à domicile, basée à Bordeaux. Elle permet aux clients de consulter le catalogue, de créer et suivre leurs commandes. Le personnel de l'entreprise dispose d'un espace staff pour gérer les commandes, le catalogue, les avis clients et les messages de contact. Un administrateur peut également gérer les comptes employés et consulter les statistiques de vente.

L'application est accessible en ligne : [vitegourmand-frontend.vercel.app](https://vitegourmand-frontend.vercel.app/)

## Technologies

- PHP 8.2, Symfony 7 et API Platform pour l'API REST
- Doctrine ORM et MySQL 8 pour les données relationnelles
- MongoDB pour les avis clients, les messages de contact et les statistiques de vente
- Authentification JWT avec gestion des rôles client, employé et administrateur
- Mailpit pour consulter les e-mails en développement local
- Docker Compose sous WSL2 pour un environnement de développement reproductible

Ce dépôt contient l'API Symfony du projet. L'interface React est disponible dans le dépôt [vitegourmand-frontend](https://github.com/robertoreynaleon/vitegourmand-frontend).

Le dossier `DOCUMENTATION APP/` rassemble les documents de conception du projet : documentation technique, manuel d'utilisation, gestion des tâches, modèle de données, diagrammes et éléments liés à l'identité visuelle.

## Développer en local avec Docker

> Cette procédure est prévue pour la branche `main`. Elle nécessite Docker Engine et le plugin Docker Compose installés dans WSL2. Docker Desktop, PHP, Composer, MySQL et MongoDB ne sont pas nécessaires sur la machine hôte.

### 1. Cloner le dépôt

Depuis un terminal WSL, clonez le dépôt puis placez-vous dans son dossier :

```bash
git clone git@github.com:robertoreynaleon/vitegourmand-back-end.git
cd vitegourmand-back-end
```

Pour cloner avec HTTPS, remplacez l'URL SSH par :

```bash
git clone https://github.com/robertoreynaleon/vitegourmand-back-end.git
```

### 2. Choisir les ports locaux

Chaque utilisateur travaille sur sa propre machine : `localhost` désigne donc toujours sa machine locale. Avant de démarrer les conteneurs, choisissez des ports qui ne sont pas utilisés par un autre projet.

Les variables suivantes permettent de définir les ports de l'API, de phpMyAdmin, de MongoDB et de Mailpit :

```bash
VG_BACKEND_PORT=<port-api> \
VG_PHPMYADMIN_PORT=<port-phpmyadmin> \
VG_MONGODB_PORT=<port-mongodb> \
VG_MAILPIT_PORT=<port-mailpit> \
docker compose up --build -d
```

Remplacez les valeurs entre chevrons par des ports libres sur votre machine. Les valeurs proposées par défaut dans le fichier Compose peuvent être conservées si elles sont disponibles.

### 3. Démarrer les conteneurs

La commande précédente construit les images et démarre les services nécessaires au back-end : Symfony, MySQL, phpMyAdmin, MongoDB et Mailpit.

Au premier démarrage, Docker installe les dépendances PHP, génère les clés JWT locales et importe automatiquement le jeu de données MySQL. Cette étape peut prendre quelques minutes.

Vérifiez ensuite que tous les services sont démarrés :

```bash
docker compose ps
```

L'API est disponible à l'adresse `http://localhost:<port-api>`. phpMyAdmin et Mailpit sont accessibles selon les ports choisis. MongoDB peut être consulté avec MongoDB Compass à l'adresse suivante :

```text
mongodb://localhost:<port-mongodb>/vitegourmand
```

Utilisez phpMyAdmin pour consulter MySQL avec les identifiants de développement définis dans le fichier Compose. Mailpit intercepte les e-mails de développement, notamment les confirmations de commande, sans les envoyer à de véritables destinataires.

### 4. Lancer le front-end

Pour utiliser l'application complète, clonez également le dépôt [vitegourmand-frontend](https://github.com/robertoreynaleon/vitegourmand-frontend) et démarrez-le dans un second terminal WSL.

Configurez l'URL de l'API du front-end avec le port choisi pour Symfony :

```bash
VG_FRONTEND_PORT=<port-front-end> \
VG_BACKEND_URL=http://localhost:<port-api> \
docker compose up --build -d
```

Consultez le README du front-end pour les instructions complètes. L'application sera disponible à l'adresse `http://localhost:<port-front-end>`.

### Commandes utiles

```bash
# Suivre les journaux de l'API
docker compose logs -f backend

# Exécuter une commande Symfony dans le conteneur
docker compose exec backend php bin/console about

# Arrêter les conteneurs en conservant les données
docker compose down

# Réinitialiser totalement l'environnement local et réimporter le jeu de données
docker compose down -v
docker compose up --build -d
```

> La dernière commande supprime les volumes Docker locaux : les données MySQL et MongoDB de développement seront perdues. Les données de démonstration seront réimportées au prochain démarrage.

## Organisation du code

```text
src/
├── Controller/      # Routes API et traitement des requêtes HTTP
├── Entity/          # Entités Doctrine représentant les données MySQL
├── Repository/      # Requêtes et accès aux données relationnelles
├── Service/         # Logique métier : commandes, e-mails, MongoDB et calculs
├── ApiResource/     # Ressources exposées par API Platform
├── DataFixtures/    # Jeux de données de développement
├── Enum/            # Valeurs métier, notamment les statuts de commande
└── EventSubscriber/ # Traitements déclenchés par les événements Symfony

config/              # Configuration Symfony, sécurité, CORS et JWT
migrations/          # Évolutions du schéma MySQL
templates/emails/    # Modèles des e-mails envoyés par l'application
public/uploads/      # Images téléversées pour les menus
```

Les contrôleurs délèguent les règles métier aux services et utilisent Doctrine pour accéder aux données MySQL. Les données documentaires MongoDB sont également centralisées dans les services dédiés.
