@extends('storefront.layout')

@section('title', $page['title'] . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ $page['eyebrow'] }}</p>
            <h1>{{ $page['title'] }}</h1>
            <p class="sf-page-lead">{{ $page['summary'] }}</p>
        </header>

        <div class="sf-policy-sections">
            @foreach ($page['sections'] as $section)
                <article class="sf-info-card">
                    <h2>{{ $section['heading'] }}</h2>
                    <p>{{ $section['body'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
