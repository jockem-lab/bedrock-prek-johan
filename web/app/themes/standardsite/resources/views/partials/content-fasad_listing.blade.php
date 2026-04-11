@php
$post_id = get_the_ID();
$address = get_the_title($post_id);

$loc_raw = get_post_meta($post_id, '_fasad_location', true);
$loc = @unserialize(is_string($loc_raw) ? $loc_raw : '');
if (is_string($loc)) $loc = @unserialize($loc);
if ($loc && !empty($loc->address)) $address = $loc->address;

$eco_raw = get_post_meta($post_id, '_fasad_economy', true);
$eco = @unserialize(is_string($eco_raw) ? $eco_raw : '');
if (is_string($eco)) $eco = @unserialize($eco);
$price = '';
if ($eco && !empty($eco->price->primary->amount))
    $price = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';

// WordPress returnerar dubbelt serialiserad data - deserialisera två gånger
$imgs_raw = get_post_meta($post_id, '_fasad_images', true);
$imgs = is_string($imgs_raw) ? @unserialize($imgs_raw) : $imgs_raw;
if (is_string($imgs)) $imgs = @unserialize($imgs);
$images = [];
if (is_array($imgs)) {
    foreach ($imgs as $img) {
        // variants är en array med type/path-objekt
        if (!empty($img->variants) && is_array($img->variants)) {
            foreach ($img->variants as $variant) {
                if (($variant->type ?? '') === 'highres' && !empty($variant->path)) {
                    $images[] = $variant->path;
                    break;
                }
            }
            // Fallback till large om highres saknas
            if (empty($images) || count($images) < count($imgs)) {
                foreach ($img->variants as $variant) {
                    if (($variant->type ?? '') === 'large' && !empty($variant->path)) {
                        $images[] = $variant->path;
                        break;
                    }
                }
            }
        } elseif (!empty($img->path)) {
            $images[] = $img->path;
        }
    }
}

$sz_raw = get_post_meta($post_id, '_fasad_size', true);
$sz = @unserialize(is_string($sz_raw) ? $sz_raw : '');
if (is_string($sz)) $sz = @unserialize($sz);
$area = $sz && !empty($sz->livingArea) ? $sz->livingArea . ' kvm' : '';
$rooms = $sz && !empty($sz->rooms) ? $sz->rooms . ' rum' : '';

$tp_raw = get_post_meta($post_id, '_fasad_descriptionType', true);
$tp = @unserialize(is_string($tp_raw) ? $tp_raw : '');
if (is_string($tp)) $tp = @unserialize($tp);
$type = $tp && !empty($tp->alias) ? $tp->alias : '';

$salesText = get_post_meta($post_id, '_fasad_salesText', true);
@endphp

{{-- Debug --}}
@php
$raw_test = get_post_meta($post_id, '_fasad_images', true);
$s1 = @unserialize($raw_test);
$s2 = is_string($s1) ? @unserialize($s1) : $s1;
@endphp
{{-- Hero-bild --}}
@if(!empty($images[0]))
  <div class="objekt-detalj-hero">
    <img src="{{ $images[0] }}" alt="{{ $address }}">
  </div>
@endif

{{-- Faktarad --}}
<div class="objekt-detalj-faktarad">
  <div class="objekt-detalj-faktarad-inner">
    @if($type)<div class="faktarad-item"><span class="faktarad-label">Typ</span><span class="faktarad-värde">{{ $type }}</span></div>@endif
    @if($rooms)<div class="faktarad-item"><span class="faktarad-label">Rum</span><span class="faktarad-värde">{{ $rooms }}</span></div>@endif
    @if($area)<div class="faktarad-item"><span class="faktarad-label">Boarea</span><span class="faktarad-värde">{{ $area }}</span></div>@endif
    @if($price)<div class="faktarad-item"><span class="faktarad-label">Pris</span><span class="faktarad-värde">{{ $price }}</span></div>@endif
  </div>
</div>

{{-- Innehåll --}}
<div class="objekt-detalj-inner">
  <div class="objekt-detalj-content">
    <h1>{{ $address }}</h1>
    @if($salesText)
      <div class="objekt-accordion">
        <div class="accordion-item open">
          <button class="accordion-trigger">Beskrivning <span class="accordion-icon">+</span></button>
          <div class="accordion-content">
            <p class="objekt-detalj-beskrivning">{!! nl2br(e($salesText)) !!}</p>
          </div>
        </div>
      </div>
    @endif
  </div>
  <div class="objekt-detalj-sidebar">
    <div class="objekt-detalj-kontakt">
      <h3>Intresserad?</h3>
      <p>Kontakta oss om {{ $address }}.</p>
      <a href="{{ home_url('/kontakt') }}" class="btn-primary">Kontakta oss</a>
    </div>
  </div>
</div>
