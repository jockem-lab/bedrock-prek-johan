@extends('layouts.app')

@section('content')
  @if($isListingsTerm || $isListingsTaxonomy)
    @include('fasad.content-single-fasad_listing_taxonomy')
  @else
    @while(have_posts())
      @php(the_post())
      @include('partials.content')
    @endwhile
  @endif
@endsection