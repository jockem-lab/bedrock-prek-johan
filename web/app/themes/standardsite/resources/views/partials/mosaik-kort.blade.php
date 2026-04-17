<a href="{{ home_url('/objekt/' . $listing->slug) }}"
   class="mosaik-kort {{ $stor ? 'mosaik-kort--stor' : '' }}">
  <div class="mosaik-bild" style="background-image:url('{{ $listing->image }}');background-color:#243558;"></div>
  @if($listing->status)
    @php $badge = match($listing->status) {
      'sald' => 'Såld', 'kommande' => 'Kommande',
      'tillsalu' => 'Till salu', 'budgivning' => 'Budgivning',
      default => ucfirst($listing->status)
    }; @endphp
    <div class="objekt-status objekt-status--{{ $listing->status }}">{{ $badge }}</div>
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
