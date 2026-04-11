@if($hero && !empty($hero['slides']))
  <div class="hero hero-slideshow" style="min-height:90vh;position:relative;overflow:hidden;">
    <div class="hero-slides">
      @foreach($hero['slides'] as $i => $slide)
        <div class="hero-slide {{ $i === 0 ? 'active' : '' }}"
             style="background-image:url('{{ $slide['image']['src'] }}')">
        </div>
      @endforeach
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-inner">
      @if(!empty($hero['slides'][0]['title']))
        <span class="hero-eyebrow">Välkommen</span>
        <h1>{{ $hero['slides'][0]['title'] }}</h1>
      @else
        <span class="hero-eyebrow">Mäklare i Linköping sedan 2001</span>
        <h1>Vi hittar rätt hem för dig</h1>
        <p class="hero-sub">Erfarna mäklare med djup lokalkännedom. Vi guidar dig genom hela processen – från första visning till nyckelöverlämning.</p>
        <div class="hero-btns">
          <a href="{{ home_url('/objekt') }}" class="btn-primary">Se objekt till salu</a>
          <a href="{{ home_url('/kontakt') }}" class="btn-secondary">Kontakta oss</a>
        </div>
      @endif
    </div>
  </div>

  <script>
    (function() {
      const slides = document.querySelectorAll('.hero-slide');
      if (slides.length <= 1) return;
      let current = 0;
      setInterval(function() {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
      }, 5000);
    })();
  </script>
@endif
