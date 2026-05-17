README — Vite & Gourmand 

→ L'application est déjà déployée et accessible en ligne : https://vitegourmand-frontend.vercel.app/ ←


· INSTALLATION EN LOCAL

Ce guide explique comment faire tourner l’application en local sur Windows.
Il y a deux projets séparés : le backend Symfony et le frontend React.
Les deux doivent être lancés en même temps pour que l’application fonctionne.

# 1. Prérequis

Avant de commencer, il faut avoir installé les outils suivants sur la machine.

WAMP64 (inclut PHP 8.2, Apache et MySQL) : https://wampserver.com
Node.js version 18 ou supérieur : https://nodejs.org
Composer version 2 ou supérieur : https://getcomposer.org
Git : https://git-scm.com
MongoDB Community Server version 6 : https://www.mongodb.com/try/download/community
MongoDB Compass (optionnel, pour voir les données MongoDB) : https://www.mongodb.com/products/compass

Pour vérifier que tout est bien installé, ouvrir un terminal et taper ces commandes :

```
php -v
composer -v
node -v
git --version
```

Chaque commande doit afficher un numéro de version. Si l’une d’elles ne fonctionne pas,
l’outil n’est pas installé ou n’est pas reconnu par Windows (problème de PATH).

Activer l’extension PHP MongoDB dans WAMP :

1. Cliquer sur l’icône WAMP dans la barre des tâches (en bas à droite)
2. Aller dans PHP puis PHP Extensions
3. Chercher php_mongodb dans la liste et cliquer dessus pour l’activer
4. WAMP redémarre les services automatiquement

Si php_mongodb n’apparaît pas dans la liste, télécharger le fichier .dll correspondant
à votre version PHP sur https://pecl.php.net/package/mongodb, le placer dans le dossier
C:\wamp64\bin\php\php8.2.x\ext\ et ajouter la ligne extension=mongodb dans le php.ini de WAMP.


# 2. Cloner les dépôts

Ouvrir un terminal et aller dans le dossier où les projets seront stockés.
Avec WAMP, le dossier recommandé est C:\wamp64\www\projects\.

```
cd C:\wamp64\www\projects
```

Cloner les deux dépôts :

```
git clone https://github.com/robertoreynaleon/vitegourmand-back-end.git
git clone https://github.com/robertoreynaleon/vitegourmand-frontend.git
```

Après le clone, on doit avoir deux dossiers :
- vitegourmand-back-end
- vitegourmand-frontend


# 3. Configurer le virtual host WAMP

Le backend Symfony a besoin d’un nom de domaine local (vitegourmand.local) pour fonctionner
avec Apache. Il faut modifier deux fichiers pour ça.

## 3.1 Modifier le fichier hosts Windows

Ce fichier dit à Windows que vitegourmand.local pointe vers notre propre machine.

1. Ouvrir le Bloc-notes en tant qu’administrateur (clic droit → Exécuter en tant qu’administrateur)
2. Aller dans Fichier → Ouvrir
3. Naviguer vers C:\Windows\System32\drivers\etc\
4. Changer le filtre en bas vers "Tous les fichiers (*.*)" et ouvrir le fichier hosts
5. Aller tout en bas et ajouter cette ligne :

```
127.0.0.1       vitegourmand.local
```

6. Sauvegarder avec Ctrl + S

## 3.2 Configurer le virtual host Apache

1. Cliquer sur l’icône WAMP dans la barre des tâches
2. Aller dans Apache → httpd-vhosts.conf
3. Aller en bas du fichier et ajouter ce bloc :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/wamp64/www/projects/vitegourmand-back-end/public"
    ServerName vitegourmand.local
    <Directory "C:/wamp64/www/projects/vitegourmand-back-end/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

ATTENTION : utiliser des slashs / et non des anti-slashs \ dans ce fichier.

4. Sauvegarder le fichier
5. Redémarrer WAMP : cliquer sur l’icône WAMP puis Redémarrer tous les services

Pour vérifier que ça fonctionne, ouvrir un navigateur et aller sur http://vitegourmand.local.
Une réponse doit s’afficher (même une erreur Symfony, c’est bon signe).


# 4. Installer les dépendances du backend

Dans un terminal, aller dans le dossier du backend :

