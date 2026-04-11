@if($videos)
  <div id="filmer" class="videos col-span-12 mb-4">
    <h3>{{ $videos['heading'] }}</h3>
    <div class="images-container col-span-12 grid grid-cols-12 gap-5">
      @foreach($videos['videos'] as $video)
        @include('fasad.elements.video')
      @endforeach
    </div>
  </div>
@endif
