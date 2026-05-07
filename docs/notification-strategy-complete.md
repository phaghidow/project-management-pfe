# 📢 Stratégie Complète de Notifications - Plan de Déploiement

## 🎯 Vision Globale

Transformer le système de notifications en un hub central de communication fluide où chaque action importante génère une notification intelligente ciblée aux utilisateurs concernés, avec gestion des préférences et des réductions de bruit.

---

## 📋 Matrice Complète des Notifications Proposées

### TIER 1: ACTIONS CRITIQUES (Implémentation Prioritaire)

#### 1. 👥 **Affectation à un Projet**
| Qui Notifier | Événement | Message | Métadonnées |
|------------|-----------|---------|------------|
| Membres affectés | `project_member_assigned` | "Vous avez été affecté au projet X" | `project_id`, `assigned_by`, `assigned_at` |
| Chef de projet | `member_assigned_to_my_project` | "X a été affecté au projet" | `member_id`, `project_id` |
| Chef de département | `member_assigned_in_my_dept` | "X assigné au projet Y" | `member_id`, `project_id`, `department_id` |

#### 2. 📌 **Affectation à une Tâche**
| Qui Notifier | Événement | Message | Métadonnées |
|------------|-----------|---------|------------|
| Utilisateur affecté | `task_assigned_to_me` | "Vous avez une nouvelle tâche: X" | `task_id`, `milestone_id`, `project_id`, `due_date` |
| Chef de projet | `task_assigned_in_my_project` | "Y a été assigné à la tâche X" | `task_id`, `assigned_user_id`, `project_id` |
| Autres assignés | `task_assignment_update` | "Un collègue a été assigné à votre tâche" | `task_id`, `new_assignee` |

#### 3. 📊 **Création de Jalon**
| Qui Notifier | Événement | Message | Métadonnées |
|------------|-----------|---------|------------|
| Membres du projet | `milestone_created_in_my_project` | "Nouveau jalon: X pour le Y" | `milestone_id`, `project_id`, `due_date` |
| Chef de département | `milestone_created_notification` | "Jalon créé dans le projet X" | `milestone_id`, `project_id`, `department_id` |

#### 4. ✅ **Changement de Statut Tâche/Jalon**
| Transition | Qui Notifier | Message | Timing |
|-----------|------------|---------|--------|
| → En Cours | Assignés, Chef projet | "Tâche commencée: X" | Immédiat |
| → Complétée | Assignés, Chef projet, Autres assignés | "Tâche complétée: X" | Immédiat |
| → Validée | Assignés, Chef projet | "Tâche validée ✅" | Immédiat |
| Problèmes | Dépendants | "⚠️ Tâche X bloquée - vérifier les dépendances" | Immédiat |

---

### TIER 2: ALERTES INTELLIGENTES (Implémentation Secondaire)

#### 5. ⏰ **Rappels de Dates Limites**
```
Logique:
- 3 jours avant: "Rappel: Tâche X due dans 3 jours"
- 1 jour avant: "Alerte: Tâche X due demain"
- À l'échéance: "Urgent: Tâche X due aujourd'hui"
- Après délai: "⚠️ Critique: Tâche X est en retard"

Recipients: 
- Assignés
- Chef de projet
- Chef de jalon (si responsable)
```

#### 6. 📈 **Progression des Jalons**
```
Trigger: Tous les 25%, 50%, 75%, 100% de complétude
Message: "Jalon X: 50% des tâches complétées"
Recipients: Chef projet, Chef département
```

#### 7. 💬 **Mentions & Commentaires**
```
Événement: Quelqu'un commente une tâche
Message: "X a commenté sur la tâche Y: [extrait du commentaire]"
Recipients:
- Assignés à la tâche
- Auteur précédent du commentaire
- Si @mention spécifique: utilisateur mentionné
```

#### 8. 🔗 **Dépendances Bloquées**
```
Trigger: Une tâche dont dépend la vôtre change de statut
Message: "Bonne nouvelle: Tâche X (dont dépend votre tâche Y) est terminée"
Message Négatif: "Alerte: Tâche X (requise pour Y) n'est pas validée"
Recipients: Assignés à la tâche dépendante
```

---

### TIER 3: COMMUNICATIONS ADMINISTRATIVES (Implémentation Optionnelle)

