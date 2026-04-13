@extends('layouts.app')

@section('content')

{{-- Hero --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('{{ content_url('uploads') }}/hero1.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads') }}/hero2.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads') }}/hero3.jpg')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <p class="kontakt-hero-eyebrow">PREK MÄKLERI</p>
    <h1>{{ $oo_hero_rubrik }}</h1>
    <p class="kontakt-hero-sub">{{ $oo_hero_underrubrik }}</p>
  </div>
</div>

{{-- Intro --}}
<section class="page-sektion">
  <div class="page-inner">
    <div class="om-oss-intro">
      <h2>{{ $oo_intro_rubrik }}</h2>
      <div class="om-oss-intro-text">{!! $oo_intro_text !!}</div>
    </div>

    @if(!empty($oo_blocks))
    <div class="om-oss-grid">
      @foreach($oo_blocks as $block)
        <div class="om-oss-block">
          @if(!empty($block['ikon']))
            <img src="{{ $block['ikon']['url'] }}" alt="{{ $block['rubrik'] }}" style="width:48px;height:48px;margin-bottom:16px;">
          @endif
          <h3>{{ $block['rubrik'] }}</h3>
          <p>{{ $block['text'] }}</p>
        </div>
      @endforeach
    </div>
    @endif
  </div>
</section>

{{-- Värderingar --}}
@if(!empty($oo_values))
<section class="page-sektion" style="background:var(--bg-warm);">
  <div class="page-inner" style="text-align:center;">
    <p class="sektion-eyebrow">{{ strtoupper($oo_values_rubrik) }}</p>
    <div class="om-oss-grid" style="margin-top:0;">
      @foreach($oo_values as $value)
        <div class="om-oss-block" style="border-top:2px solid var(--accent);padding-top:24px;">
          <h3>{{ $value['rubrik'] }}</h3>
          <p>{{ $value['text'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@endsection
