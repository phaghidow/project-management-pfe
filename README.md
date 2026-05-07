Cahier des charges – Plateforme web de gestion de projets
1. Objectif général
Développer une plateforme web permettant la gestion complète du cycle de vie d'un projet, de sa création à sa clôture, en passant par le suivi, la validation et la collaboration. La solution intègre une gestion fine des utilisateurs, des rôles, des tâches, des jalons et des dépendances, tout en assurant traçabilité, notifications et ergonomie.

L'interface utilisateur doit adopter une charte graphique Light UI inspirée des couleurs d'Algérie Télécom, avec un menu latéral fixe pour la navigation principale et un en-tête dédié aux actions utilisateur (profil, notifications, déconnexion).

2. Périmètre fonctionnel
2.1 Gestion des utilisateurs et authentification
L'authentification à la plateforme se fait par un couple nom d'utilisateur et mot de passe.

Quatre rôles sont définis : Administrateur, Chef de département, Chef de projet et Membre (utilisateur standard sans responsabilité hiérarchique).

Chaque utilisateur est rattaché à une structure organisationnelle issue de l'organigramme de l'entreprise, avec une structure parente permettant de reconstituer la hiérarchie complète. L'adresse email professionnelle est restreinte aux domaines @at.dz et @algerietelecom.dz.

La création d'un utilisateur est réalisée exclusivement par l'administrateur. Un utilisateur peut avoir trois statuts : en attente (compte créé mais non activé, destiné à disparaître après décision), activé ou désactivé. Un historique complet des changements de statut est conservé pour assurer la traçabilité.

2.2 Gestion des rôles, permissions et restrictions
Chaque rôle dispose de permissions spécifiques et de restrictions d'accès.

L'Administrateur dispose d'un accès complet et sans restriction à l'ensemble des données de la plateforme. Il peut créer, modifier, supprimer et visualiser tous les utilisateurs, structures, projets, jalons, tâches, documents et commentaires. Il est le seul habilité à gérer les utilisateurs (création, modification, suppression, activation, désactivation) et les structures organisationnelles.

Le Chef de département a un accès limité à son propre département, c'est-à-dire aux utilisateurs, projets, jalons et tâches rattachés à sa structure parente. Il peut créer, modifier et visualiser les projets de son département, mais ne peut pas les supprimer. Il peut créer, modifier et supprimer les jalons et tâches des projets de son département. Il peut assigner des tâches aux membres de son département et voir la liste des utilisateurs de son périmètre. En revanche, il ne peut pas valider les tâches individuelles des membres ni valider un projet entier, ces actions étant réservées au chef de projet. Il peut uploader des fichiers et commenter sur les projets de son département, et supprimer les documents et commentaires relevant de son périmètre.

Le Chef de projet a un accès limité aux projets dont il est directement responsable, c'est-à-dire ceux où son identifiant figure comme responsable du projet. Il peut créer, modifier et supprimer les jalons et tâches de ses projets. Il peut assigner des tâches aux membres de son équipe et visualiser tous les membres impliqués dans ses projets. Il peut valider les tâches de ses membres et, lorsque toutes les tâches d'un projet sont validées, il peut valider l'intégralité du projet. Il peut uploader des fichiers et commenter sur ses projets et tâches, et supprimer les documents et commentaires associés à ses projets. Il ne peut pas voir les projets dont il n'est pas responsable.

