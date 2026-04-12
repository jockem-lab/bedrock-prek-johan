@php
$post_id = get_the_ID();

// Helper: dubbel unserialize
function fasad_unserialize($raw) {
    if (!is_string($raw)) return $raw;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}

// Location
$loc = fasad_unserialize(get_post_meta($post_id, '_fasad_location', true));
$address = ($loc && !empty($loc->address)) ? $loc->address : get_the_title($post_id);
$city    = ($loc && !empty($loc->city) && is_string($loc->city)) ? $loc->city : '';
$zip     = ($loc && !empty($loc->zipCode) && is_string($loc->zipCode)) ? $loc->zipCode : '';
$commune = ($loc && !empty($loc->commune) && is_string($loc->commune)) ? $loc->commune : '';
$full_address = $address . ($city ? ', ' . $city : '');

// Economy
$eco = fasad_unserialize(get_post_meta($post_id, '_fasad_economy', true));
$price = '';
if ($eco && !empty($eco->price->primary->amount))
    $price = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';
$fee = '';
if ($eco && !empty($eco->association->fee->amount))
    $fee = number_format($eco->association->fee->amount, 0, ',', ' ') . ' kr/mån';

// Images
$imgs_raw = get_post_meta($post_id, '_fasad_images', true);
$imgs = fasad_unserialize($imgs_raw);
$images = [];
if (is_array($imgs)) {
    foreach ($imgs as $img) {
        if (!empty($img->variants) && is_array($img->variants)) {
            foreach ($img->variants as $v) {
                if (($v->type ?? '') === 'highres' && !empty($v->path)) {
                    $images[] = $v->path; break;
                }
            }
        }
    }
}

// Size
$sz = fasad_unserialize(get_post_meta($post_id, '_fasad_size', true));
$area  = ($sz && !empty($sz->livingArea) && is_scalar($sz->livingArea)) ? $sz->livingArea . ' kvm' : '';
$rooms = ($sz && !empty($sz->rooms) && is_scalar($sz->rooms)) ? $sz->rooms . ' rum' : '';

// Type
$tp = fasad_unserialize(get_post_meta($post_id, '_fasad_descriptionType', true));
$type = ($tp && !empty($tp->alias) && is_string($tp->alias)) ? $tp->alias : '';

// Facts
$facts = fasad_unserialize(get_post_meta($post_id, '_fasad_facts', true));
$floor    = ($facts && !empty($facts->floor)) ? $facts->floor : '';
$built    = ($facts && !empty($facts->built)) ? $facts->built : '';
$elevator = ($facts && isset($facts->elevator)) ? ($facts->elevator ? 'Ja' : 'Nej') : '';

// Building
$building = fasad_unserialize(get_post_meta($post_id, '_fasad_building', true));
$built_year = ($building && !empty($building->constructionYear)) ? $building->constructionYear : $built;

// Sales texts
$salesTitle = is_string(get_post_meta($post_id, '_fasad_salesTitle', true)) ? get_post_meta($post_id, '_fasad_salesTitle', true) : $full_address;
$raw_st = get_post_meta($post_id, '_fasad_salesText', true);
$salesText = is_string($raw_st) ? $raw_st : '';

// Realtors
$realtors_raw = fasad_unserialize(get_post_meta($post_id, '_fasad_realtors', true));
$first_realtor = null;
if (is_array($realtors_raw) && !empty($realtors_raw)) {
    $first_realtor = $realtors_raw[0]; // Data direkt på objektet
}

// Status
$status_raw = fasad_unserialize(get_post_meta($post_id, '_fasad_status', true));
$status = ($status_raw && !empty($status_raw->alias)) ? $status_raw->alias : '';
@endphp

{{-- Hero-bild --}}
@if(!empty($images[0]))
  <div class="objekt-detalj-hero">
    <img src="{{ $images[0] }}" alt="{{ $full_address }}">
    @if($status)
      <div class="objekt-detalj-status objekt-status--{{ $status }}">{{ ucfirst($status) }}</div>
    @endif
  </div>
@endif

{{-- Faktarad --}}
<div class="objekt-detalj-faktarad">
  <div class="objekt-detalj-faktarad-inner">
    @if($full_address)
      <div class="faktarad-item">
        <span class="faktarad-label">Adress</span>
        <span class="faktarad-värde">{{ $full_address }}</span>
      </div>
    @endif
    @if($type)
      <div class="faktarad-item">
        <span class="faktarad-label">Typ</span>
        <span class="faktarad-värde">{{ $type }}</span>
      </div>
    @endif
    @if($rooms)
      <div class="faktarad-item">
        <span class="faktarad-label">Rum</span>
        <span class="faktarad-värde">{{ $rooms }}</span>
      </div>
    @endif
    @if($area)
      <div class="faktarad-item">
        <span class="faktarad-label">Boarea</span>
        <span class="faktarad-värde">{{ $area }}</span>
      </div>
    @endif
    @if($price)
      <div class="faktarad-item">
        <span class="faktarad-label">Pris</span>
        <span class="faktarad-värde">{{ $price }}</span>
      </div>
    @endif
    @if($fee)
      <div class="faktarad-item">
        <span class="faktarad-label">Avgift</span>
        <span class="faktarad-värde">{{ $fee }}</span>
      </div>
    @endif
  </div>
