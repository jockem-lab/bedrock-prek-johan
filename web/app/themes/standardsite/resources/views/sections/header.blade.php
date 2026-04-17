<header class="theme-custom-header-background">
  <div class="header-inner">
    <nav class="nav-left">
      <a href="{{ home_url('/objekt') }}" id="dd-trigger">Våra hem</a>
      <a href="{{ home_url('/underhand') }}">Underhand</a>
      <a href="{{ home_url('/salda') }}">Sålda</a>
    </nav>

    <div class="site-branding">
      <a href="{{ home_url('/') }}" class="site-name" style="display:flex;flex-direction:column;align-items:center;gap:2px;text-decoration:none;">
        <span style="font-size:9px;letter-spacing:0.2em;color:rgba(255,255,255,0.5);font-family:var(--font-body);font-weight:400;text-transform:uppercase;">Östermalm sedan 2001</span>
        <span style="font-size:15px;letter-spacing:0.22em;color:#fff;font-family:var(--font-body);font-weight:500;text-transform:uppercase;">Oscars</span>
        <span style="font-size:8px;letter-spacing:0.18em;color:rgba(255,255,255,0.5);font-family:var(--font-body);font-weight:400;text-transform:uppercase;">Fastighetsmäkleri</span>
      </a>
    </div>

    <nav class="nav-right">
      @if(has_nav_menu('secondary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'menu_class' => 'nav-right-items', 'container' => false, 'echo' => false, 'depth' => 1]) !!}
      @else
        <a href="{{ home_url('/kontakt') }}">Sälj bostad</a>
        <a href="{{ home_url('/om-oss') }}">Om oss</a>
        <a href="{{ home_url('/journal') }}">Journal</a>
      @endif
    </nav>

    <button class="menu-toggle" id="menu-toggle" aria-label="Meny">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

{{-- Dropdown panel -- utanför header för korrekt positionering --}}
<div id="dd-panel" style="display:none;position:fixed;top:72px;left:50%;transform:translateX(-50%);width:580px;background:#fff;border:0.5px solid #e8e2da;box-shadow:0 8px 40px rgba(0,0,0,0.10);z-index:9999;">
  <div style="display:grid;grid-template-columns:200px 1fr;">
    <div style="padding:28px 24px;border-right:0.5px solid #e8e2da;">
      <a href="{{ home_url('/kommande') }}" style="display:block;padding:12px 0;border-bottom:0.5px solid #e8e2da;text-decoration:none;">
        <span style="display:block;font-family:'DM Sans',sans-serif;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:#1c1c1e;">Kommande</span>
        <span style="display:block;font-size:12px;color:#b8a99a;margin-top:2px;">Objekt på väg ut</span>
      </a>
      <a href="{{ home_url('/objekt') }}" style="display:block;padding:12px 0;border-bottom:0.5px solid #e8e2da;text-decoration:none;">
        <span style="display:block;font-family:'DM Sans',sans-serif;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:#1c1c1e;">Till salu</span>
        <span style="display:block;font-size:12px;color:#b8a99a;margin-top:2px;">Alla aktuella objekt</span>
      </a>
      <a href="{{ home_url('/underhand') }}" style="display:block;padding:12px 0;border-bottom:0.5px solid #e8e2da;text-decoration:none;">
        <span style="display:block;font-family:'DM Sans',sans-serif;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:#1c1c1e;">Underhand</span>
        <span style="display:block;font-size:12px;color:#b8a99a;margin-top:2px;">Exklusiva objekt</span>
      </a>
      <a href="{{ home_url('/salda') }}" style="display:block;padding:12px 0;text-decoration:none;">
        <span style="display:block;font-family:'DM Sans',sans-serif;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:#1c1c1e;">Sålda</span>
        <span style="display:block;font-size:12px;color:#b8a99a;margin-top:2px;">Tidigare försäljningar</span>
      </a>
    </div>
    <div style="padding:28px 24px;display:flex;flex-direction:column;gap:16px;">
      @php
        $featured = new WP_Query([
          'post_type'      => 'fasad_listing',
          'posts_per_page' => 2,
          'meta_query'     => [
            ['key' => '_fasad_published', 'value' => '1'],
            ['key' => '_fasad_sold',      'value' => '0'],
          ],
        ]);
        $featured_posts = $featured->posts ?? [];
      @endphp
      @foreach($featured_posts as $fp)
        @php
          $images = get_post_meta($fp->ID, '_fasad_images', true);
          $imgs   = maybe_unserialize($images);
          $img    = '';
          if (is_array($imgs) && !empty($imgs)) {
            foreach (($imgs[0]->variants ?? []) as $v) {
              if (in_array($v->type, ['large','highres'])) { $img = $v->path ?? ''; break; }
            }
          }
          $addr = get_post_meta($fp->ID, '_fasad_salesTitle', true);
          $slug = $fp->post_name;
        @endphp
        <a href="{{ home_url('/objekt/' . $slug) }}" style="text-decoration:none;">
          <div style="width:100%;height:110px;background:url('{{ $img }}') center/cover no-repeat #e8e2da;"></div>
          <span style="display:block;margin-top:8px;font-family:'DM Sans',sans-serif;font-size:11px;color:#1c1c1e;letter-spacing:0.04em;">{{ $addr }}</span>
        </a>
      @endforeach
    </div>
  </div>
</div>

<div class="mobile-menu-overlay" id="mobile-menu">
  <nav class="mobile-menu-nav">
    <a href="{{ home_url('/objekt') }}">Våra hem</a>
    <a href="{{ home_url('/underhand') }}">Underhand</a>
    <a href="{{ home_url('/salda') }}">Sålda</a>
    <a href="{{ home_url('/kontakt') }}">Sälj bostad</a>
    <a href="{{ home_url('/om-oss') }}">Om oss</a>
    <a href="{{ home_url('/journal') }}">Journal</a>
  </nav>
  <button class="menu-toggle is-active" id="menu-close" style="position:absolute;top:24px;right:24px;">
    <span></span>
    <span></span>
    <span></span>
  </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var trigger = document.getElementById('dd-trigger');
  var panel   = document.getElementById('dd-panel');
  if (!trigger || !panel) return;

  function position() {
    var r = trigger.getBoundingClientRect();
    var panelW = 580;
    var left = r.left + (r.width / 2) - (panelW / 2);
    left = Math.max(16, Math.min(left, window.innerWidth - panelW - 16));
    panel.style.left = left + 'px';
    panel.style.transform = 'none';
    panel.style.top = '72px';
  }

  trigger.addEventListener('mouseenter', function() { position(); panel.style.display = 'block'; });
  trigger.addEventListener('mouseleave', function(e) {
    if (!panel.contains(e.relatedTarget)) panel.style.display = 'none';
  });
  panel.addEventListener('mouseleave', function(e) {
    if (e.relatedTarget !== trigger) panel.style.display = 'none';
  });
});
</script>

@include('partials.hero')
