@if($objectFacts['bids'])
  <div class="col-span-12">
    <x-accordion title="Budhistorik" group="descriptions_facts" id="bids">
      <div class="row col-span-12">
        <div class="col-span-12">
          <div class="grid gap-2 grid-cols-[auto_auto_1fr]">
            @foreach($objectFacts['bids'] as $bid)
              <span>{{ $bid['amount'] }}</span>
              <span>{{ $bid['id'] }}</span>
              <span>{{ $bid['date'] }}</span>
            @endforeach
          </div>
          {{--            @foreach($objectFacts['facts'] as $fact)--}}
          {{--              <li class="dot-leaders">--}}
          {{--                <span class="label text-allcaps">{{ $fact['label'] }}</span>--}}
          {{--                <span class="value">{!! $fact['value'] !!}</span>--}}
          {{--              </li>--}}
          {{--            @endforeach--}}
        </div>
      </div>
    </x-accordion>
  </div>
@endif