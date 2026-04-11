@if(!$listing->sold)
  <div class="col-span-12 mb-8">
    <div class="flex formlinks">
      @if($listing->anyBookable)
        <a href="#visningsanmalan" data-cy="go-to-showing-form" class="theme-color-primary flex justify-center items-center uppercase font-bold anchor-link">visningsanmälan ▼</a>
      @endif
      <a href="#intresseanmalan" data-cy="go-to-interest-form" class="theme-color-primary flex justify-center items-center uppercase font-bold anchor-link">intresseanmälan ▼</a>
    </div>
  </div>
@endif