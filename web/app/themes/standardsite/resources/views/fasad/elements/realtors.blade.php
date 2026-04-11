@if($realtors)
    <div class="{{ $realtors['wrapperClass'] }}">
    @foreach($realtors['realtors'] as $realtor)
        <div class="{{ count($realtors['realtors']) > 2 ? 'sm:col-span-3 col-span-6' : 'col-span-6'  }}">
          <x-coworker :coworker="$realtor" :hide="['titleExtra']"> </x-coworker>
        </div>
    @endforeach
  </div>
@endif
