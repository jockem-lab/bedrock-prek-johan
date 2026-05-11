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
$plan_images = [];
$other_images = [];
if (is_array($imgs)) {
    foreach ($imgs as $img) {
        if (!empty($img->variants) && is_array($img->variants)) {
            foreach ($img->variants as $v) {
                if (($v->type ?? '') === 'highres' && !empty($v->path)) {
                    $img_url = rest_url('prek/v1/bildproxy?url=') . urlencode($v->path);
                    // Identifiera planlösning via flera signaler:
                    // 1. Kategori (id=2 eller alias innehåller "plan")
                    // 2. Bildens text/namn innehåller plan-relaterade ord
                    // 3. Filändelse .png (vanligt för planlösningar från ritprogram)
                    $is_plan = false;
                    $cat_id = isset($img->category) ? ($img->category->id ?? 0) : 0;
                    $cat_alias = isset($img->category) ? strtolower($img->category->alias ?? '') : '';
                    $img_text = strtolower($img->text ?? '');
                    $path_lower = strtolower($v->path);
                    $plan_keywords = ['plan', 'ritning', '2d', 'skiss', 'planlösning', 'planritning'];

                    if ($cat_id == 2) {
                        $is_plan = true;
                    } elseif (strpos($cat_alias, 'plan') !== false) {
                        $is_plan = true;
                    } else {
                        foreach ($plan_keywords as $kw) {
                            if (strpos($img_text, $kw) !== false) {
                                $is_plan = true;
                                break;
                            }
                        }
                        if (!$is_plan && substr($path_lower, -4) === '.png') {
                            $is_plan = true;
                        }
                    }

                    if ($is_plan) $plan_images[] = $img_url;
                    else $other_images[] = $img_url;
                    break;
                }
            }
        }
    }
}
// Hero: bara vanliga bilder (planlösning visas bara i galleri), max 5 i slideshow
$images_hero = array_slice($other_images, 0, 5);
// Galleri: planlösning först, sedan resten
$images = array_merge($plan_images, $other_images);

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

// Förening (association)
$assoc_raw = fasad_unserialize(get_post_meta($post_id, '_fasad_association', true));
$assoc_name = '';
$assoc_org = '';
$assoc_text = '';
if ($assoc_raw) {
    $assoc_name = $assoc_raw->name ?? '';
    $assoc_org  = $assoc_raw->organisationNumber ?? '';
    $assoc_text = $assoc_raw->description ?? '';
}

// Område (areaDescription)
$area_desc_raw = get_post_meta($post_id, '_fasad_areaDescription', true);
$area_desc = is_string($area_desc_raw) ? $area_desc_raw : '';

// Lat/Lng för karta
$lat = ($loc && !empty($loc->lat)) ? $loc->lat : '';
$lng = ($loc && !empty($loc->lng)) ? $loc->lng : '';

// Sortera dokument — planlösning först
$documents_sorted = $documents;
usort($documents_sorted, function($a, $b) {
    $a_plan = stripos($a->alias ?? '', 'plan') !== false ? 0 : 1;
    $b_plan = stripos($b->alias ?? '', 'plan') !== false ? 0 : 1;
    return $a_plan - $b_plan;
});

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
        <div class="objekt-hero-overlay-knappar">
          <button onclick="(function(){var imgs=document.querySelectorAll('.objekt-galleri-bild-wrap');var target=imgs.length>1?imgs[1]:imgs[0];if(target)target.scrollIntoView({behavior:'smooth',block:'start'});})()" class="objekt-hero-knapp">
            Alla bilder
          </button>
          @if(!empty($plan_images))
            <button onclick="document.querySelector('.objekt-galleri').scrollIntoView({behavior:'smooth'}); setTimeout(function(){var first=document.querySelector('.objekt-galleri-bild-wrap img'); if(first) first.click();}, 800);" class="objekt-hero-knapp">
              Planlösning
            </button>
          @endif
        </div>
      </div>
    @endif
  </div>
  <div class="objekt-split-info">
    <h1 class="objekt-split-adress">{{ $full_address }}</h1>
    @if($salesTextShort)
      <p class="objekt-split-intro">
        {{ $salesTextShort }}
        @if($salesText && strlen($salesText) > strlen($salesTextShort))
          <a href="#beskrivning" class="objekt-split-lasmer" onclick="document.querySelector('.accordion-item .accordion-trigger').click(); document.querySelector('.objekt-detalj-content').scrollIntoView({behavior:'smooth'}); return false;">Läs mer →</a>
        @endif
      </p>
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

    {{-- Visningsruta --}}
    <div class="objekt-split-visning">
      <div class="objekt-split-visning-label">Visning</div>
      @if(!empty($showings))
        @foreach($showings as $showing)
          @php
            $start = strtotime($showing->startDate);
            $end   = strtotime($showing->endDate);
            $dagar = ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'];
            $manader = ['','januari','februari','mars','april','maj','juni','juli','augusti','september','oktober','november','december'];
            $datum = $dagar[date('w', $start)] . ' ' . date('j', $start) . ' ' . $manader[(int)date('n', $start)];
            $tid   = date('H:i', $start) . '–' . date('H:i', $end);
          @endphp
          <div class="objekt-split-visning-rad">
            <span>{{ ucfirst($datum) }}</span>
            <span>{{ $tid }}</span>
          </div>
        @endforeach
      @else
        <p class="objekt-split-visning-tom"><a href="#" onclick="document.getElementById('intresse-modal').style.display='flex'; return false;">Kontakta oss för visning →</a></p>
      @endif
    </div>

    {{-- Knappar --}}
    <div class="objekt-split-knappar">
      <button onclick="document.getElementById('intresse-modal').style.display='flex'" class="btn-primary">
        Intresseanmälan
      </button>
      @if(!empty($showings) && $first_realtor && !empty($first_realtor->email))
        @php
          $mail_subject = rawurlencode('Boka visning: ' . $full_address);
          $mail_body = rawurlencode("Hej!\n\nJag vill boka visning av " . $full_address . ".\n\nMvh,\n");
        @endphp
        <a href="mailto:{{ $first_realtor->email }}?subject={{ $mail_subject }}&body={{ $mail_body }}" class="btn-secondary">
          Boka visning
        </a>
      @endif
    </div>
  </div>
