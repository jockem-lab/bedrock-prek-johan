@if($data && !empty($data['slug']))
  @include('components.componentheader')
  @if($data['isCorporationForm'] && $corporationID)
    @if($heading = \App\getAttribute('heading', $data))
      <h3 class="text-center color-primary">{{ $heading }}</h3>
    @endif
    @if($content = \App\getAttribute('content', $data))
      <div class="wysiwyg">
        {!! $content !!}
      </div>
    @endif
    <x-form slug="{{ $data['slug'] }}" />
  @elseif(is_user_logged_in())
    <x-alert type="warning">
      <div class="text-center">
        Här ska det visas ett formulär, men bolagsID saknas i inställningar.
        <p class="italic">(Detta meddelande visas bara för inloggade)</p>
      </div>
    </x-alert>
  @endif
  @include('components.componentfooter')
@endif
