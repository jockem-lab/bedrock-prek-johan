@if($floorplans)
  <div id="planritning" class="images col-span-12 mb-4">
    <h3>Planritning</h3>
    <div class="images col-span-12 grid grid-cols-12 gap-5">
      @foreach($floorplans['images'] as $image)
        @include('fasad.elements.image')
      @endforeach
    </div>
  </div>
@endif
