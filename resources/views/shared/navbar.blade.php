<nav id="navbar" class="navbar navbar-light" aria-label="Hoofdnavigatie">
    <div class="container app-shell">
        <a class="navbar-brand" href="{{ route('dashboard') }}" aria-label="Eventicks scanner, naar organisaties">
            <img src="{{ asset('images/eventicks-logo.png') }}" alt="Eventicks" width="185" height="30">
        </a>
        <div class="navbar-actions">
            <a class="navbar-actions__home" href="{{ route('dashboard') }}">Organisaties</a>
            <button id="navbar-toggler" class="navbar-toggler" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#navigation" aria-controls="navigation"
                    aria-expanded="false" aria-label="Open menu">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</nav>

<div class="offcanvas offcanvas-end app-menu" tabindex="-1" id="navigation" aria-labelledby="navigationLabel">
    <div class="offcanvas-header">
        <div>
            <small class="app-menu__eyebrow">EVENTICKS</small>
            <h2 class="offcanvas-title" id="navigationLabel">Menu</h2>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Sluit menu"></button>
    </div>
    <div class="offcanvas-body">
        <a href="{{ route('dashboard') }}" class="app-menu__link">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Organisaties
        </a>
        <div class="app-menu__account">
            <span class="app-menu__avatar"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
            <div><small>Ingelogd als</small><strong>{{ auth()->user()->name }}</strong></div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="app-menu__logout">
            @csrf
            <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Uitloggen</button>
        </form>
    </div>
</div>
