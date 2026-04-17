@extends('layouts.app')

@section('content')

{{-- Objekt-sektion --}}
<section class="objekt-sektion">
  <div class="objekt-inner">
    <div class="sektion-header">
      <span class="sektion-eyebrow-label">Fastigheter</span>
      <h2 class="sektion-rubrik">{{ $fp_listings_rubrik ?? 'Aktuella objekt' }}</h2>
    </div>
    @if(!empty($listings))
      <div class="mosaik-grid">
        @foreach($listings as $i => $listing)
          <a href="{{ home_url('/objekt/' . $listing->slug) }}"
             class="mosaik-kort {{ $i === 0 ? 'mosaik-kort--stor' : '' }}">
            <div class="mosaik-bild" style="background-image:url('{{ $listing->image }}')">
              @if(!$listing->image)
                <div style="width:100%;height:100%;background:#243558;"></div>
              @endif
            </div>
            @if($listing->status)
              <div class="objekt-status objekt-status--{{ $listing->status }}">
                @php echo match($listing->status) {
                  'sald' => 'Såld', 'kommande' => 'Kommande',
                  'tillsalu' => 'Till salu', 'budgivning' => 'Budgivning',
                  default => ucfirst($listing->status)
                }; @endphp
              </div>
            @endif
            <div class="mosaik-info">
              @if($listing->address)
                <div class="mosaik-adress">{{ $listing->address }}</div>
              @endif
              @if($listing->price)
                <div class="mosaik-pris">{{ $listing->price }}</div>
              @endif
              <div class="mosaik-meta">
                @if($listing->rooms) <span>{{ $listing->rooms }}</span> @endif
                @if($listing->area) <span>{{ $listing->area }}</span> @endif
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <div style="text-align:center;margin-top:48px;">
        <a href="{{ home_url('/objekt') }}" class="btn-primary">Se alla objekt</a>
      </div>
    @else
      <p class="objekt-tomma">Inga objekt tillgängliga just nu.</p>
    @endif

  </div>
</section>



@endsection
