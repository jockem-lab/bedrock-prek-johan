@if(has_nav_menu('primary_navigation') && $menuType === 'horizontal')
  <div id="horizontal-navigation-wrapper">
    <div id="navigation" class="horizontal-navigation">
      <nav class="horizontal-navigation-primary__menu font-bold uppercase">
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'link_after' => '<span class="chevron down"></span>']) !!}
      </nav>
    </div>
  </div>
@endif