</div>

{{-- Huvudinnehåll --}}
<div class="objekt-detalj-inner">
  <div class="objekt-detalj-content">
    <h1>{{ $full_address }}</h1>
    @if($salesTitle && $salesTitle !== $full_address)
      <p class="objekt-detalj-undertitel">{{ $salesTitle }}</p>
    @endif

    <div class="objekt-accordion">
      {{-- Beskrivning --}}
      @if($salesText)
        <div class="accordion-item open">
          <button class="accordion-trigger">Beskrivning <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <div class="objekt-detalj-beskrivning">{!! nl2br(e($salesText)) !!}</div>
          </div>
        </div>
      @endif

      {{-- Fakta --}}
      <div class="accordion-item open">
        <button class="accordion-trigger">Fakta <span class="accordion-icon">+</span></button>
        <div class="accordion-content">
          <table class="fakta-tabell">
            @if($commune)<tr><th>Kommun</th><td>{{ $commune }}</td></tr>@endif
            @if($zip)<tr><th>Postnummer</th><td>{{ $zip }}</td></tr>@endif
            @if($type)<tr><th>Bostadstyp</th><td>{{ $type }}</td></tr>@endif
            @if($rooms)<tr><th>Antal rum</th><td>{{ $rooms }}</td></tr>@endif
            @if($area)<tr><th>Boarea</th><td>{{ $area }}</td></tr>@endif
            @if($floor)<tr><th>Våningsplan</th><td>{{ $floor }}</td></tr>@endif
            @if($elevator)<tr><th>Hiss</th><td>{{ $elevator }}</td></tr>@endif
          </table>
        </div>
      </div>

      {{-- Byggnad --}}
      @if($built_year)
        <div class="accordion-item">
          <button class="accordion-trigger">Byggnad <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <table class="fakta-tabell">
              <tr><th>Byggnadsår</th><td>{{ $built_year }}</td></tr>
            </table>
          </div>
        </div>
      @endif

      {{-- Kostnader --}}
      @if($price || $fee)
        <div class="accordion-item">
          <button class="accordion-trigger">Kostnader <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <table class="fakta-tabell">
              @if($price)<tr><th>Pris</th><td>{{ $price }}</td></tr>@endif
              @if($fee)<tr><th>Månadsavgift</th><td>{{ $fee }}</td></tr>@endif
            </table>
          </div>
        </div>
      @endif

      {{-- Dokument --}}
      <div class="accordion-item">
        <button class="accordion-trigger">Dokument <span class="accordion-icon">+</span></button>
        <div class="accordion-content">
          <p class="dokument-tomma">Inga dokument har lagts till ännu.</p>
        </div>
      </div>
    </div>

    {{-- Bildgalleri --}}
    @if(count($images) > 1)
      <div class="objekt-galleri">
        <div class="objekt-galleri-grid">
          @foreach($images as $i => $img)
            <div class="objekt-galleri-item {{ $i === 0 ? 'objekt-galleri-item--stor' : '' }}">
              <a href="{{ $img }}">
                <img src="{{ $img }}" alt="Bild {{ $i + 1 }}">
              </a>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  {{-- Sidebar: mäklarkort --}}
  <div class="objekt-detalj-sidebar">
    <div class="objekt-detalj-kontakt">
      @if($first_realtor)
        @if(!empty($first_realtor->image->path))
          <img src="{{ $first_realtor->image->path }}" alt="{{ $first_realtor->firstname ?? '' }}" class="maklare-bild">
        @else
          <img src="https://via.placeholder.com/80" alt="Mäklare" class="maklare-bild">
        @endif
        <h3>{{ ($first_realtor->firstname ?? '') . ' ' . ($first_realtor->lastname ?? '') }}</h3>
        @if(!empty($first_realtor->title))
          <p class="maklare-titel">{{ $first_realtor->title }}</p>
        @endif
        @if(!empty($first_realtor->cellphone))
          <p><a href="tel:{{ $first_realtor->cellphone }}">{{ $first_realtor->cellphone }}</a></p>
        @endif
        @if(!empty($first_realtor->email))
          <p><a href="mailto:{{ $first_realtor->email }}">{{ $first_realtor->email }}</a></p>
        @endif
        <a href="{{ home_url('/kontakt') }}" class="btn-primary">Kontakta mäklaren</a>
      @else
        <h3>Intresserad?</h3>
        <p>Kontakta oss om {{ $full_address }}.</p>
        <a href="{{ home_url('/kontakt') }}" class="btn-primary">Kontakta oss</a>
      @endif
    </div>
  </div>
</div>
