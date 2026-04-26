document.addEventListener('DOMContentLoaded', function() {
    // Cascade select for task forms
    const projectSelects = document.querySelectorAll('#project_id');
    projectSelects.forEach(projectSelect => {
        const milestoneSelect = document.getElementById('milestone_id');
        if (!milestoneSelect) return;

        projectSelect.addEventListener('change', function() {
            const projectId = this.value;
            milestoneSelect.disabled = !projectId;
            milestoneSelect.innerHTML = '<option value="">Chargement...</option>';

            if (!projectId) {
                // CORRECTION 1 : Utilisation de l'échappement \ pour l'apostrophe
                milestoneSelect.innerHTML = '<option value="">Sélectionnez un projet d\'abord</option>';
                milestoneSelect.value = '';
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/api/milestones/by-project/${projectId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(milestones => {
                milestoneSelect.innerHTML = '<option value="">Sélectionnez un jalon</option>';
                if (milestones.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Aucun jalon pour ce projet';
                    option.disabled = true;
                    milestoneSelect.appendChild(option);
                } else {
                    milestones.forEach(milestone => {
                        const option = document.createElement('option');
                        option.value = milestone.id;
                        option.textContent = milestone.name;
                        milestoneSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                milestoneSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
        });

        // CORRECTION 2 : Suppression de la syntaxe Blade {{ }} qui ne fonctionne pas en .js
        // Pour initialiser, on utilise simplement la valeur actuelle du select (déjà remplie par Laravel dans le HTML)
        const initialProjectId = projectSelect.value;
        if (initialProjectId) {
            projectSelect.dispatchEvent(new Event('change'));
        }
    });
});