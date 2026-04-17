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
        <div style="margin-top:20px;display:flex;gap:10px;">
          @if($instagram)
            <a href="{{ $instagram }}" aria-label="Instagram" class="social-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/>
              </svg>
              <span>Instagram</span>
            </a>
          @endif
          @if($facebook)
            <a href="{{ $facebook }}" aria-label="Facebook" class="social-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
              </svg>
              <span>Facebook</span>
            </a>
          @endif
          @if($linkedin)
            <a href="{{ $linkedin }}" aria-label="LinkedIn" class="social-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>
              </svg>
              <span>LinkedIn</span>
            </a>
          @endif
        </div>
      @endif
    </div>
  </div>

  <div class="footer-bottom">
    © {{ date('Y') }} {{ $siteName }}{{ $orgNr ? ' · Org.nr ' . $orgNr : '' }}. Alla rättigheter förbehållna.
  </div>
</footer>
