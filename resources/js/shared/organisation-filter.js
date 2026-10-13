const input = document.getElementById('organisationSearch');
const cards = [...document.querySelectorAll('.entity-list .entity-card')];
const count = document.getElementById('organisationCount');
const empty = document.getElementById('organisationSearchEmpty');

input?.addEventListener('input', () => {
    const query = input.value.trim().toLocaleLowerCase('nl');
    let visible = 0;

    for (const card of cards) {
        const matches = card.textContent.toLocaleLowerCase('nl').includes(query);
        card.classList.toggle('d-none', !matches);
        if (matches) visible += 1;
    }

    if (count) count.textContent = visible + (visible === 1 ? ' organisatie' : ' organisaties');
    empty?.classList.toggle('d-none', visible > 0);
});
