@if($salestexts)
    <div class="col-span-12 mb-4">
      <h2 class="salestitle">{{ $salestexts['salesTitle'] }}</h2>
{{--      @if($salestexts['salesTextShort'])--}}
{{--        <div class="salestext_short mb-4">--}}
{{--          {{ $salestexts['salesTextShort'] }}--}}
{{--        </div>--}}
{{--      @endif--}}
      @if($salestexts['salesText'])
        <div class="salestext mb-4">
          {!! $salestexts['salesText'] !!}
        </div>
      @endif
    </div>
@endif