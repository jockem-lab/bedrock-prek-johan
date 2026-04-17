@extends('layouts.app')

@section('content')
<section style="padding:120px 24px;text-align:center;background:var(--bg-light);">
  <div style="max-width:600px;margin:0 auto;">
    <span class="sektion-eyebrow-label">Oscars Mäkleri</span>
    @if(!$isListing)
      <h1 class="sektion-rubrik" style="margin-bottom:24px;">Sidan hittas ej</h1>
      <p style="font-family:var(--font-body);font-size:15px;color:var(--text-mid);line-height:1.8;margin-bottom:40px;">Det kan bero på ett stavfel, att sidan inte längre finns eller att den har flyttats.</p>
      <a href="{{ home_url() }}" class="btn-primary">Gå till startsidan</a>
    @else
      <h1 class="sektion-rubrik" style="margin-bottom:24px;">Objektet hittas ej</h1>
      <p style="font-family:var(--font-body);font-size:15px;color:var(--text-mid);line-height:1.8;margin-bottom:40px;">Det kan bero på att objektet har sålts eller tagits bort.</p>
      @if($listingsPage)
        <a href="{{ $listingsPage['url'] }}" class="btn-primary">Se aktuella objekt</a>
      @else
        <a href="{{ home_url() }}" class="btn-primary">Gå till startsidan</a>
      @endif
    @endif
  </div>
</section>
@endsection
