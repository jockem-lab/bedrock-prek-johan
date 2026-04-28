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

// Showings
$showings_raw = get_post_meta($post_id, '_fasad_showings', true);
$showings = fasad_unserialize($showings_raw);
if (!is_array($showings)) $showings = [];
$showings = array_filter($showings, function($s) {
    return !empty($s->startDate) && strtotime($s->startDate) > time() - 3600;
});

// Documents
$docs_raw = get_post_meta($post_id, '_fasad_documents', true);
$docs_obj = fasad_unserialize($docs_raw);
$documents = [];
if ($docs_obj && !empty($docs_obj->listingDocuments)) {
    foreach ($docs_obj->listingDocuments as $doc) {
        $documents[] = (object)[
            'alias' => $doc->alias ?? '',
            'href'  => $doc->href ?? '',
        ];
    }
}

// Bids
$bids_raw = get_post_meta($post_id, '_fasad_bids', true);
$bids = fasad_unserialize($bids_raw);
if (!is_array($bids)) $bids = [];

// Images
$imgs_raw = get_post_meta($post_id, '_fasad_images', true);
$imgs = fasad_unserialize($imgs_raw);
$images = [];
if (is_array($imgs)) {
    foreach ($imgs as $img) {
        if (!empty($img->variants) && is_array($img->variants)) {
            foreach ($img->variants as $v) {
                if (($v->type ?? '') === 'highres' && !empty($v->path)) {
                    $images[] = rest_url('prek/v1/bildproxy?url=') . urlencode($v->path); break;
                }
            }
        }
    }
}
$images_hero = array_slice($images, 0, 5);

// Size
$sz = fasad_unserialize(get_post_meta($post_id, '_fasad_size', true));
$rooms = ($sz && !empty($sz->rooms) && is_scalar($sz->rooms)) ? $sz->rooms . ' rum' : '';
if (!empty($sz->roomsInformation) && is_string($sz->roomsInformation)) {
    $rooms = $sz->rooms . ' ' . $sz->roomsInformation;
}
$area = '';
if (!empty($sz->area->areas) && is_array($sz->area->areas)) {
    foreach ($sz->area->areas as $a) {
        if (!empty($a->type) && $a->type === 'Boarea' && !empty($a->size)) {
            $area = $a->size . ' ' . strtolower($a->unit ?? 'kvm');
            break;
        }
    }
    if (empty($area) && !empty($sz->area->areas[0]->size)) {
        $area = $sz->area->areas[0]->size . ' ' . strtolower($sz->area->areas[0]->unit ?? 'kvm');
    }
}

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
$words = explode(' ', wp_strip_all_tags($salesText));
$salesTextShort = count($words) > 30 ? implode(' ', array_slice($words, 0, 30)) . '…' : $salesText;

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

