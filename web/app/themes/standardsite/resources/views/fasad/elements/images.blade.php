@if($images)
  <div id="bilder" class="images col-span-12 mb-4">
    <h3>Alla bilder</h3>
    <div class="images-container col-span-12 grid grid-cols-12 gap-5">
      @foreach($images['images'] as $image)
        @include('fasad.elements.image')
      @endforeach
      @if($images['hiddenImages'] > 0)
        <div class="col-span-12 text-center flex justify-center">
          <a href="#" class="showhidden button theme-custom-background theme-custom-color" data-container=".images-container">Alla bilder</a>
        </div>
      @endif
    </div>
  </div>
@endif
