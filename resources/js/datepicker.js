import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';
import 'flatpickr/dist/flatpickr.min.css';

const DATE_SELECTOR = 'input[type="date"], input[type="datetime-local"]';

function buildOptions(input) {
    const isDateTime = input.type === 'datetime-local';

    return {
        locale: French,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
        time_24hr: true,
        enableTime: isDateTime,
        minuteIncrement: 5,
        dateFormat: isDateTime ? 'Y-m-d\\TH:i' : 'Y-m-d',
        altInput: true,
        altFormat: isDateTime ? 'd/m/Y H:i' : 'd/m/Y',
        altInputClass: `${input.className} flatpickr-input`,
    };
}

function initDatePicker(input) {
    if (!input || input.dataset.datePickerInitialized === 'true' || input._flatpickr) {
        return;
    }

    input.dataset.datePickerInitialized = 'true';
    flatpickr(input, buildOptions(input));
}

export function initDatePickers(root = document) {
    const context = root instanceof Element ? root : document;

    context.querySelectorAll(DATE_SELECTOR).forEach(initDatePicker);
}

function observeDatePickers() {
    if (typeof MutationObserver === 'undefined') {
        return;
    }

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof Element)) {
                    return;
                }

                if (node.matches?.(DATE_SELECTOR)) {
                    initDatePicker(node);
                }

                initDatePickers(node);
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initDatePickers();
        observeDatePickers();
    });
} else {
    initDatePickers();
    observeDatePickers();
}