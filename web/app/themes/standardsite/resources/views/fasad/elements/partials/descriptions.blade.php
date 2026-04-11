@foreach($objectFacts['descriptions'] as $description)
  <div class="col-span-12">
    <x-accordion title="{{ $description['title'] }}" group="descriptions_facts" content="{!! $description['content'] !!}" />
  </div>
@endforeach