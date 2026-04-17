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
              @php
                $badge = match($listing->status) {
                  'sald'      => 'Såld',
                  'kommande'  => 'Kommande',
                  'till-salu' => 'Till salu',
                  'budgivning'=> 'Budgivning',
                  default     => ucfirst($listing->status)
                };
              @endphp
              <div class="objekt-status objekt-status--{{ $listing->status }}">{{ $badge }}</div>
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
                  @if($listing->rooms) <span>{{ $listing->rooms }}</span> @endif
                  @if($listing->area)  <span>{{ $listing->area }}</span>  @endif
                </div>
              </div>
            </div>
          </div>
        </a>
      </div>
    @endforeach
  </div>
  <p class="objekt-antal">{{ $antal }} objekt</p>
@else
  <p class="objekt-tomma">Inga objekt just nu.</p>
@endif
