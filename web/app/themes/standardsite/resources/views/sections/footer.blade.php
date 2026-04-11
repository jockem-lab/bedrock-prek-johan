<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-col">
      <div class="footer-logo">
        @if($logo['footer'])
          <img src="{{ $logo['footer']['url'] }}" alt="{{ $siteName }}" style="max-height:48px;width:auto;filter:brightness(0) invert(1);">
        @else
          {{ $siteName }}
        @endif
      </div>
      <p class="footer-tagline">{{ $footerText }}</p>
      @if($officesInfo)
        @foreach($officesInfo as $office)
          <p style="margin-top:8px;color:rgba(255,255,255,0.6);font-size:13px;">
            {{ implode(', ', $office) }}
          </p>
        @endforeach
      @endif
    </div>

    <div class="footer-col">
      <h4>Navigation</h4>
      <ul>
        <li><a href="{{ home_url('/') }}">Hem</a></li>
        <li><a href="{{ home_url('/objekt') }}">Till salu</a></li>
        <li><a href="{{ home_url('/om-oss') }}">Om oss</a></li>
        <li><a href="{{ home_url('/kontakt') }}">Kontakt</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Kontakt</h4>
      <p>Kontakta oss för mer information om våra tjänster.</p>
    </div>
  </div>

  <div class="footer-bottom">
    © {{ date('Y') }} {{ $siteName }}. Alla rättigheter förbehållna.
  </div>
</footer>
