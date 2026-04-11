@if($shortfacts)
  <div class="{{ $shortfacts['wrapperClass'] }}">
    @include('fasad.elements.formlinks')
    <ul>
      @foreach($shortfacts['facts'] as $key => $fact)
        <li>
          <span class="label {{ $key }}">{{ $fact['label'] }}</span>
          <span class="value">{{ $fact['value'] }}</span>
        </li>
      @endforeach
    </ul>
  </div>
@endif