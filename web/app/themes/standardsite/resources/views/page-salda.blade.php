@extends('layouts.app')
@section('content')
<div class="kontakt-hero page-slideshow" style="height:380px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
  <div class="page-slides">
    <div class="page-slide active" style="background-image:url('{{ content_url('uploads/oscars-hero4.jpg') }}')"></div>
    <div class="page-slide" style="background-image:url('{{ content_url('uploads/oscars-hero1.jpg') }}')"></div>
  </div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner" style="position:relative;z-index:1;text-align:center;">
    <h1 class="undersida-rubrik">Sålda</h1>
  </div>
</div>
<section class="objekt-sektion">
  <div class="objekt-inner">
    <div class="sektion-header">
      <span class="sektion-eyebrow-label">Oscars Mäkleri</span>
      <h2 class="sektion-rubrik">Sålda objekt</h2>
    </div>
    @include('partials.objekt-grid')
  </div>
</section>
@endsection
