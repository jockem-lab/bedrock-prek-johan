@if($data)
  @include('components.componentheader')
  <div class="row !md:gap-10">
    @if($data['has']['content'])
      <div class="{{ $data['class']['textContainer'] }}">
        @if($data['heading'])
          <h3 class="text-center color-primary">{{ $data['heading'] }}</h3>
        @endif
        @if($data['content'])
          <div class="wysiwyg">
            {!! $data['content'] !!}
          </div>
        @endif
        @if($data['links'])
          <div class="mt-4">
            @foreach($data['links'] as $link)
                <a class="button theme-custom-background theme-custom-color" href="{{ $link['link']['url'] }}" target="{{ !empty($link['link']['target']) ? $link['link']['target'] : '_self' }}">{{ $link['link']['title'] }}</a>
            @endforeach
          </div>
        @endif
      </div>
    @endif
    @if($data['has']['image'])
      <div class="{{ $data['class']['imageContainer'] }}">
        <img src="{{ $data['image']['src'] }}" alt="{{ $data['image']['alt'] }}" loading="lazy">
      </div>
    @endif
  </div>
  @include('components.componentfooter')
@endif
