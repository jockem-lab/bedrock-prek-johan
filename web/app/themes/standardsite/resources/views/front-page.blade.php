@extends('layouts.app')

@section('content')
  <section class="objekt-sektion">
    <div class="objekt-inner">
      <h2>Till salu</h2>
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
@endsection