#### 9. 📢 **Annonces Générales**
- By Role: `role_announcement`
- By Department: `department_announcement`
- By Project: `project_announcement`

#### 10. 🔄 **Changements Structurels**
- Structure reorganization
- Permission changes
- Resource allocation updates

---

## 🏗️ Architecture Proposée

### Nouvelle Structure d'Événements

```
app/Events/
├── Project/
│   ├── MemberAssignedToProject.php          [NEW]
│   ├── ProjectStatusChanged.php             [NEW]
│   └── ProjectClosed.php                    [EXISTING]
├── Task/
│   ├── TaskAssigned.php                     [NEW]
│   ├── TaskStatusChanged.php                [NEW]
│   ├── TaskDependencyBlocked.php            [NEW]
│   └── TaskDeadlineApproaching.php          [NEW]
├── Milestone/
│   ├── MilestoneCreated.php                 [NEW]
│   ├── MilestoneStatusChanged.php           [NEW]
│   ├── MilestoneProgressUpdated.php         [NEW]
│   └── MilestoneCreated.php                 [EXISTING: move here]
└── Comment/
    └── CommentAdded.php                     [NEW]

app/Listeners/
├── NotifyOnProjectMemberAssigned.php        [NEW]
├── NotifyOnTaskAssigned.php                 [NEW]
├── NotifyOnTaskStatusChange.php             [NEW]
├── NotifyOnMilestoneCreated.php             [NEW]
├── NotifyOnMilestoneProgress.php            [NEW]
├── NotifyOnTaskDependencyBlocked.php        [NEW]
└── SendDeadlineReminders.php                [NEW: Queue Job]
```

### Nouvelle Table: `notification_preferences`

```sql
CREATE TABLE notification_preferences (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    notification_type VARCHAR(100),
    channel ENUM('in_app', 'email', 'disabled'),
    enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id, notification_type)
);
```

### Extension Table: `notifications`

```sql
ALTER TABLE notifications ADD COLUMN:
- priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium'
- action_url VARCHAR(500) -- direct link to resource
- read_by_roles JSON -- {admin, chef_projet, etc.}
- related_users JSON -- array of relevant user IDs
```

---

## 🔄 Flux d'Implémentation Proposé

### Phase 1: Base Solide (Semaine 1)
```
1. ✅ Créer événements Project et Task de base
2. ✅ Ajouter listeners pour notifications
3. ✅ Mettre à jour contrôleurs (ProjectController, TaskController)
4. ✅ Tests unitaires et d'intégration
```

### Phase 2: Fluidité (Semaine 2)
```
1. ✅ Créer table notification_preferences
2. ✅ Implémenter UI de gestion des préférences
3. ✅ Ajouter logique de filtrage (ne pas notifier si désactivé)
4. ✅ Grouper les notifications similaires
```

### Phase 3: Intelligence (Semaine 3)
```
1. ✅ Ajouter rappels de dates limites (console command)
2. ✅ Implémenter détection de dépendances bloquées
3. ✅ Ajouter mentions (@username)
4. ✅ Calcul progressif des jalons
```

---

## 💡 Suggestions pour Optimisation & Fluidité

### 1. **Réduction du Bruit** 🔇
```
Stratégies:
✓ Grouper les notifications similaires
  "3 tâches vous ont été assignées" au lieu de 3 notifications
✓ Batch processing pour les mises à jour en masse
✓ Préférences utilisateur (opt-in/out par type)
✓ Limiter les rappels (max 1 par jour)
```

### 2. **Priorisation Intelligente** ⭐
```
Critères de priorité:
- CRITICAL: Vous êtes assigné + date limite < 24h
- HIGH: Vous êtes assigné + date limite < 3j
- MEDIUM: Mise à jour générale du projet
- LOW: Commentaires, changes structurels

Affichage:
- Header badge: Count CRITICAL seulement
- Dropdown: Triés par priorité
- Classement visuel avec couleurs
```

### 3. **Personnalisation** 🎛️
```
Préférences Utilisateur:
□ Recevoir notif quand affecté à tâche
□ Recevoir notif quand jalon créé
□ Rappels 3j/1j avant deadline
□ Mises à jour de tâche (changement statut)
□ Notifications de commentaires

Par Projet/Jalon:
- "Watch" un projet pour toutes les notifications
- "Mute" pour désactiver temporairement
```

