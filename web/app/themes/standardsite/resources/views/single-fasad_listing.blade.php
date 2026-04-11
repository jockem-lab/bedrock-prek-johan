@extends('layouts.app')

@section('content')
@if($listing)

  {{-- Hero-bild --}}
  @if(!empty($listing->images[0]))
    <div class="objekt-detalj-hero">
      <img src="{{ $listing->images[0] }}" alt="{{ $listing->address }}">
      @if($listing->status)
        <div class="objekt-detalj-status objekt-status--{{ $listing->status }}">
          {{ ucfirst($listing->status) }}
        </div>
      @endif
    </div>
  @endif

  {{-- Faktarad --}}
  <div class="objekt-detalj-faktarad">
    <div class="objekt-detalj-faktarad-inner">
      @if($listing->type)
        <div class="faktarad-item">
          <span class="faktarad-label">Typ</span>
          <span class="faktarad-värde">{{ $listing->type }}</span>
        </div>
      @endif
      @if($listing->rooms)
        <div class="faktarad-item">
          <span class="faktarad-label">Rum</span>
          <span class="faktarad-värde">{{ $listing->rooms }}</span>
        </div>
      @endif
      @if($listing->livingArea)
        <div class="faktarad-item">
          <span class="faktarad-label">Boarea</span>
          <span class="faktarad-värde">{{ $listing->livingArea }}</span>
        </div>
      @endif
      @if($listing->price)
        <div class="faktarad-item">
          <span class="faktarad-label">Pris</span>
          <span class="faktarad-värde">{{ $listing->price }}</span>
        </div>
      @endif
      @if($listing->fee)
        <div class="faktarad-item">
          <span class="faktarad-label">Avgift</span>
          <span class="faktarad-värde">{{ $listing->fee }}</span>
        </div>
      @endif
      @if($listing->builtYear)
        <div class="faktarad-item">
          <span class="faktarad-label">Byggnadsår</span>
          <span class="faktarad-värde">{{ $listing->builtYear }}</span>
        </div>
      @endif
    </div>
  </div>

  {{-- Innehåll --}}
  <div class="objekt-detalj-inner">
    <div class="objekt-detalj-content">
      <h1>{{ $listing->address }}@if($listing->city), {{ $listing->city }}@endif</h1>

      {{-- Accordion --}}
      <div class="objekt-accordion">
        @if($listing->salesText)
          <div class="accordion-item open">
            <button class="accordion-trigger">
              Beskrivning
              <span class="accordion-icon">+</span>
            </button>
            <div class="accordion-content">
              <p class="objekt-detalj-beskrivning">{!! nl2br(e($listing->salesText)) !!}</p>
            </div>
          </div>
        @endif
      </div>

      {{-- Bildgalleri --}}
      @if(count($listing->images) > 1)
        <div class="objekt-galleri">
          <div class="objekt-galleri-grid">
            @foreach($listing->images as $i => $img)
              <div class="objekt-galleri-item {{ $i === 0 ? 'objekt-galleri-item--stor' : '' }}">
                <a href="{{ $img }}" class="lightbox-trigger" data-index="{{ $i }}">
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
        <h3>Intresserad?</h3>
        <p>Kontakta oss för mer information om {{ $listing->address }}.</p>
        <a href="{{ home_url('/kontakt') }}" class="btn-primary">Kontakta oss</a>
      </div>
    </div>
  </div>

@else
  <div style="padding:80px 24px;text-align:center;">
    <h1>Objektet hittades inte</h1>
    <a href="{{ home_url('/objekt') }}" class="btn-primary" style="margin-top:24px;display:inline-block;">Se alla objekt</a>
  </div>
@endif
@endsection
