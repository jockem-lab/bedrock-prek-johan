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
      <p><a href="mailto:info@prek.se">info@prek.se</a></p>
      <p><a href="tel:+46131234567">013-12 34 56</a></p>
      <p style="margin-top:12px;font-size:12px;opacity:0.6;">
        Mån–Fre: 09–17<br>
        Lör: 10–14
      </p>
      <div style="margin-top:16px;display:flex;gap:12px;">
        <a href="#" aria-label="Instagram" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;">Instagram</a>
        <a href="#" aria-label="Facebook" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;">Facebook</a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    © {{ date('Y') }} {{ $siteName }}. Alla rättigheter förbehållna.
  </div>
</footer>