</div>

{{-- Faktarad --}}
{{-- Huvudinnehåll --}}
<div class="objekt-detalj-inner">
  <div class="objekt-detalj-content">


    <div class="objekt-accordion">

      {{-- 1. Beskrivning --}}
      @if($salesText)
        <div class="accordion-item open" id="beskrivning">
          <button class="accordion-trigger">Beskrivning <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <div class="objekt-detalj-beskrivning">{!! nl2br(e($salesText)) !!}</div>
          </div>
        </div>
      @endif

      {{-- 2. Fakta --}}
      <div class="accordion-item">
        <button class="accordion-trigger">Fakta <span class="accordion-icon">+</span></button>
        <div class="accordion-content">
          <table class="fakta-tabell">
            @if($type)<tr><th>Bostadstyp</th><td>{{ $type }}</td></tr>@endif
            @if($rooms)<tr><th>Antal rum</th><td>{{ $rooms }}</td></tr>@endif
            @if($area)<tr><th>Boarea</th><td>{{ $area }}</td></tr>@endif
            @if($floor)<tr><th>Våningsplan</th><td>{{ $floor }}</td></tr>@endif
            @if($elevator)<tr><th>Hiss</th><td>{{ $elevator }}</td></tr>@endif
            @if($built_year)<tr><th>Byggnadsår</th><td>{{ $built_year }}</td></tr>@endif
            @if($price)<tr><th>Pris</th><td>{{ $price }}</td></tr>@endif
            @if($fee)<tr><th>Månadsavgift</th><td>{{ $fee }}</td></tr>@endif
            @if($zip)<tr><th>Postnummer</th><td>{{ $zip }}</td></tr>@endif
            @if($commune)<tr><th>Kommun</th><td>{{ $commune }}</td></tr>@endif
          </table>
        </div>
      </div>

      {{-- 3. Förening --}}
      @if($assoc_name || $assoc_text)
        <div class="accordion-item">
          <button class="accordion-trigger">Förening <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <table class="fakta-tabell">
              @if($assoc_name)<tr><th>Namn</th><td>{{ $assoc_name }}</td></tr>@endif
              @if($assoc_org)<tr><th>Org.nr</th><td>{{ $assoc_org }}</td></tr>@endif
            </table>
            @if($assoc_text)
              <div class="objekt-detalj-beskrivning" style="margin-top:16px;">{!! nl2br(e($assoc_text)) !!}</div>
            @endif
          </div>
        </div>
      @endif

      {{-- 4. Område --}}
      @if($area_desc)
        <div class="accordion-item">
          <button class="accordion-trigger">Område <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <div class="objekt-detalj-beskrivning">{!! nl2br(e($area_desc)) !!}</div>
          </div>
        </div>
      @endif

      {{-- 5. Dokument --}}
      @if(!empty($documents_sorted))
        <div class="accordion-item">
          <button class="accordion-trigger">Dokument <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            @foreach($documents_sorted as $doc)
              <a href="{{ $doc->href }}" target="_blank" class="dokument-rad">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                {{ $doc->alias }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      {{-- 6. Karta --}}
      @if($lat && $lng)
        <div class="accordion-item">
          <button class="accordion-trigger">Karta <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <iframe
              src="https://www.openstreetmap.org/export/embed.html?bbox={{ $lng - 0.005 }},{{ $lat - 0.003 }},{{ $lng + 0.005 }},{{ $lat + 0.003 }}&layer=mapnik&marker={{ $lat }},{{ $lng }}"
              style="width:100%;height:400px;border:none;"
              loading="lazy"></iframe>
          </div>
        </div>
      @endif

      {{-- 7. Bilder --}}
      @if(!empty($images))
        <div class="accordion-item">
          <button class="accordion-trigger">Bilder <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <p><a href="#galleri" onclick="document.querySelector('.objekt-galleri').scrollIntoView({behavior:'smooth'}); return false;" class="objekt-bilder-lank">Visa alla {{ count($images) }} bilder ↓</a></p>
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
              if (is_string($first_realtor->image)) $maklare_bild = $first_realtor->image;
              elseif (!empty($first_realtor->image->path)) $maklare_bild = $first_realtor->image->path;
          }
          $tel = $first_realtor->cellphone ?? '';
          $tel_display = preg_replace('/^46/', '0', $tel);
          $tel_display = preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '$1-$2 $3 $4', $tel_display);
          $tel_href = '+' . ltrim($tel, '+');
        @endphp
        <div class="maklare-kort objekt-maklare-kort">
          <div class="maklare-kort-bild">
            @if($maklare_bild)
              <img src="{{ $maklare_bild }}" alt="{{ ($first_realtor->firstname ?? '') . ' ' . ($first_realtor->lastname ?? '') }}" style="width:100%;height:100%;object-fit:cover;object-position:top;">
            @else
              <div style="width:100%;height:100%;background:var(--bg-warm);"></div>
            @endif
          </div>
          <div class="maklare-kort-info">
            <h3>{{ ($first_realtor->firstname ?? '') . ' ' . ($first_realtor->lastname ?? '') }}</h3>
            @if(!empty($first_realtor->title))
              <p class="maklare-kort-titel">{{ $first_realtor->title }}</p>
            @endif
            @if($tel)
              <p><a href="tel:{{ $tel_href }}">{{ $tel_display }}</a></p>
            @endif
            @if(!empty($first_realtor->email))
              <p><a href="mailto:{{ $first_realtor->email }}">{{ $first_realtor->email }}</a></p>
            @endif
          </div>
        </div>
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

