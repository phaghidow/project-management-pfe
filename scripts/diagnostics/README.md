# Diagnostics

- `check_fk_orphans.php` -- detecte les references orphelines `parent_id` et `audit_logs` apres une migration ou un move en masse. Usage: `php scripts/diagnostics/check_fk_orphans.php`
- `verify_api_endpoint.php` -- verifie le format plat et la coherence de la reponse `/api/organigramme-v2`. Usage: `php scripts/diagnostics/verify_api_endpoint.php`
- `verify_type_and_parents.php` -- controle les types exposes et les references `parent_id` de l'API. Usage: `php scripts/diagnostics/verify_type_and_parents.php`
