document.addEventListener('DOMContentLoaded', function() {
    const treeContainer = document.getElementById('tree-container');
    if (!treeContainer) return;

    // Load initial hierarchy
    loadStructureTree();

    // Delegate event listeners
    treeContainer.addEventListener('click', function(e) {
        if (e.target.matches('.toggle-tree')) {
            e.preventDefault();
            toggleTree(e.target);
        }
    });

    function loadStructureTree(parentId = null) {
        fetch(`/admin/structures-hierarchy${parentId ? '?parent_id=' + parentId : ''}`)
            .then(res => res.json())
            .then(data => {
                renderTree(data, parentId);
            })
            .catch(err => console.error('Error loading tree:', err));
    }

    function renderTree(structures, parentId) {
        const container = parentId ? document.querySelector(`[data-parent="${parentId}"]`) : treeContainer;
        if (!container) return;

        container.innerHTML = structures.map(structure => `
            <div class="structure-item mb-4 p-4 bg-white rounded-lg shadow border-l-4 border-blue-500 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-gray-900">${structure.name} (${structure.type.toUpperCase()})</h4>
                        <p class="text-sm text-gray-600">Niveau ${structure.level} • Code: ${structure.code || 'N/A'}</p>
                        ${structure.description ? `<p class="text-xs text-gray-500 mt-1">${structure.description}</p>` : ''}
                    </div>
                    <div class="flex gap-2">
                        <a href="/admin/structures/${structure.id}/edit" class="text-yellow-600 hover:text-yellow-800 font-medium text-sm">Éditer</a>
                        <button class="toggle-tree text-gray-500 hover:text-gray-700" data-id="${structure.id}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-4 children" data-parent="${structure.id}" style="display: none;"></div>
            </div>
        `).join('');

        // Bind toggle listeners after render
        document.querySelectorAll(`[data-id="${parentId}"] ~ .toggle-tree`).forEach(btn => {
            btn.addEventListener('click', function() {
                toggleTree(this);
            });
        });
    }

    function toggleTree(button) {
        const itemId = button.dataset.id;
        const childrenContainer = document.querySelector(`[data-parent="${itemId}"]`);
        if (childrenContainer.style.display === 'none') {
            childrenContainer.style.display = 'block';
            loadStructureTree(itemId);
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
            `;
        } else {
            childrenContainer.style.display = 'none';
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            `;
        }
    }

    // Dynamic parent selector for forms
    const parentSelects = document.querySelectorAll('select[name="parent_id"]');
    parentSelects.forEach(select => {
        select.addEventListener('change', function() {
            const levelField = document.getElementById('structure-level');
            if (levelField) {
                fetch(`/api/structures/${this.value}`)
                    .then(res => res.json())
                    .then(parent => {
                        levelField.value = parent.level + 1;
                    });
            }
        });
    });
});