### 4. **Liens Directs** 🔗
```
notification.action_url = "/projects/{id}/tasks/{task_id}"

Avantages:
- Clic direct sur notification → à l'action
- Même en cas d'absence = contexte préservé
- Amélior SEO et tracking
```

### 5. **Timestamps Contextuels** ⏱️
```
Affichage intelligent:
- "Il y a 2 min" → juste créé
- "Hier à 14:30" → pour les anciennes
- "Il y a 3 jours" → archivées
- Grouper par jour pour readabilité
```

### 6. **Real-time vs Batch** ⚡
```
Real-time (immédiat):
- Affectation d'utilisateur (action directe)
- Changement de statut critique
- Mentions

Batch (regroupé):
- Reminders de deadline (1x/jour)
- Progression de jalon (1x/jour)
- Annonces générales
```

---

## 📊 Exemple de Structure de Données Enrichie

```json
{
  "notification": {
    "id": 1,
    "user_id": 5,
    "title": "Vous avez été assigné à la tâche 'Design UI'",
    "message": "Dans le jalon 'Phase 1' du projet 'Website Redesign'",
    "type": "task_assigned_to_me",
    "priority": "high",
    "action_url": "/projects/2/milestones/5/tasks/12",
    "related_type": "Task",
    "related_id": 12,
    "metadata": {
      "task_name": "Design UI",
      "task_due_date": "2026-05-15",
      "project_id": 2,
      "milestone_id": 5,
      "assigned_by": {
        "id": 3,
        "name": "Jean Dupont",
        "avatar": "url"
      },
      "related_users": [4, 6, 8],
      "task_description": "Créer les maquettes pour..."
    },
    "related_users": [3, 4, 6],
    "read_at": null,
    "acknowledged_at": null,
    "sent_at": "2026-05-03T10:30:00Z"
  }
}
```

---

## 🎨 Amélioration UI/UX

### Notification Item Enhanced
```blade
<div class="notification-item {{ $notification->priority }} {{ $notification->read_at ? 'read' : 'unread' }}">
    {{-- Priority Badge --}}
    <div class="priority-badge" style="background: {{ $priorityColors[$notification->priority] }}">
        {{ $notification->priority }}
    </div>
    
    {{-- Avatar + Message --}}
    <div class="notification-content">
        <a href="{{ $notification->action_url }}" class="notification-link">
            <h4>{{ $notification->title }}</h4>
            <p>{{ $notification->message }}</p>
            
            {{-- Related Users Avatars --}}
            @if($notification->metadata['related_users'])
                <div class="avatars-preview">
                    @foreach($notification->metadata['related_users'] as $userId)
                        <img src="{{ User::find($userId)->avatar_url }}" 
                             title="{{ User::find($userId)->name }}" />
                    @endforeach
                </div>
            @endif
        </a>
    </div>
    
    {{-- Actions --}}
    <div class="notification-actions">
        <button @click="markAsRead({{ $notification->id }})" 
                class="btn-icon" title="Marquer comme lu">
            ✓
        </button>
        <button @click="archive({{ $notification->id }})" 
                class="btn-icon" title="Archiver">
            📦
        </button>
    </div>
    
    {{-- Time --}}
    <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
</div>
```

### Preferences Panel
```blade
<div class="preferences-panel">
    <h3>Paramètres de Notifications</h3>
    
    <div class="preference-group">
        <h4>Quand je suis assigné</h4>
        <label>
            <input type="checkbox" name="task_assigned_to_me" v-model="prefs">
            Notification en temps réel
        </label>
        <select v-model="channel">
            <option value="in_app">In-app seulement</option>
            <option value="email">Email + In-app</option>
            <option value="disabled">Désactivé</option>
        </select>
    </div>
    
    <div class="preference-group">
        <h4>Rappels de Deadline</h4>
        <label>
            <input type="checkbox" name="deadline_3days">
            3 jours avant
        </label>
        <label>
            <input type="checkbox" name="deadline_1day">
            1 jour avant
        </label>
    </div>
</div>
```

---

## 📚 Types de Notifications Recommandées

