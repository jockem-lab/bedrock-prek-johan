@extends('layouts.app')
@section('content')

{{-- Hero --}}
<div class="kontakt-hero" style="height:380px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
  <div class="kontakt-hero-slide active" style="background-image:url('{{ content_url('uploads/oscars-hero1.jpg') }}')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner" style="position:relative;z-index:1;text-align:center;">
    <h1 class="undersida-rubrik">Journal</h1>
  </div>
</div>

{{-- Masonry grid --}}
<section style="padding:64px 40px 80px;background:var(--bg-light);">
  <div style="max-width:1200px;margin:0 auto;">
    <div class="sektion-header" style="margin-bottom:48px;">
      <span class="sektion-eyebrow-label">Oscars Mäkleri</span>
      <h2 class="sektion-rubrik">Journal</h2>
    </div>

    @if(!empty($artiklar))
      <div class="journal-grid" id="journal-grid">
        @foreach($artiklar as $i => $artikel)
          <a href="{{ home_url('/journal/' . $artikel->slug) }}"
             class="journal-kort {{ $i === 0 ? 'journal-kort--featured' : '' }}">
            <div class="journal-bild">
              @if($artikel->hero_typ === 'video' && $artikel->hero_video)
                <div class="journal-video-badge">▶</div>
              @endif
              @if($artikel->hero_bild)
                <div class="journal-bild-inner" style="background-image:url('{{ $artikel->hero_bild }}')"></div>
              @else
                <div class="journal-bild-inner" style="background:#243558;"></div>
              @endif
            </div>
            <div class="journal-info">
              @if($artikel->kategori)
                <span class="journal-kategori">{{ $artikel->kategori }}</span>
              @endif
              <h3 class="journal-titel">{{ $artikel->titel }}</h3>
              @if($artikel->lasttid)
                <span class="journal-meta">{{ $artikel->lasttid }} minuter</span>
              @endif
            </div>
          </a>
        @endforeach
      </div>

      @if(count($artiklar) > 3)
        <div id="journal-visa-fler-wrap" style="text-align:center;margin-top:48px;">
          <button class="btn-primary" id="journal-visa-fler">
            Äldre artiklar ({{ count($artiklar) - 3 }} till)
          </button>
        </div>
      @endif

      <script>
      document.addEventListener('DOMContentLoaded', function() {
        var kort = document.querySelectorAll('#journal-grid .journal-kort');
        kort.forEach(function(k, i) {
          if (i >= 3) k.style.display = 'none';
        });
        var btn = document.getElementById('journal-visa-fler');
        if (btn) {
          btn.addEventListener('click', function() {
            kort.forEach(function(k) { k.style.display = 'block'; });
            document.getElementById('journal-visa-fler-wrap').style.display = 'none';
          });
        }
      });
      </script>

    @else
      <p style="color:var(--text-mid);text-align:center;padding:48px 0;">
        Inga artiklar publicerade ännu.
      </p>
    @endif
  </div>
</section>

@endsection