{{-- Intresseanmälan modal --}}
<div id="intresse-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(10,18,35,0.85);align-items:center;justify-content:center;">
  <div style="background:var(--bg-light);max-width:520px;width:90%;padding:48px;position:relative;border:1px solid var(--bg-warm);">
    <button onclick="document.getElementById('intresse-modal').style.display='none'"
            style="position:absolute;top:20px;right:24px;background:none;border:none;color:var(--text-dark);font-size:28px;cursor:pointer;line-height:1;">&times;</button>

    <span class="sektion-eyebrow-label">Intresseanmälan</span>
    <h2 style="font-family:var(--font-heading);font-size:32px;font-weight:300;color:var(--text-dark);margin:12px 0 8px;letter-spacing:-0.01em;">{{ $full_address }}</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:var(--text-mid);line-height:1.7;margin-bottom:32px;">Lämna dina uppgifter så kontaktar vi dig.</p>

    <form method="POST" action="{{ home_url('/kontakt') }}">
      @php echo wp_nonce_field('intresse_form', 'intresse_nonce', true, false); @endphp
      <input type="hidden" name="form_type" value="intresse">
      <input type="hidden" name="intresse_objekt" value="{{ $full_address }}">

      <div style="display:flex;flex-direction:column;gap:16px;">
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-mid);margin-bottom:8px;">Namn</label>
          <input type="text" name="intresse_namn" required placeholder="Ditt namn"
                 style="width:100%;padding:12px 16px;background:#fff;border:1px solid var(--bg-warm);color:var(--text-dark);font-family:var(--font-body);font-size:14px;outline:none;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-mid);margin-bottom:8px;">E-post</label>
          <input type="email" name="intresse_email" required placeholder="din@email.se"
                 style="width:100%;padding:12px 16px;background:#fff;border:1px solid var(--bg-warm);color:var(--text-dark);font-family:var(--font-body);font-size:14px;outline:none;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-mid);margin-bottom:8px;">Telefon</label>
          <input type="tel" name="intresse_tel" placeholder="070-123 45 67"
                 style="width:100%;padding:12px 16px;background:#fff;border:1px solid var(--bg-warm);color:var(--text-dark);font-family:var(--font-body);font-size:14px;outline:none;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--text-mid);margin-bottom:8px;">Meddelande</label>
          <textarea name="intresse_meddelande" rows="4" placeholder="Berätta gärna lite om dig själv..."
                    style="width:100%;padding:12px 16px;background:#fff;border:1px solid var(--bg-warm);color:var(--text-dark);font-family:var(--font-body);font-size:14px;outline:none;resize:vertical;box-sizing:border-box;"></textarea>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;padding:14px;text-align:center;cursor:pointer;">
          Skicka intresseanmälan
        </button>
      </div>
    </form>
  </div>
</div>
