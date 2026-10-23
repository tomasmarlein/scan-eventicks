@forelse($organisations as $organisation)
    <a href="{{ route('organisation.overview', $organisation->slug) }}" class="entity-card">
        <span class="entity-card__icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
        <span class="entity-card__content"><strong>{{ $organisation->name }}</strong><small>Evenementen bekijken</small></span>
        <i class="fa-solid fa-arrow-right entity-card__arrow" aria-hidden="true"></i>
    </a>
@empty
    @if($showEmptyState ?? false)
        <div class="surface-card empty-state">
            <i class="fa-solid fa-building-circle-exclamation" aria-hidden="true"></i>
            <h2>Geen organisaties gevonden</h2>
            <p>Je hebt momenteel geen toegang tot een organisatie.</p>
        </div>
    @endif
@endforelse
