const panel = document.querySelector('[data-list-search]');
const input = panel?.querySelector('input[type="search"]');
const list = document.getElementById(panel?.dataset.listId);
const count = document.getElementById(panel?.dataset.countId);
const empty = document.getElementById(panel?.dataset.emptyId);
const loading = panel?.querySelector('.search-loading');
const errorMessage = panel?.querySelector('.search-error');

if (panel && input && list) {
    const initialHtml = list.innerHTML;
    const initialCount = count?.textContent || '';
    let debounceTimer = null;
    let currentRequest = null;

    input.addEventListener('input', () => {
        const query = input.value.trim();
        window.clearTimeout(debounceTimer);
        currentRequest?.abort();
        currentRequest = null;
        loading?.classList.add('d-none');
        errorMessage?.classList.add('d-none');
        empty?.classList.add('d-none');

        if (!query) {
            list.innerHTML = initialHtml;
            if (count) count.textContent = initialCount;
            return;
        }

        debounceTimer = window.setTimeout(async () => {
            const controller = new AbortController();
            currentRequest = controller;
            loading?.classList.remove('d-none');

            try {
                const url = new URL(panel.dataset.searchUrl, window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('Zoekopdracht mislukt');

                const data = await response.json();
                if (controller !== currentRequest) return;

                list.innerHTML = data.html || '';
                if (count) count.textContent = data.count + ' ' + (data.count === 1 ? panel.dataset.singular : panel.dataset.plural);
                empty?.classList.toggle('d-none', data.count > 0);
            } catch (error) {
                if (error.name !== 'AbortError' && controller === currentRequest) {
                    errorMessage?.classList.remove('d-none');
                }
            } finally {
                if (controller === currentRequest) {
                    loading?.classList.add('d-none');
                    currentRequest = null;
                }
            }
        }, 250);
    });
}
