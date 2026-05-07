import './bootstrap';
import './datepicker';
import './flash-toast-mount';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import TomSelect from 'tom-select';
import { createApp } from 'vue';
import GanttChart from './components/GanttChart.vue';
    import './form-handler';

window.Alpine = Alpine;
Alpine.plugin(collapse);

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

function getTreeOptionMeta(data) {
    const optionElement = data.$option;
    const level = Number.parseInt(optionElement?.dataset.level ?? '0', 10) || 0;
    const name = optionElement?.dataset.name || data.text;
    const path = optionElement?.dataset.path || data.text;

    return { level, name, path };
}

function initStructureTreeSelect() {
    const selects = document.querySelectorAll('select[data-tree-select="structures"]');

    selects.forEach((select) => {
        if (select.tomselect) {
            return;
        }

        new TomSelect(select, {
            create: false,
            allowEmptyOption: true,
            placeholder: select.dataset.placeholder || 'Rechercher une structure...',
            plugins: {
                clear_button: {
                    title: 'Effacer'
                }
            },
            render: {
                option(data, escape) {
                    if (!data.value) {
                        return `<div class="tree-option-root">${escape(data.text)}</div>`;
                    }

                    const { level, name, path } = getTreeOptionMeta(data);
                    const indent = level * 18;
                    const connectors = level > 0 ? `<span class="tree-option-connector" style="width:${level * 10}px"></span>` : '';

                    return `
                        <div class="tree-option" style="padding-left:${indent}px">
                            ${connectors}
                            <span class="tree-option-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                            <span class="tree-option-content">
                                <span class="tree-option-name">${escape(name)}</span>
                                <span class="tree-option-path">${escape(path)}</span>
                            </span>
                        </div>
                    `;
                },
                item(data, escape) {
                    if (!data.value) {
                        return `<div>${escape(data.text)}</div>`;
                    }

                    const { name } = getTreeOptionMeta(data);

                    return `
                        <div class="tree-item-selected">
                            <span class="tree-item-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                            <span>${escape(name)}</span>
                        </div>
                    `;
                },
                no_results(data, escape) {
                    return `<div class="p-2 text-sm text-gray-500">Aucun resultat pour "${escape(data.input)}"</div>`;
                }
            },
            score(search) {
                const scoreByText = this.getScoreFunction(search);

                return (item) => {
                    const baseScore = scoreByText(item);
                    if (!search.length) {
                        return baseScore;
                    }

                    const needle = search.toLowerCase();
                    const itemPath = (item.$option?.dataset.path || '').toLowerCase();
                    const itemName = (item.$option?.dataset.name || '').toLowerCase();

                    if (itemName.startsWith(needle)) {
                        return baseScore + 1;
                    }

                    if (itemPath.includes(needle)) {
                        return baseScore + 0.5;
                    }

                    return baseScore;
                };
            },
            onInitialize() {
                this.wrapper.classList.add('tree-structure-select');
            }
        });
    });
}

function mountGanttCharts() {
    document.querySelectorAll('gantt-chart').forEach((element) => {
        if (element.__vue_app__) {
            return;
        }

        const app = createApp(GanttChart, {
            apiUrl: element.dataset.apiUrl || '/api/tasks-gantt',
            viewMode: element.dataset.viewMode || 'Day',
            language: element.dataset.language || 'en'
        });

        app.mount(element);
        element.__vue_app__ = app;
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStructureTreeSelect);
    document.addEventListener('DOMContentLoaded', mountGanttCharts);
} else {
    initStructureTreeSelect();
    mountGanttCharts();
}

Alpine.start();

