@php
$post_id = get_the_ID();

function fasad_unserialize($raw) {
    if (!is_string($raw)) return $raw;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}

// Location
$loc     = fasad_unserialize(get_post_meta($post_id, '_fasad_location', true));
$address = ($loc && !empty($loc->address)) ? $loc->address : get_the_title($post_id);
$city    = ($loc && !empty($loc->city) && is_string($loc->city)) ? $loc->city : '';
$area    = ($loc && !empty($loc->area) && is_string($loc->area)) ? $loc->area : '';
$district = ($loc && !empty($loc->district) && is_string($loc->district)) ? $loc->district : '';
$subarea = $area ?: $district ?: $city;

// Economy
$eco   = fasad_unserialize(get_post_meta($post_id, '_fasad_economy', true));
$price = '';
if ($eco && !empty($eco->price->primary->amount))
    $price = number_format($eco->price->primary->amount, 0, ',', '.') . ' kr/bud';
$fee = '';
if ($eco && !empty($eco->association->fee->amount))
    $fee = number_format($eco->association->fee->amount, 0, ',', '.') . ' kr/mån';

// Size
$sz = fasad_unserialize(get_post_meta($post_id, '_fasad_size', true));
$rooms_count = ($sz && !empty($sz->rooms) && is_scalar($sz->rooms)) ? $sz->rooms : '';
$living_area = '';
$biarea = '';
if (!empty($sz->area->areas) && is_array($sz->area->areas)) {
    foreach ($sz->area->areas as $a) {
        if (!empty($a->type) && $a->type === 'Boarea' && !empty($a->size))
            $living_area = $a->size . ' kvm';
        if (!empty($a->type) && $a->type === 'Biarea' && !empty($a->size))
            $biarea = $a->size . ' kvm';
    }
}
$area_str = $living_area . ($biarea ? ' + ' . $biarea : '');
$floor = '';
$elevator = '';
if (!empty($sz->floor)) $floor = $sz->floor;
if (!empty($sz->hasElevator)) $elevator = $sz->hasElevator ? 'Ja' : 'Nej';
$built_year = '';
$build = fasad_unserialize(get_post_meta($post_id, '_fasad_building', true));
if ($build && !empty($build->constructionYear)) $built_year = $build->constructionYear;

// Images
$imgs_raw = get_post_meta($post_id, '_fasad_images', true);
$imgs     = fasad_unserialize($imgs_raw);
$images   = [];
if (is_array($imgs)) {
    foreach ($imgs as $img) {
        if (!empty($img->variants) && is_array($img->variants)) {
            foreach ($img->variants as $v) {
                if (($v->type ?? '') === 'highres' && !empty($v->path)) {
                    $images[] = rest_url('prek/v1/bildproxy?url=') . urlencode($v->path);
                    break;
                }
            }
        }
    }
}

// Realtors
$realtors_raw  = get_post_meta($post_id, '_fasad_realtors', true);
$realtors_data = fasad_unserialize($realtors_raw);
$first_realtor = null;
if (is_array($realtors_data) && !empty($realtors_data)) {
    $first_realtor = $realtors_data[0];
} elseif (is_object($realtors_data)) {
    $first_realtor = $realtors_data;
}

// Realtor image
$maklare_bild = '';
if ($first_realtor && !empty($first_realtor->image)) {
    if (is_string($first_realtor->image)) $maklare_bild = $first_realtor->image;
    elseif (!empty($first_realtor->image->path)) $maklare_bild = $first_realtor->image->path;
}
$maklare_namn = $first_realtor ? trim(($first_realtor->firstname ?? '') . ' ' . ($first_realtor->lastname ?? '')) : '';
$maklare_tel = '';
if ($first_realtor && !empty($first_realtor->cellphone)) {
    $tel = $first_realtor->cellphone;
    $maklare_tel_display = preg_replace('/^46/', '0', $tel);
    $maklare_tel_href = '+' . ltrim($tel, '+');
}
$maklare_email = $first_realtor->email ?? '';
$maklare_titel = $first_realtor->title ?? 'Fastighetsmäklare';

// Status
$status_raw = get_post_meta($post_id, '_fasad_status', true);
$status = is_string($status_raw) ? strtolower(trim($status_raw)) : '';
$minilist = get_post_meta($post_id, '_fasad_minilist', true);

// Descriptions
$desc_raw  = get_post_meta($post_id, '_fasad_descriptions', true);
$desc_data = fasad_unserialize($desc_raw);
$desc_text = '';
if (is_array($desc_data)) {
    foreach ($desc_data as $d) {
        if (!empty($d->text)) { $desc_text = $d->text; break; }
    }
} elseif (is_object($desc_data) && !empty($desc_data->text)) {
    $desc_text = $desc_data->text;
}

// Showings
$showings_raw = get_post_meta($post_id, '_fasad_showings', true);
$showings = fasad_unserialize($showings_raw);
if (!is_array($showings)) $showings = [];
$showings = array_filter($showings, function($s) {
    return !empty($s->startDate) && strtotime($s->startDate) > time() - 3600;
});

// Documents
$docs_obj  = fasad_unserialize(get_post_meta($post_id, '_fasad_documents', true));
$documents = [];
if ($docs_obj && !empty($docs_obj->listingDocuments)) {
    foreach ($docs_obj->listingDocuments as $doc) {
        $documents[] = (object)['alias' => $doc->alias ?? '', 'href' => $doc->href ?? ''];
    }
}

