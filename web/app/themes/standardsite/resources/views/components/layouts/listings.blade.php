@if($data)
@include('components.componentheader')
<Listings :count="{{ $data['listings_count'] }}" :sold="{{ $data['listings_sold'] }}"></Listings>
@include('components.componentfooter')
@endif
