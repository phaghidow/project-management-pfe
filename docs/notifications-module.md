# Notifications Module

## Overview

The notifications module supports:
- Real-time delivery (Laravel Echo + broadcast events)
- Persistent in-app notifications
- Read/unread tracking
- Role and structure communication
- Delay support for scheduled broadcasts
- Deduplication on user/type/related resource

## Main Endpoints

All endpoints require authenticated and active user session.

- `GET /notifications`
  - Returns paginated unread notifications for current user.
  - Query: `per_page` (default: 10)

- `GET /notifications/count`
  - Returns unread notifications count for current user.

- `POST /notifications/{notification}/read`
  - Marks one notification as read.
  - Only owner can mark it.

- `POST /notifications/read-all`
  - Marks all unread notifications as read for current user.

- `POST /notifications/role/{role}`
  - Sends notification to all active users of target role.
  - Allowed sender roles: `admin`, `chef_departement`, `chef_projet`.
  - Sender is excluded from recipients.
  - Body:
    - `title` (required)
    - `message` (required)
    - `type` (optional, default `role_announcement`)
    - `delay_seconds` (optional, 0..604800)
    - `metadata` (optional object)

- `POST /notifications/structure/{structureId}`
  - Sends notification to all active users of target structure.
  - Allowed sender roles: `admin`, `chef_departement`.
  - Sender is excluded from recipients.
  - Body:
    - `title` (required)
    - `message` (required)
    - `type` (optional, default `structure_announcement`)
    - `delay_seconds` (optional, 0..604800)
    - `metadata` (optional object)

## Broadcast Events

- `NewNotification`
  - Channel: `private-user.{id}`
  - Queueable event (`ShouldQueue`)

- `NotificationRead`
  - Channel: `private-user.{id}`
  - Queueable event (`ShouldQueue`)

## Notification Types

`notifications.type` is stored as `string(100)` to allow extensible categories.

Examples used currently:
- `task_assigned`
- `task_due_soon`
- `task_overdue`
- `task_validated`
- `project_ready_review`
- `project_closed`
- `role_announcement`
- `structure_announcement`

## Queue and Deployment Notes

Start workers to process queued broadcast events:

```bash
php artisan queue:work
```

Recommended production setup:
- `queue:work` managed by Supervisor/systemd
- retry and failed jobs monitoring
- broadcast driver configured (`pusher`, `reverb`, or equivalent)

## Frontend Behavior

Header dropdown:
- loads unread list and count
- supports mark single as read
- supports mark all as read
- updates in real-time via Echo
- falls back to polling every 5 seconds

## Tests Added

- `tests/Feature/NotificationsReadAllTest.php`
- `tests/Feature/NotificationCommunicationTest.php`
- `tests/Unit/NotificationModelTest.php`

Run notification-focused suite:

```bash
php vendor/bin/phpunit --filter Notification
```
