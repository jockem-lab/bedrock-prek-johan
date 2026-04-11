@if($objectFacts['facts'] || $objectFacts['descriptions'] || $objectFacts['documents'] || $objectFacts['bids'])
  <div class="col-span-12 row !gap-0">
    @include('fasad.elements.partials.descriptions')
    @include('fasad.elements.partials.documents')
    @include('fasad.elements.partials.links')
    @include('fasad.elements.partials.facts')
    @include('fasad.elements.partials.bids')
  </div>
@endif