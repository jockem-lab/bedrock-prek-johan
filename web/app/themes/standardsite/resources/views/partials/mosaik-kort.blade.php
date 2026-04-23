<a href="{{ home_url('/objekt/' . $listing->slug) }}" class="fdr-objekt-kort">
  <div class="fdr-objekt-bild">
    @if($listing->image)
      <img src="{{ $listing->image }}" alt="{{ $listing->address ?? '' }}" loading="lazy">
    @else
      <div class="fdr-objekt-bild-placeholder"></div>
    @endif
    @if($listing->status)
      @php $badge = match($listing->status) {
        'sald' => 'Såld', 'kommande' => 'Kommande',
        'tillsalu' => 'Till salu', 'budgivning' => 'Budgivning',
        default => ucfirst($listing->status)
      }; @endphp
      <span class="fdr-objekt-status fdr-status--{{ $listing->status }}">{{ $badge }}</span>
    @endif
  </div>
  <div class="fdr-objekt-info">
    @if($listing->address)
      <div class="fdr-objekt-adress">{{ $listing->address }}</div>
    @endif
    <div class="fdr-objekt-meta">
      @if($listing->rooms)<span>{{ $listing->rooms }}</span>@endif
      @if($listing->area)<span>{{ $listing->area }}</span>@endif
    </div>
    @if($listing->price)
      <div class="fdr-objekt-pris">{{ $listing->price }}</div>
    @endif
  </div>
</a>
