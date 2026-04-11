@extends('layouts.app')

@section('content')
  @if (! have_posts())
    <div class="wrapper mx-auto text mx-auto max-w-xl">
      <div class="container">
        <div class="row !md:gap-10">
          <div class="col-span-12 wysiwyg">
            <div class="text-center">
              @if(!$isListing)
                <h2>Sidan kan inte hittas</h2>
                <p>Det kan bero på ett stavfel, att sidan inte längre finns eller att den har flyttats.</p>
                <a class="button theme-custom-background theme-custom-color inline" href="{{ home_url() }}">Startsidan</a>
              @else
                <h2>Objektet kan inte hittas</h2>
                <p>Det kan bero på att objektet har sålts.</p>
                @if($listingsPage)
                  <p>Se våra aktuella objekt under</p>
                  <a class="button theme-custom-background theme-custom-color inline" href="{{ $listingsPage['url'] }}">{{ $listingsPage['title'] }}</a>
                @else
                  <a class="button theme-custom-background theme-custom-color inline" href="{{ home_url() }}">Startsidan</a>
                @endif
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
@endsection