Le Membre (utilisateur standard) a un accès strictement limité à ses propres tâches, c'est-à-dire celles qui lui sont assignées via la table pivot. Il ne peut pas créer, modifier ou supprimer un projet, un jalon ou une tâche. Il ne peut pas voir les projets ou tâches des autres utilisateurs. Sa seule action de validation consiste à valider ses propres tâches lorsqu'il les a terminées. Il peut uploader des fichiers et commenter uniquement sur ses tâches assignées, mais ne peut pas supprimer ses propres fichiers ou commentaires (seuls l'administrateur, le chef de département ou le chef de projet peuvent le faire). Il reçoit des notifications concernant ses affectations et échéances.

2.3 Gestion des projets
Un projet est assigné à un seul utilisateur responsable, qui est généralement le chef de projet.

Les jalons sont intégrés directement dans la fenêtre de visualisation du projet. Ainsi, lorsqu'un utilisateur consulte la page d'un projet, il voit simultanément la liste des jalons associés à ce projet, avec leurs dates d'échéance et leur état d'avancement.

Chaque jalon contient plusieurs tâches. Une tâche peut être assignée à un ou plusieurs utilisateurs. Chaque tâche possède une date de début, une date de fin et une date d'échéance.

Les dépendances entre tâches sont gérées : une tâche peut dépendre d'une ou plusieurs autres tâches. Tout retard sur une tâche impacte automatiquement la durée globale du projet, c'est-à-dire sa date de fin prévisionnelle.

2.4 Validation et suivi des tâches
Chaque utilisateur peut valider ses propres tâches. Les statuts possibles d'une tâche sont : en cours et validée. Une fois qu'une tâche est validée, elle devient grisée, non modifiable, et ne peut être validée qu'une seule fois.

Le chef de projet peut valider l'ensemble du projet uniquement lorsque toutes les tâches du projet sont validées par leurs assignés respectifs.

2.5 Tableaux de bord et visualisation
Un tableau de bord général (dashboard) présente des indicateurs clés adaptés au rôle de l'utilisateur connecté.

Une vue calendrier est disponible, listant l'ensemble des projets, les tâches assignées à chaque membre et les tâches déjà validées.

L'interface intègre une gestion dynamique en JavaScript des structures utilisateurs et des identifiants des structures parentes, permettant d'afficher et manipuler l'organigramme de manière interactive (polymorphisme des structures).

2.6 Notifications
La plateforme génère plusieurs types de notifications : des alertes sur les dates approchantes (échéances de projets ou de tâches), une notification lors de l'affectation d'un utilisateur à un projet, et une notification automatique lorsqu'un projet a toutes ses tâches validées, signalant qu'il est prêt pour révision par le chef de projet.

2.7 Collaboration et partage de documents
L'administrateur peut uploader des fichiers pour un projet ou une tâche. Un espace d'échange sous forme de commentaires est également prévu pour faciliter la collaboration entre les membres d'une équipe projet.

3. Architecture de l'interface utilisateur
3.1 Menu latéral (Sidebar)
La navigation principale de l'application repose sur un menu latéral fixe positionné à gauche de l'écran. Ce menu reste visible en permanence et contient les accès aux fonctionnalités principales de l'application :

Tableau de bord (Dashboard)

Projets

Mes projets (vue restreinte aux projets dont l'utilisateur est responsable)

Tâches

Mes tâches (vue restreinte aux tâches assignées à l'utilisateur connecté)

Calendrier

Jalons (vue globale, si rôle le permet)

Utilisateurs (réservé à l'administrateur)

Structures / Organigramme (réservé à l'administrateur)

Paramètres (optionnel)

Le menu latéral doit être adaptatif : il peut être réduit à une icône sur les petits écrans ou sur demande de l'utilisateur, tout en conservant l'accessibilité aux fonctions principales.

3.2 En-tête (Header)
L'en-tête, situé en haut de l'écran à droite du menu latéral, contient exclusivement les éléments d'action utilisateur :

Icône de notification avec badge indiquant le nombre de notifications non lues, et un dropdown listing les dernières notifications

Nom et avatar de l'utilisateur connecté, avec un dropdown permettant d'accéder à son profil et de se déconnecter

L'en-tête ne contient aucun élément de navigation principale, ceux-ci étant déjà présents dans le menu latéral.

4. Charte graphique et identité visuelle
4.1 Principes généraux – Light UI
L'interface adopte une charte Light UI privilégiant les surfaces claires et les versions adoucies des couleurs pour éviter la fatigue visuelle, améliorer la lisibilité et donner un aspect professionnel et moderne, cohérent avec l'image d'Algérie Télécom.

4.2 Couleurs de surface et de fond
Fond de page : #F8F9FC – Un blanc bleuté très léger, choisi pour donner un aspect technologique tout en restant neutre et reposant pour les yeux.

Cartes, blocs et conteneurs : #FFFFFF – Blanc pur utilisé pour détacher les éléments interactifs (formulaires, fiches projets, listes de tâches) du fond de page.

Bordures et séparateurs : #E2E8F0 – Gris neutre permettant de structurer visuellement l'interface sans ajouter de lourdeur ou de contraste excessif.

4.3 Couleurs d'accentuation (interactions, boutons, états)
Bouton principal (Primary) : #2E3192 – Bleu profond représentant la couleur institutionnelle d'Algérie Télécom, utilisé pour les actions principales (créer, enregistrer, valider).

Bouton principal au survol (Primary Hover) : #1E216D – Version plus sombre du bleu pour indiquer l'interactivité et le retour visuel au survol.

État de succès (Success) : #397B44 – Vert utilisé pour les validations, les tâches terminées, les statuts positifs et les confirmations d'action.

État d'alerte ou d'avertissement (Warning) : #F59E0B – Orange utilisé pour les échéances approchantes, les notifications de retard ou les actions nécessitant une attention particulière.

4.4 Typographie et icônes
La typographie privilégie des polices sans sérif modernes telles que Inter, Poppins ou la stack système par défaut (system-ui, -apple-system, sans-serif). Les icônes sont de type outline (contour fin) pour s'intégrer harmonieusement dans l'esthétique Light UI, avec une couleur neutre passant à la couleur primaire au survol ou à l'état actif.

4.5 Accessibilité et contraste
L'ensemble des combinaisons de couleurs respecte un contraste suffisant pour garantir l'accessibilité aux personnes souffrant de déficiences visuelles. Les textes sur fond blanc ou bleuté utilisent des nuances de gris foncé (proches de #1A202C) pour une lisibilité optimale.

5. Exigences techniques
Le système de gestion de base de données relationnel utilisé est MySQL. Le langage de développement backend est PHP avec le framework Laravel. Le frontend repose sur HTML, CSS et JavaScript pour la gestion dynamique des structures et de l'interface. L'utilisation de TailwindCSS ou d'un framework CSS équivalent est recommandée pour faciliter l'implémentation de la charte Light UI et du menu latéral.

L'authentification est assurée par le système de session natif de Laravel ou via les packages Breeze ou Jetstream. L'architecture est de type MVC (Modèle-Vue-Contrôleur).

La sécurité repose sur une restriction stricte des domaines email, une validation des rôles à chaque accès, une protection des routes par middleware et l'utilisation des Policies Laravel pour le scoping automatique des données par rôle.

6. Contraintes non fonctionnelles
La plateforme assure une traçabilité complète des actions utilisateurs : création, validation, activation, désactivation, avec conservation d'un historique.

La sécurité est renforcée par la validation des emails, le contrôle d'accès basé sur les rôles et l'isolation des données par rôle (scoping) : un utilisateur ne peut accéder qu'aux données relevant de son périmètre (son département, ses projets ou ses tâches selon son rôle).

L'évolutivité est prise en compte : il doit être possible d'ajouter de nouveaux rôles ou de nouvelles structures organisationnelles sans remanier profondément l'application.

L'ergonomie de l'interface doit être intuitive, avec un menu latéral clair et un en-tête épuré. La charte Light UI doit être appliquée de manière cohérente sur l'ensemble des pages (dashboard, formulaires, tableaux, fiches projets, calendrier). L'application doit être responsive pour s'adapter aux différents écrans (ordinateurs, tablettes, mobiles), avec un menu latéral repliable sur les petits formats.

7. Livrables attendus
À l'issue du projet, seront fournis : une application web fonctionnelle, une base de données modélisée et implémentée, le code source complet et commenté, une documentation technique à destination des développeurs, une documentation utilisateur à destination des administrateurs et des utilisateurs finaux, ainsi qu'une présentation orale du projet soutenue devant le jury.

8. Résumé synthétique des permissions par rôle
Administrateur : accès total et sans restriction. Gère les utilisateurs, les structures, tous les projets, jalons, tâches, documents, commentaires. Peut tout créer, modifier, supprimer, visualiser et valider.

Chef de département : accès limité à son département. Peut créer, modifier, visualiser les projets de son département. Peut créer, modifier, supprimer les jalons et tâches de son département. Peut assigner des tâches et visualiser les utilisateurs de son périmètre. Ne peut pas supprimer un projet. Ne peut pas valider les tâches individuelles ni les projets entiers. Peut uploader des fichiers et commenter sur son périmètre.

Chef de projet : accès limité aux projets dont il est responsable. Peut créer, modifier, supprimer les jalons et tâches de ses projets. Peut assigner des tâches, visualiser les membres de son équipe. Peut valider les tâches de ses membres et valider l'intégralité d'un projet si toutes les tâches sont validées. Peut uploader des fichiers et commenter sur ses projets et tâches.

<<<<<<< HEAD
Membre : accès limité à ses propres tâches. Peut uniquement visualiser et valider ses tâches assignées. Peut commenter et uploader des fichiers uniquement sur ses tâches. Ne peut ni créer, ni modifier, ni supprimer aucun projet, jalon ou tâche. Ne peut pas voir les tâches des autres utilisateurs.
=======
Membre : accès limité à ses propres tâches. Peut uniquement visualiser et valider ses tâches assignées. Peut commenter et uploader des fichiers uniquement sur ses tâches. Ne peut ni créer, ni modifier, ni supprimer aucun projet, jalon ou tâche. Ne peut pas voir les tâches des autres utilisateurs.

Fin du cahier des charges
>>>>>>> 9932dba6070033be48b5c82e509b4bc69b982219
