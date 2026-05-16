import './bootstrap';
import '../sass/app.scss';

async function bootDatePickers() {
    const hasDatePickers = document.querySelector('.datepicker, .datepicker-year, .datepicker-time');

    if (!hasDatePickers) {
        return;
    }

    const [{ default: flatpickr }, { Dutch }] = await Promise.all([
        import('flatpickr'),
        import('flatpickr/dist/l10n/nl.js'),
        import('flatpickr/dist/flatpickr.min.css'),
    ]);

    document.querySelectorAll('.datepicker').forEach(element => {
        flatpickr(element, {
            dateFormat: 'Y-m-d',
            locale: Dutch,
        });
    });

    document.querySelectorAll('.datepicker-year').forEach(element => {
        flatpickr(element, {
            enableTime: false,
            dateFormat: 'Y',
            locale: Dutch,
            defaultDate: new Date(),
            onReady: function (_selectedDates, _dateStr, instance) {
                instance.calendarContainer.classList.add('flatpickr-year-only');
                instance.calendarContainer.querySelector('.flatpickr-days')?.style.setProperty('display', 'none');
                instance.calendarContainer.querySelector('.flatpickr-months')?.style.setProperty('display', 'none');
            },
        });
    });

    document.querySelectorAll('.datepicker-time').forEach(element => {
        flatpickr(element, {
            enableTime: true,
            dateFormat: 'Y-m-d H:i:S',
            locale: Dutch,
            time_24hr: true,
            inline: false,
        });
    });
}

function bootTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
        new window.bootstrap.Tooltip(element);
    });
}

async function bootOptionalModules() {
    if (document.querySelector('.ckeditor')) {
        await import('./shared/ckeditor');
    }

    if (document.querySelector('[data-cookie-consent]')) {
        await import('./shared/cookie-consent');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    bootTooltips();
    void bootDatePickers();
    void bootOptionalModules();
});
