@if(isset($hero['floats']))
  <div class="herofloats">

  @foreach($hero['floats'] as $float)
    <div class="herofloat theme-custom-background theme-custom-color flex flex-col">
      @foreach($float as $item)
        <span class="font-bold mb-2">{!! $item !!}</span>
      @endforeach
    </div>
  @endforeach
  </div>
@endif