```
cd C:\wamp64\www\projects\vitegourmand-back-end
```
Lancer l’installation des dépendances PHP :

```
composer install
```

Cette commande lit le fichier composer.json et installe tous les packages dans le dossier vendor/.
Cela peut prendre quelques minutes.


# 5. Créer le fichier .env.local

Le fichier .env contient la configuration du projet. Il ne faut jamais le modifier directement.
À la place, créer un fichier .env.local dans le dossier vitegourmand-back-end/.
Ce fichier contient les valeurs adaptées à l’environnement local et n’est pas versionné dans Git.

Créer le fichier .env.local et y coller le contenu suivant :

```env
APP_ENV=dev
APP_SECRET=local_dev_secret_change_this_32chars

DATABASE_URL="mysql://root:@127.0.0.1:3306/vitegourmand?serverVersion=8.0.32&charset=utf8mb4"

MONGODB_URL=mongodb://localhost:27017
MONGODB_DATABASE=vitegourmand

CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=bc94135689fedacf43931d9d454298a62ecf51e6461330f11ac137ae09bb72bb

MAILER_DSN=null://null
```

Note sur MySQL : avec WAMP, le compte par défaut est root sans mot de passe.
Si un mot de passe a été configuré, remplacer root:@ par root:votre_mot_de_passe@.

Note sur APP_SECRET : cette valeur peut être n’importe quelle chaîne de 32 caractères.
Elle sert à Symfony pour sécuriser les sessions.

Note sur MAILER_DSN : avec null://null les emails ne s’envoient pas mais l’application
fonctionne normalement. Voir l’étape 9 pour activer les emails en local.


# 6. Créer la base de données MySQL

## 6.1 Créer la base de données

1. S’assurer que WAMP est démarré (icône verte dans la barre des tâches)
2. Ouvrir un navigateur et aller sur http://localhost/phpmyadmin
3. Se connecter avec root et sans mot de passe
4. Cliquer sur Nouvelle base de données dans le panneau de gauche
5. Entrer le nom vitegourmand
6. Choisir l’interclassement utf8mb4_unicode_ci
7. Cliquer sur Créer

## 6.2 Importer les données

Le fichier vitegourmand_data.sql contient toutes les tables et les données de démonstration
(menus, plats, allergènes, utilisateurs, commandes, etc.).

1. Dans phpMyAdmin, cliquer sur la base de données vitegourmand dans le panneau de gauche
2. Cliquer sur l’onglet Importer en haut
3. Cliquer sur Choisir un fichier et sélectionner :
   C:\wamp64\www\projects\vitegourmand-back-end\vitegourmand_data.sql
4. Cliquer sur Importer
5. Vérifier que les tables apparaissent dans le panneau de gauche :
   allergens, dishes, dish_allergens, menu_dishes, menu_images, menus,
   order_menus, orders, password_reset_tokens, regimes, roles, users


# 7. Configurer MongoDB

Le backend utilise MongoDB pour stocker les avis clients, les statistiques et les messages de contact.

1. Télécharger et installer MongoDB Community Server : https://www.mongodb.com/try/download/community
2. Pendant l’installation, cocher "Install MongoDB as a Service" pour qu’il démarre automatiquement
3. MongoDB tourne sur le port 27017 par défaut

Pour vérifier que MongoDB est bien démarré, ouvrir PowerShell et taper :

```
Get-Service -Name MongoDB
```

Le statut doit être Running. Si ce n’est pas le cas :

```
Start-Service -Name MongoDB
```

La base de données vitegourmand et ses collections se créent automatiquement lors du premier
usage de l’application. Il n’y a rien à faire manuellement.


# 8. Générer les clés JWT

L’authentification utilise des clés RSA pour signer les tokens JWT.
Ces clés ne sont pas dans le dépôt Git, il faut les générer une fois en local.

Dans le terminal, se placer dans le dossier backend et lancer la commande :

```
cd C:\wamp64\www\projects\vitegourmand-back-end
php bin/console lexik:jwt:generate-keypair
```

Cette commande crée deux fichiers dans le dossier config/jwt/ :
- private.pem (clé privée, à ne jamais partager)
- public.pem (clé publique)

Si la commande demande une passphrase, entrer la valeur du JWT_PASSPHRASE du fichier .env.local.
Si le dossier config/jwt/ n’existe pas, le créer manuellement avant de lancer la commande.


