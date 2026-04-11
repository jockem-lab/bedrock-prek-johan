@if($listing)
  <div class="container">
    <div class="row">
      @include('fasad.elements.salestext')
      <div class="col-span-12 mb-4 row">
        @include('fasad.elements.shortfacts')
        @include('fasad.elements.realtors')
      </div>
      @include('fasad.elements.videos')
      @include('fasad.elements.images')
      @include('fasad.elements.floorplans')
      @include('fasad.elements.objectfacts')
      @include('fasad.elements.map')
      @include('fasad.elements.forms')
    </div>
  </div>
@endif
