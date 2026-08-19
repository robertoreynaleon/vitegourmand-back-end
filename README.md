# Vite & Gourmand — Back-end

Vite & Gourmand est une application web de gestion de menus traiteur. Elle permet aux visiteurs de consulter le catalogue, aux clients de commander et de suivre leurs commandes, et aux employés ou administrateurs de gérer le catalogue, les commandes, les avis et les statistiques.

L'application est accessible en ligne : [vitegourmand-frontend.vercel.app](https://vitegourmand-frontend.vercel.app/)

Ce dépôt contient l'API REST du projet. Le front-end React est disponible dans le dépôt [vitegourmand-frontend](https://github.com/robertoreynaleon/vitegourmand-frontend).

## Technologies

- PHP 8.2, Symfony 7 et API Platform
- Doctrine ORM et MySQL 8 pour les données relationnelles
- MongoDB pour les avis, messages de contact et statistiques
- Authentification JWT avec gestion des rôles
- Mailpit pour consulter les e-mails en développement local
- Docker Compose sous WSL2 pour un environnement reproductible

## Développer en local avec Docker

> Cette procédure correspond à la branche `chore/docker-wsl`, qui contient la configuration Docker du projet. Elle nécessite Docker Engine et le plugin Docker Compose installés dans WSL2. Docker Desktop, PHP, Composer, MySQL et MongoDB ne sont pas nécessaires sur la machine hôte.

### 1. Cloner le dépôt

Depuis un terminal WSL, clonez le dépôt puis placez-vous sur la branche Docker :

```bash
git clone git@github.com:robertoreynaleon/vitegourmand-back-end.git
cd vitegourmand-back-end
git switch chore/docker-wsl
```

Pour cloner avec HTTPS, remplacez l'URL SSH par :

```bash
git clone https://github.com/robertoreynaleon/vitegourmand-back-end.git
```

### 2. Démarrer les conteneurs

Construisez les images et démarrez l'environnement :

```bash
docker compose up --build -d
```

Au premier démarrage, Docker installe les dépendances PHP, génère les clés JWT locales et importe automatiquement `vitegourmand_data.sql` dans MySQL. Cette étape peut prendre quelques minutes.

Vérifiez que tous les services sont démarrés :

```bash
docker compose ps
```

### 3. Services disponibles

| Service | Rôle | Adresse locale |
| --- | --- | --- |
| `backend` | API Symfony / Apache | [http://localhost:8000](http://localhost:8000) |
| API des menus | Vérification rapide de l'API | [http://localhost:8000/api/menus](http://localhost:8000/api/menus) |
| `database` | Base de données MySQL 8 | Accessible depuis les conteneurs |
| `phpmyadmin` | Administration de MySQL | [http://localhost:8090](http://localhost:8090) |
| `mongodb` | Base documentaire MongoDB 6 | `mongodb://localhost:27018/vitegourmand` |
| `mailer` | Boîte de réception de développement Mailpit | [http://localhost:8025](http://localhost:8025) |

MySQL est volontairement accessible uniquement depuis le réseau Docker. Utilisez phpMyAdmin pour le consulter :

```text
Serveur : database
Utilisateur : vitegourmand
Mot de passe : local_dev_password
Base de données : vitegourmand
```

MongoDB peut être consulté avec MongoDB Compass installé sur le poste, grâce à l'URL suivante :

```text
mongodb://localhost:27018/vitegourmand
```

Mailpit intercepte les e-mails de développement, notamment les confirmations de commande, sans les envoyer à de véritables destinataires.

### 4. Lancer le front-end

Pour utiliser l'application complète, clonez également le dépôt front-end et démarrez sa branche Docker dans un second terminal WSL :

```bash
git clone git@github.com:robertoreynaleon/vitegourmand-frontend.git
cd vitegourmand-frontend
git switch chore/docker-wsl
docker compose up --build -d
```

Le front-end est alors disponible sur [http://localhost:3000](http://localhost:3000) et communique avec l'API sur `http://localhost:8000`.

### Commandes utiles

```bash
# Suivre les journaux de l'API
docker compose logs -f backend

# Exécuter une commande Symfony dans le conteneur
docker compose exec backend php bin/console about

# Arrêter les conteneurs en conservant les données
docker compose down

# Réinitialiser totalement l'environnement local et réimporter le dump SQL
docker compose down -v
docker compose up --build -d
```

> La dernière commande supprime les volumes Docker locaux : les données MySQL et MongoDB de développement seront perdues. Les données de démonstration seront réimportées au prochain démarrage.

## Contribution

Créez une branche dédiée pour chaque évolution, puis conservez des commits courts et explicites. Les secrets de production, les clés JWT et les fichiers d'environnement personnels ne doivent jamais être ajoutés à Git.
