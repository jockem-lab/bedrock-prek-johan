<header class="theme-custom-header-background">
  <div class="header-inner">
    <nav class="nav-left">
      @if(has_nav_menu('primary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav-left-items', 'container' => false, 'echo' => false, 'depth' => 1]) !!}
      @else
        <a href="{{ home_url('/objekt') }}">Till salu</a>
        <a href="{{ home_url('/om-oss') }}">Om oss</a>
      @endif
    </nav>

    <div class="site-branding">
      <a href="{{ home_url('/') }}" class="site-name">
        @if($logo['header'])
          <img src="{{ $logo['header']['url'] }}" alt="{{ $siteName }}" style="{{ $logo['header']['style'] ?? '' }}">
        @else
          {{ $siteName }}
        @endif
      </a>
    </div>

    <nav class="nav-right">
      @if(has_nav_menu('secondary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'menu_class' => 'nav-right-items', 'container' => false, 'echo' => false, 'depth' => 1]) !!}
      @else
        <a href="{{ home_url('/kontakt') }}">Kontakt</a>
      @endif
    </nav>

    <button class="menu-toggle" id="menu-toggle" aria-label="Meny">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

<div class="mobile-menu-overlay" id="mobile-menu">
  <nav class="mobile-menu-nav">
    <a href="{{ home_url('/objekt') }}">Till salu</a>
    <a href="{{ home_url('/om-oss') }}">Om oss</a>
    <a href="{{ home_url('/kontakt') }}">Kontakt</a>
  </nav>
  <button class="menu-toggle is-active" id="menu-close" style="position:absolute;top:24px;right:24px;">
    <span></span>
    <span></span>
    <span></span>
  </button>
</div>

@include('partials.hero')
