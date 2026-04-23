<header class="fdr-header" id="site-header">
  <div class="fdr-header-inner">
    <a href="{{ home_url('/') }}" class="fdr-logo">ETT MÄKLERI</a>
    <button class="fdr-meny-btn" id="fdr-menu-btn" aria-label="Öppna meny">MENY</button>
  </div>
</header>

<div class="fdr-menu-overlay" id="fdr-menu-overlay">
  <div class="fdr-menu-panel">
    <div class="fdr-menu-panel-header">
      <a href="{{ home_url('/') }}" class="fdr-logo" style="color:#000;">ETT MÄKLERI</a>
      <button class="fdr-menu-close" id="fdr-menu-close">✕</button>
    </div>
    <nav class="fdr-menu-nav">
      <a href="{{ home_url('/objekt') }}">Lägenheter</a>
      <a href="{{ home_url('/objekt') }}">Hus</a>
      <a href="{{ home_url('/underhand') }}">Underhand</a>
      <a href="{{ home_url('/kontakt') }}">Anlita oss</a>
      <a href="{{ home_url('/om-oss') }}">Om oss</a>
    </nav>
    <div class="fdr-menu-footer">
      <p>Grev Turegatan 50, 114 38 Stockholm</p>
      <a href="mailto:info@ettmakleri.se">info@ettmakleri.se</a>
    </div>
  </div>
</div>