# 9. Configurer les emails avec Ethereal Mail (optionnel)

Par défaut les emails sont désactivés (MAILER_DSN=null://null). L’application fonctionne
normalement mais les emails de confirmation, bienvenue, etc. ne s’envoient pas.

Pour les voir en local, on peut utiliser Ethereal Mail, un service gratuit qui intercepte
les emails de test sans les envoyer aux vrais destinataires.

1. Aller sur https://ethereal.email
2. Cliquer sur Create Ethereal Account (un compte de test est créé instantanément)
3. Noter les identifiants SMTP affichés : Host, Port, Username et Password

Dans le fichier .env.local, remplacer la ligne MAILER_DSN par :

```env
MAILER_DSN=smtp://VOTRE_USERNAME:VOTRE_PASSWORD@smtp.ethereal.email:587
```

Si le username contient un @, le remplacer par %40 dans l’URL.

Pour voir les emails reçus, aller sur https://ethereal.email et se connecter avec le même compte.


# 10. Tester le backend

Vider le cache Symfony après toutes les configurations :

```
cd C:\wamp64\www\projects\vitegourmand-back-end
php bin/console cache:clear
```

Ouvrir un navigateur et aller sur http://vitegourmand.local/api/menus

Si une réponse JSON s’affiche avec la liste des menus, le backend fonctionne correctement.


# 11. Lancer le frontend React

Ouvrir un nouveau terminal (séparé du terminal backend) et aller dans le dossier frontend :

```
cd C:\wamp64\www\projects\vitegourmand-frontend
```

Installer les dépendances :

```
npm install
```

Le fichier .env du frontend contient déjà la bonne URL pour le développement local :

```
REACT_APP_API_URL=http://vitegourmand.local
```

Démarrer le serveur de développement React :

```
npm start
```

Après quelques secondes, le navigateur s’ouvre automatiquement sur http://localhost:3000.
Laisser ce terminal ouvert pendant toute la session de développement.


# 12. Vérification finale

À ce stade, voilà ce qui doit tourner :

- WAMP : icône verte dans la barre des tâches (Apache + MySQL actifs)
- Backend : http://vitegourmand.local/api/menus répond en JSON
- Frontend : http://localhost:3000 affiche la page d’accueil
- MongoDB : service Running sur le port 27017

Pour tester le parcours complet :
1. Aller sur http://localhost:3000
2. Créer un compte client via le formulaire d’inscription
3. Se connecter et parcourir les menus
4. Ajouter un menu au panier et passer une commande
5. Vérifier que la commande apparaît dans l’espace client


# 13. Identifiants de démonstration

Ces comptes sont inclus dans les données importées depuis vitegourmand_data.sql.

Administrateur : admin@vitegourmand.fr / Admin@1234
Staff : staff@vitegourmand.fr / Staff@1234
Client : client@vitegourmand.fr / Client@1234


# 15. Installation sur Mac

Les étapes sont similaires à Windows avec quelques différences.

Installer Homebrew si ce n’est pas déjà fait :

```
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Installer les outils nécessaires :

```
brew install php@8.2
brew install mysql
brew install mongodb-community@6.0
brew install composer
brew install node
```

Démarrer les services :

```
brew services start mysql
brew services start mongodb-community@6.0
```

Sur Mac, WAMP n’existe pas. Le backend Symfony peut tourner avec le serveur intégré de Symfony :

```
cd /chemin/vers/vitegourmand-back-end
symfony server:start
```

Cela démarre le backend sur http://localhost:8000.
Dans ce cas, mettre à jour le .env du frontend :

```
REACT_APP_API_URL=http://localhost:8000
```

Pour modifier le fichier hosts sur Mac :

```
sudo nano /etc/hosts
```

Ajouter à la fin : 127.0.0.1  vitegourmand.local
Sauvegarder avec Ctrl+X, puis Y, puis Entrée.

Si des erreurs de permission apparaissent sur le dossier var/, les corriger avec :

```
chmod -R 777 var/
```
La génération des clés JWT et le démarrage du frontend sont identiques à Windows.


Vite & Gourmand — Application web de commande de menus traiteur à domicile, Bordeaux.
Développé avec React 19, Symfony 7.4, MySQL 8 et MongoDB 6.
