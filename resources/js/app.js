import './bootstrap';
import '../sass/app.scss';
import './shared/cookie-consent.js';
import './shared/ckeditor';


// Flatpickr
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Dutch } from "flatpickr/dist/l10n/nl.js";

document.addEventListener('DOMContentLoaded', () => {
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d", // Optioneel: Stel het formaat van de datum in
        locale: Dutch        // Optioneel: Stel de taal in (in dit geval Nederlands)
    });

    flatpickr(".datepicker-year", {
        enableTime: false,
        dateFormat: "Y",
        locale: Dutch,
        defaultDate: new Date(), // Optioneel: standaard het huidige jaar selecteren
        onReady: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.classList.add("flatpickr-year-only");
            document.querySelector(".flatpickr-days").style.display = "none"; // Verberg dagen
            document.querySelector(".flatpickr-months").style.display = "none"; // Verberg maanden
        }
    });

    flatpickr(".datepicker-time", {
        enableTime: true,
        dateFormat: "Y-m-d H:i:s",
        locale: Dutch,
        time_24hr: true,
        inline: false,
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
});
