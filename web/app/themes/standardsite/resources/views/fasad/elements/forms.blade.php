@if(!$listing->sold)
  @if($listing->anyBookable)
    <x-form slug="showingform"/>
  @endif
  <x-form slug="interestform"/>
@endif
