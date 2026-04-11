@if($data)
  @include('components.componentheader')
  @if($data['heading'])
    <div class="col-span-12 flex flex-col">
      <h3 class="text-center color-primary">{{ $data['heading'] }}</h3>
    </div>
  @endif
  <div class="row !md:gap-10">
    @foreach($data['coworkers'] as $coworker)
      <div class="{{ $data['class']['coworkerContainer'] }}">
        <x-coworker :coworker="$coworker" :hide="['titleExtra']"> </x-coworker>
      </div>
    @endforeach
  </div>
  @include('components.componentfooter')
@endif