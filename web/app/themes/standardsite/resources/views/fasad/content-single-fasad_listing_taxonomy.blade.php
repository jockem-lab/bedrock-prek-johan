<div class="wrapper mx-auto">
  <div class="container">
    <div class="col-span-12 flex flex-col">
      @if($heading)
        <h3 class="text-center color-primary">{{ $heading }}</h3>
      @endif
      @if($terms)
        @foreach($terms as $term)
          @if($isListingsTaxonomy)
            <span class="text-center text-xl mb-4"><a class="button button-primary inline" href="{{ get_term_link($term) }}">{{ $term->name }}</a></span>
          @endif
          @if(!empty($content[$term->slug]))
            <div class="text-center wysiwyg">
              {!! $content[$term->slug] !!}
            </div>
          @endif
          @if(!empty($listings[$term->slug]['listings']))
            @if($titles[$term->slug][$listings[$term->slug]['listingsType']])
              <div class="col-span-12 flex flex-col wysiwyg">
                <p class="text-center color-primary mb-4 text-xl">{{ $titles[$term->slug][$listings[$term->slug]['listingsType']] }}</p>
              </div>
            @endif
            <div class="vue-component">
              <div class="row !gap-x-5 !gap-y-9">
                @foreach($listings[$term->slug]['listings'] as $listing)
                  <listingcard :listing="{{ json_encode($listing) }}"></listingcard>
                @endforeach
              </div>
            </div>
          @else
            <div class="col-span-12 wysiwyg text-center pb-5">
              @if($listingsPage)
                <p class="text-center color-primary mb-4 text-xl">Just nu har vi inga objekt här. <br>Se våra aktuella objekt under</p>
                <a class="button theme-custom-background theme-custom-color inline" href="{{ $listingsPage['url'] }}">{{ $listingsPage['title'] }}</a>
              @else
                <a class="button theme-custom-background theme-custom-color inline" href="{{ home_url() }}">Startsidan</a>
              @endif
            </div>
          @endif
        @endforeach
      @endif
    </div>
  </div>
</div>
