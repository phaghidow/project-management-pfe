/*
 * FormHandler: Gère les envois AJAX de formulaires avec mise à jour dynamique
 * Supprime le rechargement complet et remplace le contenu mis à jour via le DOM
 */

class FormHandler {
    constructor(formSelector, options = {}) {
        this.formSelector = formSelector;
        this.options = {
            onSuccess: options.onSuccess || this.defaultOnSuccess,
            onError: options.onError || this.defaultOnError,
            successMessage: options.successMessage || 'Opération réussie',
            errorMessage: options.errorMessage || 'Une erreur est survenue',
            successCallback: options.successCallback || null,
            containerSelector: options.containerSelector || null,
            ...options
        };
        this.init();
    }

    init() {
        document.addEventListener('submit', (e) => this.handleSubmit(e));
        document.addEventListener('click', (e) => this.handleDelete(e));
        this.setupDynamicListeners();
    }

    setupDynamicListeners() {
        const observer = new MutationObserver(() => {
            this.reinitFormHandlers();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    reinitFormHandlers() {
        // Delegate submit listener covers new forms
    }

    // Intercepte les envois de formulaires AJAX
    handleSubmit(event) {
        const form = event.target;
        if (!form.classList || !form.classList.contains('ajax-form')) return;

        event.preventDefault();

        const formData = new FormData(form);
        const action = form.action;
        const method = (form.method || 'POST').toUpperCase();

        // Désactiver le bouton pendant l'envoi
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="inline animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Envoi en cours...';
        }

        // Clear previous validation errors for this form
        form.querySelectorAll('.ajax-error').forEach(el => el.remove());

        fetch(action, {
            method: method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => this.parseResponse(response))
        .then(data => {
            this.handleSuccess(form, data);
            if (this.options.successCallback) {
                this.options.successCallback(data, form);
            }
        })
        .catch(error => {
            console.error('FormHandler Error:', error);
            this.handleError(form, error);
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Intercepte les clics de suppression (avec confirmation)
    handleDelete(event) {
        const deleteBtn = event.target.closest('[data-confirm-delete]');
        if (!deleteBtn) return;

        event.preventDefault();

        const message = deleteBtn.dataset.confirmDelete || 'Êtes-vous sûr de vouloir supprimer ?';
        if (!confirm(message)) return;

        const form = deleteBtn.closest('form');
        if (form) {
            this.submitDeleteForm(form);
        } else {
            const action = deleteBtn.dataset.action || deleteBtn.href;
            const method = deleteBtn.dataset.method || 'DELETE';
            this.submitAjaxDelete(action, method, deleteBtn);
        }
    }

    submitDeleteForm(form) {
        const action = form.action;
        const method = form.method.toUpperCase();
        const removableItem = form.closest('[data-item-id]') || form;

        const deleteBtn = form.querySelector('[data-confirm-delete]');
        const originalText = deleteBtn?.innerHTML;
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<svg class="inline animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Suppression...';
        }

        fetch(action, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(response => this.parseResponse(response))
        .then(data => {
            this.handleDeleteSuccess(removableItem, data, form);
        })
        .catch(error => {
            console.error('Delete Error:', error);
            this.handleError(form, error);
        })
        .finally(() => {
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            }
        });
    }

    submitAjaxDelete(action, method, element) {
        const originalText = element.innerHTML;
        element.disabled = true;
        element.innerHTML = '<svg class="inline animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Suppression...';

        fetch(action, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => this.parseResponse(response))
        .then(data => {
            this.handleDeleteSuccess(element.closest('[data-item-id]') || element.parentElement, data, null);
        })
        .catch(error => {
            console.error('Delete Error:', error);
            this.handleError(element, error);
        })
        .finally(() => {
            element.disabled = false;
            element.innerHTML = originalText;
        });
    }

    // Gestion du succès des envois de formulaire
    handleSuccess(form, data) {
        if (form && !form.matches('[method="GET"]')) {
            try { form.reset(); } catch (e) {}
        }

        this.showToast(data?.message || this.options.successMessage, 'success');

        if (this.options.onSuccess) {
            this.options.onSuccess(form, data);
        }

        if (!form?.dataset?.noRefresh) {
            this.softRefreshPage();
        }
    }

    // Gestion du succès de la suppression
    handleDeleteSuccess(element, data, sourceForm = null) {
        if (element && element !== sourceForm) {
            element.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => { element.remove(); }, 300);
        }

        this.showToast(data?.message || 'Élément supprimé avec succès', 'success');

        if (this.options.onSuccess) {
            this.options.onSuccess(element, data);
        }

        if (!sourceForm || !sourceForm.dataset.noRefresh) {
            this.softRefreshPage();
        }
    }

    // Gestion des erreurs
    handleError(form, error) {
        console.error('Form submission error:', error);
        const message = error?.message || this.options.errorMessage;

        // If validation errors are present, render them inline
        const errors = error?.payload?.errors || null;
        if (errors && form) {
            Object.keys(errors).forEach((field) => {
                const fieldSelector = `[name="${field}"]`;
                let input = form.querySelector(fieldSelector);

                if (!input && field.includes('.')) {
                    const bracketName = field.replace(/\.(\d+)/g, '[$1]').replace(/\./g, '][');
                    input = form.querySelector(`[name="${bracketName}"]`);
                }

                if (!input) {
                    input = form.querySelector(`[name$="${field.split('.').pop()}]"]`) || form.querySelector(`[name$="${field.split('.').pop()}"]`);
                }

                const messageText = Array.isArray(errors[field]) ? errors[field][0] : String(errors[field]);
                const errorEl = document.createElement('p');
                errorEl.className = 'ajax-error mt-1 text-sm text-red-600';
                errorEl.textContent = messageText;

                if (input && input.parentNode) {
                    input.parentNode.appendChild(errorEl);
                } else {
                    form.appendChild(errorEl);
                }
            });

            this.showToast(message, 'error');
            if (this.options.onError) {
                this.options.onError(form, error);
            }
            return;
        }

        this.showToast(message, 'error');

        if (this.options.onError) {
            this.options.onError(form, error);
        }
    }

    // Affiche un toast de notification
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 animate-fade-in`;
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 text-white font-medium ${bgColor}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.animation = 'fadeOut 0.3s ease-out'; setTimeout(() => toast.remove(), 300); }, 3000);
    }

    defaultOnSuccess() {}
    defaultOnError() {}

    async parseResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        let payload;

        if (contentType.includes('application/json')) {
            payload = await response.json();
        } else {
            const text = await response.text();
            payload = { html: text };
        }

        if (!response.ok) {
            const message = payload?.message || `HTTP error! status: ${response.status}`;
            const err = new Error(message);
            err.status = response.status;
            err.payload = payload;
            throw err;
        }

        return payload;
    }

    // Recharge dynamiquement une section de la page via AJAX
    refreshSection(selector, newContent) {
        const element = document.querySelector(selector);
        if (element && newContent) {
            element.innerHTML = newContent;
            element.style.animation = 'fadeIn 0.3s ease-out';
        }
    }

    // Recharge la page sans rechargement complet (soft refresh)
    async softRefreshPage() {
        try {
            const response = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            const mainContent = newDoc.querySelector('main') || newDoc.querySelector('.page-mobile');
            const currentContent = document.querySelector('main') || document.querySelector('.page-mobile');
            if (mainContent && currentContent) {
                currentContent.innerHTML = mainContent.innerHTML;
                currentContent.style.animation = 'fadeIn 0.3s ease-out';
            }
        } catch (error) {
            console.error('Soft refresh error:', error);
        }
    }
}

// Initialiser automatiquement au chargement du document
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { new FormHandler(); });
} else {
    new FormHandler();
}

window.FormHandler = FormHandler;
