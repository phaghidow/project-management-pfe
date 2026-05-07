# Fiche technique de l'application

## 1. Identification du projet

- **Nom du projet** : Plateforme web de gestion de projets
- **Type** : Application web de gestion collaborative
- **Domaine** : Gestion de projets, tâches, jalons, notifications et suivi d'activité
- **Finalité** : Centraliser la planification, l'affectation, la validation et le suivi des projets au sein d'une organisation

## 2. Objectif de l'application

L'application permet de gérer le cycle de vie complet d'un projet, depuis sa création jusqu'à sa clôture. Elle intègre la gestion des utilisateurs, des structures organisationnelles, des projets, des jalons, des tâches, des pièces jointes, des commentaires et des notifications. L'interface est adaptée aux différents rôles utilisateurs afin de respecter les droits d'accès et le périmètre de visibilité de chacun.

## 3. Technologies utilisées

### 3.1 Backend

- **Langage** : PHP 8.2
- **Framework** : Laravel 12
- **Architecture** : MVC (Modèle-Vue-Contrôleur)
- **Authentification** : Système natif Laravel avec contrôle des sessions et middleware
- **Génération de documents** : `barryvdh/laravel-dompdf`

### 3.2 Frontend

- **Langages** : HTML, CSS, JavaScript
- **Bundler** : Vite
- **UI / CSS** : Tailwind CSS
- **Composants interactifs** : Alpine.js, Vue 3

### 3.3 Bibliothèques front-end

- **Calendrier** : FullCalendar
- **Diagramme de tâches** : Frappe Gantt
- **Graphiques** : Chart.js
- **Sélecteurs avancés** : Tom Select
- **Notifications temps réel** : Laravel Echo, Pusher
- **Sélecteur de date** : Flatpickr

### 3.4 Base de données

- **SGBD** : MySQL
- **Modèle de données** : relationnel

## 4. Fonctionnalités principales

### 4.1 Gestion des utilisateurs

- Création, modification, activation et désactivation des comptes
- Rattachement à une structure organisationnelle
- Gestion des statuts utilisateur et de l'historique des changements
- Restriction des comptes aux domaines email autorisés

### 4.2 Gestion des rôles

- **Administrateur** : accès complet à l'application
- **Chef de département** : accès limité à son département
- **Chef de projet** : accès limité aux projets dont il est responsable
- **Membre** : accès limité à ses propres tâches

### 4.3 Gestion des projets

- Création, consultation, modification et suppression des projets selon le rôle
- Affectation d'un responsable à chaque projet
- Suivi de l'état d'avancement du projet
- Validation du projet lorsque toutes les tâches sont validées

### 4.4 Gestion des jalons et des tâches

- Création et suivi des jalons par projet
- Création, affectation et validation des tâches
- Gestion des dépendances entre tâches
- Suivi des dates de début, de fin et d'échéance

### 4.5 Collaboration

- Ajout de pièces jointes
- Système de commentaires sur projets et tâches
- Notifications liées aux affectations, aux échéances et aux validations

### 4.6 Visualisation et tableaux de bord

- Tableau de bord adapté au rôle de l'utilisateur connecté
- Vue calendrier des projets et tâches
- Vue Gantt pour le suivi temporel des tâches
- Indicateurs visuels pour les notifications et les statuts

## 5. Architecture fonctionnelle

L'application repose sur une séparation claire des responsabilités :

- **Contrôleurs** : traitement des requêtes et orchestration des actions
- **Modèles** : gestion des entités métier et des relations
- **Vues Blade** : affichage de l'interface utilisateur
- **Policies et middleware** : contrôle d'accès selon le rôle et le périmètre utilisateur
- **Services de notification** : diffusion des alertes et du temps réel

## 6. Sécurité et contrôle d'accès

- Authentification par session Laravel
- Protection des routes par middleware
- Vérification des rôles à chaque accès
- Isolation des données selon le périmètre de l'utilisateur
- Validation des permissions sur les projets, tâches, commentaires et pièces jointes

## 7. Interface utilisateur

L'interface suit une approche Light UI avec une navigation latérale fixe et un en-tête dédié aux actions utilisateur.

- **Menu latéral** : accès aux tableaux de bord, projets, tâches, calendrier, utilisateurs et structures
- **En-tête** : notifications, profil utilisateur et déconnexion
- **Responsive design** : adaptation aux écrans desktop, tablette et mobile

## 8. Routes et modules exposés

L'application met à disposition les modules suivants :

- Tableau de bord principal
- Gestion des projets
- Gestion des jalons
- Gestion des tâches
- Calendrier
- Notifications
- Organigramme / structures
- Espace administrateur
- Espace membre
- Espace chef de département

## 9. Commandes de lancement

```bash
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

## 10. Livrables associés

- Code source complet de l'application
- Base de données relationnelle
- Support de présentation pour la soutenance

## 11. Résumé

Cette application est une plateforme web de gestion de projets conçue avec Laravel, MySQL, Tailwind CSS et Vite. Elle couvre les besoins de planification, de suivi, de validation et de collaboration, tout en appliquant une politique de sécurité fondée sur les rôles et les permissions.
