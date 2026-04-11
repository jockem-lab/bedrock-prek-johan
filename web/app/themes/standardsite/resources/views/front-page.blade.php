@extends('layouts.app')

@section('content')
  <section class="objekt-sektion">
    <div class="objekt-inner">
      <h2>Till salu</h2>
      @if(!empty($listings))
        <div class="objekt-grid">
          @foreach($listings as $listing)
            @php
              $adress = $listing->location->address ?? '';
              $pris   = $listing->price ?? '';
              $typ    = $listing->descriptionType ?? '';
              $rum    = !empty($listing->rooms) ? $listing->rooms . ' rum' : '';
              $yta    = !empty($listing->livingArea) ? $listing->livingArea . ' kvm' : '';
              $bild   = !empty($listing->objectImages[0]->path) ? $listing->objectImages[0]->path : '';
              $slug   = $listing->slug ?? '';
              $status = $listing->statusAlias ?? 'till-salu';
            @endphp
            <div class="objekt-kort">
              <a href="{{ home_url('/objekt/' . $slug) }}" class="objekt-kort-inner">
                <div class="objekt-bild">
                  @if($bild)
                    <img src="{{ $bild }}" alt="{{ $adress }}">
                  @else
                    <div class="objekt-bild-placeholder"></div>
                  @endif
                  <div class="objekt-overlay">
                    <div class="objekt-info">
                      @if($adress)
                        <div class="objekt-adress">{{ $adress }}</div>
                      @endif
                      @if($pris)
                        <div class="objekt-pris">{{ $pris }}</div>
                      @endif
                      <div class="objekt-meta">
                        @if($typ) <span>{{ $typ }}</span> @endif
                        @if($rum) <span>{{ $rum }}</span> @endif
                        @if($yta) <span>{{ $yta }}</span> @endif
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