```
TASK NOTIFICATIONS:
- task_assigned_to_me
- task_unassigned_from_me
- task_status_changed
- task_commented_on
- task_deadline_approaching
- task_deadline_passed
- task_dependency_blocked
- task_dependency_resolved

MILESTONE NOTIFICATIONS:
- milestone_created_in_my_project
- milestone_progress_updated
- milestone_completed
- milestone_delayed

PROJECT NOTIFICATIONS:
- project_member_assigned
- project_started
- project_completed
- project_status_changed

COMMENT NOTIFICATIONS:
- comment_added_to_my_task
- comment_mentioned_me
- comment_replied_to_mine

ANNOUNCEMENT NOTIFICATIONS:
- role_announcement
- department_announcement
- project_announcement

SYSTEM NOTIFICATIONS:
- permissions_changed
- structure_reorganized
```

---

## 🚀 Plan d'Implémentation Détaillé

### Week 1: Core Events & Listeners

**Tasks:**
1. Create `app/Events/Task/TaskAssigned.php`
2. Create `app/Events/Project/MemberAssignedToProject.php`
3. Create `app/Listeners/NotifyOnTaskAssigned.php`
4. Create `app/Listeners/NotifyOnProjectMemberAssigned.php`
5. Update `TaskController::store()` to dispatch event
6. Update `ProjectController::assignMembers()` to dispatch event
7. Add tests for each listener

**Code Example:**
```php
// In TaskController::assign() or update()
public function assign(Request $request, Task $task)
{
    $users = User::find($request->user_ids);
    $task->users()->sync($users);
    
    // Dispatch event for each new user
    foreach ($users as $user) {
        TaskAssigned::dispatch($task, $user, auth()->user());
    }
}
```

### Week 2: Status Changes & Preferences

**Tasks:**
1. Create `app/Events/Task/TaskStatusChanged.php`
2. Create `notification_preferences` migration
3. Create `NotificationPreference` model
4. Add preference UI to user settings
5. Update listeners to check preferences
6. Create tests for preference logic

### Week 3: Smart Reminders & Blocking

**Tasks:**
1. Create `app/Console/Commands/SendDeadlineReminders.php`
2. Create `app/Events/Task/TaskDependencyBlocked.php`
3. Implement in `TaskController::updateStatus()`
4. Add task scheduler configuration
5. Create comprehensive tests

---

## ✅ Checklist d'Implémentation

### Phase 1 (Foundation)
- [ ] Create Event classes (5 events)
- [ ] Create Listener classes (5 listeners)
- [ ] Update Controllers to dispatch events
- [ ] Write tests (minimum 20 tests)
- [ ] Verify all tests pass

### Phase 2 (Preferences)
- [ ] Migration for notification_preferences
- [ ] Model for preferences
- [ ] UI for preference management
- [ ] Filter logic in notification retrieval
- [ ] Tests for preferences (10+ tests)

### Phase 3 (Intelligence)
- [ ] Console command for deadline reminders
- [ ] Scheduled task configuration
- [ ] Dependency blocking detection
- [ ] Grouping logic for similar notifications
- [ ] Tests for complex scenarios (15+ tests)

---

## 📈 Métriques de Succès

```
✓ 95%+ notification delivery rate
✓ <1s latency for real-time notifications
✓ >80% user preference adoption
✓ 0 notification spam complaints
✓ >90% test coverage on notification code
✓ All existing tests continue to pass
```

---

## 🎯 Quick Win: Start Here

**Minimum Viable Implementation (2 hours):**

1. Create `TaskAssigned` event
2. Create `NotifyOnTaskAssigned` listener  
3. Update `TaskController` to dispatch event
4. Test manually

**This gives you:**
- Immediate value (users know when assigned)
- Clean architecture for expansion
- Foundation for all other notifications

---

## 📞 Questions de Conception

**À décider:**
1. Email aussi ou seulement in-app?
2. Grouper les notifications par projet?
3. Limite de reminders par jour?
4. Format des notifications (court ou détaillé)?
5. Archive automatique après X jours?

**Suggestions:**
- ✅ Start with in-app (faster, simpler)
- ✅ Add email later (when system mature)
- ✅ Limit to 2 reminders/jour max
- ✅ Détaillé (metadata est rich)
- ✅ Archive après 30 jours
