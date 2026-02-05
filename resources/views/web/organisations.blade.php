@extends('template.template')

@section('main')
    <header class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="text-primary">{{ env('APP_NAME') }}</h3>
                    <div class="ck-text text-muted">
                        Selecteer een organisatie om verder te kunnen gaan
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="section pt-0">
        <div class="container">
            <div class="row g-4">
                @foreach($organisations as $organisation)
                    <div class="col-12">
                        <div class="event-card card p-4 bg-white border-radius-lg border-1 text-center">
                            <h5 class="mb-3">{{ $organisation['name'] }}</h5>
                            <div class="btn-wrapper justify-content-sm-center">
                                <a href="{{ route('organisation.overview', $organisation['slug']) }}" class="btn btn-primary">
                                    Naar organisatie gaan
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
