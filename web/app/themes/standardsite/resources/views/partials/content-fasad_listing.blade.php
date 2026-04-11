@php
$post_id = get_the_ID();
$address = get_the_title($post_id);

$loc = @unserialize(get_post_meta($post_id, '_fasad_location', true));
if ($loc && !empty($loc->address)) $address = $loc->address;

$eco = @unserialize(get_post_meta($post_id, '_fasad_economy', true));
$price = '';
if ($eco && !empty($eco->price->primary->amount))
    $price = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';

$imgs = @unserialize(get_post_meta($post_id, '_fasad_images', true));
$images = [];
if (is_array($imgs)) foreach ($imgs as $img) if (!empty($img->path)) $images[] = $img->path;

$sz = @unserialize(get_post_meta($post_id, '_fasad_size', true));
$area = $sz && !empty($sz->livingArea) ? $sz->livingArea . ' kvm' : '';
$rooms = $sz && !empty($sz->rooms) ? $sz->rooms . ' rum' : '';

$tp = @unserialize(get_post_meta($post_id, '_fasad_descriptionType', true));
$type = $tp && !empty($tp->alias) ? $tp->alias : '';

$salesText = get_post_meta($post_id, '_fasad_salesText', true);
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
