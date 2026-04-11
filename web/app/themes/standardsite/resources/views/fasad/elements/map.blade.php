@if($map)
  <div id="karta" class="map-wrapper col-span-12 mb-4">
    <h3>Karta</h3>
    <div class="map-container col-span-12 grid grid-cols-12">
      <div id="map-container" class="map-object col-span-12" {!! $map['data'] !!}>
      </div>
      @if(!empty($map['location']))
        <div class="fixed-address">
          @if(!empty($map['location']['address']))
            <span class="address">{{ $map['location']['address'] }}</span>
          @endif
          @if(!empty($map['location']['city']))
            <span class="city">{{ $map['location']['city'] }}</span>
          @endif
        </div>
      @endif
    </div>
  </div>
@endif