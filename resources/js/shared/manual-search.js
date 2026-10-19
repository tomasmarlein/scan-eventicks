const panel = document.querySelector('.search-panel[data-search-url]');
const input = document.getElementById('ticketSearch');
const list = document.getElementById('orderlinesList');
const spinner = document.getElementById('searchSpinner');
const errorMessage = document.getElementById('searchError');
const limitedState = document.getElementById('limitedState');
const resultCount = document.getElementById('resultCount');

if (panel && input && list) {
    const initialHtml = list.innerHTML;
    const initialCount = resultCount?.textContent || '';
    let debounceTimer = null;
    let currentRequest = null;

    input.addEventListener('input', () => {
        const query = input.value.trim();
        window.clearTimeout(debounceTimer);
        currentRequest?.abort();
        currentRequest = null;
        spinner?.classList.add('d-none');
        errorMessage?.classList.add('d-none');
        limitedState?.classList.add('d-none');

        if (!query) {
            list.innerHTML = initialHtml;
            if (resultCount) resultCount.textContent = initialCount;
            return;
        }

        debounceTimer = window.setTimeout(async () => {
            const controller = new AbortController();
            currentRequest = controller;
            spinner?.classList.remove('d-none');

            try {
                const response = await fetch(panel.dataset.searchUrl + '?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Zoekopdracht mislukt');

                const data = await response.json();
                if (controller !== currentRequest) return;

                list.innerHTML = data.html || '';
                if (resultCount) resultCount.textContent = data.count + (data.count === 1 ? ' ticket' : ' tickets');
                if (data.has_more && limitedState) {
                    limitedState.textContent = 'Er worden maximaal ' + data.limit + ' resultaten getoond. Verfijn je zoekopdracht.';
                    limitedState.classList.remove('d-none');
                }
            } catch (error) {
                if (error.name !== 'AbortError' && controller === currentRequest) {
                    errorMessage?.classList.remove('d-none');
                }
            } finally {
                if (controller === currentRequest) {
                    spinner?.classList.add('d-none');
                    currentRequest = null;
                }
            }
        }, 250);
    });
}
