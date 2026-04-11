@if(has_nav_menu('primary_navigation'))
  <div id="burger-navigation-wrapper">
    <a class="burger-navigation-trigger {{ $menuType === 'horizontal' ? '!hidden' : '' }}">
      <span></span>
      <span></span>
      <span></span>
    </a>
    <div id="navigation" class="burger-navigation theme-custom-header-background">
      <div class="burger-navigation-primary__header">
      </div>
      <nav class="burger-navigation-primary__menu font-bold uppercase">
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
      </nav>
      <div class="burger-navigation-primary__footer">
        @if($logo['header'])
          <img src="{{ $logo['header']['url'] }}" alt="{{ $siteName }}" class="burger-navigation-primary__footer__logo">
        @endif
      </div>
    </div>
  </div>
@endif
