import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('toasts', () => ({
    show: false,
    init() {
        if (this.$el) {
            setTimeout(() => {
                this.show = false;
            }, 3000);
        }
    }
}));

Alpine.start();

