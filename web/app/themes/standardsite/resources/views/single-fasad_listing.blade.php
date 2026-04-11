@extends('layouts.app')

@section('content')
<div style="padding:40px;background:red;color:white;font-size:24px;">
  OBJEKTSIDA FUNGERAR
  <br>
  Post ID: {{ get_the_ID() }}
  <br>  
  Query var: {{ get_query_var('fasad_listing') }}
</div>
@endsection
