import 'https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.umd.js';

CookieConsent.run({
    guiOptions: {
        consentModal: {
            layout: "box",
            position: "bottom right",
            equalWeightButtons: false,
            flipButtons: false
        },
        preferencesModal: {
            layout: "box",
            position: "right",
            equalWeightButtons: true,
            flipButtons: false
        }
    },
    categories: {
        necessary: {
            readOnly: true
        },
        functionality: {},
        analytics: {},
        marketing: {}
    },
    language: {
        default: "nl",
        autoDetect: "browser",
        translations: {
            nl: {
                consentModal: {
                    title: "Wij gebruiken cookies",
                    description: "Welkom bij Mol-Centrum Rozenberg Lichtstoet. Wij gebruiken cookies op onze site. Lees hierover meer in onze <a href=\"/cookie-policy\">Cookie Statement</a>.",
                    acceptAllBtn: "Ik ga akkoord met alle cookies",
                    // acceptNecessaryBtn: "Reject all",
                    showPreferencesBtn: "Beheer mijn voorkeuren",
                    // footer: "<a href=\"#link\">Privacy Policy</a>\n<a href=\"#link\">Terms and conditions</a>"
                },
                preferencesModal: {
                    title: "Mijn cookie-instellingen",
                    acceptAllBtn: "Ik ga akkoord met alle cookies",
                    // acceptNecessaryBtn: "Reject all",
                    savePreferencesBtn: "Opslaan",
                    closeIconLabel: "Close modal",
                    serviceCounterLabel: "Service|Services",
                    sections: [
                        {
                            title: "Cookie gebruik",
                            description: "Welkom bij Mol-Centrum Rozenberg Lichtstoet. Wij gebruiken cookies op onze site.\n"
                        },
                        {
                            title: "Noodzakelijke cookies <span class=\"pm__badge\">Always Enabled</span>",
                            description: "Hierbij gaat het om cookies die nodig zijn om de website te laten functioneren. Zonder deze cookies werkt de website niet naar behoren.",
                            linkedCategory: "necessary"
                        },
                        {
                            title: "Functionele cookies",
                            description: "Hierbij gaat het om cookies die de website gebruiksvriendelijker maken voor de bezoeker. Denk hierbij aan cookies die de website onthouden voor een volgend bezoek.",
                            linkedCategory: "functionality"
                        },
                        {
                            title: "Analistische cookies",
                            description: "Hierbij gaat het om cookies die informatie verzamelen over het gebruik van de website. Denk hierbij aan het aantal bezoekers en de populairste pagina's.",
                            linkedCategory: "analytics"
                        },
                        {
                            title: "Advertentiecookies",
                            description: "Hierbij gaat het om cookies die informatie verzamelen over het surfgedrag van de bezoeker. Denk hierbij aan de pagina's die de bezoeker heeft bezocht en de tijd die de bezoeker op de website heeft doorgebracht.",
                            linkedCategory: "marketing"
                        },
                        {
                            title: "Meer informatie",
                            description: "Lees hierover meer in onze <a class=\"cc__link\" href=\"/cookie-policy\">Cookie Statement</a>."
                        }
                    ]
                }
            }
        }
    },
    disablePageInteraction: true
});
