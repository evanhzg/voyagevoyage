# Voyage API

## 1. Notre projet

Nous créons une API qui permet de renvoyer les lieux touristiques (musées, restaurants, hôtels...)
pour une ville donnée et les villes touristiques pour un pays donné.

## 2. Installation 

Cloner le repository dans le dossier de son choix :

```bash
git clone https://github.com/evanhzg/voyagevoyage.git
```

Installer les dépendances :
```bash
cd 'Mon dossier'/voyagevoyage
composer install
```

Créer une paire clé privée/publique :

```bash
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

Créez une copie du fichier .env nommée .env.local et <b>remplissez la ligne 27</b> pour assurer la connection avec votre basse de donnée ainsi que <b>la ligne 34</b> avec la passphrase qui vous a permis de générer votre clé privée.

N'oubliez pas de créer la base de donnée :

```bash
php bin/console d:d:c
```

Mettre à jour le schéma de base de données :

```bash
php bin/console d:s:u --force
```

Et alimenter la base de données :

```bash
php bin/console d:f:l
```

N'oubliez pas de lancer votre Apache et MySQL, et de faire la commande :

```bash
symfony serve
```

C'est prêt !

## 3. Utilisation

Un dossier contenant des requêtes pratiques est accessible en important le lien postman :

<a>https://www.getpostman.com/collections/2fd0d55f5c8e4481b84e</a>

Pour commencer à utiliser l'api il faut d'abord se connecter

La requête de connexion est dans le dossier Postman. Les credentials sont :

    {
        "username": "admin",
        "password": "admin"
    }

Récupérez le token donné, et renseignez-le en tant que Bearer Token pour toutes les prochaines requêtes sur l'API.

L'API manipule 3 types de ressources : Country, City et Place.
Chacune de ses ressources est accessibles via 'ip du serveur'/api/countries, 'ip du serveur'/api/cities, 'ip du serveur'/api/places.

## 4. Documentation

La documentation est disponible via deux endpoints :
* /api/doc pour la version navigateur
* /api/doc.json pour la version json