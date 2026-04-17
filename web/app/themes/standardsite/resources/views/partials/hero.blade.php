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
        <h1 style="font-family:var(--font-heading);font-size:clamp(48px,6vw,96px);font-weight:300;letter-spacing:-0.02em;line-height:1.0;color:#fff;text-transform:uppercase;">Med hjärtat<br><em style="font-style:italic;font-weight:300;">i varje affär</em></h1>
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
