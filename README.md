1/ PMS_AlgerieTelecom | 2/ (Cahier des charges – Plateforme web de gestion de projets
1. Objectif général
Développer une plateforme web permettant la gestion complète du cycle de vie d’un projet (création, suivi, validation, clôture), avec une gestion fine des utilisateurs, des rôles, des tâches, des jalons, et des dépendances, tout en assurant traçabilité, notifications et collaboration.

2. Périmètre fonctionnel
2.1 Gestion des utilisateurs et authentification
Authentification par username + mot de passe.

Trois rôles prédéfinis :

Administrateur

Chef de département

Chef de projet

Chaque utilisateur est rattaché à :

Une structure organisationnelle (avec structure parente issue de l’organigramme).

Un email professionnel restreint aux domaines : @at.dz ou @algerietelecom.dz.

Création d’un utilisateur par l’administrateur avec un statut :

En attente (disparaît après activation/désactivation)

Activé

Désactivé

Traçabilité : conservation de l’historique des statuts.

2.2 Gestion des projets
Un projet est assigné à un seul utilisateur responsable.

Un projet contient :

Plusieurs jalons

Plusieurs tâches

Un jalon contient plusieurs tâches.

Une tâche peut être assignée à 1 à N utilisateurs.

Chaque tâche possède :

Date de début

Date de fin

Date d’échéance

Dépendances entre tâches :

Une tâche peut dépendre d’une ou plusieurs autres.

Tout retard sur une tâche impacte la durée globale du projet (date de fin).

2.3 Validation et suivi des tâches
Chaque utilisateur valide ses propres tâches.

Statuts possibles d’une tâche :

En cours

Validée

Une tâche validée est grisée (non modifiable, validation unique).

Le chef de projet peut valider l’ensemble du projet une fois toutes les tâches validées.

2.4 Tableaux de bord et visualisation
Dashboard général avec indicateurs clés.

Vue calendrier :

Liste des projets

Tâches assignées à chaque membre

Tâches validées

Interface dynamique avec JavaScript pour gérer :

Les structures utilisateurs (polymorphisme)

Les IDs des structures parentes

2.5 Notifications
Alertes sur les dates approchantes (échéances de projets/tâches).

Notification lors de l’affectation d’un utilisateur à un projet.

Notification automatique lorsque toutes les tâches d’un projet sont validées (prêt pour révision).

2.6 Collaboration et partage de documents
L’administrateur peut uploader des fichiers pour un projet ou une tâche.

Espace d’échange (commentaires) à définir (optionnel selon les notes, mais présent dans le sujet).

3. Exigences techniques
Composant  Technologie
SGBDR  MySQL
Langage backend  PHP (framework Laravel)
Frontend  HTML, CSS, JavaScript (avec gestion dynamique des structures)
Authentification  Session / Laravel Breeze ou Jetstream
Architecture  MVC
4. Contraintes non fonctionnelles
Traçabilité : historique des actions utilisateurs (création, validation, activation/désactivation).

Sécurité : restriction des emails, validation des rôles, protection des routes.

Évolutivité : possibilité d’ajouter de nouveaux rôles ou structures.

Ergonomie : interface intuitive, responsive (optionnel).

5. Livrables attendus
Application web fonctionnelle.

Base de données modélisée et implémentée.

Code source commenté.

Documentation technique et utilisateur.

Présentation orale du projet.) 
