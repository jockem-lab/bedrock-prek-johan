@if($objectFacts['facts'])
  <div class="col-span-12">
    <x-accordion title="Fakta" group="descriptions_facts" id="fakta">
      <div class="row col-span-12">
        <div class="col-span-12 sm:columns-2">
          <ul>
            @foreach($objectFacts['facts'] as $key => $fact)
              <li class="facts-container {{ $key }} {{ strlen($fact['value']) > 100 ? 'long' : '' }}">
                <span class="label text-allcaps">{{ $fact['label'] }}</span>
                <span class="value">{!! $fact['value'] !!}</span>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </x-accordion>
  </div>
@endif
