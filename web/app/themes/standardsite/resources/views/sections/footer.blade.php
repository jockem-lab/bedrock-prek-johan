<footer class="fdr-footer">
  <div class="fdr-footer-inner">

    {{-- Vänster: Logo + adress --}}
    <div class="fdr-footer-col fdr-footer-brand">
      <a href="{{ home_url('/') }}" class="fdr-footer-logo">ETT MÄKLERI</a>
      <div class="fdr-footer-adress">
        <p>Grev Turegatan 50</p>
        <p>114 38 Stockholm</p>
        <a href="mailto:info@ettmakleri.se">info@ettmakleri.se</a>
      </div>
      <div class="fdr-footer-social">
        <a href="https://www.facebook.com/franzondurietz" target="_blank">Facebook</a>
        <a href="https://www.instagram.com/franzondurietz" target="_blank">Instagram</a>
      </div>
    </div>

    {{-- Mitten: Navigation --}}
    <div class="fdr-footer-col fdr-footer-nav">
      <h4>Navigation</h4>
      <ul>
        <li><a href="{{ home_url('/objekt') }}">Lägenheter</a></li>
        <li><a href="{{ home_url('/objekt') }}">Hus</a></li>
        <li><a href="{{ home_url('/underhand') }}">Underhand</a></li>
        <li><a href="{{ home_url('/kontakt') }}">Anlita oss</a></li>
        <li><a href="{{ home_url('/kontakt') }}">Spekulantregister</a></li>
        <li><a href="{{ home_url('/om-oss') }}">Om oss</a></li>
      </ul>
    </div>

    {{-- Höger: Kontakt --}}
    <div class="fdr-footer-col fdr-footer-kontakt">
      <h4>Kontakt</h4>
      @if($kontaktPhone)
        <a href="tel:{{ $kontaktPhone }}">{{ $kontaktPhone }}</a>
      @else
        <a href="tel:0704455180">070-445 51 80</a>
      @endif
      @if($kontaktEmail)
        <a href="mailto:{{ $kontaktEmail }}">{{ $kontaktEmail }}</a>
      @else
        <a href="mailto:info@ettmakleri.se">info@ettmakleri.se</a>
      @endif
    </div>

  </div>

  <div class="fdr-footer-bottom">
    <span>© {{ date('Y') }} Ett Mäkleri AB</span>
  </div>
</footer>