// Bids
$bids = fasad_unserialize(get_post_meta($post_id, '_fasad_bids', true));
if (!is_array($bids)) $bids = [];
@endphp

<article class="em-listing">

  {{-- Rubrik --}}
  <header class="em-listing-header">
    @if($subarea)
      <p class="em-listing-omrade">{{ strtoupper($subarea) }}</p>
    @endif
    <h1 class="em-listing-adress">{{ strtoupper($address) }}</h1>
  </header>

  {{-- Hero-bild --}}
  @if(!empty($images))
  <div class="em-listing-hero">
    <img src="{{ $images[0] }}" alt="{{ $address }}" class="em-listing-hero-img">
    @if(count($images) > 1)
      <button class="em-listing-alla-bilder" onclick="document.getElementById('em-galleri').scrollIntoView({behavior:'smooth'})">
        ALLA BILDER
      </button>
    @endif
  </div>
  @endif

  {{-- Fakta + Mäklare --}}
  <div class="em-listing-fakta-wrap">
    <div class="em-listing-fakta">
      @if($price)
        <div class="em-fakta-rad">
          <span class="em-fakta-label">Pris</span>
          <span class="em-fakta-värde">{{ $price }}</span>
        </div>
      @endif
      @if($fee)
        <div class="em-fakta-rad">
          <span class="em-fakta-label">Avgift</span>
          <span class="em-fakta-värde">{{ $fee }}</span>
        </div>
      @endif
      @if($area_str)
        <div class="em-fakta-rad">
          <span class="em-fakta-label">Storlek</span>
          <span class="em-fakta-värde">{{ $area_str }}</span>
        </div>
      @endif
      @if($rooms_count)
        <div class="em-fakta-rad">
          <span class="em-fakta-label">Antal Rum</span>
          <span class="em-fakta-värde">{{ $rooms_count }}</span>
        </div>
      @endif
      @if($floor)
        <div class="em-fakta-rad">
          <span class="em-fakta-label">Våning</span>
          <span class="em-fakta-värde">{{ $floor }}{{ $elevator === 'Ja' ? ', hiss finns' : '' }}</span>
        </div>
      @endif
      @if($built_year)
        <div class="em-fakta-rad">
          <span class="em-fakta-label">Byggår</span>
          <span class="em-fakta-värde">{{ $built_year }}</span>
        </div>
      @endif
    </div>

    <div class="em-listing-divider"></div>

    <div class="em-listing-maklare">
      <p class="em-maklare-label">ANSVARIG MÄKLARE</p>
      <h2 class="em-maklare-namn">{{ strtoupper($maklare_namn) }}</h2>
      @if(!empty($maklare_tel_href))
        <a href="tel:{{ $maklare_tel_href }}" class="em-maklare-kontakt">{{ $maklare_tel_display ?? $first_realtor->cellphone }}</a>
      @endif
      @if($maklare_email)
        <a href="mailto:{{ $maklare_email }}" class="em-maklare-kontakt">{{ strtoupper($maklare_email) }}</a>
      @endif
    </div>
  </div>

  {{-- Beskrivning --}}
  @if($desc_text)
  <div class="em-listing-beskrivning">
    <div class="em-listing-beskrivning-text">{!! nl2br(e($desc_text)) !!}</div>
  </div>
  @endif

  {{-- Visningstider --}}
  @if(!empty($showings))
  <div class="em-listing-sektion">
    <h3 class="em-sektion-rubrik">VISNINGSTIDER</h3>
    @foreach($showings as $showing)
      @php
        $start  = strtotime($showing->startDate);
        $end    = strtotime($showing->endDate);
        $dagar  = ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'];
        $mån    = ['','Jan','Feb','Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
        $datum  = $dagar[date('w',$start)] . ' ' . date('j',$start) . ' ' . $mån[(int)date('n',$start)];
        $tid    = date('H:i',$start) . '–' . date('H:i',$end);
      @endphp
      <div class="em-visning-rad">
        <span class="em-visning-datum">{{ $datum }}</span>
        <span class="em-visning-tid">{{ $tid }}</span>
      </div>
    @endforeach
  </div>
  @endif

  {{-- Dokument --}}
  @if(!empty($documents))
  <div class="em-listing-sektion">
    <h3 class="em-sektion-rubrik">DOKUMENT</h3>
    @foreach($documents as $doc)
      <a href="{{ $doc->href }}" target="_blank" class="em-dokument-rad">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        {{ $doc->alias }}
      </a>
    @endforeach
  </div>
  @endif

  {{-- Bildgalleri --}}
  @if(count($images) > 1)
  <div class="em-galleri" id="em-galleri">
    @foreach($images as $i => $img)
      <div class="em-galleri-item{{ $i >= 4 ? ' em-galleri-dold' : '' }}">
        <img src="{{ $img }}" alt="Bild {{ $i+1 }}" loading="lazy">
      </div>
    @endforeach
    @if(count($images) > 4)
    <div class="em-galleri-visa-fler" id="em-visa-fler">
      <button onclick="visaAllaEM()" class="em-visa-fler-btn">VISA ALLA {{ count($images) }} BILDER</button>
    </div>
    @endif
  </div>
  <script>
  function visaAllaEM() {
    document.querySelectorAll('.em-galleri-dold').forEach(e => e.classList.remove('em-galleri-dold'));
    document.getElementById('em-visa-fler')?.remove();
  }
  </script>
  @endif

</article>
