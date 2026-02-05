<nav id="navbar" class="navbar navbar-light">
    <div class="container">
        <a class="navbar-brand" href="/">
            {{ env('APP_NAME') }}
        </a>
        <button id="navbar-toggler" class="navbar-toggler border-0" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#navigation" aria-controls="navigation"
                aria-expanded="false"
                aria-label="Navigatie">
            <i class="fa-light fa-bars"></i>
        </button>
    </div>
</nav>


<div class="offcanvas offcanvas-end p-4 bg-white" tabindex="-1" id="navigation" aria-labelledby="navigationLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="navigationLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <div class="mb-4">
            <small class="text-muted mb-2">Ingelogd als</small>
            <div class="card p-4 border-radius-md border-0">
                <div class="row align-items-center">
                    <div class="col-4">
                        <div class="round-circle">
                            <i class="fa-light fa-user"></i>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="">{{ auth()->user()->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="card p-4 border-radius-md bg-transparent border-1">
                <div class="row align-items-center">
                    <div class="col-12">
                        <span class="text-muted">
                            Ticketscanner systeem <br>
                            Versie 1.0
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav gap-2">
            <li class="card p-3 bg-transparent border-radius-md border-1 w-100">
                <a href="{{ route('logout') }}" class="text-danger">
                    <i class="fa-light fa-arrow-right-from-bracket me-3"></i>
                    uitloggen
                </a>
            </li>
        </ul>
    </div>
</div>
