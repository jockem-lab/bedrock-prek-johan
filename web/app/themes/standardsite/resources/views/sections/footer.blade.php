<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-col">
      <div class="footer-logo">
        @if(!empty($logo['footer']['url']))
          <img src="{{ $logo['footer']['url'] }}" alt="{{ $siteName }}" style="max-height:48px;width:auto;filter:brightness(0) invert(1);">
        @else
          {{ $siteName }}
        @endif
      </div>
      @if($footerText)
        <p class="footer-tagline">{{ $footerText }}</p>
      @endif
      @if($footerExtra)
        <p style="margin-top:8px;color:rgba(255,255,255,0.6);font-size:13px;">{{ $footerExtra }}</p>
      @endif
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
      @if($kontaktEmail)
        <p><a href="mailto:{{ $kontaktEmail }}">{{ $kontaktEmail }}</a></p>
      @endif
      @if($kontaktPhone)
        <p><a href="tel:{{ $kontaktPhone }}">{{ $kontaktPhone }}</a></p>
      @endif
      @if($kontaktAddress || $kontaktCity)
        <p style="margin-top:8px;color:rgba(255,255,255,0.6);font-size:13px;">
          {{ $kontaktAddress }}{{ $kontaktAddress && $kontaktCity ? ', ' : '' }}{{ $kontaktCity }}
        </p>
      @endif
      @if($openingHours)
        <p style="margin-top:12px;font-size:12px;opacity:0.6;">{!! nl2br(e($openingHours)) !!}</p>
      @endif
      @if($instagram || $facebook || $linkedin)
        <div style="margin-top:16px;display:flex;gap:12px;">
          @if($instagram)
            <a href="{{ $instagram }}" aria-label="Instagram" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;">Instagram</a>
          @endif
          @if($facebook)
            <a href="{{ $facebook }}" aria-label="Facebook" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;">Facebook</a>
          @endif
          @if($linkedin)
            <a href="{{ $linkedin }}" aria-label="LinkedIn" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;">LinkedIn</a>
          @endif
        </div>
      @endif
    </div>
  </div>

  <div class="footer-bottom">
    © {{ date('Y') }} {{ $siteName }}{{ $orgNr ? ' · Org.nr ' . $orgNr : '' }}. Alla rättigheter förbehållna.
  </div>
</footer>
