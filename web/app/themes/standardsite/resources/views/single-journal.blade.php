@extends('layouts.app')
@section('content')

@php
$video_embed = '';
if ($hero_typ === 'video' && $hero_video) {
    if (strpos($hero_video, 'youtube.com/watch') !== false) {
        parse_str(parse_url($hero_video, PHP_URL_QUERY), $yt_params);
        $vid = $yt_params['v'] ?? '';
        $video_embed = 'https://www.youtube.com/embed/' . $vid . '?autoplay=1&mute=1&loop=1&playlist=' . $vid;
    } elseif (strpos($hero_video, 'youtu.be/') !== false) {
        $vid = ltrim(parse_url($hero_video, PHP_URL_PATH), '/');
        $video_embed = 'https://www.youtube.com/embed/' . $vid . '?autoplay=1&mute=1&loop=1&playlist=' . $vid;
    } elseif (strpos($hero_video, 'vimeo.com/') !== false) {
        $vid = ltrim(parse_url($hero_video, PHP_URL_PATH), '/');
        $video_embed = 'https://player.vimeo.com/video/' . $vid . '?autoplay=1&muted=1&loop=1';
    }
}
@endphp

{{-- Hero --}}
<div class="journal-artikel-hero">
  @if($video_embed)
    <iframe src="{{ $video_embed }}" frameborder="0" allowfullscreen
            allow="autoplay; fullscreen"
            style="position:absolute;inset:0;width:100%;height:100%;"></iframe>
  @elseif($hero_bild)
    <div style="position:absolute;inset:0;background:url('{{ $hero_bild }}') center/cover no-repeat;"></div>
  @endif
  <div style="position:absolute;inset:0;background:rgba(10,18,35,0.5);"></div>
  <div style="position:relative;z-index:1;text-align:center;padding:0 24px;max-width:800px;margin:0 auto;">
    @if($kategori)
      <span class="journal-kategori" style="color:rgba(255,255,255,0.7);">{{ $kategori }}</span>
    @endif
    <h1 style="font-family:var(--font-heading);font-size:clamp(32px,5vw,64px);font-weight:300;color:#fff;letter-spacing:-0.02em;line-height:1.1;margin:16px 0;">{{ $titel }}</h1>
    <div style="font-family:var(--font-body);font-size:12px;color:rgba(255,255,255,0.5);letter-spacing:0.1em;text-transform:uppercase;">
      {{ $datum }}{{ $lasttid ? ' · ' . $lasttid . ' min' : '' }}
    </div>
  </div>
</div>

{{-- Innehåll --}}
<article style="padding:64px 24px 80px;max-width:720px;margin:0 auto;">
  <div class="journal-innehall">
    {!! $innehall !!}
  </div>
  <div style="margin-top:48px;padding-top:32px;border-top:0.5px solid rgba(255,255,255,0.08);">
    <a href="{{ home_url('/journal') }}" style="font-family:var(--font-body);font-size:12px;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);text-decoration:none;">
      ← Tillbaka till Journal
    </a>
  </div>
</article>


@if(!empty($relaterade))
<section class="journal-relaterade">
  <div class="journal-relaterade-inner">
    <h2 class="journal-relaterade-rubrik">Fler artiklar</h2>
    <div class="journal-relaterade-grid">
      @foreach($relaterade as $rel)
        <a href="{{ $rel['url'] }}" class="journal-rel-kort">
          <div class="journal-rel-bild" style="background-image:url('{{ $rel['bild'] }}')"></div>
          <div class="journal-rel-info">
            @if($rel['kategori'])
              <span class="journal-kategori">{{ $rel['kategori'] }}</span>
            @endif
            <h3 class="journal-rel-titel">{{ $rel['titel'] }}</h3>
            @if($rel['lasttid'])
              <span class="journal-meta">{{ $rel['lasttid'] }} min</span>
            @endif
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

@endsection
