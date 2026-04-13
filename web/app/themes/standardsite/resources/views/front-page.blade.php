@extends('layouts.app')

@section('content')

{{-- Intro-sektion --}}
@if(!empty($fp_intro_rubrik) || !empty($fp_intro_text))
<section class="page-sektion" style="text-align:center;">
  <div class="page-inner">
    @if(!empty($fp_intro_rubrik))
      <h2 style="font-family:var(--font-heading);font-size:2.5rem;font-weight:400;margin-bottom:24px;">{{ $fp_intro_rubrik }}</h2>
    @endif
    @if(!empty($fp_intro_text))
      <p style="max-width:600px;margin:0 auto 32px;color:var(--text-mid);line-height:1.8;">{{ $fp_intro_text }}</p>
    @endif
    @if(!empty($fp_intro_knapp['text']))
      <a href="{{ $fp_intro_knapp['url'] }}" class="btn-primary">{{ $fp_intro_knapp['text'] }}</a>
    @endif
  </div>
</section>
@endif

{{-- Objekt-sektion --}}
<section class="objekt-sektion">
  <div class="objekt-inner">
    <h2>{{ $fp_listings_rubrik ?? 'Aktuella objekt' }}</h2>
    @if(!empty($listings))
      <div class="objekt-grid">
        @foreach($listings as $listing)
          <div class="objekt-kort">
            <a href="{{ home_url('/objekt/' . $listing->slug) }}" class="objekt-kort-inner">
              <div class="objekt-bild">
                @if($listing->image)
                  <img src="{{ $listing->image }}" alt="{{ $listing->address }}">
                @else
                  <div class="objekt-bild-placeholder"></div>
                @endif
                @if($listing->status)
                  <div class="objekt-status objekt-status--{{ $listing->status }}">
                    {{ ucfirst($listing->status) }}
                  </div>
                @endif
                <div class="objekt-overlay">
                  <div class="objekt-info">
                    @if($listing->address)
                      <div class="objekt-adress">{{ $listing->address }}</div>
                    @endif
                    @if($listing->price)
                      <div class="objekt-pris">{{ $listing->price }}</div>
                    @endif
                    <div class="objekt-meta">
                      @if($listing->type) <span>{{ $listing->type }}</span> @endif
                      @if($listing->rooms) <span>{{ $listing->rooms }}</span> @endif
                      @if($listing->area) <span>{{ $listing->area }}</span> @endif
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    @else
      <p class="objekt-tomma">Inga objekt tillgängliga just nu.</p>
    @endif
    <div style="margin-top:40px;text-align:center;">
      <a href="{{ home_url('/objekt') }}" class="btn-primary">Se alla objekt</a>
    </div>
  </div>
</section>

{{-- Värdering-sektion --}}
@if(!empty($fp_valuation['visa']))
<section class="page-sektion" style="background:var(--bg-dark);color:white;text-align:center;">
  <div class="page-inner">
    <p class="sektion-eyebrow" style="color:rgba(255,255,255,0.5);">KOSTNADSFRITT</p>
    <h2 style="font-family:var(--font-heading);font-size:2.5rem;font-weight:400;color:white;margin-bottom:24px;">
      {{ $fp_valuation['rubrik'] }}
    </h2>
    @if(!empty($fp_valuation['text']))
      <p style="max-width:560px;margin:0 auto 32px;color:rgba(255,255,255,0.7);line-height:1.8;">
        {{ $fp_valuation['text'] }}
      </p>
    @endif
    <a href="{{ home_url('/kontakt') }}" class="btn-primary" style="background:white;color:var(--bg-dark);">
      {{ $fp_valuation['knapp'] }}
    </a>
  </div>
</section>
@endif

@endsection
