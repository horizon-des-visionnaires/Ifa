# Project Details

 - on utilise docker pour run le projet,
 - on utilise l'architecture MVC pour une meilleure organisation du code,
 - Twig et un moteur de templates pour l'affichage des données,
 - Beekeeper pour voir les données dans la BDD

# Technologie

  - PHP
  - HTML/Twig
  - CSS/JS
  - MySQL
  - Docker
  - Beekeeper

# Prerequisites

  - Avoir docker d'installer
  - Avoir wsl 2 d'installer (suivre ce Readme pour l'installer https://github.com/lucasrebl/WSL)
  - Avoir un ide tel que (vs code, PHPStorm...)

# Installation

  - Première étape: clone le repository
    ```bash
    https://github.com/horizon-des-visionnaires/Ifa.git
    ```

  - Deuxième étape: Cliquer sur le lien suivant pour télécharger les fichier nécessaire au lancement du projet
      les fchiers à télécharger sont :
        - Dockerfile
        - docker-compose.yaml
        - .htaccess
    
      https://github.com/horizon-des-visionnaires/Ifa/releases

      Attention le fichier .htaccess est nommé 'default.htaccess',
      une fois télécharger renommé le juste '.htaccess' sinon cela ne fonctionnera pas.

  - Troisième étape: Ouvrer le projet dans un ide puis placé les 2 fichiers docker à la raçine du projet

  - Quatrième étape: Lancer docker et éxecuter la commande suivant
    ```bash
    docker compose up -d --build
    ```

    puis cette commande
    ```bash
    sudo chmod -R 777 src
    ```
    cette commande vous demanderz votre mot de passe

  - Cinquième étape: Aller dans le dossier src et éxecuter la commande suivant
    ```bash
    composer require "twig/twig:^3.0"
    ```
   
  - Sixième étape: dans le dossier src placé le fichier .htaccess télécharger précédemment 
    
  - Septième étape: Aller sur ce lien pour voir le site
    ```bash
    http://localhost:8080/
    ```