{{-- Split-layout: bild vänster, info höger --}}
<div class="objekt-split">
  <div class="objekt-split-bild">
    @if(!empty($images))
      @if($status)
        <div class="objekt-status-badge objekt-status--{{ $status }}">{{ strtoupper($status) }}</div>
      @endif
      <div class="objekt-hero-slideshow">
        @foreach($images_hero as $i => $img)
          <div class="objekt-hero-slide {{ $i === 0 ? 'active' : '' }}" style="background-image:url('{{ $img }}')"></div>
        @endforeach
        @if(count($images_hero) > 1)
          <button class="objekt-hero-prev">&#8592;</button>
          <button class="objekt-hero-next">&#8594;</button>
          <div class="objekt-hero-dots">
            @foreach($images_hero as $i => $img)
              <span class="objekt-hero-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></span>
            @endforeach
          </div>
        @endif
      </div>
    @endif
  </div>
  <div class="objekt-split-info">
    @if($city)
      <div class="objekt-split-omrade">{{ strtoupper($city) }}@if($commune) · {{ strtoupper($commune) }}@endif</div>
    @endif
    <h1 class="objekt-split-adress">{{ $full_address }}</h1>
    @if($salesTitle && $salesTitle !== $full_address)
      <p class="objekt-split-undertitel"><em>{{ $salesTitle }}</em></p>
    @endif
    @if($salesTextShort)
      <p class="objekt-split-intro">{{ $salesTextShort }}</p>
    @endif
    <div class="objekt-split-fakta">
      @if($area)
        <div class="objekt-split-fakta-item">
          <span class="objekt-split-fakta-label">Boarea</span>
          <span class="objekt-split-fakta-värde">{{ $area }}</span>
        </div>
      @endif
      @if($rooms)
        <div class="objekt-split-fakta-item">
          <span class="objekt-split-fakta-label">Rum</span>
          <span class="objekt-split-fakta-värde">{{ $rooms }}</span>
        </div>
      @endif
      @if($floor)
        <div class="objekt-split-fakta-item">
          <span class="objekt-split-fakta-label">Våning</span>
          <span class="objekt-split-fakta-värde">{{ $floor }}</span>
        </div>
      @endif
      @if($price)
        <div class="objekt-split-fakta-item">
          <span class="objekt-split-fakta-label">Utgångspris</span>
          <span class="objekt-split-fakta-värde">{{ $price }}</span>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Faktarad --}}
{{-- Huvudinnehåll --}}
<div class="objekt-detalj-inner">
  <div class="objekt-detalj-content">
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
      @if(!empty($documents))
      <div class="accordion-item">
        <button class="accordion-trigger">Dokument <span class="accordion-icon">+</span></button>
        <div class="accordion-content">
          @foreach($documents as $doc)
            <a href="{{ $doc->href }}" target="_blank" class="dokument-rad">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              {{ $doc->alias }}
            </a>
          @endforeach
        </div>
      </div>
      @endif
    </div>

  </div>

  {{-- Sidebar: mäklarkort --}}
  <div class="objekt-detalj-sidebar">
    <div class="objekt-detalj-kontakt">
      @if($first_realtor)
        @php
          $maklare_bild = '';
          if (!empty($first_realtor->image)) {
              if (is_string($first_realtor->image)) {
                  $maklare_bild = $first_realtor->image;
              } elseif (!empty($first_realtor->image->path)) {
                  $maklare_bild = $first_realtor->image->path;
              }
          }
        @endphp
        @if($maklare_bild)
          <img src="{{ $maklare_bild }}" alt="{{ $first_realtor->firstname ?? '' }}" class="maklare-bild">
        @else
          <div class="maklare-bild-placeholder"></div>
        @endif
        <h3>{{ ($first_realtor->firstname ?? '') . ' ' . ($first_realtor->lastname ?? '') }}</h3>
        @if(!empty($first_realtor->title))
          <p class="maklare-titel">{{ $first_realtor->title }}</p>
        @endif
        @if(!empty($first_realtor->cellphone))
          @php
            $tel = $first_realtor->cellphone ?? '';
            $tel_display = preg_replace('/^46/', '0', $tel);
            $tel_display = preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '$1-$2 $3 $4', $tel_display);
            $tel_href = '+' . ltrim($tel, '+');
          @endphp
          <p><a href="tel:{{ $tel_href }}">{{ $tel_display }}</a></p>
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
    {{-- Budgivning --}}
    @if(!empty($bids))
    <div class="objekt-budgivning">
      <h3>Budgivning</h3>
      @foreach($bids as $bid)
        @php
          $belopp = !empty($bid->amount) ? number_format($bid->amount, 0, ',', ' ') . ' kr' : '';
          $tid = !empty($bid->createdAt) ? date('d/m H:i', strtotime($bid->createdAt)) : '';
        @endphp
        <div class="bud-rad">
          <span class="bud-belopp">{{ $belopp }}</span>
          <span class="bud-tid">{{ $tid }}</span>
        </div>
      @endforeach
    </div>
    @endif

    {{-- Visningstider --}}
    @if(!empty($showings))
    <div class="objekt-visningar">
      <h3>Visningstider</h3>
      @foreach($showings as $showing)
        @php
          $start = strtotime($showing->startDate);
          $end   = strtotime($showing->endDate);
          $dagar = ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'];
          $manader = ['','Januari','Februari','Mars','April','Maj','Juni','Juli','Augusti','September','Oktober','November','December'];
          $datum = $dagar[date('w', $start)] . ' ' . date('j', $start) . ' ' . $manader[(int)date('n', $start)];
          $tid   = date('H:i', $start) . ' – ' . date('H:i', $end);
        @endphp
        <div class="visning-rad">
          <div class="visning-datum">{{ ucfirst($datum) }}</div>
          <div class="visning-tid">{{ $tid }}</div>
          @if(!empty($showing->openForRegistration))
            <a href="#visningsanmalan" class="btn-primary visning-anmalan-btn">Anmäl intresse</a>
          @endif
        </div>
      @endforeach
    </div>
    @endif

    {{-- Dokument --}}
    @if(!empty($documents))
    <div class="objekt-dokument">
      <h3>Ladda ner dokument</h3>
      @foreach($documents as $doc)
        <a href="{{ $doc->href }}" target="_blank" class="dokument-rad">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          {{ $doc->alias }}
        </a>
      @endforeach
    </div>
    @endif

  </div>
</div>

{{-- Bildgalleri med lightbox --}}
@if(count($images) > 1)
<div class="objekt-galleri">
  <div class="objekt-galleri-lista">
    @foreach($images as $i => $img)
      <div class="objekt-galleri-bild-wrap">
        <img src="{{ $img }}" alt="Bild {{ $i + 1 }}" loading="lazy">
      </div>
    @endforeach
  </div>
</div>

{{-- Alla bilder för lightbox --}}
<script>
var allImages = @json($images);

function visaAllaGalleri() {
  document.querySelectorAll('.galleri-dold').forEach(function(el) {
    el.classList.remove('galleri-dold');
  });
  // Byt till jämnt rutnät
  document.querySelectorAll('.objekt-galleri-item--stor').forEach(function(el) {
    el.classList.remove('objekt-galleri-item--stor');
  });
  var grid = document.getElementById('galleri-grid');
  if (grid) grid.classList.add('galleri-grid--alla');
  var wrap = document.getElementById('galleri-visa-fler-wrap');
  if (wrap) wrap.style.display = 'none';
}
</script>
{{-- Lightbox --}}
<div id="lightbox" class="lightbox">
  <button id="lightbox-close" class="lightbox-close">&times;</button>
  <button id="lightbox-prev" class="lightbox-prev">&#8592;</button>
  <button id="lightbox-next" class="lightbox-next">&#8594;</button>
  <div class="lightbox-inner">
    <img id="lightbox-img" src="" alt="">
    <p id="lightbox-caption" class="lightbox-caption"></p>
    <p id="lightbox-counter" class="lightbox-counter"></p>
  </div>
</div>
@endif
